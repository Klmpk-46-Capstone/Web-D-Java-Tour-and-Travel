<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Package;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleUnit;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTripFleetTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Package $package;

    private Vehicle $vehicle;

    private VehicleUnit $firstUnit;

    private VehicleUnit $secondUnit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'super_admin']);

        $this->package = Package::create([
            'name' => 'Trip Fleet Package',
            'slug' => 'trip-fleet-package',
            'category' => 'Wisata Bahari',
            'route_type' => 'bahari',
            'duration' => '3 Hari 2 Malam',
            'price' => 1500000,
            'is_active' => true,
        ]);

        $this->vehicle = Vehicle::create([
            'name' => 'Trip Fleet HiAce',
            'slug' => 'trip-fleet-hiace',
            'category' => 'minibus',
            'seats' => 14,
            'price_per_day' => 900000,
            'is_active' => true,
        ]);

        $this->firstUnit = VehicleUnit::create([
            'vehicle_id' => $this->vehicle->id,
            'plate_number' => 'B 7101 TRP',
            'status' => VehicleUnit::STATUS_ACTIVE,
        ]);

        $this->secondUnit = VehicleUnit::create([
            'vehicle_id' => $this->vehicle->id,
            'plate_number' => 'B 7102 TRP',
            'status' => VehicleUnit::STATUS_ACTIVE,
        ]);
    }

    public function test_trip_store_auto_allocates_units_and_rejects_when_all_units_are_used(): void
    {
        $departure = Carbon::today()->addDays(10)->format('Y-m-d');
        $return = Carbon::today()->addDays(12)->format('Y-m-d');

        $this->actingAs($this->admin)->post(route('admin.trips.store'), [
            'package_id' => $this->package->id,
            'batch_name' => 'Batch Auto Allocate 1',
            'departure_date' => $departure,
            'return_date' => $return,
            'quota' => 12,
            'vehicle_id' => $this->vehicle->id,
        ])->assertRedirect();

        $this->assertSame(
            $this->firstUnit->id,
            Trip::where('batch_name', 'Batch Auto Allocate 1')->firstOrFail()->vehicle_unit_id
        );

        $this->actingAs($this->admin)->post(route('admin.trips.store'), [
            'package_id' => $this->package->id,
            'batch_name' => 'Batch Auto Allocate 2',
            'departure_date' => $departure,
            'return_date' => $return,
            'quota' => 12,
            'vehicle_id' => $this->vehicle->id,
        ])->assertRedirect();

        $this->assertSame(
            $this->secondUnit->id,
            Trip::where('batch_name', 'Batch Auto Allocate 2')->firstOrFail()->vehicle_unit_id
        );

        $response = $this->actingAs($this->admin)->post(route('admin.trips.store'), [
            'package_id' => $this->package->id,
            'batch_name' => 'Batch Auto Allocate 3',
            'departure_date' => $departure,
            'return_date' => $return,
            'quota' => 12,
            'vehicle_id' => $this->vehicle->id,
        ]);

        $response->assertSessionHasErrors('vehicle_id');
        $this->assertSame(2, Trip::count());
    }

    public function test_optional_plate_override_is_used_and_must_match_vehicle_type(): void
    {
        $otherVehicle = Vehicle::create([
            'name' => 'Other Trip Vehicle',
            'slug' => 'other-trip-vehicle',
            'category' => 'mpv',
            'seats' => 7,
            'price_per_day' => 500000,
            'is_active' => true,
        ]);

        $otherUnit = VehicleUnit::create([
            'vehicle_id' => $otherVehicle->id,
            'plate_number' => 'B 7201 TRP',
            'status' => VehicleUnit::STATUS_ACTIVE,
        ]);

        $base = [
            'package_id' => $this->package->id,
            'batch_name' => 'Batch Override',
            'departure_date' => Carbon::today()->addDays(20)->format('Y-m-d'),
            'return_date' => Carbon::today()->addDays(22)->format('Y-m-d'),
            'quota' => 7,
            'vehicle_id' => $this->vehicle->id,
            'vehicle_unit_id' => $this->secondUnit->id,
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.trips.store'), $base)
            ->assertRedirect();

        $this->assertSame(
            $this->secondUnit->id,
            Trip::where('batch_name', 'Batch Override')->firstOrFail()->vehicle_unit_id
        );

        $invalid = $base;
        $invalid['batch_name'] = 'Batch Override Salah Tipe';
        $invalid['vehicle_unit_id'] = $otherUnit->id;

        $this->actingAs($this->admin)
            ->post(route('admin.trips.store'), $invalid)
            ->assertSessionHasErrors('vehicle_unit_id');
    }

    public function test_vehicle_without_active_unit_cannot_be_scheduled(): void
    {
        $emptyVehicle = Vehicle::create([
            'name' => 'Empty Fleet Vehicle',
            'slug' => 'empty-fleet-vehicle',
            'category' => 'mpv',
            'seats' => 7,
            'price_per_day' => 500000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.trips.store'), [
            'package_id' => $this->package->id,
            'batch_name' => 'Batch Tanpa Unit',
            'departure_date' => Carbon::today()->addDays(30)->format('Y-m-d'),
            'return_date' => Carbon::today()->addDays(32)->format('Y-m-d'),
            'quota' => 7,
            'vehicle_id' => $emptyVehicle->id,
        ]);

        $response->assertSessionHasErrors('vehicle_id');
        $this->assertSame(0, Trip::count());
    }

    public function test_trip_update_revalidates_against_rental_booking_on_same_unit(): void
    {
        $singleVehicle = Vehicle::create([
            'name' => 'Single Unit Fleet Vehicle',
            'slug' => 'single-unit-fleet-vehicle',
            'category' => 'mpv',
            'seats' => 7,
            'price_per_day' => 500000,
            'is_active' => true,
        ]);

        $unit = VehicleUnit::create([
            'vehicle_id' => $singleVehicle->id,
            'plate_number' => 'B 7301 TRP',
            'status' => VehicleUnit::STATUS_ACTIVE,
        ]);

        $trip = Trip::create([
            'package_id' => $this->package->id,
            'batch_name' => 'Batch Revalidate',
            'departure_date' => Carbon::today()->addDays(40),
            'return_date' => Carbon::today()->addDays(42),
            'quota' => 7,
            'booked_seats' => 0,
            'status' => 'available',
            'vehicle_id' => $singleVehicle->id,
            'vehicle_unit_id' => $unit->id,
        ]);

        $customer = User::factory()->create(['role' => 'customer']);

        Booking::create([
            'booking_code' => 'BK-REVALIDATE',
            'user_id' => $customer->id,
            'vehicle_id' => $singleVehicle->id,
            'vehicle_unit_id' => $unit->id,
            'rental_date' => Carbon::today()->addDays(50),
            'duration' => 3,
            'pax' => 1,
            'rate' => 900000,
            'total_price' => 2700000,
            'customer_name' => 'Revalidate Customer',
            'customer_email' => 'revalidate@example.com',
            'customer_phone' => '08123456789',
            'payment_method' => 'Xendit',
            'payment_status' => 'pending',
            'booking_status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.trips.update', $trip), [
            'package_id' => $this->package->id,
            'batch_name' => 'Batch Revalidate',
            'departure_date' => Carbon::today()->addDays(50)->format('Y-m-d'),
            'return_date' => Carbon::today()->addDays(52)->format('Y-m-d'),
            'quota' => 7,
            'vehicle_id' => $singleVehicle->id,
            'status' => 'available',
        ]);

        $response->assertSessionHasErrors('vehicle_id');
    }
}
