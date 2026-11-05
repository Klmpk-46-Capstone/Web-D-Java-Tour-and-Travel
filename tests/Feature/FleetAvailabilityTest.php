<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Package;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleUnit;
use App\Services\FleetAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private FleetAvailability $fleet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fleet = app(FleetAvailability::class);
    }

    public function test_trip_usage_is_inclusive_and_cancelled_trip_releases_unit(): void
    {
        $vehicle = $this->makeVehicle();
        $unit = $this->makeUnit($vehicle, 'B 1001 FLEET');

        $trip = $this->makeTrip($vehicle, $unit, '2026-12-10', '2026-12-12');

        $this->assertFalse($this->fleet->isUnitAvailable($unit, '2026-12-10', '2026-12-10'));
        $this->assertFalse($this->fleet->isUnitAvailable($unit, '2026-12-12', '2026-12-12'));
        $this->assertTrue($this->fleet->isUnitAvailable($unit, '2026-12-13', '2026-12-13'));

        $trip->update(['status' => 'cancelled']);

        $this->assertTrue($this->fleet->isUnitAvailable($unit, '2026-12-10', '2026-12-12'));
    }

    public function test_rental_booking_usage_uses_duration_and_ignores_cancelled_booking(): void
    {
        $vehicle = $this->makeVehicle();
        $unit = $this->makeUnit($vehicle, 'B 1002 FLEET');

        $booking = $this->makeRentalBooking($vehicle, $unit, '2026-12-10', 3, 'confirmed');

        $this->assertFalse($this->fleet->isUnitAvailable($unit, '2026-12-12', '2026-12-12'));
        $this->assertTrue($this->fleet->isUnitAvailable($unit, '2026-12-13', '2026-12-13'));

        $booking->update(['booking_status' => 'cancelled']);

        $this->assertTrue($this->fleet->isUnitAvailable($unit, '2026-12-10', '2026-12-12'));
    }

    public function test_allocate_is_deterministic_and_skips_maintenance_units(): void
    {
        $vehicle = $this->makeVehicle();
        $first = $this->makeUnit($vehicle, 'B 1011 FLEET');
        $this->makeUnit($vehicle, 'B 1012 FLEET', VehicleUnit::STATUS_MAINTENANCE);
        $third = $this->makeUnit($vehicle, 'B 1013 FLEET');

        $allocated = $this->fleet->allocate($vehicle, '2027-01-10', '2027-01-12');
        $this->assertTrue($allocated->is($first));

        $this->makeTrip($vehicle, $first, '2027-01-10', '2027-01-12');

        $secondAllocation = $this->fleet->allocate($vehicle, '2027-01-10', '2027-01-12');
        $this->assertTrue($secondAllocation->is($third));

        $this->makeTrip($vehicle, $third, '2027-01-10', '2027-01-12');

        $this->assertNull($this->fleet->allocate($vehicle, '2027-01-10', '2027-01-12'));
    }

    public function test_allocation_can_ignore_the_trip_being_updated(): void
    {
        $vehicle = $this->makeVehicle();
        $unit = $this->makeUnit($vehicle, 'B 1021 FLEET');
        $trip = $this->makeTrip($vehicle, $unit, '2027-02-10', '2027-02-12');

        $this->assertNull($this->fleet->allocate($vehicle, '2027-02-10', '2027-02-12'));
        $this->assertTrue(
            $this->fleet->allocate($vehicle, '2027-02-10', '2027-02-12', $trip)?->is($unit) ?? false
        );
    }

    public function test_usage_map_returns_the_occupying_trip_or_booking(): void
    {
        $vehicle = $this->makeVehicle();
        $tripUnit = $this->makeUnit($vehicle, 'B 1031 FLEET');
        $bookingUnit = $this->makeUnit($vehicle, 'B 1032 FLEET');

        $trip = $this->makeTrip($vehicle, $tripUnit, '2027-03-10', '2027-03-11');
        $booking = $this->makeRentalBooking($vehicle, $bookingUnit, '2027-03-10', 1, 'pending');

        $usage = $this->fleet->usageForUnits(
            [$tripUnit->id, $bookingUnit->id],
            '2027-03-10',
            '2027-03-10'
        );

        $this->assertTrue($usage[$tripUnit->id]->is($trip));
        $this->assertTrue($usage[$bookingUnit->id]->is($booking));
    }

    private function makeVehicle(array $attributes = []): Vehicle
    {
        return Vehicle::create([
            'name' => 'Test Fleet Vehicle',
            'slug' => 'test-fleet-vehicle-' . Vehicle::count(),
            'category' => 'mpv',
            'seats' => 14,
            'price_per_day' => 500000,
            'is_active' => true,
            ...$attributes,
        ]);
    }

    private function makeUnit(
        Vehicle $vehicle,
        string $plate,
        string $status = VehicleUnit::STATUS_ACTIVE
    ): VehicleUnit {
        return VehicleUnit::create([
            'vehicle_id' => $vehicle->id,
            'plate_number' => $plate,
            'status' => $status,
        ]);
    }

    private function makeTrip(
        Vehicle $vehicle,
        VehicleUnit $unit,
        string $departure,
        string $return
    ): Trip {
        $package = Package::create([
            'name' => 'Fleet Test Package',
            'slug' => 'fleet-test-package-' . Package::count(),
            'category' => 'Wisata Bahari',
            'route_type' => 'bahari',
            'duration' => '3 Hari 2 Malam',
            'price' => 1000000,
            'is_active' => true,
        ]);

        return Trip::create([
            'package_id' => $package->id,
            'batch_name' => 'Fleet Test Batch',
            'departure_date' => $departure,
            'return_date' => $return,
            'quota' => 14,
            'booked_seats' => 0,
            'status' => 'available',
            'vehicle_id' => $vehicle->id,
            'vehicle_unit_id' => $unit->id,
        ]);
    }

    private function makeRentalBooking(
        Vehicle $vehicle,
        VehicleUnit $unit,
        string $rentalDate,
        int $duration,
        string $bookingStatus
    ): Booking {
        $customer = User::factory()->create(['role' => 'customer']);

        return Booking::create([
            'booking_code' => 'BK-FLEET-' . strtoupper(uniqid()),
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'vehicle_unit_id' => $unit->id,
            'rental_date' => $rentalDate,
            'duration' => $duration,
            'pax' => 1,
            'rate' => 500000,
            'total_price' => 500000 * $duration,
            'customer_name' => 'Fleet Tester',
            'customer_email' => 'fleet@example.com',
            'customer_phone' => '08123456789',
            'payment_method' => 'Xendit',
            'payment_status' => 'pending',
            'booking_status' => $bookingStatus,
        ]);
    }
}
