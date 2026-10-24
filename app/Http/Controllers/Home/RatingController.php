<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RatingController extends Controller
{
    private const PAID_STATUSES = [
        'success',
        'paid',
        'settled',
        'settlement',
        'completed',
        'capture',
    ];

    private const EXPERIENCE_TAGS = [
        'driver_ramah',
        'armada_bersih',
        'rute_tepat',
        'driver_berpengalaman',
        'cs_responsif',
        'mobil_nyaman',
    ];

    /**
     * Mengambil booking milik pengguna yang sedang login.
     */
    private function findOwnedBooking(
        Request $request,
        string $bookingCode
    ): Booking {
        $user = $request->user();

        abort_unless($user, 401);

        return Booking::query()
            ->with(['package', 'trip', 'transactions'])
            ->where('booking_code', $bookingCode)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($legacy) use ($user) {
                        $legacy->whereNull('user_id')
                            ->where('customer_email', $user->email);
                    });
            })
            ->firstOrFail();
    }

    /**
     * Menghitung jumlah pembayaran yang berhasil.
     */
    private function paidAmount(Booking $booking): int
    {
        return (int) $booking->transactions
            ->filter(fn ($transaction) => in_array(
                strtolower((string) $transaction->status),
                self::PAID_STATUSES,
                true
            ))
            ->sum('amount');
    }

    /**
     * Memastikan booking sudah lunas dan memenuhi syarat rating.
     */
    private function ensureEligibleBooking(Booking $booking): void
    {
        $bookingStatus = strtolower(
            (string) $booking->booking_status
        );

        if (in_array(
            $bookingStatus,
            ['cancelled', 'canceled', 'refund_requested'],
            true
        )) {
            abort(403, 'Booking yang dibatalkan tidak dapat diberi rating.');
        }

        $totalPrice = (int) $booking->total_price;

        if (
            $totalPrice <= 0
            || $this->paidAmount($booking) < $totalPrice
        ) {
            abort(403, 'Rating hanya tersedia untuk booking yang sudah lunas.');
        }
    }

    /**
     * Menampilkan formulir rating.
     */
    public function create(
        Request $request,
        string $bookingCode
    ): View|RedirectResponse {
        $booking = $this->findOwnedBooking($request, $bookingCode);

        $this->ensureEligibleBooking($booking);

        $existingReview = Review::query()
            ->where('booking_id', $booking->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingReview) {
            return redirect()
                ->route('customer.bookings.detail', [
                    'bookingCode' => $bookingCode,
                ])
                ->with('warning', 'Booking ini sudah memiliki ulasan.');
        }

        return view('pages.dashboard.rating.index', [
            'booking' => $booking,
            'existingReview' => null,
        ]);
    }

    /**
     * Menyimpan rating, penilaian layanan, tag, dan foto.
     */
    public function store(
        Request $request,
        string $bookingCode
    ): RedirectResponse {
        $validated = $request->validate(
            [
                'rating' => [
                    'required',
                    'integer',
                    'between:1,5',
                ],

                'comment' => [
                    'required',
                    'string',
                    'min:5',
                    'max:1000',
                ],

                'service_ratings' => [
                    'required',
                    'array',
                ],

                'service_ratings.armada' => [
                    'required',
                    'integer',
                    'between:1,5',
                ],

                'service_ratings.driver' => [
                    'required',
                    'integer',
                    'between:1,5',
                ],

                'service_ratings.route' => [
                    'required',
                    'integer',
                    'between:1,5',
                ],

                'experience_tags' => [
                    'nullable',
                    'array',
                    'max:6',
                ],

                'experience_tags.*' => [
                    'required',
                    'string',
                    'distinct',
                    'in:' . implode(',', self::EXPERIENCE_TAGS),
                ],

                'photos' => [
                    'nullable',
                    'array',
                    'max:5',
                ],

                'photos.*' => [
                    'required',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:5120',
                ],

                'is_public' => [
                    'sometimes',
                    'boolean',
                ],

                'identity_verified' => [
                    'sometimes',
                    'boolean',
                ],
            ],
            [
                'service_ratings.armada.required' => 'Silakan beri rating untuk kondisi armada.',
                'service_ratings.driver.required' => 'Silakan beri rating untuk pelayanan driver.',
                'service_ratings.route.required' => 'Silakan beri rating untuk rute dan ketepatan.',
            ]
        );

        $booking = $this->findOwnedBooking($request, $bookingCode);

        $this->ensureEligibleBooking($booking);

        $user = $request->user();

        $storedPhotos = [];

        try {
            DB::transaction(function () use (
                $booking,
                $user,
                $validated,
                $request,
                &$storedPhotos
            ): void {
                // Mencegah dua ulasan untuk booking yang sama.
                $lockedBooking = Booking::query()
                    ->whereKey($booking->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $existingReview = Review::query()
                    ->where('booking_id', $lockedBooking->id)
                    ->exists();

                if ($existingReview) {
                    throw ValidationException::withMessages([
                        'rating' => 'Booking ini sudah memiliki rating.',
                    ]);
                }

                foreach ($request->file('photos', []) as $photo) {
                    $storedPhotos[] = $photo->store(
                        'reviews/' . $lockedBooking->id,
                        'public'
                    );
                }

                Review::query()->create([
                    'booking_id' => $lockedBooking->id,
                    'package_id' => $lockedBooking->package_id,
                    'user_id' => $user->id,
                    'customer_name' => $lockedBooking->customer_name,
                    'rating' => $validated['rating'],
                    'comment' => $validated['comment'],
                    'service_ratings' => $validated['service_ratings'],
                    'experience_tags' => $validated['experience_tags'] ?? [],
                    'photos' => $storedPhotos,
                    'is_approved' => true,
                    'is_hidden' => false,
                    'is_public' => $request->boolean('is_public'),
                    'identity_verified' => $request->boolean(
                        'identity_verified'
                    ),
                ]);
            });
        } catch (\Throwable $exception) {
            foreach ($storedPhotos as $photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $exception;
        }

        return redirect()
            ->route('customer.bookings.detail', [
                'bookingCode' => $bookingCode,
            ])
            ->with('success', 'Terima kasih! Rating kamu berhasil dikirim.');
    }
}
