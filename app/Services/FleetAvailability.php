<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\VehicleUnit;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Satu sumber kebenaran untuk ketersediaan unit armada.
 *
 * Unit dianggap terpakai bila ada trip aktif atau booking sewa aktif yang
 * rentang tanggalnya beririsan (inklusif) dengan rentang yang dicek.
 */
class FleetAvailability
{
    private const BOOKING_HOLDING_STATUSES = [
        'pending',
        'confirmed',
        'completed',
    ];

    public function availableUnits(
        Vehicle $vehicle,
        mixed $from,
        mixed $to,
        ?Trip $ignoreTrip = null,
        ?Booking $ignoreBooking = null
    ): EloquentCollection {
        [$fromDate, $toDate] = $this->normalizeRange($from, $to);

        $units = $vehicle->units()
            ->where('status', VehicleUnit::STATUS_ACTIVE)
            ->orderBy('id')
            ->get();

        if ($units->isEmpty()) {
            return new EloquentCollection();
        }

        $usedIds = $this->usedUnitIds(
            $units->pluck('id')->all(),
            $fromDate,
            $toDate,
            $ignoreTrip,
            $ignoreBooking
        );

        return $units
            ->reject(fn (VehicleUnit $unit) => isset($usedIds[$unit->id]))
            ->values();
    }

    public function countAvailable(
        Vehicle $vehicle,
        mixed $from,
        mixed $to,
        ?Trip $ignoreTrip = null,
        ?Booking $ignoreBooking = null
    ): int {
        return $this->availableUnits($vehicle, $from, $to, $ignoreTrip, $ignoreBooking)->count();
    }

    public function countTotal(Vehicle $vehicle): int
    {
        return $vehicle->units()
            ->where('status', VehicleUnit::STATUS_ACTIVE)
            ->count();
    }

    public function countInUse(
        Vehicle $vehicle,
        mixed $from = null,
        mixed $to = null,
        ?Trip $ignoreTrip = null,
        ?Booking $ignoreBooking = null
    ): int {
        $fromDate = $from ?? Carbon::today();
        $toDate = $to ?? $fromDate;

        return max(
            0,
            $this->countTotal($vehicle)
                - $this->countAvailable($vehicle, $fromDate, $toDate, $ignoreTrip, $ignoreBooking)
        );
    }

    public function isUnitAvailable(
        VehicleUnit $unit,
        mixed $from,
        mixed $to,
        ?Trip $ignoreTrip = null,
        ?Booking $ignoreBooking = null
    ): bool {
        if ($unit->status !== VehicleUnit::STATUS_ACTIVE) {
            return false;
        }

        [$fromDate, $toDate] = $this->normalizeRange($from, $to);

        $usedIds = $this->usedUnitIds(
            [$unit->id],
            $fromDate,
            $toDate,
            $ignoreTrip,
            $ignoreBooking
        );

        return ! isset($usedIds[$unit->id]);
    }

    /**
     * Alokasikan unit bebas dengan id terkecil. Wajib dipanggil dalam transaksi.
     */
    public function allocate(
        Vehicle $vehicle,
        mixed $from,
        mixed $to,
        ?Trip $ignoreTrip = null,
        ?Booking $ignoreBooking = null
    ): ?VehicleUnit {
        return DB::transaction(function () use ($vehicle, $from, $to, $ignoreTrip, $ignoreBooking) {
            [$fromDate, $toDate] = $this->normalizeRange($from, $to);

            $units = $vehicle->units()
                ->where('status', VehicleUnit::STATUS_ACTIVE)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($units->isEmpty()) {
                return null;
            }

            $usedIds = $this->usedUnitIds(
                $units->pluck('id')->all(),
                $fromDate,
                $toDate,
                $ignoreTrip,
                $ignoreBooking
            );

            return $units->first(fn (VehicleUnit $unit) => ! isset($usedIds[$unit->id]));
        });
    }

    /**
     * Peta unit_id => Trip|Booking pertama yang menempati rentang tersebut.
     *
     * @param  iterable<int>  $unitIds
     * @return array<int, Trip|Booking>
     */
    public function usageForUnits(iterable $unitIds, mixed $from, mixed $to): array
    {
        [$fromDate, $toDate] = $this->normalizeRange($from, $to);

        $ids = collect($unitIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $usage = [];

        Trip::query()
            ->with(['package', 'vehicle'])
            ->whereIn('vehicle_unit_id', $ids)
            ->where('status', '!=', 'cancelled')
            ->whereDate('departure_date', '<=', $toDate->toDateString())
            ->orderBy('departure_date')
            ->orderBy('id')
            ->get()
            ->each(function (Trip $trip) use (&$usage, $fromDate, $toDate) {
                if (isset($usage[$trip->vehicle_unit_id])) {
                    return;
                }

                $start = Carbon::parse($trip->departure_date)->startOfDay();
                $end = Carbon::parse($trip->return_date ?? $trip->departure_date)->startOfDay();

                if ($this->overlaps($start, $end, $fromDate, $toDate)) {
                    $usage[(int) $trip->vehicle_unit_id] = $trip;
                }
            });

        Booking::query()
            ->with('vehicle')
            ->whereIn('vehicle_unit_id', $ids)
            ->whereIn('booking_status', self::BOOKING_HOLDING_STATUSES)
            ->whereNotNull('rental_date')
            ->whereDate('rental_date', '<=', $toDate->toDateString())
            ->orderBy('rental_date')
            ->orderBy('id')
            ->get()
            ->each(function (Booking $booking) use (&$usage, $fromDate, $toDate) {
                if (isset($usage[$booking->vehicle_unit_id])) {
                    return;
                }

                [$start, $end] = $this->bookingRange($booking);

                if ($this->overlaps($start, $end, $fromDate, $toDate)) {
                    $usage[(int) $booking->vehicle_unit_id] = $booking;
                }
            });

        return $usage;
    }

    public function hasFutureAllocations(VehicleUnit $unit): bool
    {
        $today = Carbon::today();

        $hasFutureTrip = Trip::query()
            ->where('vehicle_unit_id', $unit->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($today) {
                $query->whereDate('departure_date', '>=', $today->toDateString())
                    ->orWhereDate('return_date', '>=', $today->toDateString());
            })
            ->exists();

        if ($hasFutureTrip) {
            return true;
        }

        return Booking::query()
            ->where('vehicle_unit_id', $unit->id)
            ->whereIn('booking_status', self::BOOKING_HOLDING_STATUSES)
            ->whereNotNull('rental_date')
            ->get(['rental_date', 'duration'])
            ->contains(function (Booking $booking) use ($today) {
                [, $end] = $this->bookingRange($booking);

                return $end->gte($today);
            });
    }

    /**
     * @param  array<int, int>  $unitIds
     * @return array<int, bool>
     */
    private function usedUnitIds(
        array $unitIds,
        Carbon $from,
        Carbon $to,
        ?Trip $ignoreTrip,
        ?Booking $ignoreBooking
    ): array {
        if ($unitIds === []) {
            return [];
        }

        $used = [];

        Trip::query()
            ->whereIn('vehicle_unit_id', $unitIds)
            ->where('status', '!=', 'cancelled')
            ->when($ignoreTrip, fn ($query) => $query->where('id', '!=', $ignoreTrip->id))
            ->whereDate('departure_date', '<=', $to->toDateString())
            ->get(['vehicle_unit_id', 'departure_date', 'return_date'])
            ->each(function (Trip $trip) use (&$used, $from, $to) {
                $start = Carbon::parse($trip->departure_date)->startOfDay();
                $end = Carbon::parse($trip->return_date ?? $trip->departure_date)->startOfDay();

                if ($this->overlaps($start, $end, $from, $to)) {
                    $used[(int) $trip->vehicle_unit_id] = true;
                }
            });

        Booking::query()
            ->whereIn('vehicle_unit_id', $unitIds)
            ->whereIn('booking_status', self::BOOKING_HOLDING_STATUSES)
            ->when($ignoreBooking, fn ($query) => $query->where('id', '!=', $ignoreBooking->id))
            ->whereNotNull('rental_date')
            ->whereDate('rental_date', '<=', $to->toDateString())
            ->get(['vehicle_unit_id', 'rental_date', 'duration'])
            ->each(function (Booking $booking) use (&$used, $from, $to) {
                [$start, $end] = $this->bookingRange($booking);

                if ($this->overlaps($start, $end, $from, $to)) {
                    $used[(int) $booking->vehicle_unit_id] = true;
                }
            });

        return $used;
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function normalizeRange(mixed $from, mixed $to): array
    {
        $fromDate = Carbon::parse($from)->startOfDay();
        $toDate = Carbon::parse($to)->startOfDay();

        if ($toDate->lt($fromDate)) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        return [$fromDate, $toDate];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function bookingRange(Booking $booking): array
    {
        $start = Carbon::parse($booking->rental_date)->startOfDay();
        $duration = max(1, (int) ($booking->duration ?? 1));

        return [$start, $start->copy()->addDays($duration - 1)];
    }

    private function overlaps(Carbon $start, Carbon $end, Carbon $from, Carbon $to): bool
    {
        return $start->lte($to) && $end->gte($from);
    }
}
