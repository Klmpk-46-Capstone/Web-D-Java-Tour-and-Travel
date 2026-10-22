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
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'boolean'],
            'mode' => ['nullable', 'in:paket,mobil'],
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

        $searchActive = $request->boolean('search');
        $mode = $filters['mode'] ?? 'paket';
        $pickup = trim((string) ($filters['pickup'] ?? ''));
        $destinationId = $filters['destination'] ?? null;
        $departureDate = $filters['departure_date'] ?? null;
        $passengers = (int) ($filters['passengers'] ?? 4);

        // Opsi dropdown destinasi selalu memakai data penuh, bukan hasil filter.
        $destinationOptions = Package::query()
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->latest('id')
            ->limit(8)
            ->get(['id', 'name']);

        $applyPackageFilters = function ($query) use (
            $searchActive,
            $mode,
            $pickup,
            $destinationId,
            $departureDate,
            $passengers
        ) {
            if (! $searchActive || $mode !== 'paket') {
                return $query;
            }

            if ($pickup !== '') {
                $keyword = '%' . $pickup . '%';

                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('name', 'like', $keyword)
                        ->orWhere('category', 'like', $keyword)
                        ->orWhere('badge', 'like', $keyword)
                        ->orWhere('description', 'like', $keyword);
                });
            }

            if (filled($destinationId)) {
                $query->whereKey($destinationId);
            }

            if (filled($departureDate)) {
                $query->whereHas('trips', function ($tripQuery) use ($departureDate, $passengers) {
                    $tripQuery->whereDate('departure_date', $departureDate)
                        ->whereIn('status', ['available', 'open', 'almost_full']);

                    if ($passengers > 0) {
                        $tripQuery->whereRaw('(quota - booked_seats) >= ?', [$passengers]);
                    }
                });
            } elseif ($passengers > 0) {
                $query->whereHas('trips', function ($tripQuery) use ($passengers) {
                    $tripQuery->whereIn('status', ['available', 'open', 'almost_full'])
                        ->where('quota', '>=', $passengers);
                });
            }

            return $query;
        };

        // 1. Featured Packages (Rekomendasi Utama) - Data-driven from CMS toggle
        $featuredPackages = $applyPackageFilters(
            Package::with(['trips', 'itineraries', 'reviews'])
                ->where('is_active', true)
                ->where('is_featured', true)
                ->latest('id')
        )->get();

        // 2. Paket yang tampil pada section destinasi / hasil pencarian paket.
        $destinations = $applyPackageFilters(
            Package::query()
                ->where('is_active', true)
                ->orderByDesc('is_featured')
                ->latest('id')
        )->limit(8)->get();

        // 3. Armada: kapasitas kursi dan kata kunci nama/kategori/deskripsi.
        $vehicles = Vehicle::query()
            ->where('is_active', true)
            ->when(
                $searchActive && $mode === 'mobil' && $pickup !== '',
                function ($query) use ($pickup) {
                    $keyword = '%' . $pickup . '%';

                    $query->where(function ($subQuery) use ($keyword) {
                        $subQuery->where('name', 'like', $keyword)
                            ->orWhere('category', 'like', $keyword)
                            ->orWhere('description', 'like', $keyword);
                    });
                }
            )
            ->when(
                $searchActive && $mode === 'mobil' && $passengers > 0,
                fn ($query) => $query->where('seats', '>=', $passengers)
            )
            ->orderBy('price_per_day')
            ->limit(6)
            ->get();

        $resultCount = $searchActive
            ? ($mode === 'paket' ? $destinations->count() : $vehicles->count())
            : 0;

        $selectedDestination = $destinationOptions->firstWhere('id', (int) $destinationId);

        return view('pages.dashboard.beranda.index', [
            'featuredPackages' => $featuredPackages,
            'destinations' => $destinations,
            'destinationOptions' => $destinationOptions,
            'vehicles' => $vehicles,
            'searchActive' => $searchActive,
            'mode' => $mode,
            'pickup' => $pickup,
            'departureDate' => $departureDate,
            'passengers' => $passengers,
            'selectedDestination' => $selectedDestination,
            'resultCount' => $resultCount,
        ]);
    }

    // Customer Package Catalog
    public function packages(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'kategori' => ['nullable', 'in:bahari,gunung,heritage'],
            'urutkan' => ['nullable', 'in:rekomendasi,harga_terendah,harga_tertinggi,rating'],
        ]);

        $q = trim((string) ($filters['q'] ?? ''));
        $category = $filters['kategori'] ?? null;
        $sort = $filters['urutkan'] ?? 'rekomendasi';

        $query = Package::with(['trips', 'itineraries', 'reviews'])
            ->where('is_active', true)
            ->when($q !== '', function ($query) use ($q) {
                $keyword = '%' . $q . '%';

                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('name', 'like', $keyword)
                        ->orWhere('category', 'like', $keyword)
                        ->orWhere('badge', 'like', $keyword)
                        ->orWhere('description', 'like', $keyword);
                });
            })
            ->when(filled($category), fn ($query) => $query->where('route_type', $category));

        $query = match ($sort) {
            'harga_terendah' => $query->orderBy('price'),
            'harga_tertinggi' => $query->orderByDesc('price'),
            'rating' => $query->orderByDesc('rating')->orderByDesc('total_reviews'),
            default => $query->orderByDesc('is_featured')
                ->orderByDesc('rating')
                ->latest('id'),
        };

        $packages = $query->paginate(9)->withQueryString();

        $categories = [
            'bahari' => 'Wisata Bahari',
            'gunung' => 'Wisata Mendaki Gunung',
            'heritage' => 'Wisata Kebudayaan',
        ];

        return view('pages.dashboard.paket_wisata.index', compact(
            'packages',
            'categories',
            'category',
            'q',
            'sort'
        ));
    }

    // Customer Vehicle Catalog (Armada)
    public function armada(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'kategori' => ['nullable', 'in:mpv,premium,minibus'],
            'transmisi' => ['nullable', 'in:at,mt'],
            'urutkan' => ['nullable', 'in:rekomendasi,harga_terendah,harga_tertinggi,kapasitas'],
        ]);

        $q = trim((string) ($filters['q'] ?? ''));
        $category = $filters['kategori'] ?? null;
        $transmission = $filters['transmisi'] ?? null;
        $sort = $filters['urutkan'] ?? 'rekomendasi';

        $query = Vehicle::query()
            ->where('is_active', true)
            ->when($q !== '', function ($query) use ($q) {
                $keyword = '%' . $q . '%';

                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('name', 'like', $keyword)
                        ->orWhere('category', 'like', $keyword)
                        ->orWhere('badge', 'like', $keyword)
                        ->orWhere('transmission', 'like', $keyword)
                        ->orWhere('description', 'like', $keyword);
                });
            })
            ->when(filled($category), fn ($query) => $query->where('category', $category))
            ->when($transmission === 'at', fn ($query) => $query->where('transmission', 'like', '%matic%'))
            ->when($transmission === 'mt', fn ($query) => $query->where('transmission', 'like', '%manual%'));

        $query = match ($sort) {
            'harga_terendah' => $query->orderBy('price_per_day'),
            'harga_tertinggi' => $query->orderByDesc('price_per_day'),
            'kapasitas' => $query->orderByDesc('seats'),
            default => $query->orderBy('price_per_day')->orderBy('id'),
        };

        $vehicles = $query->paginate(9)->withQueryString();

        $categories = [
            'mpv' => 'MPV Keluarga',
            'premium' => 'Premium & Hybrid',
            'minibus' => 'Minibus Rombongan',
        ];

        return view('pages.dashboard.armada.index', compact(
            'vehicles',
            'categories',
            'category',
            'transmission',
            'q',
            'sort'
        ));
    }

    // Customer Vehicle Detail: spesifikasi, opsi sewa (dengan/tanpa supir), dan unit terkait.
    public function armadaDetail(Vehicle $vehicle): View
    {
        abort_unless($vehicle->is_active, 404);

        $relatedVehicles = Vehicle::query()
            ->active()
            ->whereKeyNot($vehicle->getKey())
            ->orderByRaw('CASE WHEN category = ? THEN 0 ELSE 1 END', [$vehicle->category])
            ->orderBy('price_per_day')
            ->limit(3)
            ->get();

        return view('pages.dashboard.armada.detail', compact(
            'vehicle',
            'relatedVehicles'
        ));
    }

    // Customer Vehicle Booking Form: mempertahankan unit, opsi sewa, tanggal, dan pax dari halaman detail.
    public function armadaBookingForm(Request $request, Vehicle $vehicle): View
    {
        abort_unless($vehicle->is_active, 404);

        $options = collect($vehicle->rentalOptions())->values();
        $optionKeys = $options->pluck('key')->all();

        $validated = $request->validate([
            'rental_date' => ['nullable', 'date', 'after_or_equal:today'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:30'],
            'pax' => ['nullable', 'integer', 'min:1', 'max:' . $vehicle->seats],
            'rental_option' => ['nullable', 'string', Rule::in($optionKeys)],
        ]);

        $selectedOptionKey = $validated['rental_option'] ?? ($optionKeys[0] ?? null);
        $selectedOption = $options->firstWhere('key', $selectedOptionKey) ?? $options->first();

        return view('pages.dashboard.armada.formulir.index', [
            'vehicle' => $vehicle,
            'options' => $options->all(),
            'selectedOption' => $selectedOption,
            'selectedOptionKey' => $selectedOptionKey,
            'selectedRentalDate' => $validated['rental_date'] ?? null,
            'selectedDuration' => (int) ($validated['duration'] ?? 1),
            'selectedPax' => (int) ($validated['pax'] ?? 1),
        ]);
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
