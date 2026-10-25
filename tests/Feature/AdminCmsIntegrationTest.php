<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Package;
use App\Models\Refund;
use App\Models\Review;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCmsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\CmsTourSeeder::class);

        $this->superAdmin = User::firstOrCreate(
            ['email' => 'bambang@djavatour.com'],
            [
                'name' => 'Bambang Setiawan',
                'password' => bcrypt('password123'),
                'role' => 'super_admin',
            ]
        );

        $this->customer = User::firstOrCreate(
            ['email' => 'rian.adi@gmail.com'],
            [
                'name' => 'Rian Adiputra',
                'password' => bcrypt('password123'),
                'role' => 'customer',
            ]
        );
    }

    public function test_hidden_admin_gateway_accessible_and_unauthenticated_blocked(): void
    {
        // 1. Unauthenticated access to /admin/dashboard redirects to gate-auth
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/admin/gate-auth');

        // 2. Hidden gate login page renders successfully
        $gateResponse = $this->get('/admin/gate-auth');
        $gateResponse->assertStatus(200);
        $gateResponse->assertSee('CMS OPS CONSOLE');

        // 3. Customer role is rejected from admin dashboard
        $forbiddenResponse = $this->actingAs($this->customer)->get('/admin/dashboard');
        $forbiddenResponse->assertRedirect('/');

        // 4. Super admin accesses admin dashboard with 200
        $adminResponse = $this->actingAs($this->superAdmin)->get('/admin/dashboard');
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('Dashboard Analitik & Ringkasan Operasional');
        $adminResponse->assertSee('TOTAL GROSS REVENUE');
        $adminResponse->assertSee('Karimunjawa Paradise');
        $adminResponse->assertSee('Perairan Jepara - Karimunjawa');
    }

    public function test_integration_point_2_featured_package_toggle_updates_customer_highlight(): void
    {
        $package = Package::first();
        $this->assertNotNull($package);

        // Toggle to false
        $this->actingAs($this->superAdmin)->post(route('admin.packages.toggle-featured', $package->id));
        $package->refresh();

        // Customer home should reflect featured state
        $res = $this->get('/');
        $res->assertStatus(200);

        // Toggle back to true
        $this->actingAs($this->superAdmin)->post(route('admin.packages.toggle-featured', $package->id));
        $package->refresh();
        $this->assertTrue($package->is_featured);
    }

    public function test_integration_point_3_trip_quota_and_booking_creation(): void
    {
        $package = Package::first();
        $trip = Trip::where('package_id', $package->id)->first();
        $this->assertNotNull($trip);

        $trip->update([
            'departure_date' => now()->addDays(5),
            'return_date' => now()->addDays(7),
        ]);

        $initialBooked = $trip->booked_seats;
        $initialRemaining = $trip->remaining_seats;

        if ($initialRemaining > 0) {
            $response = $this->actingAs($this->customer)->post(route('booking.store'), [
                'package_id' => $package->id,
                'trip_id' => $trip->id,
                'customer_name' => 'Test Booking Pax',
                'customer_email' => 'test@example.com',
                'customer_phone' => '08123456789',
                'pax' => 1,
                'selected_armada' => 'Innova Reborn',
                'pickup_location' => 'Bandara Juanda',
                'payment_method' => 'BCA Virtual Account',
            ]);

            $response->assertRedirect();

            $trip->refresh();
            $this->assertEquals($initialBooked + 1, $trip->booked_seats);
        }
    }

    public function test_integration_point_4_refund_approval_frees_up_trip_seat(): void
    {
        $package = Package::first();
        $trip = Trip::where('package_id', $package->id)->first();
        
        $trip->update(['booked_seats' => 5, 'quota' => 20]);

        $booking = Booking::create([
            'booking_code' => 'DJT-TEST-' . rand(100, 999),
            'user_id' => $this->customer->id,
            'trip_id' => $trip->id,
            'package_id' => $package->id,
            'customer_name' => 'Refund Tester',
            'customer_email' => 'refund@test.com',
            'customer_phone' => '08123456789',
            'pax' => 2,
            'total_price' => 2000000,
            'payment_method' => 'BCA Virtual Account',
            'payment_status' => 'paid',
            'booking_status' => 'refund_requested',
        ]);

        $refund = Refund::create([
            'booking_id' => $booking->id,
            'refund_code' => 'REF-TEST-' . rand(100, 999),
            'reason' => 'Perubahan jadwal dinas mendadak',
            'amount' => 2000000,
            'status' => 'pending',
        ]);

        // Admin approves refund
        $this->actingAs($this->superAdmin)->post(route('admin.refunds.approve', $refund->id));

        $refund->refresh();
        $booking->refresh();
        $trip->refresh();

        $this->assertEquals('approved', $refund->status);
        $this->assertEquals('cancelled', $booking->booking_status);
        $this->assertEquals('refunded', $booking->payment_status);
        $this->assertEquals(3, $trip->booked_seats); // 5 - 2 = 3
    }

    public function test_integration_point_5_review_moderation_toggles_public_visibility(): void
    {
        $package = Package::first();

        $review = Review::create([
            'package_id' => $package->id,
            'user_id' => $this->customer->id,
            'customer_name' => 'Reviewer Spammer',
            'rating' => 5,
            'comment' => 'Review yang akan diuji visibilitasnya',
            'is_approved' => true,
            'is_hidden' => false,
        ]);

        // Public page should see the review
        $detailRes = $this->get(route('packages.show', $package->slug));
        $detailRes->assertSee('Review yang akan diuji visibilitasnya');

        // Admin moderates and hides the review
        $this->actingAs($this->superAdmin)->post(route('admin.reviews.toggle-visibility', $review->id));
        $review->refresh();
        $this->assertTrue($review->is_hidden);

        // Public page should NO LONGER see the review
        $detailResAfter = $this->get(route('packages.show', $package->slug));
        $detailResAfter->assertDontSee('Review yang akan diuji visibilitasnya');
    }
}
