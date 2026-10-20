<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\Package;
use App\Models\Refund;
use App\Models\Review;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->validate([
            'pickup' => ['nullable', 'string', 'max:150'],
            'destination' => ['nullable', 'string', 'max:150'],
            'departure_date' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
            'passengers' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        // 1. Featured Packages (Rekomendasi Utama) - Data-driven from CMS toggle
        $featuredPackages = Package::with(['trips', 'itineraries', 'reviews'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->latest('id')
            ->get();

        // 2. All Active Packages
        $packages = Package::with(['trips', 'itineraries', 'reviews'])
            ->where('is_active', true)
            ->when(filled($search['destination'] ?? null), function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search['destination'] . '%')
                  ->orWhere('category', 'like', '%' . $search['destination'] . '%');
            })
            ->latest('id')
            ->limit(6)
            ->get();

        $destinations = Destination::query()
            ->where('is_active', true)
            ->when(
                filled($search['destination'] ?? null),
                function ($query) use ($search) {
                    $query->where(
                        'name',
                        'like',
                        '%' . $search['destination'] . '%'
                    );
                }
            )
            ->orderBy('name')
            ->limit(8)
            ->get();

        $vehicles = Vehicle::query()
            ->where('is_active', true)
            ->when(
                isset($search['passengers']),
                function ($query) use ($search) {
                    $query->where(
                        'seats',
                        '>=',
                        (int) $search['passengers']
                    );
                }
            )
            ->orderBy('price_per_day')
            ->limit(6)
            ->get();

        return view('pages.dashboard.beranda.index', [
            'featuredPackages' => $featuredPackages,
            'packages' => $packages,
            'destinations' => $destinations,
            'vehicles' => $vehicles,
            'search' => $search,
        ]);
    }

    // Customer Package Catalog
    public function packages(Request $request): View
    {
        $category = $request->query('kategori');
        $route = $request->query('rute');
        $q = $request->query('q');

        $packages = Package::with(['trips', 'itineraries', 'reviews'])
            ->where('is_active', true)
            ->when($category, fn($query) => $query->where('category', $category))
            ->when($route, fn($query) => $query->where('route_type', $route))
            ->when($q, fn($query) => $query->where('name', 'like', "%{$q}%")->orWhere('description', 'like', "%{$q}%"))
            ->latest('id')
            ->paginate(9);

        return view('pages.customer.packages', compact('packages', 'category', 'route', 'q'));
    }

    // Customer Package Detail with Itinerary, Dynamic Schedule/Quota & Reviews
    public function packageDetail(string $slug): View
    {
        $package = Package::with([
            'itineraries',
            'trips' => fn($q) => $q->orderBy('departure_date', 'asc'),
            'reviews' => fn($q) => $q->where('is_approved', true)->where('is_hidden', false)->latest('id'),
        ])->where('slug', $slug)->firstOrFail();

        $relatedPackages = Package::where('is_active', true)
            ->where('id', '!=', $package->id)
            ->where('category', $package->category)
            ->limit(3)
            ->get();

        return view('pages.customer.package_detail', compact('package', 'relatedPackages'));
    }

    // Customer Store Booking with dynamic quota validation
    public function storeBooking(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'trip_id' => ['required', 'exists:trips,id'],
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_email' => ['required', 'email', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'pax' => ['required', 'integer', 'min:1', 'max:50'],
            'payment_method' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $trip = Trip::with('package')->findOrFail($validated['trip_id']);

        // Check quota availability
        if ($trip->remaining_seats < $validated['pax']) {
            return back()->withInput()->with('error', "Maaf, sisa kursi untuk jadwal batch ini hanya tersisa {$trip->remaining_seats} kursi.");
        }

        $totalPrice = $trip->package->price * $validated['pax'];
        $bookingCode = 'DJT-' . rand(1000, 9999);

        $booking = Booking::create([
            'booking_code' => $bookingCode,
            'user_id' => auth()->id(),
            'trip_id' => $trip->id,
            'package_id' => $trip->package_id,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'],
            'pax' => $validated['pax'],
            'total_price' => $totalPrice,
            'payment_method' => $validated['payment_method'],
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Increment trip booked seats and update status
        $trip->increment('booked_seats', $validated['pax']);
        if ($trip->remaining_seats <= 0) {
            $trip->update(['status' => 'full']);
        } elseif ($trip->percentage_booked >= 80) {
            $trip->update(['status' => 'almost_full']);
        }

        // Record transaction
        $booking->transactions()->create([
            'transaction_code' => 'TRX-' . Str::upper(Str::random(10)),
            'amount' => $totalPrice,
            'payment_method' => $validated['payment_method'],
            'payment_channel' => 'Online Payment',
            'paid_at' => now(),
            'status' => 'success',
        ]);

        return redirect()->route('customer.bookings')
            ->with('success', "Reservasi berhasil dibuat! Kode Booking Anda: {$bookingCode}. Data telah terhubung ke CMS Back-Office.");
    }

    // Customer Booking History & Cancellation / Refund Flow
    public function customerBookings(Request $request): View
    {
        $userEmail = auth()->check() ? auth()->user()->email : $request->query('email', 'rian.adi@gmail.com');

        $bookings = Booking::with(['package', 'trip', 'transactions', 'refund'])
            ->where(function ($q) use ($userEmail) {
                if (auth()->check()) {
                    $q->where('user_id', auth()->id())
                      ->orWhere('customer_email', $userEmail);
                } else {
                    $q->where('customer_email', $userEmail);
                }
            })
            ->latest('id')
            ->get();

        return view('pages.customer.bookings', compact('bookings', 'userEmail'));
    }

    // Customer Submits Refund Request
    public function requestRefund(Request $request, string $bookingCode): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $booking = Booking::where('booking_code', $bookingCode)->firstOrFail();

        if ($booking->booking_status === 'refund_requested' || $booking->booking_status === 'cancelled') {
            return back()->with('warning', 'Booking ini sudah dalam proses refund atau telah dibatalkan.');
        }

        $booking->update([
            'booking_status' => 'refund_requested',
        ]);

        Refund::create([
            'booking_id' => $booking->id,
            'refund_code' => 'REF-' . substr($booking->booking_code, 4),
            'reason' => $validated['reason'],
            'amount' => $booking->total_price,
            'status' => 'pending',
        ]);

        return back()->with('success', "Pengajuan pembatalan & refund untuk #{$bookingCode} telah dikirim ke tim CMS Ops Console untuk direview.");
    }

    // Customer Submits Review
    public function storeReview(Request $request, int $packageId): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        Review::create([
            'package_id' => $packageId,
            'user_id' => auth()->id(),
            'customer_name' => $validated['customer_name'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'is_approved' => true,
            'is_hidden' => false,
        ]);

        return back()->with('success', 'Terima kasih! Ulasan Anda telah terkirim dan masuk ke antrean CMS.');
    }
}
