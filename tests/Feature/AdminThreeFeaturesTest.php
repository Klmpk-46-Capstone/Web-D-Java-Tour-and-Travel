<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Package;
use App\Models\Transaction;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminThreeFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $adminOps;
    protected User $customer;
    protected Package $package;
    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup users
        $this->superAdmin = User::factory()->create([
            'name' => 'Bambang Super Admin',
            'email' => 'super@djavatour.com',
            'role' => 'super_admin',
        ]);

        $this->adminOps = User::factory()->create([
            'name' => 'Ahmad Ops',
            'email' => 'ops@djavatour.com',
            'role' => 'admin_ops',
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Rian Customer',
            'email' => 'rian@customer.com',
            'role' => 'customer',
        ]);

        // 2. Setup package
        $this->package = Package::create([
            'name' => 'Karimunjawa Paradise 3D2N',
            'slug' => 'karimunjawa-paradise-3d2n',
            'category' => 'Wisata Bahari',
            'route_type' => 'bahari',
            'duration' => '3 Hari 2 Malam',
            'price' => 1250000,
            'is_active' => true,
        ]);

        // 3. Setup vehicle
        $this->vehicle = Vehicle::create([
            'name' => 'Toyota HiAce Commuter VIP',
            'slug' => 'toyota-hiace-commuter-vip',
            'category' => 'Minibus',
            'seats' => 14,
            'plate_number' => 'L 7788 DJ',
            'is_active' => true,
        ]);
    }

    /**
     * Test 1: Role Matrix Authorization.
     * - Guest is redirected to gate-auth
     * - Customer is blocked from admin area
     * - admin_ops can access manifest, but blocked from trips & reports
     * - super_admin can access trips, manifest, and reports
     */
    public function test_role_matrix_authorization(): void
    {
        // 1. Guest redirect
        $this->get('/admin/trips')->assertRedirect('/admin/gate-auth');
        $this->get('/admin/manifest')->assertRedirect('/admin/gate-auth');
        $this->get('/admin/reports')->assertRedirect('/admin/gate-auth');

        // 2. Customer rejected
        $this->actingAs($this->customer)->get('/admin/trips')->assertRedirect('/');
        $this->actingAs($this->customer)->get('/admin/manifest')->assertRedirect('/');
        $this->actingAs($this->customer)->get('/admin/reports')->assertRedirect('/');

        // 3. Admin Ops access (all features accessible for checking)
        $this->actingAs($this->adminOps)->get('/admin/manifest')->assertStatus(200);
        $this->actingAs($this->adminOps)->get('/admin/trips')->assertStatus(200);
        $this->actingAs($this->adminOps)->get('/admin/reports')->assertStatus(200);

        // Sidebar renders all items for admin_ops
        $dashRes = $this->actingAs($this->adminOps)->get('/admin/dashboard');
        $dashRes->assertStatus(200);
        $dashRes->assertSee('Jadwal &amp; Detail Trip', false);
        $dashRes->assertSee('Laporan Transaksi &amp; Keuangan', false);
        $dashRes->assertSee('Manifes Penumpang', false);

        // 4. Super Admin access
        $this->actingAs($this->superAdmin)->get('/admin/trips')->assertStatus(200);
        $this->actingAs($this->superAdmin)->get('/admin/manifest')->assertStatus(200);
        $this->actingAs($this->superAdmin)->get('/admin/reports')->assertStatus(200);
    }

    /**
     * Test 2: Manajemen Jadwal & Detail Trip - Create, Update, & Conflicts
     */
    public function test_super_admin_trip_management_and_validation(): void
    {
        $depDate = Carbon::now()->addDays(5)->format('Y-m-d');
        $retDate = Carbon::now()->addDays(7)->format('Y-m-d');

        // 1. Create Trip successfully
        $createResponse = $this->actingAs($this->superAdmin)->post('/admin/trips', [
            'package_id' => $this->package->id,
            'batch_name' => 'Batch Liburan Eksklusif #01',
            'departure_date' => $depDate,
            'return_date' => $retDate,
            'quota' => 12,
            'vehicle_id' => $this->vehicle->id,
            'driver_name' => 'Pak Joko Widodo',
            'departure_time' => '07:30',
            'meeting_point' => 'Stasiun Pasar Turi Gate 2',
        ]);

        $createResponse->assertRedirect();
        $this->assertDatabaseHas('trips', [
            'batch_name' => 'Batch Liburan Eksklusif #01',
            'quota' => 12,
            'vehicle_id' => $this->vehicle->id,
            'driver_name' => 'Pak Joko Widodo',
        ]);

        $trip = Trip::where('batch_name', 'Batch Liburan Eksklusif #01')->first();
        $this->assertNotNull($trip);

        // 2. Update Trip
        $updateResponse = $this->actingAs($this->superAdmin)->put('/admin/trips/' . $trip->id, [
            'package_id' => $this->package->id,
            'batch_name' => 'Batch Liburan Eksklusif #01 (Updated)',
            'departure_date' => $depDate,
            'return_date' => $retDate,
            'quota' => 14,
            'vehicle_id' => $this->vehicle->id,
            'driver_name' => 'Pak Bambang S.',
            'departure_time' => '08:00',
            'meeting_point' => 'Terminal Purabaya Pintu 1',
            'status' => 'available',
        ]);

        $updateResponse->assertRedirect();
        $trip->refresh();
        $this->assertEquals('Batch Liburan Eksklusif #01 (Updated)', $trip->batch_name);
        $this->assertEquals(14, $trip->quota);
        $this->assertEquals('Pak Bambang S.', $trip->driver_name);
        $this->assertEquals('Terminal Purabaya Pintu 1', $trip->meeting_point);

        // 3. Validation: Quota exceeds vehicle seats (14 seats, request 20)
        $invalidQuotaResponse = $this->actingAs($this->superAdmin)->post('/admin/trips', [
            'package_id' => $this->package->id,
            'batch_name' => 'Batch Over Quota',
            'departure_date' => Carbon::now()->addDays(20)->format('Y-m-d'),
            'return_date' => Carbon::now()->addDays(22)->format('Y-m-d'),
            'quota' => 20, // exceeds vehicle seats (14)
            'vehicle_id' => $this->vehicle->id,
            'driver_name' => 'Pak Agus',
        ]);
        $invalidQuotaResponse->assertSessionHasErrors(['quota']);

        // 4. Overlap Conflict: Same vehicle overlapping date
        $conflictVehicleResponse = $this->actingAs($this->superAdmin)->post('/admin/trips', [
            'package_id' => $this->package->id,
            'batch_name' => 'Batch Bentrok Armada',
            'departure_date' => $depDate, // overlaps with first trip
            'return_date' => $retDate,
            'quota' => 10,
            'vehicle_id' => $this->vehicle->id,
            'driver_name' => 'Driver Lain',
        ]);
        $conflictVehicleResponse->assertSessionHasErrors(['vehicle_id']);

        // 5. Overlap Conflict: Same driver overlapping date
        $secondVehicle = Vehicle::create([
            'name' => 'Toyota HiAce Second',
            'slug' => 'hiace-second',
            'category' => 'Minibus',
            'seats' => 14,
            'is_active' => true,
        ]);

        $conflictDriverResponse = $this->actingAs($this->superAdmin)->post('/admin/trips', [
            'package_id' => $this->package->id,
            'batch_name' => 'Batch Bentrok Driver',
            'departure_date' => $depDate, // overlaps with first trip
            'return_date' => $retDate,
            'quota' => 10,
            'vehicle_id' => $secondVehicle->id,
            'driver_name' => 'Pak Bambang S.', // driver already assigned on trip 1
        ]);
        $conflictDriverResponse->assertSessionHasErrors(['driver_name']);
    }

    /**
     * Test 3: Trip Quota Auto Sync
     */
    public function test_trip_quota_sync(): void
    {
        $trip = Trip::create([
            'package_id' => $this->package->id,
            'batch_name' => 'Batch Sync Test',
            'departure_date' => Carbon::now()->addDays(10),
            'quota' => 20,
            'booked_seats' => 0,
            'status' => 'available',
        ]);

        // Create confirmed booking
        Booking::create([
            'booking_code' => 'BK-SYNC-01',
            'user_id' => $this->customer->id,
            'trip_id' => $trip->id,
            'package_id' => $this->package->id,
            'customer_name' => 'Peserta Satu',
            'customer_email' => 'peserta1@example.com',
            'customer_phone' => '08123456789',
            'pax' => 3,
            'total_price' => 3750000,
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        // Sync via POST /admin/trips/sync
        $res = $this->actingAs($this->superAdmin)->post('/admin/trips/sync');
        $res->assertRedirect();

        $trip->refresh();
        $this->assertEquals(3, $trip->booked_seats);
    }

    /**
     * Test 4: Manifes Penumpang - Check-in Toggle & Walk-in Passenger
     */
    public function test_manifest_check_in_and_offline_passenger(): void
    {
        $trip = Trip::create([
            'package_id' => $this->package->id,
            'batch_name' => 'Batch Manifest Test',
            'departure_date' => Carbon::now()->addDays(3),
            'quota' => 14,
            'booked_seats' => 2,
            'status' => 'available',
        ]);

        $booking = Booking::create([
            'booking_code' => 'BK-MNF-001',
            'user_id' => $this->customer->id,
            'trip_id' => $trip->id,
            'package_id' => $this->package->id,
            'customer_name' => 'Budi Passenger',
            'customer_email' => 'budi@example.com',
            'customer_phone' => '08123456789',
            'pax' => 2,
            'total_price' => 2500000,
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
            'checked_in_at' => null,
        ]);

        // 1. Admin Ops toggles check-in ON
        $checkInResponse = $this->actingAs($this->adminOps)->post('/admin/manifest/' . $booking->id . '/check-in');
        $checkInResponse->assertRedirect();

        $booking->refresh();
        $this->assertNotNull($booking->checked_in_at);
        $this->assertEquals($this->adminOps->id, $booking->checked_in_by);

        // 2. Admin Ops toggles check-in OFF
        $checkOutResponse = $this->actingAs($this->adminOps)->post('/admin/manifest/' . $booking->id . '/check-in');
        $checkOutResponse->assertRedirect();

        $booking->refresh();
        $this->assertNull($booking->checked_in_at);

        // 3. Admin Ops registers Walk-in Passenger
        $walkInResponse = $this->actingAs($this->adminOps)->post('/admin/manifest/passengers', [
            'trip_id' => $trip->id,
            'customer_name' => 'Walk-in Guest John Doe',
            'customer_phone' => '085566778899',
            'customer_email' => 'walkin@example.com',
            'pax' => 2,
            'payment_status' => 'paid',
            'check_in_now' => true,
            'notes' => 'Bayar cash di dermaga',
        ]);

        $walkInResponse->assertRedirect();

        // Check new booking in DB
        $walkInBooking = Booking::where('customer_name', 'Walk-in Guest John Doe')->first();
        $this->assertNotNull($walkInBooking);
        $this->assertEquals('paid', $walkInBooking->payment_status);
        $this->assertEquals('confirmed', $walkInBooking->booking_status);
        $this->assertEquals(2, $walkInBooking->pax);
        $this->assertNotNull($walkInBooking->checked_in_at);

        // Check cash transaction
        $transaction = Transaction::where('booking_id', $walkInBooking->id)->first();
        $this->assertNotNull($transaction);
        $this->assertEquals('success', $transaction->status);
        $this->assertEquals(2 * $this->package->price, $transaction->amount);

        // Trip booked_seats updated
        $trip->refresh();
        $this->assertEquals(4, $trip->booked_seats); // 2 initial + 2 walk-in
    }

    /**
     * Test 5: Manifes CSV Export
     */
    public function test_manifest_export_csv(): void
    {
        $trip = Trip::create([
            'package_id' => $this->package->id,
            'batch_name' => 'Batch Export Test',
            'departure_date' => Carbon::now()->addDays(2),
            'quota' => 10,
            'booked_seats' => 1,
            'status' => 'available',
        ]);

        Booking::create([
            'booking_code' => 'BK-EXP-999',
            'user_id' => $this->customer->id,
            'trip_id' => $trip->id,
            'package_id' => $this->package->id,
            'customer_name' => 'Export Tester Name',
            'customer_email' => 'export@example.com',
            'customer_phone' => '08123456789',
            'pax' => 1,
            'total_price' => 1250000,
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->adminOps)->get('/admin/manifest/export?trip_id=' . $trip->id);
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));

        // Stream output contains header and row
        ob_start();
        $response->sendContent();
        $csvContent = ob_get_clean();

        $this->assertStringContainsString('BK-EXP-999', $csvContent);
        $this->assertStringContainsString('Export Tester Name', $csvContent);
    }

    /**
     * Test 6: Laporan Transaksi - Reconcile & CSV Export
     */
    public function test_reports_reconcile_and_export_csv(): void
    {
        $booking = Booking::create([
            'booking_code' => 'BK-REP-01',
            'user_id' => $this->customer->id,
            'package_id' => $this->package->id,
            'customer_name' => 'Laporan Tester',
            'customer_email' => 'laporan@example.com',
            'customer_phone' => '08123456789',
            'pax' => 1,
            'total_price' => 1250000,
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        Transaction::create([
            'transaction_code' => 'INV-REP-001',
            'booking_id' => $booking->id,
            'amount' => 1250000,
            'payment_method' => 'BCA Virtual Account',
            'status' => 'success',
            'paid_at' => Carbon::now(),
        ]);

        // 1. Access reports page with 200
        $pageResponse = $this->actingAs($this->superAdmin)->get('/admin/reports');
        $pageResponse->assertStatus(200);

        // 2. Bank reconcile action
        $reconcileResponse = $this->actingAs($this->superAdmin)->post('/admin/reports/reconcile');
        $reconcileResponse->assertRedirect();
        $reconcileResponse->assertSessionHas('success');

        // 3. Export CSV
        $exportResponse = $this->actingAs($this->superAdmin)->get('/admin/reports/export-csv');
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $exportResponse->headers->get('Content-Type'));

        ob_start();
        $exportResponse->sendContent();
        $csvContent = ob_get_clean();

        $this->assertStringContainsString('INV-REP-001', $csvContent);
        $this->assertStringContainsString('Laporan Tester', $csvContent);
    }

    /**
     * Test 7: Quick Duplicate Batch & Safe Trip Deletion
     */
    public function test_quick_add_batch_and_trip_deletion_safety(): void
    {
        $trip = Trip::create([
            'package_id' => $this->package->id,
            'batch_name' => 'Batch Original',
            'departure_date' => Carbon::now()->addDays(5),
            'return_date' => Carbon::now()->addDays(7),
            'quota' => 15,
            'booked_seats' => 0,
            'status' => 'available',
            'meeting_point' => 'Bandara Juanda T1',
            'departure_time' => '06:00',
        ]);

        // 1. Quick add batch (duplicate +7 days)
        $dupResponse = $this->actingAs($this->superAdmin)->post('/admin/trips/quick-add-batch', [
            'trip_id' => $trip->id,
            'days_ahead' => 7,
        ]);
        $dupResponse->assertRedirect();

        $expectedDate = Carbon::parse($trip->departure_date)->addDays(7)->format('Y-m-d');
        $this->assertDatabaseHas('trips', [
            'package_id' => $this->package->id,
            'quota' => 15,
            'meeting_point' => 'Bandara Juanda T1',
            'departure_time' => '06:00',
        ]);

        // 2. Safe deletion: Cannot delete trip if booking exists
        Booking::create([
            'booking_code' => 'BK-DEL-SAFETY',
            'user_id' => $this->customer->id,
            'trip_id' => $trip->id,
            'package_id' => $this->package->id,
            'customer_name' => 'Passenger Bound',
            'customer_email' => 'passenger@example.com',
            'customer_phone' => '08123456789',
            'pax' => 1,
            'total_price' => 1250000,
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        $failDelete = $this->actingAs($this->superAdmin)->delete('/admin/trips/' . $trip->id);
        $failDelete->assertRedirect();
        $failDelete->assertSessionHas('error');
        $this->assertDatabaseHas('trips', ['id' => $trip->id]);

        // 3. Trip without booking can be safely deleted
        $emptyTrip = Trip::create([
            'package_id' => $this->package->id,
            'batch_name' => 'Empty Batch Deletable',
            'departure_date' => Carbon::now()->addDays(30),
            'quota' => 10,
            'booked_seats' => 0,
            'status' => 'available',
        ]);

        $successDelete = $this->actingAs($this->superAdmin)->delete('/admin/trips/' . $emptyTrip->id);
        $successDelete->assertRedirect();
        $successDelete->assertSessionHas('success');
        $this->assertDatabaseMissing('trips', ['id' => $emptyTrip->id]);
    }
}
