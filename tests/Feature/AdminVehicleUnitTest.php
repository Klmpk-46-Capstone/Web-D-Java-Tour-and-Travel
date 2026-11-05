<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleUnit;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVehicleUnitTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'super_admin']);

        $this->vehicle = Vehicle::create([
            'name' => 'Unit Test HiAce',
            'slug' => 'unit-test-hiace',
            'category' => 'minibus',
            'seats' => 14,
            'price_per_day' => 800000,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_and_create_normalized_unit_plate(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.units.index'))
            ->assertStatus(200)
            ->assertSee('Unit &amp; Plat Armada', false)
            ->assertSee($this->vehicle->name);

        $response = $this->actingAs($this->admin)->post(route('admin.units.store'), [
            'vehicle_id' => $this->vehicle->id,
            'plate_number' => '  b   1234   ckr ',
            'status' => VehicleUnit::STATUS_ACTIVE,
            'notes' => 'Unit uji coba',
        ]);

        $response->assertRedirect(route('admin.units.index'));
        $this->assertDatabaseHas('vehicle_units', [
            'vehicle_id' => $this->vehicle->id,
            'plate_number' => 'B 1234 CKR',
            'status' => VehicleUnit::STATUS_ACTIVE,
        ]);
    }

    public function test_duplicate_plate_is_rejected(): void
    {
        VehicleUnit::create([
            'vehicle_id' => $this->vehicle->id,
            'plate_number' => 'B 1234 CKR',
            'status' => VehicleUnit::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.units.store'), [
            'vehicle_id' => $this->vehicle->id,
            'plate_number' => 'b 1234 ckr',
            'status' => VehicleUnit::STATUS_ACTIVE,
        ]);

        $response->assertSessionHasErrors('plate_number');
        $this->assertSame(1, VehicleUnit::count());
    }

    public function test_unit_with_allocation_cannot_be_deleted_or_put_into_maintenance(): void
    {
        $unit = VehicleUnit::create([
            'vehicle_id' => $this->vehicle->id,
            'plate_number' => 'B 4321 CKR',
            'status' => VehicleUnit::STATUS_ACTIVE,
        ]);

        $package = Package::create([
            'name' => 'Unit Guard Package',
            'slug' => 'unit-guard-package',
            'category' => 'Wisata Bahari',
            'route_type' => 'bahari',
            'duration' => '2 Hari 1 Malam',
            'price' => 900000,
            'is_active' => true,
        ]);

        Trip::create([
            'package_id' => $package->id,
            'batch_name' => 'Unit Guard Batch',
            'departure_date' => Carbon::today()->addDays(3),
            'return_date' => Carbon::today()->addDays(4),
            'quota' => 14,
            'booked_seats' => 0,
            'status' => 'available',
            'vehicle_id' => $this->vehicle->id,
            'vehicle_unit_id' => $unit->id,
        ]);

        $deleteResponse = $this->actingAs($this->admin)
            ->delete(route('admin.units.destroy', $unit));

        $deleteResponse->assertRedirect(route('admin.units.index'));
        $deleteResponse->assertSessionHas('error');
        $this->assertDatabaseHas('vehicle_units', ['id' => $unit->id]);

        $toggleResponse = $this->actingAs($this->admin)
            ->post(route('admin.units.toggle-status', $unit));

        $toggleResponse->assertRedirect();
        $toggleResponse->assertSessionHas('error');
        $this->assertSame(VehicleUnit::STATUS_ACTIVE, $unit->fresh()->status);
    }

    public function test_units_filter_supports_tersedia_terpakai_and_servis(): void
    {
        $usedUnit = VehicleUnit::create([
            'vehicle_id' => $this->vehicle->id,
            'plate_number' => 'B 5001 USE',
            'status' => VehicleUnit::STATUS_ACTIVE,
        ]);

        $freeUnit = VehicleUnit::create([
            'vehicle_id' => $this->vehicle->id,
            'plate_number' => 'B 5002 FREE',
            'status' => VehicleUnit::STATUS_ACTIVE,
        ]);

        $maintenanceUnit = VehicleUnit::create([
            'vehicle_id' => $this->vehicle->id,
            'plate_number' => 'B 5003 SRV',
            'status' => VehicleUnit::STATUS_MAINTENANCE,
        ]);

        $package = Package::create([
            'name' => 'Unit Usage Package',
            'slug' => 'unit-usage-package',
            'category' => 'Wisata Bahari',
            'route_type' => 'bahari',
            'duration' => '1 Hari',
            'price' => 500000,
            'is_active' => true,
        ]);

        Trip::create([
            'package_id' => $package->id,
            'batch_name' => 'Unit Usage Batch',
            'departure_date' => Carbon::today(),
            'return_date' => Carbon::today(),
            'quota' => 14,
            'booked_seats' => 0,
            'status' => 'available',
            'vehicle_id' => $this->vehicle->id,
            'vehicle_unit_id' => $usedUnit->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.units.index', ['status' => 'in_use']))
            ->assertOk()
            ->assertSee('Tersedia')
            ->assertSee('Terpakai')
            ->assertSee('Servis')
            ->assertSee($usedUnit->plate_number)
            ->assertDontSee($freeUnit->plate_number);

        $this->actingAs($this->admin)
            ->get(route('admin.units.index', ['status' => 'available']))
            ->assertOk()
            ->assertSee($freeUnit->plate_number)
            ->assertDontSee($usedUnit->plate_number)
            ->assertDontSee($maintenanceUnit->plate_number);

        $this->actingAs($this->admin)
            ->get(route('admin.units.index', ['status' => 'maintenance']))
            ->assertOk()
            ->assertSee($maintenanceUnit->plate_number)
            ->assertDontSee($usedUnit->plate_number)
            ->assertDontSee($freeUnit->plate_number);
    }

    public function test_customer_cannot_access_unit_management(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->get(route('admin.units.index'))
            ->assertRedirect('/');
    }
}
