<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Package;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'name' => 'Bambang Setiawan',
            'email' => 'bambang@djavatour.com',
            'role' => 'super_admin',
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Rian Adiputra',
            'email' => 'rian.adi@gmail.com',
            'password' => Hash::make('password123'),
            'whatsapp' => '081299881122',
            'role' => 'customer',
            'last_login_at' => Carbon::now()->subMinutes(5),
            'last_seen_at' => Carbon::now()->subMinutes(2),
        ]);
    }

    public function test_admin_can_view_customers_index_with_metrics_and_status(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.customers.index'));

        $response->assertStatus(200);
        $response->assertSee('Akun Customer');
        $response->assertSee('Rian Adiputra');
        $response->assertSee('081299881122');
        $response->assertSee('Aktif Normal');
        $response->assertSee('Online Sekarang');
    }

    public function test_admin_can_view_customer_details_with_booking_history_and_activities(): void
    {
        $vehicle = Vehicle::create([
            'name' => 'HiAce Commuter',
            'slug' => 'hiace-commuter',
            'category' => 'Minibus',
            'seats' => 14,
            'plate_number' => 'N 1234 AB',
            'is_active' => true,
        ]);
        $package = Package::create([
            'name' => 'Bromo Midnight Sunrise',
            'slug' => 'bromo-midnight-sunrise',
            'category' => 'Mountain Trekking & Sunrise',
            'price' => 780000,
            'is_active' => true,
        ]);
        $trip = Trip::create([
            'package_id' => $package->id,
            'vehicle_id' => $vehicle->id,
            'batch_name' => 'Batch 1',
            'batch_number' => 1,
            'departure_date' => Carbon::parse('2026-11-20'),
            'return_date' => Carbon::parse('2026-11-21'),
            'departure_time' => '00:30',
            'pickup_point' => 'Stasiun Malang',
            'quota' => 15,
            'booked_seats' => 2,
            'status' => 'available',
        ]);

        $booking = Booking::create([
            'booking_code' => 'DJT-TESTCUST',
            'user_id' => $this->customer->id,
            'trip_id' => $trip->id,
            'package_id' => $package->id,
            'customer_name' => 'Rian Adiputra',
            'customer_email' => 'rian.adi@gmail.com',
            'customer_phone' => '081299881122',
            'pax' => 2,
            'pickup_location' => 'Stasiun Malang',
            'total_price' => 1500000,
            'down_payment' => 450000,
            'payment_status' => 'partial',
            'booking_status' => 'confirmed',
            'participants' => [
                ['name' => 'Rian Adiputra', 'phone' => '081299881122', 'is_lead' => true],
                ['name' => 'Dina Kartika', 'phone' => null, 'is_lead' => false],
            ],
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.customers.show', $this->customer));

        $response->assertStatus(200);
        $response->assertSee('Rian Adiputra');
        $response->assertSee('DJT-TESTCUST');
        $response->assertSee('Bromo Midnight Sunrise');
        $response->assertSee('Dina Kartika');
        $response->assertSee('Rekam Jejak Aktivitas');
    }

    public function test_admin_can_blacklist_and_unblacklist_customer(): void
    {
        // 1. Blacklist
        $res = $this->actingAs($this->superAdmin)->post(route('admin.customers.toggle-blacklist', $this->customer), [
            'blacklist_reason' => 'Terindikasi melakukan pemesanan palsu dan fraud tiket.',
        ]);

        $res->assertRedirect();
        $this->customer->refresh();
        $this->assertTrue($this->customer->is_blacklisted);
        $this->assertEquals('Terindikasi melakukan pemesanan palsu dan fraud tiket.', $this->customer->blacklist_reason);
        $this->assertNotNull($this->customer->blacklisted_at);

        // 2. Un-blacklist
        $resUnblock = $this->actingAs($this->superAdmin)->post(route('admin.customers.toggle-blacklist', $this->customer));

        $resUnblock->assertRedirect();
        $this->customer->refresh();
        $this->assertFalse($this->customer->is_blacklisted);
        $this->assertNull($this->customer->blacklist_reason);
    }

    public function test_blacklisted_customer_cannot_login(): void
    {
        $this->customer->update([
            'is_blacklisted' => true,
            'blacklist_reason' => 'Pelanggaran keamanan operasional.',
        ]);

        $res = $this->post(route('login.store'), [
            'login' => $this->customer->email,
            'password' => 'password123',
        ]);

        $res->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_admin_can_update_customer_profile_data_but_cannot_change_identity_number(): void
    {
        $this->customer->update(['identity_number' => '3507112233440001']);

        $res = $this->actingAs($this->superAdmin)->put(route('admin.customers.update', $this->customer), [
            'name' => 'Rian Adiputra S.Kom',
            'email' => 'rian.new@gmail.com',
            'whatsapp' => '6281299880000',
            'identity_number' => '9999999999999999', // Admin attempts to change identity number
        ]);

        $res->assertRedirect();
        $this->customer->refresh();
        $this->assertEquals('Rian Adiputra S.Kom', $this->customer->name);
        $this->assertEquals('rian.new@gmail.com', $this->customer->email);
        $this->assertEquals('6281299880000', $this->customer->whatsapp);
        // Identity number must NOT be modified by admin
        $this->assertEquals('3507112233440001', $this->customer->identity_number);
    }

    public function test_re_registering_previously_blacklisted_email_resets_total_data_completely(): void
    {
        // 1. Create package & trip
        $package = Package::create([
            'name' => 'Kawah Ijen Blue Fire',
            'slug' => 'kawah-ijen-blue-fire',
            'category' => 'Mountain Trekking & Sunrise',
            'price' => 650000,
            'is_active' => true,
        ]);
        $trip = Trip::create([
            'package_id' => $package->id,
            'batch_name' => 'Batch Blue Fire',
            'batch_number' => 1,
            'departure_date' => Carbon::parse('2026-11-28'),
            'return_date' => Carbon::parse('2026-11-29'),
            'quota' => 10,
            'booked_seats' => 1,
            'status' => 'available',
        ]);

        // 2. Blacklist user and attach old booking
        $blacklistedUser = User::create([
            'name' => 'User Bermasalah',
            'email' => 'baduser@gmail.com',
            'whatsapp' => '628111222333',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'is_blacklisted' => true,
            'blacklist_reason' => 'Indikasi fraud bukti pembayaran.',
            'blacklisted_at' => now(),
        ]);

        $oldBooking = Booking::create([
            'booking_code' => 'DJT-BADOLD',
            'user_id' => $blacklistedUser->id,
            'trip_id' => $trip->id,
            'package_id' => $package->id,
            'customer_name' => 'User Bermasalah',
            'customer_email' => 'baduser@gmail.com',
            'customer_phone' => '628111222333',
            'pax' => 1,
            'total_price' => 650000,
            'payment_status' => 'unpaid',
            'booking_status' => 'pending',
        ]);

        // 3. User registers again with the SAME email and whatsapp
        $regResponse = $this->post(route('register.store'), [
            'name' => 'User Baru Bersih',
            'whatsapp' => '08111222333',
            'email' => 'baduser@gmail.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
            'terms' => '1',
        ]);

        $regResponse->assertRedirect();

        // 4. Verify old booking has been dissociated & email archived
        $oldBooking->refresh();
        $this->assertNull($oldBooking->user_id);
        $this->assertStringStartsWith('archived_', $oldBooking->customer_email);

        // 5. Verify new fresh account exists and has 0 bookings
        $newUser = User::whereRaw('LOWER(email) = ?', ['baduser@gmail.com'])->first();
        $this->assertNotNull($newUser);
        $this->assertNotEquals($blacklistedUser->id, $newUser->id);
        $this->assertFalse($newUser->is_blacklisted);
        $this->assertNull($newUser->blacklist_reason);
        $this->assertEquals(0, $newUser->bookings()->count());

        // 6. Admin viewing new customer's detail sees 0 bookings
        $showRes = $this->actingAs($this->superAdmin)->get(route('admin.customers.show', $newUser));
        $showRes->assertStatus(200);
        $showRes->assertSee('User Baru Bersih');
        $showRes->assertDontSee('DJT-BADOLD');
        $showRes->assertSee('Customer ini belum memiliki riwayat reservasi atau pemesanan.');
    }

    public function test_admin_can_delete_customer_safely(): void
    {
        $package = Package::create([
            'name' => 'Dieng Escapade',
            'slug' => 'dieng-escapade',
            'category' => 'Mountain Trekking & Sunrise',
            'price' => 850000,
            'is_active' => true,
        ]);
        $trip = Trip::create([
            'package_id' => $package->id,
            'batch_name' => 'Batch 2',
            'batch_number' => 2,
            'departure_date' => Carbon::parse('2026-11-25'),
            'return_date' => Carbon::parse('2026-11-26'),
            'quota' => 10,
            'booked_seats' => 1,
            'status' => 'available',
        ]);

        $booking = Booking::create([
            'booking_code' => 'DJT-DELTEST',
            'user_id' => $this->customer->id,
            'trip_id' => $trip->id,
            'package_id' => $package->id,
            'customer_name' => 'Rian Adiputra',
            'customer_email' => 'rian.adi@gmail.com',
            'customer_phone' => '081299881122',
            'pax' => 1,
            'total_price' => 500000,
            'payment_status' => 'paid',
            'booking_status' => 'completed',
        ]);

        $res = $this->actingAs($this->superAdmin)->delete(route('admin.customers.destroy', $this->customer));

        $res->assertRedirect(route('admin.customers.index'));
        $this->assertDatabaseMissing('users', ['id' => $this->customer->id]);

        // Historical booking remains intact with user_id = null
        $booking->refresh();
        $this->assertNull($booking->user_id);
        $this->assertEquals('DJT-DELTEST', $booking->booking_code);
    }
}
