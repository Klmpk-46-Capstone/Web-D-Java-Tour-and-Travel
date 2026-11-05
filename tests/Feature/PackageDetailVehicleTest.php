<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\VehicleUnit;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageDetailVehicleTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_detail_shows_scheduled_vehicle_type_without_plate(): void
    {
        $vehicle = Vehicle::create([
            'name' => 'Detail Fleet Innova',
            'slug' => 'detail-fleet-innova',
            'category' => 'mpv',
            'seats' => 7,
            'transmission' => 'Matic',
            'price_per_day' => 650000,
            'is_active' => true,
        ]);

        $unit = VehicleUnit::create([
            'vehicle_id' => $vehicle->id,
            'plate_number' => 'B 8801 DTL',
            'status' => VehicleUnit::STATUS_ACTIVE,
        ]);

        $package = Package::create([
            'name' => 'Detail Fleet Package',
            'slug' => 'detail-fleet-package',
            'category' => 'Wisata Bahari',
            'route_type' => 'bahari',
            'duration' => '3 Hari 2 Malam',
            'price' => 1200000,
            'description' => 'Paket untuk menguji tampilan armada terjadwal.',
            'is_active' => true,
        ]);

        Trip::create([
            'package_id' => $package->id,
            'batch_name' => 'Detail Fleet Batch',
            'departure_date' => Carbon::today()->addDays(5),
            'return_date' => Carbon::today()->addDays(7),
            'quota' => 20,
            'booked_seats' => 0,
            'status' => 'available',
            'vehicle_id' => $vehicle->id,
            'vehicle_unit_id' => $unit->id,
        ]);

        $response = $this->get(route('packages.show', $package->slug));

        $response->assertStatus(200);
        $response->assertSee('Detail Fleet Innova');
        $response->assertSee('7 kursi');
        $response->assertSee('Matic');
        $response->assertDontSee('B 8801 DTL');
    }

    public function test_quota_check_json_does_not_expose_plate(): void
    {
        $vehicle = Vehicle::create([
            'name' => 'Quota Fleet Vehicle',
            'slug' => 'quota-fleet-vehicle',
            'category' => 'minibus',
            'seats' => 14,
            'price_per_day' => 900000,
            'is_active' => true,
        ]);

        $unit = VehicleUnit::create([
            'vehicle_id' => $vehicle->id,
            'plate_number' => 'B 8802 QTA',
            'status' => VehicleUnit::STATUS_ACTIVE,
        ]);

        $package = Package::create([
            'name' => 'Quota Fleet Package',
            'slug' => 'quota-fleet-package',
            'category' => 'Wisata Bahari',
            'route_type' => 'bahari',
            'duration' => '2 Hari 1 Malam',
            'price' => 1000000,
            'is_active' => true,
        ]);

        $trip = Trip::create([
            'package_id' => $package->id,
            'batch_name' => 'Quota Fleet Batch',
            'departure_date' => Carbon::today()->addDays(8),
            'return_date' => Carbon::today()->addDays(9),
            'quota' => 14,
            'booked_seats' => 3,
            'status' => 'available',
            'vehicle_id' => $vehicle->id,
            'vehicle_unit_id' => $unit->id,
        ]);

        $response = $this->getJson(route('trips.check-quota', $trip));

        $response->assertStatus(200);
        $response->assertJsonPath('remaining_seats', 11);
        $this->assertStringNotContainsString('B 8802 QTA', $response->getContent());
        $this->assertStringNotContainsString('vehicle_unit', $response->getContent());
    }

    public function test_package_detail_hides_vehicle_type_without_active_unit(): void
    {
        $vehicle = Vehicle::create([
            'name' => 'Hidden Fleet Innova',
            'slug' => 'hidden-fleet-innova',
            'category' => 'mpv',
            'seats' => 7,
            'transmission' => 'Matic',
            'price_per_day' => 650000,
            'is_active' => true,
        ]);

        $package = Package::create([
            'name' => 'Hidden Fleet Package',
            'slug' => 'hidden-fleet-package',
            'category' => 'Wisata Bahari',
            'route_type' => 'bahari',
            'duration' => '3 Hari 2 Malam',
            'price' => 1200000,
            'is_active' => true,
        ]);

        Trip::create([
            'package_id' => $package->id,
            'batch_name' => 'Hidden Fleet Batch',
            'departure_date' => Carbon::today()->addDays(6),
            'return_date' => Carbon::today()->addDays(8),
            'quota' => 20,
            'booked_seats' => 0,
            'status' => 'available',
            'vehicle_id' => $vehicle->id,
        ]);

        $visibleVehicle = Vehicle::create([
            'name' => 'Visible Fleet Innova',
            'slug' => 'visible-fleet-innova',
            'category' => 'mpv',
            'seats' => 7,
            'transmission' => 'Matic',
            'price_per_day' => 650000,
            'is_active' => true,
        ]);

        $visibleUnit = VehicleUnit::create([
            'vehicle_id' => $visibleVehicle->id,
            'plate_number' => 'B 8803 VSB',
            'status' => VehicleUnit::STATUS_ACTIVE,
        ]);

        Trip::create([
            'package_id' => $package->id,
            'batch_name' => 'Visible Fleet Batch',
            'departure_date' => Carbon::today()->addDays(10),
            'return_date' => Carbon::today()->addDays(12),
            'quota' => 20,
            'booked_seats' => 0,
            'status' => 'available',
            'vehicle_id' => $visibleVehicle->id,
            'vehicle_unit_id' => $visibleUnit->id,
        ]);

        $this->get(route('packages.show', $package->slug))
            ->assertOk()
            ->assertSee('Visible Fleet Innova')
            ->assertDontSee('Hidden Fleet Innova')
            ->assertDontSee('Hidden Fleet Batch');
    }

    public function test_package_with_only_unscheduled_vehicle_trip_is_hidden_from_customer(): void
    {
        $package = Package::create([
            'name' => 'Unscheduled Fleet Package',
            'slug' => 'unscheduled-fleet-package',
            'category' => 'Wisata Bahari',
            'route_type' => 'bahari',
            'duration' => '2 Hari 1 Malam',
            'price' => 900000,
            'is_active' => true,
        ]);

        $trip = Trip::create([
            'package_id' => $package->id,
            'batch_name' => 'Unscheduled Fleet Batch',
            'departure_date' => Carbon::today()->addDays(7),
            'return_date' => Carbon::today()->addDays(8),
            'quota' => 20,
            'booked_seats' => 0,
            'status' => 'available',
        ]);

        $this->get(route('packages.index'))
            ->assertOk()
            ->assertDontSee('Unscheduled Fleet Package');

        $this->get(route('packages.show', $package->slug))->assertNotFound();
        $this->getJson(route('trips.check-quota', $trip))->assertNotFound();
    }
}
