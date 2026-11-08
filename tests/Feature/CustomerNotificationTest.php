<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Package;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleUnit;
use App\Services\CustomerNotificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::create([
            'name' => 'Admin Ops',
            'email' => 'admin@djavatour.com',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
        ]);

        $this->customer = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'whatsapp' => '081234567890',
            'whatsapp_notifications_enabled' => true,
        ]);
    }

    protected function createPackage(string $name = 'Trip Bromo Sunrise Spesial'): Package
    {
        return Package::create([
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . \Illuminate\Support\Str::random(4),
            'category' => 'Mountain Trekking & Sunrise',
            'route_type' => 'gunung',
            'duration' => '1 Hari',
            'price' => 500000,
            'is_active' => true,
        ]);
    }

    public function test_admin_dashboard_shows_h1_monitor_empty_state_when_no_trips_tomorrow(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Monitor Keberangkatan H-1');
        $response->assertSee('Operasional Terkendali');
    }

    public function test_admin_dashboard_shows_h1_notification_when_trips_depart_tomorrow(): void
    {
        $package = $this->createPackage('Trip Bromo Sunrise Spesial');

        $vehicle = Vehicle::create([
            'name' => 'Toyota HiAce Premio',
            'slug' => 'toyota-hiace-premio-' . \Illuminate\Support\Str::random(4),
            'category' => 'minibus',
            'seats' => 12,
            'luggage_capacity' => 6,
            'transmission' => 'Manual',
            'price_per_day' => 1200000,
            'is_active' => true,
        ]);

        $unit = VehicleUnit::create([
            'vehicle_id' => $vehicle->id,
            'plate_number' => 'N 1234 XY',
            'is_active' => true,
            'status' => 'ready',
        ]);

        $tomorrow = Carbon::tomorrow()->toDateString();
        $trip = Trip::create([
            'package_id' => $package->id,
            'batch_name' => 'Batch Sunrise H-1 #01',
            'departure_date' => $tomorrow,
            'return_date' => $tomorrow,
            'quota' => 12,
            'booked_seats' => 4,
            'status' => 'available',
            'vehicle_id' => $vehicle->id,
            'vehicle_unit_id' => $unit->id,
            'driver_name' => 'Pak Joko',
            'meeting_point' => 'Stasiun Malang Kota Baru',
            'departure_time' => '03:00:00',
        ]);

        Booking::create([
            'booking_code' => 'DJV-H1TEST',
            'user_id' => $this->customer->id,
            'trip_id' => $trip->id,
            'package_id' => $package->id,
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@example.com',
            'customer_phone' => '081234567890',
            'pax' => 4,
            'total_price' => 2000000,
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Notifikasi H-1 — Trip Berangkat Besok');
        $response->assertSee('Trip Bromo Sunrise Spesial');
        $response->assertSee('Pak Joko');
        $response->assertSee('N 1234 XY');
        $response->assertSee('DJV-H1TEST');
    }

    public function test_customer_notification_service_generates_h1_reminder(): void
    {
        $package = $this->createPackage('Paket Karimunjawa Tropical');
        $tomorrow = Carbon::tomorrow()->toDateString();

        $trip = Trip::create([
            'package_id' => $package->id,
            'batch_name' => 'Batch Karimun H-1',
            'departure_date' => $tomorrow,
            'return_date' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'quota' => 20,
            'booked_seats' => 2,
            'status' => 'available',
            'departure_time' => '07:30:00',
            'meeting_point' => 'Pelabuhan Kartini Jepara',
        ]);

        Booking::create([
            'booking_code' => 'DJV-KMJ001',
            'user_id' => $this->customer->id,
            'trip_id' => $trip->id,
            'package_id' => $package->id,
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@example.com',
            'customer_phone' => '081234567890',
            'pax' => 2,
            'total_price' => 1800000,
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        $notifs = CustomerNotificationService::forUser($this->customer);

        $this->assertTrue($notifs->contains('type', 'h1_reminder'));
        $h1 = $notifs->firstWhere('type', 'h1_reminder');
        $this->assertEquals('Trip Berangkat Besok!', $h1['title']);
        $this->assertStringContainsString('Paket Karimunjawa Tropical', $h1['message']);
        $this->assertStringContainsString('07:30 WIB', $h1['message']);
        $this->assertStringContainsString('Pelabuhan Kartini Jepara', $h1['message']);
    }

    public function test_customer_sees_h1_notification_banner_in_booking_history(): void
    {
        $package = $this->createPackage('Wisata Dieng Negeri di Atas Awan');
        $tomorrow = Carbon::tomorrow()->toDateString();

        $trip = Trip::create([
            'package_id' => $package->id,
            'batch_name' => 'Batch Dieng H-1',
            'departure_date' => $tomorrow,
            'return_date' => $tomorrow,
            'quota' => 15,
            'booked_seats' => 1,
            'status' => 'available',
        ]);

        Booking::create([
            'booking_code' => 'DJV-DIENG99',
            'user_id' => $this->customer->id,
            'trip_id' => $trip->id,
            'package_id' => $package->id,
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@example.com',
            'customer_phone' => '081234567890',
            'pax' => 1,
            'total_price' => 750000,
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->customer)->get(route('customer.bookings.index'));

        $response->assertOk();
        $response->assertSee('Trip Berangkat Besok!');
        $response->assertSee('Wisata Dieng Negeri di Atas Awan');
    }

    public function test_artisan_send_h1_command_runs_successfully(): void
    {
        $this->artisan('notifications:send-h1')
            ->assertExitCode(0)
            ->expectsOutputToContain("Proses Notifikasi Pengingat H-1");
    }
}
