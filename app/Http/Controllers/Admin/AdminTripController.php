<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Package;
use App\Models\Transaction;
use App\Models\Trip;
use App\Models\Vehicle;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Manajemen Jadwal & Detail Trip (khusus super_admin).
 */
class AdminTripController extends Controller
{
    public function index(Request $request): View
    {
        $packageId = $request->integer('package_id') ?: null;

        $selectedTrip = $request->filled('trip_id')
            ? Trip::with(['package', 'vehicle'])->find($request->query('trip_id'))
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

        // Log armada: unit yang bertugas hari ini & unit cadangan (belum terjadwal).
        $today = CarbonImmutable::today();
        $onDuty = Trip::with(['vehicle', 'package'])
            ->whereNotNull('vehicle_id')
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->whereDate('departure_date', '<=', $today->toDateString())
            ->where(function ($q) use ($today) {
                $q->whereDate('return_date', '>=', $today->toDateString())
                    ->orWhere(function ($q2) use ($today) {
                        $q2->whereNull('return_date')->whereDate('departure_date', '=', $today->toDateString());
                    });
            })
            ->get();
        $standby = $vehicles->whereNotIn('id', $onDuty->pluck('vehicle_id'))->values();

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
            'packageChips' => $packageChips,
            'packageId' => $packageId,
            'onDuty' => $onDuty,
            'standby' => $standby,
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
            'driver_name' => ['nullable', 'string', 'max:120'],
            'meeting_point' => ['nullable', 'string', 'max:200'],
            'departure_time' => ['nullable', 'date_format:H:i'],
        ]);

        $this->assertVehicleRules($validated, null);

        $trip = Trip::create([
            ...$validated,
            'return_date' => $validated['return_date'] ?? null,
            'booked_seats' => 0,
            'status' => 'available',
        ]);

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
            'driver_name' => ['nullable', 'string', 'max:120'],
            'meeting_point' => ['nullable', 'string', 'max:200'],
            'departure_time' => ['nullable', 'date_format:H:i'],
        ]);

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

        $trip->fill([
            ...$validated,
            'return_date' => $validated['return_date'] ?? null,
        ]);
        $trip->booked_seats = $activeSeats;
        $trip->status = $trip->resolveStatus($requested);
        $trip->save();

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
        $newRetDate = $sourceTrip->return_date ? Carbon::parse($sourceTrip->return_date)->addDays($daysAhead) : null;

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
        return Trip::with(['package', 'vehicle'])
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
     * Aturan armada: kapasitas kursi & bentrok jadwal armada/driver.
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

            if ($this->hasOverlap('vehicle_id', $data['vehicle_id'], $departure, $return, $current)) {
                $errors['vehicle_id'] = 'Armada ini sudah dijadwalkan pada trip lain di rentang tanggal tersebut.';
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
