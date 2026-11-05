<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleUnit;
use App\Services\FleetAvailability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminVehicleUnitController extends Controller
{
    public function __construct(private readonly FleetAvailability $fleet)
    {
    }

    public function index(Request $request): View
    {
        $vehicleId = $request->integer('vehicle_id') ?: null;
        $status = $request->query('status');
        $allowedStatuses = [...array_keys(VehicleUnit::STATUS_LABELS), 'available', 'in_use'];
        $status = in_array($status, $allowedStatuses, true) ? $status : null;

        $today = Carbon::today();

        $vehicles = Vehicle::query()
            ->orderBy('name')
            ->get();

        $summaries = $vehicles->map(function (Vehicle $vehicle) use ($today) {
            $total = $this->fleet->countTotal($vehicle);
            $available = $this->fleet->countAvailable($vehicle, $today, $today);

            return [
                'vehicle' => $vehicle,
                'total' => $total,
                'available' => $available,
                'in_use' => max(0, $total - $available),
                'maintenance' => $vehicle->units()
                    ->where('status', VehicleUnit::STATUS_MAINTENANCE)
                    ->count(),
            ];
        });

        $usedUnitIds = [];

        if (in_array($status, ['available', 'in_use'], true)) {
            $usedUnitIds = array_keys($this->fleet->usageForUnits(
                VehicleUnit::query()
                    ->where('status', VehicleUnit::STATUS_ACTIVE)
                    ->when($vehicleId, fn ($unitQuery) => $unitQuery->where('vehicle_id', $vehicleId))
                    ->pluck('id'),
                $today,
                $today
            ));
        }

        $units = VehicleUnit::query()
            ->with('vehicle')
            ->when($vehicleId, fn ($query) => $query->where('vehicle_id', $vehicleId))
            ->when($status === 'in_use', fn ($query) => $query->whereIn('id', $usedUnitIds ?: [0]))
            ->when($status === 'available', function ($query) use ($usedUnitIds) {
                $query->where('status', VehicleUnit::STATUS_ACTIVE);

                if ($usedUnitIds !== []) {
                    $query->whereNotIn('id', $usedUnitIds);
                }
            })
            ->when(
                in_array($status, array_keys(VehicleUnit::STATUS_LABELS), true),
                fn ($query) => $query->where('status', $status)
            )
            ->orderBy('vehicle_id')
            ->orderBy('plate_number')
            ->paginate(20)
            ->withQueryString();

        $usageByUnit = $this->fleet->usageForUnits(
            $units->getCollection()->pluck('id'),
            $today,
            $today
        );

        return view('admin.units.index', compact(
            'vehicles',
            'summaries',
            'units',
            'usageByUnit',
            'vehicleId',
            'status',
            'today'
        ));
    }

    public function create(): View
    {
        return view('admin.units.create', [
            'vehicles' => Vehicle::orderBy('name')->get(),
            'statuses' => VehicleUnit::STATUS_LABELS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        $unit = VehicleUnit::create($validated);

        return redirect()
            ->route('admin.units.index')
            ->with('success', "Unit armada dengan plat '{$unit->plate_number}' berhasil ditambahkan.");
    }

    public function edit(VehicleUnit $unit): View
    {
        return view('admin.units.edit', [
            'unit' => $unit,
            'vehicles' => Vehicle::orderBy('name')->get(),
            'statuses' => VehicleUnit::STATUS_LABELS,
        ]);
    }

    public function update(Request $request, VehicleUnit $unit): RedirectResponse
    {
        $validated = $this->validatedData($request, $unit);

        if (
            $validated['status'] === VehicleUnit::STATUS_MAINTENANCE
            && $unit->status !== VehicleUnit::STATUS_MAINTENANCE
            && $this->fleet->hasFutureAllocations($unit)
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    "Unit '{$unit->plate_number}' masih memiliki alokasi trip/booking pada atau setelah hari ini. Selesaikan atau pindahkan alokasi tersebut sebelum mengubah status menjadi Servis."
                );
        }

        $unit->update($validated);

        return redirect()
            ->route('admin.units.index')
            ->with('success', "Unit armada '{$unit->plate_number}' berhasil diperbarui.");
    }

    public function destroy(VehicleUnit $unit): RedirectResponse
    {
        if ($unit->trips()->exists() || $unit->bookings()->exists()) {
            return redirect()
                ->route('admin.units.index')
                ->with(
                    'error',
                    "Unit '{$unit->plate_number}' pernah dipakai pada trip atau booking sehingga tidak dapat dihapus. Ubah status unit menjadi Servis sebagai gantinya."
                );
        }

        $plate = $unit->plate_number;
        $unit->delete();

        return redirect()
            ->route('admin.units.index')
            ->with('success', "Unit armada '{$plate}' telah dihapus dari sistem.");
    }

    public function toggleStatus(Request $request, VehicleUnit $unit): JsonResponse|RedirectResponse
    {
        $newStatus = $unit->status === VehicleUnit::STATUS_ACTIVE
            ? VehicleUnit::STATUS_MAINTENANCE
            : VehicleUnit::STATUS_ACTIVE;

        if (
            $newStatus === VehicleUnit::STATUS_MAINTENANCE
            && $this->fleet->hasFutureAllocations($unit)
        ) {
            $message = "Unit '{$unit->plate_number}' masih memiliki alokasi trip/booking pada atau setelah hari ini. Selesaikan atau pindahkan alokasi tersebut terlebih dahulu.";

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $unit->update(['status' => $newStatus]);

        $message = $newStatus === VehicleUnit::STATUS_ACTIVE
            ? "Unit '{$unit->plate_number}' telah diaktifkan."
            : "Unit '{$unit->plate_number}' ditandai sedang servis.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $unit->status,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?VehicleUnit $unit = null): array
    {
        $request->merge([
            'plate_number' => $this->normalizePlate($request->input('plate_number')),
        ]);

        return $request->validate([
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'plate_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z]{1,2}\s?\d{1,4}\s?[A-Z]{0,3}$/',
                Rule::unique('vehicle_units', 'plate_number')->ignore($unit?->id),
            ],
            'status' => ['required', 'string', Rule::in(array_keys(VehicleUnit::STATUS_LABELS))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'plate_number.regex' => 'Format plat tidak valid. Contoh yang benar: B 1234 CKR.',
            'plate_number.unique' => 'Plat ini sudah terdaftar pada unit lain.',
        ]);
    }

    private function normalizePlate(mixed $value): string
    {
        return strtoupper((string) preg_replace('/\s+/', ' ', trim((string) $value)));
    }
}
