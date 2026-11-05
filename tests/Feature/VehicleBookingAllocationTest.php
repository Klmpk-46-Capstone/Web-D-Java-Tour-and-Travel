<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Package;
use App\Models\Refund;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleUnit;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleBookingAllocationTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private Vehicle $vehicle;

    private VehicleUnit $firstUnit;

    private VehicleUnit $secondUnit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create(['role' => 'customer']);

        $this->vehicle = Vehicle::create([
            'name' => 'Rental Fleet Avanza',
            'slug' => 'rental-fleet-avanza',
            'category' => 'mpv',
            'seats' => 7,
            'transmission' => 'Matic',
            'price_per_day' => 500000,
            'price_with_driver' => 550000,
            'includes_driver' => true,
            'is_active' => true,
        ]);

        $this->firstUnit = VehicleUnit::create([
            'vehicle_id' => $this->vehicle->id,
            'plate_number' => 'B 6101 RNT',
            'status' => VehicleUnit::STATUS_ACTIVE,
        ]);

        $this->secondUnit = VehicleUnit::create([
            'vehicle_id' => $this->vehicle->id,
            'plate_number' => 'B 6102 RNT',
            'status' => VehicleUnit::STATUS_ACTIVE,
        ]);
    }

    public function test_rental_booking_allocates_units_and_rejects_when_exhausted(): void
    {
        $rentalDate = Carbon::today()->addDays(6)->format('Y-m-d');

        $payload = [
            'rental_date' => $rentalDate,
            'duration' => 2,
            'pax' => 3,
            'rental_option' => 'with_driver',
            'customer_name' => 'Rental Customer',
            'customer_phone' => '081234567890',
            'customer_email' => 'rental@example.com',
            'delivery_method' => 'self_pickup',
            'pickup_location' => 'Bandara Juanda',
        ];

        $this->actingAs($this->customer)
            ->post(route('armada.booking.store', $this->vehicle), $payload)
            ->assertRedirect();

        $firstBooking = Booking::orderBy('id')->firstOrFail();
        $this->assertSame($this->firstUnit->id, $firstBooking->vehicle_unit_id);

        $this->actingAs($this->customer)
            ->post(route('armada.booking.store', $this->vehicle), $payload)
            ->assertRedirect();

        $secondBooking = Booking::orderByDesc('id')->firstOrFail();
        $this->assertSame($this->secondUnit->id, $secondBooking->vehicle_unit_id);

        $this->actingAs($this->customer)
            ->post(route('armada.booking.store', $this->vehicle), $payload)
            ->assertSessionHasErrors('rental_date');

        $this->assertSame(2, Booking::count());
    }

    public function test_vehicle_without_active_units_rejects_rental_booking(): void
    {
        $emptyVehicle = Vehicle::create([
            'name' => 'Empty Rental Vehicle',
            'slug' => 'empty-rental-vehicle',
            'category' => 'mpv',
            'seats' => 7,
            'price_per_day' => 500000,
            'price_with_driver' => 550000,
            'includes_driver' => true,
            'is_active' => true,
        ]);

        $this->actingAs($this->customer)
            ->post(route('armada.booking.store', $emptyVehicle), [
                'rental_date' => Carbon::today()->addDays(6)->format('Y-m-d'),
                'duration' => 1,
                'pax' => 1,
                'rental_option' => 'with_driver',
                'customer_name' => 'Rental Customer',
                'customer_phone' => '081234567890',
                'customer_email' => 'rental@example.com',
            ])
            ->assertSessionHasErrors('rental_date');

        $this->assertSame(0, Booking::count());
    }

    public function test_plate_is_revealed_only_after_partial_or_paid_payment(): void
    {
        $booking = $this->makeRentalBooking($this->firstUnit, 'pending');

        $this->assertNull($booking->revealed_plate);

        $booking->update(['payment_status' => 'partial']);
        $this->assertSame($this->firstUnit->plate_number, $booking->fresh()->revealed_plate);

        $booking->update(['booking_status' => 'cancelled']);
        $this->assertNull($booking->fresh()->revealed_plate);
    }

    public function test_plate_from_trip_is_revealed_for_package_booking_after_dp(): void
    {
        $package = Package::create([
            'name' => 'Plate Reveal Package',
            'slug' => 'plate-reveal-package',
            'category' => 'Wisata Bahari',
            'route_type' => 'bahari',
            'duration' => '2 Hari 1 Malam',
            'price' => 1000000,
            'is_active' => true,
        ]);

        $trip = Trip::create([
            'package_id' => $package->id,
            'batch_name' => 'Plate Reveal Batch',
            'departure_date' => Carbon::today()->addDays(10),
            'return_date' => Carbon::today()->addDays(11),
            'quota' => 14,
            'booked_seats' => 1,
            'status' => 'available',
            'vehicle_id' => $this->vehicle->id,
            'vehicle_unit_id' => $this->secondUnit->id,
        ]);

        $booking = Booking::create([
            'booking_code' => 'BK-PLATE-PACKAGE',
            'user_id' => $this->customer->id,
            'trip_id' => $trip->id,
            'package_id' => $package->id,
            'pax' => 1,
            'customer_name' => 'Package Customer',
            'customer_email' => 'package@example.com',
            'customer_phone' => '081234567891',
            'total_price' => 1000000,
            'payment_method' => 'Xendit',
            'payment_status' => 'partial',
            'booking_status' => 'confirmed',
        ]);

        $this->assertSame($this->secondUnit->plate_number, $booking->revealed_plate);

        Refund::create([
            'booking_id' => $booking->id,
            'refund_code' => 'REF-PLATE-PACKAGE',
            'reason' => 'Test refund',
            'amount' => 300000,
            'status' => 'approved',
        ]);

        $booking->unsetRelation('refund');

        $this->assertNull($booking->revealed_plate);
    }

    public function test_customer_can_open_booking_detail_and_invoice_with_revealed_plate(): void
    {
        $booking = $this->makeRentalBooking($this->firstUnit, 'partial');

        $this->actingAs($this->customer)
            ->get(route('customer.bookings.detail', $booking->booking_code))
            ->assertOk()
            ->assertSee($this->firstUnit->plate_number);

        $this->actingAs($this->customer)
            ->get(route('customer.bookings.invoice', $booking->booking_code))
            ->assertOk()
            ->assertSee($this->firstUnit->plate_number);
    }

    public function test_rental_catalog_and_form_hide_vehicle_type_without_active_unit(): void
    {
        $emptyVehicle = Vehicle::create([
            'name' => 'Empty Catalog Vehicle',
            'slug' => 'empty-catalog-vehicle',
            'category' => 'mpv',
            'seats' => 7,
            'price_per_day' => 500000,
            'price_with_driver' => 550000,
            'includes_driver' => true,
            'is_active' => true,
        ]);

        $this->get(route('armada'))
            ->assertOk()
            ->assertSee($this->vehicle->name)
            ->assertDontSee($emptyVehicle->name);

        $this->get(route('armada.show', $emptyVehicle))->assertNotFound();

        $this->get(route('armada.booking.form', [
            'vehicle' => $emptyVehicle,
            'rental_date' => Carbon::today()->addDays(6)->format('Y-m-d'),
        ]))->assertNotFound();
    }

    public function test_booking_form_redirects_when_no_unit_is_available_for_date(): void
    {
        $date = Carbon::today()->addDays(3);

        $this->makeRentalBooking($this->firstUnit, 'pending');
        $this->makeRentalBooking($this->secondUnit, 'pending');

        $this->get(route('armada.booking.form', [
            'vehicle' => $this->vehicle,
            'rental_date' => $date->format('Y-m-d'),
            'duration' => 1,
        ]))->assertRedirect(route('armada.show', [
            'vehicle' => $this->vehicle->slug,
            'rental_date' => $date->toDateString(),
        ]));
    }

    public function test_availability_endpoint_never_returns_plate(): void
    {
        $date = Carbon::today()->addDays(12)->format('Y-m-d');

        $response = $this->getJson(route('armada.availability', [
            'vehicle' => $this->vehicle,
            'rental_date' => $date,
            'duration' => 2,
        ]));

        $response->assertStatus(200);
        $response->assertJsonPath('available', 2);
        $response->assertJsonPath('total', 2);
        $this->assertStringNotContainsString('B 6101 RNT', $response->getContent());
        $this->assertStringNotContainsString('B 6102 RNT', $response->getContent());
    }

    private function makeRentalBooking(VehicleUnit $unit, string $paymentStatus): Booking
    {
        return Booking::create([
            'booking_code' => 'BK-PLATE-' . strtoupper(uniqid()),
            'user_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'vehicle_unit_id' => $unit->id,
            'rental_date' => Carbon::today()->addDays(3),
            'duration' => 1,
            'pax' => 1,
            'rate' => 500000,
            'total_price' => 500000,
            'customer_name' => 'Plate Customer',
            'customer_email' => 'plate@example.com',
            'customer_phone' => '081234567892',
            'payment_method' => 'Xendit',
            'payment_status' => $paymentStatus,
            'booking_status' => 'confirmed',
        ]);
    }
}
