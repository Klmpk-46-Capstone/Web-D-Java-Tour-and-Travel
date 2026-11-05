<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Package;
use App\Models\Transaction;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\VehicleUnit;
use App\Services\FleetAvailability;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Manajemen Jadwal & Detail Trip (khusus super_admin).
 */
class AdminTripController extends Controller
{
    public function __construct(private readonly FleetAvailability $fleet)
    {
    }

    public function index(Request $request): View
    {
        $packageId = $request->integer('package_id') ?: null;

        $selectedTrip = $request->filled('trip_id')
            ? Trip::with(['package', 'vehicle', 'vehicleUnit'])->find($request->query('trip_id'))
            : null;

        $month = $this->resolveMonth($request->query('month'), $selectedTrip);
        $monthStart = $month->startOfMonth();
        $monthEnd = $month->endOfMonth();

        $monthTrips = $this->tripsOverlapping($monthStart, $monthEnd, $packageId);

        // Trip terpilih: dari query, atau trip berjalan/terdekat di bulan ini.
        if (! $selectedTrip || ! $monthTrips->contains('id', $selectedTrip->id)) {
            $today = CarbonImmutable::today();
            $selectedTrip = $monthTrips->first(
                fn (Trip $t) => $t->status !== 'cancelled'
                    && $t->departure_date->lte($today)
                    && ($t->return_date ?? $t->departure_date)->gte($today)
            ) ?? $monthTrips->first(
                fn (Trip $t) => $t->departure_date->gte($today)
            ) ?? $monthTrips->first();
        }

        $stats = $this->buildStats($monthTrips, $monthStart);
        $calendar = $this->buildCalendar($month, $monthTrips);

        $packages = Package::where('is_active', true)->orderBy('name')->get();
        $vehicles = Vehicle::where('is_active', true)->orderBy('name')->get();
        $vehicleUnits = VehicleUnit::query()
            ->with('vehicle')
            ->where('status', VehicleUnit::STATUS_ACTIVE)
            ->orderBy('vehicle_id')
            ->orderBy('plate_number')
            ->get();

        // Daftar paket yang punya trip di bulan ini (untuk chip filter).
        $packageChips = $this->tripsOverlapping($monthStart, $monthEnd, null)
            ->groupBy('package_id')
            ->map(fn ($group) => [
                'id' => $group->first()->package_id,
                'name' => $group->first()->package->name ?? 'Trip Khusus',
                'count' => $group->count(),
            ])
            ->values();

        $selectedPrice = (int) ($selectedTrip?->package?->price ?? 0);
        $selectedTotal = $selectedPrice * (int) ($selectedTrip?->booked_seats ?? 0);

        // Log armada berbasis unit fisik (bukan tipe), agar tipe dengan banyak
        // plat tidak dianggap satu unit yang sama.
        $today = CarbonImmutable::today();
        $loggedUnits = VehicleUnit::query()
            ->with('vehicle')
            ->where('status', VehicleUnit::STATUS_ACTIVE)
            ->orderBy('vehicle_id')
            ->orderBy('id')
            ->get();
        $usageByUnit = $this->fleet->usageForUnits($loggedUnits->pluck('id'), $today, $today);
        $onDuty = $loggedUnits
            ->filter(fn (VehicleUnit $unit) => isset($usageByUnit[$unit->id]))
            ->values();
        $standby = $loggedUnits
            ->reject(fn (VehicleUnit $unit) => isset($usageByUnit[$unit->id]))
            ->values();

        return view('admin.trips.index', [
            'month' => $month,
            'prevMonth' => $month->subMonth()->format('Y-m'),
            'nextMonth' => $month->addMonth()->format('Y-m'),
            'currentMonth' => CarbonImmutable::today()->format('Y-m'),
            'monthTrips' => $monthTrips,
            'selectedTrip' => $selectedTrip,
            'selectedPrice' => $selectedPrice,
            'selectedTotal' => $selectedTotal,
            'stats' => $stats,
            'calendar' => $calendar,
            'packages' => $packages,
            'vehicles' => $vehicles,
            'vehicleUnits' => $vehicleUnits,
            'packageChips' => $packageChips,
            'packageId' => $packageId,
            'onDuty' => $onDuty,
            'standby' => $standby,
            'usageByUnit' => $usageByUnit,
            'statusLabels' => Trip::STATUS_LABELS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'package_id' => ['required', 'exists:packages,id'],
            'batch_name' => ['required', 'string', 'max:150'],
            'departure_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date' => ['nullable', 'date', 'after_or_equal:departure_date'],
            'quota' => ['required', 'integer', 'min:1', 'max:500'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'vehicle_unit_id' => ['nullable', 'integer', 'exists:vehicle_units,id'],
            'driver_name' => ['nullable', 'string', 'max:120'],
            'meeting_point' => ['nullable', 'string', 'max:200'],
            'departure_time' => ['nullable', 'date_format:H:i'],
        ]);

        $this->validateAndBindTripDuration($validated);
        $this->assertVehicleRules($validated, null);

        $trip = DB::transaction(function () use ($validated) {
            $unit = $this->resolveUnit($validated, null);

            return Trip::create([
                ...$validated,
                'vehicle_unit_id' => $unit?->id,
                'booked_seats' => 0,
                'status' => 'available',
            ]);
        });

        return redirect()
            ->route('admin.trips.index', [
                'month' => $trip->departure_date->format('Y-m'),
                'trip_id' => $trip->id,
            ])
            ->with('success', "Jadwal trip '{$trip->batch_name}' berhasil dibuat dan langsung tampil di booking customer.");
    }

    public function update(Request $request, Trip $trip): RedirectResponse
    {
        $validated = $request->validate([
            'package_id' => ['required', 'exists:packages,id'],
            'batch_name' => ['required', 'string', 'max:150'],
            'departure_date' => ['required', 'date'],
            'return_date' => ['nullable', 'date', 'after_or_equal:departure_date'],
            'quota' => ['required', 'integer', 'min:1', 'max:500'],
            'status' => ['required', 'in:' . implode(',', array_keys(Trip::STATUS_LABELS))],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'vehicle_unit_id' => ['nullable', 'integer', 'exists:vehicle_units,id'],
            'driver_name' => ['nullable', 'string', 'max:120'],
            'meeting_point' => ['nullable', 'string', 'max:200'],
            'departure_time' => ['nullable', 'date_format:H:i'],
        ]);

        $this->validateAndBindTripDuration($validated);

        $activeSeats = max($trip->activeSeatCount(), (int) $trip->booked_seats);

        if ((int) $validated['quota'] < $activeSeats) {
            throw ValidationException::withMessages([
                'quota' => "Kuota tidak boleh lebih kecil dari kursi yang sudah terpesan ({$activeSeats} pax).",
            ]);
        }

        $this->assertVehicleRules($validated, $trip);

        $requested = $validated['status'];

        if ($requested === 'cancelled' && $activeSeats > 0 && $trip->status !== 'cancelled') {
            throw ValidationException::withMessages([
                'status' => "Trip masih memiliki {$activeSeats} pax aktif. Proses refund atau pindahkan jadwal penumpang terlebih dahulu sebelum membatalkan trip.",
            ]);
        }

        if ($requested === 'completed') {
            $end = Carbon::parse($validated['return_date'] ?? $validated['departure_date']);
            if ($end->isFuture() && ! $end->isToday()) {
                throw ValidationException::withMessages([
                    'status' => 'Trip belum bisa ditandai selesai karena tanggal kepulangan belum terlewati.',
                ]);
            }
        }

        DB::transaction(function () use ($trip, $validated, $requested, $activeSeats) {
            $unit = $this->resolveUnit($validated, $trip);

            $trip->fill([
                ...$validated,
                'vehicle_unit_id' => $unit?->id,
            ]);
            $trip->booked_seats = $activeSeats;
            $trip->status = $trip->resolveStatus($requested);
            $trip->save();
        });

        return redirect()
            ->route('admin.trips.index', [
                'month' => $trip->departure_date->format('Y-m'),
                'trip_id' => $trip->id,
            ])
            ->with('success', "Perubahan trip '{$trip->batch_name}' berhasil disimpan.");
    }

    public function destroy(Trip $trip): RedirectResponse
    {
        if ($trip->bookings()->exists()) {
            return redirect()
                ->route('admin.trips.index', [
                    'month' => $trip->departure_date->format('Y-m'),
                    'trip_id' => $trip->id,
                ])
                ->with('error', "Trip '{$trip->batch_name}' sudah memiliki riwayat booking dan tidak dapat dihapus. Ubah status menjadi 'Dibatalkan' untuk menutup jadwal.");
        }

        $name = $trip->batch_name;
        $month = $trip->departure_date->format('Y-m');
        $trip->delete();

        return redirect()
            ->route('admin.trips.index', ['month' => $month])
            ->with('success', "Batch trip '{$name}' telah dihapus.");
    }

    /**
     * Sinkronisasi otomatis: hitung ulang kursi terpesan dari booking aktif
     * dan perbarui status okupansi trip.
     */
    public function sync(Request $request): RedirectResponse
    {
        $corrected = 0;

        Trip::with('bookings')->get()->each(function (Trip $trip) use (&$corrected) {
            $seats = $trip->bookings
                ->whereIn('booking_status', Booking::SEAT_HOLDING_STATUSES)
                ->sum('pax');

            $trip->booked_seats = (int) $seats;
            $newStatus = $trip->resolveStatus();

            if ($trip->isDirty(['booked_seats']) || $newStatus !== $trip->status) {
                $corrected++;
            }

            $trip->status = $newStatus;
            $trip->save();
        });

        return redirect()
            ->route('admin.trips.index', array_filter([
                'month' => $request->input('month'),
                'trip_id' => $request->input('trip_id'),
            ]))
            ->with('success', $corrected > 0
                ? "Sinkronisasi selesai: {$corrected} trip dikoreksi sesuai data booking terbaru."
                : 'Sinkronisasi selesai: seluruh kuota trip sudah sesuai dengan data booking.');
    }

    public function quickAddBatch(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'trip_id' => ['required', 'exists:trips,id'],
            'days_ahead' => ['nullable', 'integer', 'min:1'],
        ]);

        $sourceTrip = Trip::with('package')->findOrFail($validated['trip_id']);
        $daysAhead = $validated['days_ahead'] ?? 7;

        $newDepDate = Carbon::parse($sourceTrip->departure_date)->addDays($daysAhead);
        $durationDays = $sourceTrip->package?->durationInDays() ?? 1;
        $newRetDate = $newDepDate->copy()->addDays($durationDays - 1);

        $newTrip = Trip::create([
            'package_id' => $sourceTrip->package_id,
            'batch_name' => ($sourceTrip->package->name ?? 'Trip') . ' (Batch Tambahan ' . $newDepDate->translatedFormat('d M Y') . ')',
            'departure_date' => $newDepDate,
            'return_date' => $newRetDate,
            'quota' => $sourceTrip->quota,
            'booked_seats' => 0,
            'status' => 'available',
            'meeting_point' => $sourceTrip->meeting_point,
            'departure_time' => $sourceTrip->departure_time,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Batch trip baru berhasil dibuka untuk tanggal ' . $newDepDate->translatedFormat('d F Y') . '!',
                'data' => $newTrip,
            ]);
        }

        return redirect()
            ->route('admin.trips.index', [
                'month' => $newDepDate->format('Y-m'),
                'trip_id' => $newTrip->id,
            ])
            ->with('success', "Batch trip tambahan berhasil dibuka untuk tanggal {$newDepDate->translatedFormat('d F Y')}. Kuota segar tersedia untuk customer!");
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Memvalidasi dan mengikat rentang tanggal trip dengan durasi perjalanan paket wisata.
     */
    private function validateAndBindTripDuration(array &$validated): void
    {
        $package = Package::findOrFail($validated['package_id']);
        $expectedDays = $package->durationInDays();

        $depDate = Carbon::parse($validated['departure_date'])->startOfDay();
        $expectedReturnDate = $depDate->copy()->addDays($expectedDays - 1)->format('Y-m-d');

        if (empty($validated['return_date'])) {
            $validated['return_date'] = $expectedReturnDate;
        } else {
            $retDate = Carbon::parse($validated['return_date'])->startOfDay();
            $actualDays = (int) round($depDate->diffInDays($retDate)) + 1;

            if ($actualDays !== $expectedDays) {
                throw ValidationException::withMessages([
                    'return_date' => "Rentang tanggal jadwal ({$actualDays} hari) harus sesuai dengan durasi perjalanan paket wisata '{$package->name}' ({$expectedDays} hari: {$package->duration}). Tanggal kepulangan yang sesuai adalah " . Carbon::parse($expectedReturnDate)->translatedFormat('d F Y') . " ({$expectedReturnDate}).",
                ]);
            }
        }
    }

    private function resolveMonth(?string $input, ?Trip $selectedTrip): CarbonImmutable
    {
        if ($input && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $input)) {
            return CarbonImmutable::createFromFormat('Y-m-d', $input . '-01')->startOfDay();
        }

        if ($selectedTrip) {
            return CarbonImmutable::parse($selectedTrip->departure_date)->startOfMonth();
        }

        return CarbonImmutable::today()->startOfMonth();
    }

    private function tripsOverlapping(CarbonImmutable $start, CarbonImmutable $end, ?int $packageId)
    {
        return Trip::with(['package', 'vehicle', 'vehicleUnit'])
            ->whereDate('departure_date', '<=', $end->toDateString())
            ->where(function ($q) use ($start) {
                $q->whereDate('return_date', '>=', $start->toDateString())
                    ->orWhere(function ($q2) use ($start) {
                        $q2->whereNull('return_date')
                            ->whereDate('departure_date', '>=', $start->toDateString());
                    });
            })
            ->when($packageId, fn ($q) => $q->where('package_id', $packageId))
            ->orderBy('departure_date')
            ->orderBy('id')
            ->get();
    }

    private function buildStats($monthTrips, CarbonImmutable $monthStart): array
    {
        $active = $monthTrips->where('status', '!=', 'cancelled');
        $today = CarbonImmutable::today();

        $prevStart = $monthStart->subMonth();
        $prevCount = Trip::where('status', '!=', 'cancelled')
            ->whereDate('departure_date', '<=', $prevStart->endOfMonth()->toDateString())
            ->where(function ($q) use ($prevStart) {
                $q->whereDate('return_date', '>=', $prevStart->toDateString())
                    ->orWhere(function ($q2) use ($prevStart) {
                        $q2->whereNull('return_date')
                            ->whereDate('departure_date', '>=', $prevStart->toDateString());
                    });
            })
            ->count();

        $running = $active->filter(
            fn (Trip $t) => $t->status !== 'completed'
                && $t->departure_date->lte($today)
                && ($t->return_date ?? $t->departure_date)->gte($today)
        );

        $quota = (int) $active->sum('quota');
        $booked = (int) $active->sum('booked_seats');
        $occupancy = $quota > 0 ? (int) round(($booked / $quota) * 100) : 0;

        $revenue = (int) Transaction::where('status', 'success')
            ->whereHas('booking', fn ($q) => $q->whereIn('trip_id', $active->pluck('id')))
            ->sum('amount');

        $growth = $prevCount > 0
            ? (int) round((($active->count() - $prevCount) / $prevCount) * 100)
            : null;

        return [
            'total' => $active->count(),
            'growth' => $growth,
            'running' => $running->count(),
            'running_names' => $running->pluck('package.name')->filter()->unique()->take(2)->implode(', '),
            'occupancy' => $occupancy,
            'booked' => $booked,
            'quota' => $quota,
            'revenue' => $revenue,
        ];
    }

    /**
     * Susun grid kalender (Senin–Minggu) lengkap dengan trip pada tiap hari.
     *
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function buildCalendar(CarbonImmutable $month, $monthTrips): array
    {
        $gridStart = $month->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $month->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $today = CarbonImmutable::today();

        $weeks = [];
        $cursor = $gridStart;

        while ($cursor->lte($gridEnd)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $dayTrips = $monthTrips->filter(
                    fn (Trip $t) => $cursor->betweenIncluded(
                        $t->departure_date,
                        $t->return_date ?? $t->departure_date
                    )
                )->values();

                $week[] = [
                    'date' => $cursor,
                    'in_month' => $cursor->month === $month->month,
                    'is_today' => $cursor->isSameDay($today),
                    'is_sunday' => $cursor->isSunday(),
                    'trips' => $dayTrips,
                ];
                $cursor = $cursor->addDay();
            }
            $weeks[] = $week;
        }

        return $weeks;
    }

    /**
     * Aturan armada: kapasitas kursi, ketersediaan unit, & bentrok driver.
     */
    private function assertVehicleRules(array $data, ?Trip $current): void
    {
        $errors = [];
        $departure = Carbon::parse($data['departure_date'])->toDateString();
        $return = Carbon::parse($data['return_date'] ?? $data['departure_date'])->toDateString();

        if (! empty($data['vehicle_id'])) {
            $vehicle = Vehicle::find($data['vehicle_id']);

            if ($vehicle && (int) $data['quota'] > (int) $vehicle->seats) {
                $errors['quota'] = "Kuota ({$data['quota']} pax) melebihi kapasitas {$vehicle->name} ({$vehicle->seats} kursi).";
            }

            if ($vehicle) {
                $requestedUnitId = $data['vehicle_unit_id'] ?? null;
                $isCancelling = ($data['status'] ?? null) === 'cancelled';
                $isLegacySameVehicle = $current
                    && ! $current->vehicle_unit_id
                    && (int) $current->vehicle_id === (int) $vehicle->id
                    && empty($requestedUnitId);

                if ($isCancelling || $isLegacySameVehicle) {
                    // Trip lama tanpa unit tetap boleh diedit/dibatalkan tanpa
                    // memaksa admin menyiapkan unit baru.
                } elseif (! empty($requestedUnitId)) {
                    $unit = VehicleUnit::find($requestedUnitId);

                    if (! $unit || (int) $unit->vehicle_id !== (int) $vehicle->id) {
                        $errors['vehicle_unit_id'] = 'Plat yang dipilih tidak sesuai dengan tipe armada.';
                    } elseif (! $this->fleet->isUnitAvailable($unit, $departure, $return, $current)) {
                        $errors['vehicle_unit_id'] = "Plat {$unit->plate_number} sudah terpakai pada rentang tanggal tersebut.";
                    }
                } else {
                    $available = $this->fleet->countAvailable($vehicle, $departure, $return, $current);

                    if ($available === 0) {
                        $total = $this->fleet->countTotal($vehicle);
                        $errors['vehicle_id'] = $total === 0
                            ? 'Belum ada unit terdaftar untuk tipe ini.'
                            : "Semua unit {$vehicle->name} sudah terpakai pada rentang tanggal tersebut.";
                    }
                }
            }
        }

        if (! empty($data['driver_name'])
            && $this->hasOverlap('driver_name', $data['driver_name'], $departure, $return, $current)) {
            $errors['driver_name'] = 'Driver ini sudah bertugas pada trip lain di rentang tanggal tersebut.';
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Tentukan unit yang dipakai trip. Unit lama dipertahankan bila masih bebas.
     */
    private function resolveUnit(array $data, ?Trip $current): ?VehicleUnit
    {
        if (empty($data['vehicle_id'])) {
            return null;
        }

        $vehicle = Vehicle::find($data['vehicle_id']);

        if (! $vehicle) {
            throw ValidationException::withMessages([
                'vehicle_id' => 'Tipe armada tidak ditemukan.',
            ]);
        }

        $from = Carbon::parse($data['departure_date'])->toDateString();
        $to = Carbon::parse($data['return_date'] ?? $data['departure_date'])->toDateString();
        $requestedUnitId = $data['vehicle_unit_id'] ?? null;
        $isLegacySameVehicle = $current
            && ! $current->vehicle_unit_id
            && (int) $current->vehicle_id === (int) $vehicle->id
            && empty($requestedUnitId);

        if (! empty($requestedUnitId)) {
            $unit = VehicleUnit::find($requestedUnitId);

            if (! $unit || (int) $unit->vehicle_id !== (int) $vehicle->id) {
                throw ValidationException::withMessages([
                    'vehicle_unit_id' => 'Plat yang dipilih tidak sesuai dengan tipe armada.',
                ]);
            }

            if (! $this->fleet->isUnitAvailable($unit, $from, $to, $current)) {
                throw ValidationException::withMessages([
                    'vehicle_unit_id' => "Plat {$unit->plate_number} sudah terpakai pada rentang tanggal tersebut.",
                ]);
            }

            return $unit;
        }

        if (
            $current
            && $current->vehicle_unit_id
            && (int) $current->vehicle_id === (int) $vehicle->id
        ) {
            $currentUnit = $current->vehicleUnit;

            if (
                $currentUnit
                && $currentUnit->status === VehicleUnit::STATUS_ACTIVE
                && $this->fleet->isUnitAvailable($currentUnit, $from, $to, $current)
            ) {
                return $currentUnit;
            }
        }

        $unit = $this->fleet->allocate($vehicle, $from, $to, $current);

        if (! $unit) {
            if ($isLegacySameVehicle) {
                return null;
            }

            $total = $this->fleet->countTotal($vehicle);

            throw ValidationException::withMessages([
                'vehicle_id' => $total === 0
                    ? 'Belum ada unit terdaftar untuk tipe ini.'
                    : "Semua unit {$vehicle->name} sudah terpakai pada rentang tanggal tersebut.",
            ]);
        }

        return $unit;
    }

    private function hasOverlap(string $column, mixed $value, string $departure, string $return, ?Trip $current): bool
    {
        return Trip::where($column, $value)
            ->where('status', '!=', 'cancelled')
            ->when($current, fn ($q) => $q->where('id', '!=', $current->id))
            ->whereDate('departure_date', '<=', $return)
            ->where(function ($q) use ($departure) {
                $q->whereDate('return_date', '>=', $departure)
                    ->orWhere(function ($q2) use ($departure) {
                        $q2->whereNull('return_date')->whereDate('departure_date', '>=', $departure);
                    });
            })
            ->exists();
    }
}
