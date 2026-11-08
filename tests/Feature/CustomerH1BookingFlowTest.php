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

class CustomerH1BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Package $package;
    protected Vehicle $vehicle;
    protected VehicleUnit $unit;
    protected Trip $tomorrowTrip;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Siapkan Admin
        $this->admin = User::create([
            'name' => 'Admin Operasional Pusat',
            'email' => 'admin.ops@djavatour.com',
            'password' => bcrypt('AdminSecret123'),
            'role' => 'admin_ops',
        ]);

        // 2. Siapkan Paket Wisata & Armada
        $this->package = Package::create([
            'name' => 'Eksplorasi Karimunjawa Bahari',
            'slug' => 'eksplorasi-karimunjawa-bahari',
            'category' => 'Wisata Bahari & Island Hopping',
            'route_type' => 'bahari',
            'duration' => '3 Hari 2 Malam',
            'price' => 1250000,
            'is_active' => true,
        ]);

        $this->vehicle = Vehicle::create([
            'name' => 'Toyota HiAce Commuter',
            'slug' => 'toyota-hiace-commuter',
            'category' => 'minibus',
            'seats' => 14,
            'luggage_capacity' => 6,
            'transmission' => 'Manual',
            'price_per_day' => 1100000,
            'is_active' => true,
        ]);

        $this->unit = VehicleUnit::create([
            'vehicle_id' => $this->vehicle->id,
            'plate_number' => 'K 8899 ZA',
            'is_active' => true,
            'status' => 'ready',
        ]);

        // 3. Siapkan Jadwal Trip untuk BESOK (H-1)
        $tomorrow = Carbon::tomorrow()->toDateString();
        $this->tomorrowTrip = Trip::create([
            'package_id' => $this->package->id,
            'batch_name' => 'Batch Bahari Besok #01',
            'departure_date' => $tomorrow,
            'return_date' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'quota' => 14,
            'booked_seats' => 0,
            'status' => 'available',
            'vehicle_id' => $this->vehicle->id,
            'vehicle_unit_id' => $this->unit->id,
            'driver_name' => 'Bambang Supriyadi',
            'meeting_point' => 'Pelabuhan Kartini Jepara (Pintu 1)',
            'departure_time' => '06:45:00',
        ]);
    }

    /**
     * Skenario Lengkap:
     * 1. Registrasi Akun Customer Baru
     * 2. Pemesanan Trip untuk Besok (H-1)
     * 3. Pembayaran & Konfirmasi Booking
     * 4. Muncul Notifikasi H-1 di Navbar Customer
     * 5. Muncul Banner H-1 di Riwayat Transaksi Customer
     * 6. Muncul Badge H-1 di Profil Customer
     * 7. Muncul Notifikasi H-1 di Dashboard Utama Admin
     * 8. Muncul di Lonceng Notifikasi Header Admin
     * 9. Eksekusi Console Command Pengingat H-1
     */
    public function test_complete_customer_h1_booking_and_notification_workflow(): void
    {
        // ------------------------------------------------------------------
        // LANGKAH 1: Customer Baru Mendaftar Akun
        // ------------------------------------------------------------------
        $customerData = [
            'name' => 'Dimas Berlian',
            'email' => 'dimas.berlian@student.telkomuniversity.ac.id',
            'whatsapp' => '082199887766',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ];

        $registerResponse = $this->post(route('register.store'), $customerData);
        $registerResponse->assertRedirect();

        $customer = User::where('email', 'dimas.berlian@student.telkomuniversity.ac.id')->first();
        $this->assertNotNull($customer, 'Akun customer baru harus berhasil tersimpan di database.');
        $this->assertEquals('customer', $customer->role);

        // ------------------------------------------------------------------
        // LANGKAH 2: Customer Melakukan Pemesanan Trip untuk Besok (H-1)
        // ------------------------------------------------------------------
        $booking = Booking::create([
            'booking_code' => 'DJV-TELKOM46',
            'user_id' => $customer->id,
            'trip_id' => $this->tomorrowTrip->id,
            'package_id' => $this->package->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->whatsapp,
            'pax' => 2,
            'pickup_location' => 'Pelabuhan Kartini Jepara (Pintu 1)',
            'total_price' => 2500000,
            'payment_method' => 'qris',
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        $this->tomorrowTrip->update([
            'booked_seats' => $this->tomorrowTrip->booked_seats + 2,
        ]);

        $this->assertDatabaseHas('bookings', [
            'booking_code' => 'DJV-TELKOM46',
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        // ------------------------------------------------------------------
        // LANGKAH 3: Verifikasi Service Notifikasi Customer Menghasilkan H-1 Reminder
        // ------------------------------------------------------------------
        $notifs = CustomerNotificationService::forUser($customer);
        $this->assertNotEmpty($notifs, 'Customer harus memiliki notifikasi aktif.');

        $h1Notif = $notifs->firstWhere('type', 'h1_reminder');
        $this->assertNotNull($h1Notif, 'Notifikasi bertipe h1_reminder harus ditemukan.');
        $this->assertEquals('Trip Berangkat Besok!', $h1Notif['title']);
        $this->assertStringContainsString('Eksplorasi Karimunjawa Bahari', $h1Notif['message']);
        $this->assertStringContainsString('06:45 WIB', $h1Notif['message']);
        $this->assertStringContainsString('Pelabuhan Kartini Jepara (Pintu 1)', $h1Notif['message']);
        $this->assertTrue($h1Notif['is_unread']);

        // ------------------------------------------------------------------
        // LANGKAH 4: Verifikasi Tampilan di Navbar Customer (Lonceng Notifikasi)
        // ------------------------------------------------------------------
        $customerHomeResponse = $this->actingAs($customer)->get(route('home'));
        $customerHomeResponse->assertOk();
        $customerHomeResponse->assertSee('Trip Berangkat Besok!');
        $customerHomeResponse->assertSee('notifMenuDesktop');
        $customerHomeResponse->assertSee('notifMenuMobile');

        // ------------------------------------------------------------------
        // LANGKAH 5: Verifikasi Banner Notifikasi H-1 di Halaman Riwayat Transaksi
        // ------------------------------------------------------------------
        $customerBookingsResponse = $this->actingAs($customer)->get(route('customer.bookings.index'));
        $customerBookingsResponse->assertOk();
        $customerBookingsResponse->assertSee('Trip Berangkat Besok!');
        $customerBookingsResponse->assertSee('DJV-TELKOM46');
        $customerBookingsResponse->assertSee('Lihat Detail');

        // ------------------------------------------------------------------
        // LANGKAH 6: Verifikasi Notifikasi di Halaman Profil Customer
        // ------------------------------------------------------------------
        $profileResponse = $this->actingAs($customer)->get(route('profile.index', ['tab' => 'notifications']));
        $profileResponse->assertOk();
        $profileResponse->assertSee('Pusat Notifikasi &amp; Aktivitas', false);
        $profileResponse->assertSee('Trip Berangkat Besok!');

        // ------------------------------------------------------------------
        // LANGKAH 7: Verifikasi Notifikasi H-1 di Dashboard Utama Admin
        // ------------------------------------------------------------------
        $adminDashboardResponse = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $adminDashboardResponse->assertOk();
        // Cek Header Notifikasi H-1
        $adminDashboardResponse->assertSee('Notifikasi H-1 — Trip Berangkat Besok');
        $adminDashboardResponse->assertSee('Eksplorasi Karimunjawa Bahari');
        $adminDashboardResponse->assertSee('Bambang Supriyadi');
        $adminDashboardResponse->assertSee('K 8899 ZA');
        // Cek Tabel Penumpang H-1
        $adminDashboardResponse->assertSee('Dimas Berlian');
        $adminDashboardResponse->assertSee('DJV-TELKOM46');
        $adminDashboardResponse->assertSee('WA Pengingat');

        // ------------------------------------------------------------------
        // LANGKAH 8: Verifikasi Lonceng Notifikasi di Header Layout Admin
        // ------------------------------------------------------------------
        $adminDashboardResponse->assertSee('adminNotifBtn');
        $adminDashboardResponse->assertSee('adminNotifDropdown');
        $adminDashboardResponse->assertSee('Trip Berangkat Besok (H-1)');

        // ------------------------------------------------------------------
        // LANGKAH 9: Verifikasi Artisan Console Command
        // ------------------------------------------------------------------
        $this->artisan('notifications:send-h1')
            ->assertExitCode(0)
            ->expectsOutputToContain("Proses Notifikasi Pengingat H-1")
            ->expectsOutputToContain("Ditemukan 1 customer yang dijadwalkan berangkat besok.");
    }
}
