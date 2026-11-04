<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Itinerary;
use App\Models\Package;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PackageItineraryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $customer;
    private Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'name' => 'Bambang Super Admin',
            'email' => 'superadmin@djavatour.com',
            'role' => 'super_admin',
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Rian Customer',
            'email' => 'customer@gmail.com',
            'role' => 'customer',
        ]);

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
     * Test 1: CRUD Paket Wisata & Itinerary terintegrasi penuh.
     */
    public function test_admin_can_create_view_and_update_package_with_itineraries(): void
    {
        // 1. Create Package with Itinerary
        $response = $this->actingAs($this->superAdmin)->post(route('admin.packages.store'), [
            'name' => 'Bromo Midnight Sunrise Express',
            'category' => 'Mountain Trekking & Sunrise',
            'route_type' => 'gunung',
            'duration' => '2 Hari 1 Malam',
            'price' => 850000,
            'badge' => 'POPULAR',
            'description' => 'Petualangan seru melihat sunrise di Bromo.',
            'is_featured' => 1,
            'is_active' => 1,
            'itineraries' => [
                [
                    'title' => 'Penjemputan & Perjalanan ke Sukapura',
                    'description' => 'Kumpul di meeting point pukul 23:00 dan briefing.',
                ],
                [
                    'title' => 'Sunrise Penanjakan & Kawah Bromo',
                    'description' => 'Naik Jeep 4x4 menuju spot sunrise dan eksplorasi kawah.',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.packages.index'));

        $package = Package::where('name', 'Bromo Midnight Sunrise Express')->first();
        $this->assertNotNull($package);
        $this->assertCount(2, $package->itineraries);
        $this->assertEquals('Penjemputan & Perjalanan ke Sukapura', $package->itineraries[0]->title);
        $this->assertEquals(1, $package->itineraries[0]->day_number);
        $this->assertEquals(2, $package->itineraries[1]->day_number);

        // 2. View Package Show Page in Admin
        $showRes = $this->actingAs($this->superAdmin)->get(route('admin.packages.show', $package->id));
        $showRes->assertStatus(200);
        $showRes->assertSee('Bromo Midnight Sunrise Express');
        $showRes->assertSee('Penjemputan & Perjalanan ke Sukapura');
        $showRes->assertSee('Sunrise Penanjakan & Kawah Bromo');

        // 3. View Package Edit Page in Admin
        $editRes = $this->actingAs($this->superAdmin)->get(route('admin.packages.edit', $package->id));
        $editRes->assertStatus(200);
        $editRes->assertSee('Penjemputan & Perjalanan ke Sukapura');
        $editRes->assertSee('Sunrise Penanjakan & Kawah Bromo');

        // 4. Update Package & Add 3rd Day Itinerary
        $updateRes = $this->actingAs($this->superAdmin)->put(route('admin.packages.update', $package->id), [
            'name' => 'Bromo Midnight Sunrise Express VIP',
            'category' => 'Mountain Trekking & Sunrise',
            'route_type' => 'gunung',
            'duration' => '3 Hari 2 Malam',
            'price' => 1100000,
            'badge' => 'BEST SELLER',
            'description' => 'Paket Bromo VIP lengkap.',
            'is_featured' => 1,
            'is_active' => 1,
            'itineraries' => [
                [
                    'title' => 'Hari 1: Penjemputan Stasiun',
                    'description' => 'Check in hotel transit.',
                ],
                [
                    'title' => 'Hari 2: Sunrise Penanjakan & Lautan Pasir',
                    'description' => 'Sunrise & safari jeep.',
                ],
                [
                    'title' => 'Hari 3: Wisata Air Terjun Madakaripura & Pulang',
                    'description' => 'Treking santai dan drop off.',
                ],
            ],
        ]);

        $updateRes->assertRedirect(route('admin.packages.index'));

        $package->refresh();
        $this->assertEquals('Bromo Midnight Sunrise Express VIP', $package->name);
        $this->assertEquals('3 Hari 2 Malam', $package->duration);
        $this->assertCount(3, $package->itineraries);
        $this->assertEquals('Hari 3: Wisata Air Terjun Madakaripura & Pulang', $package->itineraries[2]->title);

        // 5. Customer Detail Page displays the synced itineraries
        $customerDetailRes = $this->get(route('packages.show', $package->slug));
        $customerDetailRes->assertStatus(200);
        $customerDetailRes->assertSee('Hari 3: Wisata Air Terjun Madakaripura & Pulang');
    }

    /**
     * Test 2: Akses Detail & Booking untuk paket Non-Featured.
     */
    public function test_all_active_packages_even_non_featured_can_be_accessed_and_booked(): void
    {
        $nonFeaturedPackage = Package::create([
            'name' => 'Paket Non-Featured Ekspedisi Merapi',
            'slug' => 'paket-non-featured-ekspedisi-merapi',
            'category' => 'Mountain Trekking & Sunrise',
            'route_type' => 'gunung',
            'duration' => '2 Hari 1 Malam',
            'price' => 750000,
            'is_featured' => false,
            'is_active' => true,
        ]);

        // Customer can open detail page
        $res = $this->get(route('packages.show', $nonFeaturedPackage->slug));
        $res->assertStatus(200);
        $res->assertSee('Paket Non-Featured Ekspedisi Merapi');

        // Create a trip for it
        $depDate = Carbon::now()->addDays(10)->format('Y-m-d');
        $retDate = Carbon::now()->addDays(11)->format('Y-m-d');

        $trip = Trip::create([
            'package_id' => $nonFeaturedPackage->id,
            'batch_name' => 'Batch Merapi #01',
            'departure_date' => $depDate,
            'return_date' => $retDate,
            'quota' => 20,
            'booked_seats' => 0,
            'status' => 'available',
        ]);

        // Customer can open booking form
        $bookingFormRes = $this->get(route('booking.form', [
            'slug' => $nonFeaturedPackage->slug,
            'trip_id' => $trip->id,
            'pax' => 2,
        ]));
        $bookingFormRes->assertStatus(200);
    }

    /**
     * Test 3: Validasi durasi perjalanan dengan jadwal keberangkatan & kepulangan.
     */
    public function test_trip_schedule_is_strictly_bound_to_package_duration(): void
    {
        $package3D = Package::create([
            'name' => 'Karimunjawa 3D2N Island Hopping',
            'slug' => 'karimunjawa-3d2n-island-hopping',
            'category' => 'Wisata Bahari',
            'route_type' => 'bahari',
            'duration' => '3 Hari 2 Malam',
            'price' => 1200000,
            'is_active' => true,
        ]);

        $depDate = Carbon::now()->addDays(5)->format('Y-m-d');

        // 1. Mismatch date range (e.g. 5 days instead of 3 days) triggers validation error
        $wrongRetDate = Carbon::now()->addDays(9)->format('Y-m-d'); // 5 days range
        $failResponse = $this->actingAs($this->superAdmin)->post(route('admin.trips.store'), [
            'package_id' => $package3D->id,
            'batch_name' => 'Batch Test Mismatch',
            'departure_date' => $depDate,
            'return_date' => $wrongRetDate,
            'quota' => 20,
        ]);

        $failResponse->assertSessionHasErrors('return_date');

        // 2. Correct date range (exactly 3 days: day 5 to day 7) succeeds
        $correctRetDate = Carbon::now()->addDays(7)->format('Y-m-d'); // 3 days range
        $successResponse = $this->actingAs($this->superAdmin)->post(route('admin.trips.store'), [
            'package_id' => $package3D->id,
            'batch_name' => 'Batch Test Durasi Cocok',
            'departure_date' => $depDate,
            'return_date' => $correctRetDate,
            'quota' => 35,
        ]);

        $successResponse->assertRedirect();
        $this->assertDatabaseHas('trips', [
            'batch_name' => 'Batch Test Durasi Cocok',
            'quota' => 35,
        ]);

        // 3. Omitting return_date automatically binds to departure_date + (duration - 1)
        $autoDateTrip = $this->actingAs($this->superAdmin)->post(route('admin.trips.store'), [
            'package_id' => $package3D->id,
            'batch_name' => 'Batch Auto Return Date',
            'departure_date' => $depDate,
            'return_date' => null,
            'quota' => 25,
        ]);

        $autoDateTrip->assertRedirect();
        $createdTrip = Trip::where('batch_name', 'Batch Auto Return Date')->first();
        $this->assertNotNull($createdTrip);
        $this->assertEquals($correctRetDate, $createdTrip->return_date->format('Y-m-d'));
    }

    /**
     * Test 4: Booking tanpa armada (Armada tidak dipilih customer).
     */
    public function test_booking_works_cleanly_without_customer_selecting_armada(): void
    {
        $package = Package::create([
            'name' => 'Dieng Sunrise Escapade',
            'slug' => 'dieng-sunrise-escapade',
            'category' => 'Mountain Trekking & Sunrise',
            'route_type' => 'gunung',
            'duration' => '2 Hari 1 Malam',
            'price' => 850000,
            'is_active' => true,
        ]);

        $depDate = Carbon::now()->addDays(4)->format('Y-m-d');
        $retDate = Carbon::now()->addDays(5)->format('Y-m-d');

        $trip = Trip::create([
            'package_id' => $package->id,
            'batch_name' => 'Batch Dieng #01',
            'departure_date' => $depDate,
            'return_date' => $retDate,
            'quota' => 15,
            'booked_seats' => 0,
            'status' => 'available',
        ]);

        // Customer submits booking without selected_armada
        $bookingResponse = $this->actingAs($this->customer)->post(route('booking.store'), [
            'package_id' => $package->id,
            'trip_id' => $trip->id,
            'pax' => 3,
            'customer_name' => 'Rian Adiputra',
            'customer_email' => 'rian@gmail.com',
            'customer_phone' => '081234567890',
            'pickup_location' => 'Stasiun Tugu Yogyakarta',
        ]);

        $bookingResponse->assertRedirect();

        $booking = Booking::where('trip_id', $trip->id)->first();
        $this->assertNotNull($booking);
        $this->assertEquals(3, $booking->pax);

        $trip->refresh();
        $this->assertEquals(3, $trip->booked_seats);
        $this->assertEquals(12, $trip->remaining_seats);
    }

    /**
     * Test 5: Sinkronisasi Dropdown Kategori Wisata Dinamis antara Admin & Customer.
     */
    public function test_dynamic_categories_synchronization_between_admin_and_customer(): void
    {
        // Create custom package with dynamic category
        Package::create([
            'name' => 'Eksotisme Kuliner Kota Lama',
            'slug' => 'eksotisme-kuliner-kota-lama',
            'category' => 'Wisata Kuliner Nusantara',
            'route_type' => 'heritage',
            'duration' => '1 Hari',
            'price' => 350000,
            'is_active' => true,
        ]);

        $availableCategories = Package::getAvailableCategories();
        $this->assertContains('Wisata Bahari & Island Hopping', $availableCategories);
        $this->assertContains('Mountain Trekking & Sunrise', $availableCategories);
        $this->assertContains('Cultural, Heritage & City Tour', $availableCategories);
        $this->assertContains('Wisata Kuliner Nusantara', $availableCategories);

        // 1. Admin Create View contains the dynamic categories
        $adminCreateRes = $this->actingAs($this->superAdmin)->get(route('admin.packages.create'));
        $adminCreateRes->assertStatus(200);
        $adminCreateRes->assertSee('Wisata Kuliner Nusantara');

        // 2. Customer Catalog View contains the dynamic categories
        $catalogRes = $this->get(route('packages.index'));
        $catalogRes->assertStatus(200);
        $catalogRes->assertSee('Wisata Kuliner Nusantara');
        $catalogRes->assertSee('Mountain Trekking & Sunrise');

        // 3. Customer can filter by the exact dynamic category
        $filterRes = $this->get(route('packages.index', ['kategori' => 'Wisata Kuliner Nusantara']));
        $filterRes->assertStatus(200);
        $filterRes->assertSee('Eksotisme Kuliner Kota Lama');
    }

    /**
     * Test 6: Otomatisasi Input Durasi Perjalanan Berdasarkan Hari Itinerary.
     */
    public function test_duration_is_automatically_calculated_based_on_itinerary_days(): void
    {
        // 1. 1 Day itinerary -> automatically "1 Hari"
        $this->actingAs($this->superAdmin)->post(route('admin.packages.store'), [
            'name' => 'One Day Trip Karimunjawa Express',
            'category' => 'Wisata Bahari & Island Hopping',
            'route_type' => 'bahari',
            'price' => 500000,
            'itineraries' => [
                [
                    'title' => 'Island Hopping & Snorkeling Seharian',
                    'description' => 'Snorkeling di Menjangan Kecil dan sunset di Ujung Gelam.',
                ],
            ],
        ]);

        $package1 = Package::where('name', 'One Day Trip Karimunjawa Express')->first();
        $this->assertNotNull($package1);
        $this->assertEquals('1 Hari', $package1->duration);
        $this->assertEquals(1, $package1->durationInDays());

        // 2. Update to 2 days -> automatically "2 Hari 1 Malam"
        $this->actingAs($this->superAdmin)->put(route('admin.packages.update', $package1->id), [
            'name' => 'Karimunjawa Weekend Escape 2D1N',
            'category' => 'Wisata Bahari & Island Hopping',
            'route_type' => 'bahari',
            'price' => 750000,
            'itineraries' => [
                ['title' => 'Hari 1: Penyeberangan & Snorkeling'],
                ['title' => 'Hari 2: Penangkaran Hiu & Kepulangan'],
            ],
        ]);

        $package1->refresh();
        $this->assertEquals('2 Hari 1 Malam', $package1->duration);
        $this->assertEquals(2, $package1->durationInDays());

        // 3. Update to 4 days -> automatically "4 Hari 3 Malam"
        $this->actingAs($this->superAdmin)->put(route('admin.packages.update', $package1->id), [
            'name' => 'Karimunjawa Ultimate Explorer 4D3N',
            'category' => 'Wisata Bahari & Island Hopping',
            'route_type' => 'bahari',
            'price' => 1500000,
            'itineraries' => [
                ['title' => 'Hari 1: Check-in & Sunset'],
                ['title' => 'Hari 2: Snorkeling Timur'],
                ['title' => 'Hari 3: Snorkeling Barat & Hiu'],
                ['title' => 'Hari 4: Souvenir & Check-out'],
            ],
        ]);

        $package1->refresh();
        $this->assertEquals('4 Hari 3 Malam', $package1->duration);
        $this->assertEquals(4, $package1->durationInDays());
    }

    /**
     * Test 7: Dinamisasi Form Data Peserta & Dropdown Titik Penjemputan Otomatis dari Admin.
     */
    public function test_booking_form_displays_dynamic_participant_inputs_and_admin_meeting_points(): void
    {
        $package = Package::create([
            'name' => 'Gunung Sindoro Sunrise Trekking',
            'slug' => 'gunung-sindoro-sunrise-trekking',
            'category' => 'Mountain Trekking & Sunrise',
            'route_type' => 'gunung',
            'duration' => '3 Hari 2 Malam',
            'price' => 1250000,
            'is_active' => true,
        ]);

        $trip = Trip::create([
            'package_id' => $package->id,
            'batch_name' => 'Batch Sindoro #01',
            'departure_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'return_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'quota' => 20,
            'booked_seats' => 0,
            'status' => 'available',
            'meeting_point' => 'Terminal Wonosobo, Alun-alun Wonosobo',
        ]);

        // Access formulir booking with 3 pax
        $response = $this->actingAs($this->customer)->get(route('booking.form', [
            'slug' => $package->slug,
            'trip_id' => $trip->id,
            'pax' => 3,
        ]));

        $response->assertStatus(200);

        // 1. Dropdown titik kumpul otomatis dari admin
        $response->assertSee('Terminal Wonosobo');
        $response->assertSee('Alun-alun Wonosobo');
        $response->assertDontSee('<input id="pickup_location" name="pickup_location" type="text"', false);

        // 2. Form peserta memuat Peserta 1 (Penanggung Jawab), Peserta 2, dan Peserta 3
        $response->assertSee('Data Pemesan Resmi (Peserta 1 - Penanggung Jawab)');
        $response->assertSee('Wajib untuk Perwakilan Pemesan');
        $response->assertSee('Data Peserta 2');
        $response->assertSee('Data Peserta 3');
        $response->assertSee('Data Diri Saja (Tanpa Berkas Identitas)');
    }

    /**
     * Test 8: Penyimpanan data rombongan lengkap dan ketentuan upload identitas hanya untuk perwakilan pemesan.
     */
    public function test_booking_submission_stores_all_pax_participants_and_enforces_identity_upload_for_leader(): void
    {
        Storage::fake('local');

        $package = Package::create([
            'name' => 'Bromo Midnight Adventure',
            'slug' => 'bromo-midnight-adventure',
            'category' => 'Mountain Trekking & Sunrise',
            'route_type' => 'gunung',
            'duration' => '2 Hari 1 Malam',
            'price' => 850000,
            'is_active' => true,
        ]);

        $trip = Trip::create([
            'package_id' => $package->id,
            'batch_name' => 'Batch Bromo #02',
            'departure_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
            'return_date' => Carbon::now()->addDays(4)->format('Y-m-d'),
            'quota' => 10,
            'booked_seats' => 0,
            'status' => 'available',
            'meeting_point' => 'Stasiun Malang Kota Baru',
        ]);

        $fakeKtp = UploadedFile::fake()->image('ktp_leader.jpg');

        $response = $this->actingAs($this->customer)->post(route('booking.store'), [
            'package_id' => $package->id,
            'trip_id' => $trip->id,
            'pax' => 2,
            'customer_name' => 'Dimas Berlian',
            'customer_email' => 'dimas@gmail.com',
            'customer_phone' => '08123456789',
            'pickup_location' => 'Stasiun Malang Kota Baru',
            'identity_file' => $fakeKtp,
            'participants' => [
                2 => [
                    'name' => 'Aulia Rahma',
                    'phone' => '08987654321',
                    'email' => 'Tidak ada alergi',
                ],
            ],
        ]);

        $response->assertRedirect();

        $booking = Booking::where('trip_id', $trip->id)->first();
        $this->assertNotNull($booking);
        $this->assertEquals(2, $booking->pax);
        $this->assertEquals('Dimas Berlian', $booking->customer_name);
        $this->assertEquals('Stasiun Malang Kota Baru', $booking->pickup_location);
        $this->assertNotNull($booking->identity_file_path);

        // Participants array contains both leader and member
        $this->assertCount(2, $booking->participants);
        $this->assertEquals('Dimas Berlian', $booking->participants[0]['name']);
        $this->assertTrue($booking->participants[0]['is_lead']);
        $this->assertEquals('Aulia Rahma', $booking->participants[1]['name']);
        $this->assertFalse($booking->participants[1]['is_lead']);

        // Check manifest view shows member name
        $manifestRes = $this->actingAs($this->superAdmin)->get(route('admin.manifest.index', ['trip_id' => $trip->id]));
        $manifestRes->assertStatus(200);
        $manifestRes->assertSee('Dimas Berlian');
        $manifestRes->assertSee('Aulia Rahma');
    }
}

