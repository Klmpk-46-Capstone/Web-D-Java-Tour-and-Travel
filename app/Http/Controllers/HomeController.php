<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Package;
use App\Models\Refund;
use App\Models\Review;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $destinations = Package::query()
            ->where('is_active', true)
            ->when(
                filled($search['destination'] ?? null),
                function ($query) use ($search) {
                    $query->where(function ($subQuery) use ($search) {
                        $keyword = '%' . $search['destination'] . '%';

                        $subQuery->where('name', 'like', $keyword)
                            ->orWhere('category', 'like', $keyword);
                    });
                }
            )
            ->orderByDesc('is_featured')
            ->latest('id')
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

        return view('pages.dashboard.paket_wisata.index', compact(
            'packages',
            'category',
            'route',
            'q'
        ));
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

        return view('pages.dashboard.paket_wisata.detail.detail_package', compact(
            'package',
            'relatedPackages'
        ));
    }

    // Customer Booking Form: data perjalanan dipertahankan dari halaman detail.
    public function bookingForm(Request $request, string $slug): View|RedirectResponse
    {
        $package = Package::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $validated = $request->validate([
            'trip_id' => ['required', 'integer', 'min:1'],
            'pax' => ['required', 'integer', 'min:1', 'max:50'],
            'selected_armada' => ['required', 'in:Innova Reborn,HiAce Commuter'],
        ]);

        $selectedTrip = Trip::query()
            ->where('package_id', $package->id)
            ->whereKey($validated['trip_id'])
            ->whereIn('status', ['available', 'open', 'almost_full'])
            ->whereDate('departure_date', '>=', now()->toDateString())
            ->first();

        if (! $selectedTrip) {
            return redirect()->route('packages.show', $package->slug)
                ->with('error', 'Jadwal keberangkatan tidak tersedia. Silakan pilih jadwal lain.');
        }

        $remainingSeats = max(0, (int) $selectedTrip->quota - (int) $selectedTrip->booked_seats);
        if ($remainingSeats < 1 || $validated['pax'] > $remainingSeats) {
            return redirect()->route('packages.show', $package->slug)
                ->with('error', 'Sisa kuota jadwal berubah. Silakan pilih ulang jadwal dan jumlah peserta.');
        }

        return view('pages.dashboard.paket_wisata.formulir.index', [
            'package' => $package,
            'selectedTrip' => $selectedTrip,
            'selectedPax' => (int) $validated['pax'],
            'selectedArmada' => $validated['selected_armada'],
        ]);
    }

    // Store booking: validasi ulang data dan kuota di server, lalu simpan secara atomik.
    public function storeBooking(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'package_id' => ['required', 'integer', 'exists:packages,id'],
            'trip_id' => ['required', 'integer', 'exists:trips,id'],
            'pax' => ['required', 'integer', 'min:1', 'max:50'],
            'selected_armada' => ['required', 'in:Innova Reborn,HiAce Commuter'],
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_email' => ['required', 'email', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'pickup_location' => ['required', 'string', 'max:200'],
            'pickup_details' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:BCA Virtual Account,Mandiri Virtual Account'],
            'notes' => ['nullable', 'string', 'max:500'],
            'identity_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $booking = DB::transaction(function () use ($request, $validated) {
            $trip = Trip::query()
                ->whereKey($validated['trip_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $trip->package_id !== (int) $validated['package_id']) {
                abort(422, 'Jadwal tidak sesuai dengan paket yang dipilih.');
            }

            $remainingSeats = max(0, (int) $trip->quota - (int) $trip->booked_seats);
            if (! in_array($trip->status, ['available', 'open', 'almost_full'], true)
                || $trip->departure_date < now()->toDateString()
                || $remainingSeats < (int) $validated['pax']) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'trip_id' => 'Kuota jadwal tidak mencukupi. Silakan kembali ke halaman detail dan pilih ulang jadwal atau jumlah peserta.',
                ]);
            }

            $package = Package::query()->whereKey($trip->package_id)->firstOrFail();
            $totalPrice = (int) $package->price * (int) $validated['pax'];
            $identityPath = null;
            if ($request->hasFile('identity_file')) {
                $identityPath = $request->file('identity_file')->store('booking-identities', 'local');
            }

            $bookingCode = 'DJT-' . Str::upper(Str::random(8));
            $booking = new Booking();
            $booking->forceFill([
                'booking_code' => $bookingCode,
                'user_id' => Auth::id(),
                'trip_id' => $trip->id,
                'package_id' => $package->id,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'pax' => (int) $validated['pax'],
                'pickup_location' => $validated['pickup_location'],
                'pickup_details' => $validated['pickup_details'] ?? null,
                'selected_armada' => $validated['selected_armada'],
                'identity_file_path' => $identityPath,
                'total_price' => $totalPrice,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'booking_status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ])->save();

            $trip->booked_seats = (int) $trip->booked_seats + (int) $validated['pax'];
            if ($trip->booked_seats >= $trip->quota) {
                $trip->status = 'full';
            } elseif ($trip->quota > 0 && ($trip->booked_seats / $trip->quota) >= 0.8) {
                $trip->status = 'almost_full';
            }
            $trip->save();

            $booking->transactions()->create([
                'transaction_code' => 'TRX-' . Str::upper(Str::random(10)),
                'amount' => (int) round($totalPrice * 0.30),
                'payment_method' => $validated['payment_method'],
                'payment_channel' => 'Transfer Bank',
                'paid_at' => null,
                'status' => 'pending',
            ]);

            return $booking;
        });

        return redirect()->route('booking.form', [
            'slug' => $booking->package->slug,
            'trip_id' => $booking->trip_id,
            'pax' => $booking->pax,
            'selected_armada' => $booking->selected_armada,
        ])->with('success', 'Permintaan reservasi berhasil dicatat. Status pembayaran masih menunggu verifikasi DP. Kode booking: ' . $booking->booking_code);
    }

    // Live Check Trip Quota (Realtime verification against stale frontends)
    public function checkTripQuota(Trip $trip): JsonResponse
    {
        $formattedDate = $trip->departure_date ? \Illuminate\Support\Carbon::parse($trip->departure_date)->translatedFormat('d F Y') : '';

        return response()->json([
            'id' => $trip->id,
            'batch_name' => $trip->batch_name,
            'departure_date' => $trip->departure_date ? \Illuminate\Support\Carbon::parse($trip->departure_date)->format('Y-m-d') : null,
            'formatted_date' => $formattedDate,
            'quota' => (int) $trip->quota,
            'booked_seats' => (int) $trip->booked_seats,
            'remaining_seats' => (int) $trip->remaining_seats,
            'is_full' => $trip->remaining_seats <= 0,
            'percentage_booked' => $trip->percentage_booked,
            'status' => $trip->status,
        ]);
    }

    // Customer Booking History & Cancellation / Refund Flow
    public function customerBookings(Request $request): View
    {
        $userEmail = Auth::check() ? Auth::user()->email : $request->query('email', 'rian.adi@gmail.com');

        $bookings = Booking::with(['package', 'trip', 'transactions', 'refund'])
            ->where(function ($q) use ($userEmail) {
                if (Auth::check()) {
                    $q->where('user_id', Auth::id())
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
            'user_id' => Auth::id(),
            'customer_name' => $validated['customer_name'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'is_approved' => true,
            'is_hidden' => false,
        ]);

        return back()->with('success', 'Terima kasih! Ulasan Anda telah terkirim dan masuk ke antrean CMS.');
    }
}
