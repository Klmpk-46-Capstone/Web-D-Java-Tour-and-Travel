<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTripController extends Controller
{
    public function index(Request $request): View
    {
        $packageId = $request->query('package_id');
        $status = $request->query('status');

        $trips = Trip::with('package')
            ->when($packageId, fn($q) => $q->where('package_id', $packageId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('departure_date', 'asc')
            ->paginate(12);

        $packages = Package::where('is_active', true)->get();

        return view('admin.trips.index', compact('trips', 'packages', 'packageId', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'package_id' => ['required', 'exists:packages,id'],
            'batch_name' => ['required', 'string', 'max:150'],
            'departure_date' => ['required', 'date'],
            'return_date' => ['nullable', 'date', 'after_or_equal:departure_date'],
            'quota' => ['required', 'integer', 'min:1'],
            'booked_seats' => ['nullable', 'integer', 'min:0'],
        ]);

        $booked = $validated['booked_seats'] ?? 0;
        $quota = $validated['quota'];
        
        $status = 'available';
        if ($booked >= $quota) {
            $status = 'full';
        } elseif (($booked / $quota) >= 0.8) {
            $status = 'almost_full';
        }

        Trip::create([
            'package_id' => $validated['package_id'],
            'batch_name' => $validated['batch_name'],
            'departure_date' => $validated['departure_date'],
            'return_date' => $validated['return_date'] ?? null,
            'quota' => $quota,
            'booked_seats' => $booked,
            'status' => $status,
        ]);

        return redirect()->back()
            ->with('success', 'Batch jadwal trip baru berhasil dibuat dan langsung aktif pada form booking customer.');
    }

    public function update(Request $request, Trip $trip): RedirectResponse
    {
        $validated = $request->validate([
            'batch_name' => ['required', 'string', 'max:150'],
            'departure_date' => ['required', 'date'],
            'return_date' => ['nullable', 'date'],
            'quota' => ['required', 'integer', 'min:1'],
            'booked_seats' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:available,almost_full,full,completed,cancelled'],
        ]);

        $trip->update($validated);

        return redirect()->back()
            ->with('success', "Data batch '{$trip->batch_name}' berhasil diperbarui.");
    }

    public function destroy(Trip $trip): RedirectResponse
    {
        $name = $trip->batch_name;
        $trip->delete();

        return redirect()->back()
            ->with('success', "Batch trip '{$name}' telah dihapus.");
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
        $newRetDate = $sourceTrip->return_date ? Carbon::parse($sourceTrip->return_date)->addDays($daysAhead) : null;

        $newTrip = Trip::create([
            'package_id' => $sourceTrip->package_id,
            'batch_name' => $sourceTrip->package->name . ' (Batch Tambahan ' . $newDepDate->translatedFormat('d M Y') . ')',
            'departure_date' => $newDepDate,
            'return_date' => $newRetDate,
            'quota' => $sourceTrip->quota,
            'booked_seats' => 0,
            'status' => 'available',
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Batch trip baru berhasil dibuka untuk tanggal " . $newDepDate->translatedFormat('d F Y') . "!",
                'data' => $newTrip,
            ]);
        }

        return redirect()->back()->with('success', "Batch trip tambahan berhasil dibuka untuk tanggal {$newDepDate->translatedFormat('d F Y')}. Kuota segar tersedia untuk customer!");
    }
}
