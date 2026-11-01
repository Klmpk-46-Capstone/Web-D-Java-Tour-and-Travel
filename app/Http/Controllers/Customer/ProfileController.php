<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman profil pengguna.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user, 401);

        // Menghitung booking yang sudah lunas berdasarkan transaksi berhasil.
        $completedBookings = Booking::query()
            ->with([
                'transactions:id,booking_id,status,amount',
            ])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($legacy) use ($user) {
                        $legacy->whereNull('user_id')
                            ->where('customer_email', $user->email);
                    });
            })
            ->get()
            ->filter(function ($booking) {
                $paymentStatus = strtolower(
                    (string) $booking->payment_status
                );

                $bookingStatus = strtolower(
                    (string) $booking->booking_status
                );

                // Status transaksi yang dianggap berhasil.
                $paidStatuses = [
                    'success',
                    'paid',
                    'settled',
                    'settlement',
                    'completed',
                    'capture',
                ];

                // Menghitung nominal seluruh transaksi berhasil.
                $paidAmount = (int) $booking->transactions
                    ->filter(fn ($transaction) => in_array(
                        strtolower((string) $transaction->status),
                        $paidStatuses,
                        true
                    ))
                    ->sum('amount');

                $totalPrice = (int) $booking->total_price;

                // Menggunakan aturan yang sama dengan booking-card.
                $isPaid = (
                    $totalPrice > 0
                    && $paidAmount >= $totalPrice
                ) || in_array(
                    $paymentStatus,
                    ['paid', 'lunas', 'settled', 'success'],
                    true
                );

                // Booking dibatalkan tidak dihitung.
                $isCancelled = in_array(
                    $bookingStatus,
                    ['cancelled', 'canceled', 'refund_requested'],
                    true
                );

                return $isPaid && ! $isCancelled;
            })
            ->count();

        // Menghitung total ulasan milik pengguna.
        $reviewCount = DB::table('reviews')
            ->where('user_id', $user->id)
            ->count();

        // Mengambil perjalanan mendatang.
        $upcomingBooking = $this->getUpcomingBooking($user);

        // Menyamarkan nomor identitas.
        $maskedIdentity = $this->getMaskedIdentity(
            $user->identity_number
        );

        // Mengambil URL foto profil.
        /** @var \Illuminate\Filesystem\FilesystemAdapter $publicDisk */
        $publicDisk = Storage::disk('public');
        $profilePhotoUrl = $user->profile_photo_path
            ? $publicDisk->url($user->profile_photo_path)
            : null;

        // Menentukan tab aktif.
        $activeTab = $request->query('tab', 'profile');

        if (! in_array($activeTab, [
            'profile',
            'security',
            'notifications',
        ], true)) {
            $activeTab = 'profile';
        }

        return view('pages.profile.index', [
            'user' => $user,
            'completedBookings' => $completedBookings,
            'reviewCount' => $reviewCount,
            'upcomingBooking' => $upcomingBooking,
            'maskedIdentity' => $maskedIdentity,
            'profilePhotoUrl' => $profilePhotoUrl,
            'activeTab' => $activeTab,
        ]);
    }

    /**
     * Mengambil booking milik pengguna yang login.
     * Mendukung booking lama yang belum memiliki user_id.
     */
    private function ownedBookingsQuery(User $user)
    {
        return DB::table('bookings')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($legacy) use ($user) {
                        $legacy->whereNull('user_id')
                            ->where('customer_email', $user->email);
                    });
            });
    }

    /**
     * Mengambil perjalanan terdekat dari database.
     */
    private function getUpcomingBooking(User $user): ?object
    {
        $booking = DB::table('bookings as b')
            ->join('trips as t', 't.id', '=', 'b.trip_id')
            ->leftJoin('packages as p', 'p.id', '=', 'b.package_id')
            ->where(function ($query) use ($user) {
                $query->where('b.user_id', $user->id)
                    ->orWhere(function ($legacy) use ($user) {
                        $legacy->whereNull('b.user_id')
                            ->where('b.customer_email', $user->email);
                    });
            })
            ->whereDate('t.departure_date', '>=', today())
            ->whereNotIn('b.booking_status', [
                'cancelled',
                'canceled',
                'refund_requested',
            ])
            ->select([
                'b.booking_code',
                'b.pax',
                'b.booking_status',
                't.departure_date',
                't.driver_name',
                'p.name as package_name',
                'p.image_url',
            ])
            ->orderBy('t.departure_date')
            ->first();

        if ($booking) {
            $booking->type = 'package';

            return $booking;
        }

        // Jika tidak ada perjalanan paket,
        // periksa penyewaan armada.
        $rental = DB::table('bookings as b')
            ->leftJoin('vehicles as v', 'v.id', '=', 'b.vehicle_id')
            ->where(function ($query) use ($user) {
                $query->where('b.user_id', $user->id)
                    ->orWhere(function ($legacy) use ($user) {
                        $legacy->whereNull('b.user_id')
                            ->where('b.customer_email', $user->email);
                    });
            })
            ->whereNotNull('b.rental_date')
            ->whereDate('b.rental_date', '>=', today())
            ->whereNotIn('b.booking_status', [
                'cancelled',
                'canceled',
                'refund_requested',
            ])
            ->select([
                'b.booking_code',
                'b.pax',
                'b.booking_status',
                'b.rental_date as departure_date',
                'v.name as package_name',
                'v.image_path as image_url',
            ])
            ->orderBy('b.rental_date')
            ->first();

        if ($rental) {
            $rental->type = 'vehicle';
        }

        return $rental;
    }

    /**
     * Menampilkan identitas dalam bentuk tersamarkan.
     */
    private function getMaskedIdentity(?string $encryptedIdentity): ?string
    {
        if (! filled($encryptedIdentity)) {
            return null;
        }

        try {
            $identity = Crypt::decryptString($encryptedIdentity);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }

        $length = strlen($identity);

        if ($length <= 4) {
            return str_repeat('•', $length);
        }

        return str_repeat('•', max(0, $length - 4)) . substr($identity, -4);
    }

    /**
     * Memperbarui data pribadi dan foto profil.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user, 401);

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'whatsapp' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/',
            ],

            'identity_number' => [
                'nullable',
                'string',
                'max:50',
            ],

            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput(
                    $request->except([
                        'identity_number',
                        'profile_photo',
                    ])
                )
                ->with('activeTab', 'profile');
        }

        $validated = $validator->validated();

        // Normalisasi nomor WhatsApp.
        $whatsapp = preg_replace(
            '/\D+/',
            '',
            trim($validated['whatsapp'])
        ) ?? '';

        if (str_starts_with($whatsapp, '08')) {
            $whatsapp = '62' . substr($whatsapp, 1);
        }

        if (! preg_match('/^628[0-9]{7,11}$/', $whatsapp)) {
            return back()
                ->withInput(
                    $request->except([
                        'identity_number',
                        'profile_photo',
                    ])
                )
                ->withErrors([
                    'whatsapp' => 'Nomor WhatsApp Indonesia tidak valid.',
                ])
                ->with('activeTab', 'profile');
        }

        // Memastikan nomor WhatsApp tidak digunakan akun lain.
        $existingWhatsApp = User::query()
            ->where('id', '!=', $user->id)
            ->where('whatsapp', $whatsapp)
            ->exists();

        if ($existingWhatsApp) {
            return back()
                ->withInput(
                    $request->except([
                        'identity_number',
                        'profile_photo',
                    ])
                )
                ->withErrors([
                    'whatsapp' => 'Nomor WhatsApp sudah digunakan akun lain.',
                ])
                ->with('activeTab', 'profile');
        }

        $updateData = [
            'name' => trim($validated['name']),
            'whatsapp' => $whatsapp,
        ];

        // Enkripsi NIK/Paspor sebelum disimpan.
        if (filled($validated['identity_number'] ?? null)) {
            $identity = preg_replace(
                '/\s+/',
                '',
                trim($validated['identity_number'])
            );

            $updateData['identity_number'] = Crypt::encryptString(
                $identity
            );
        }

        // Menyimpan foto profil.
        if ($request->hasFile('profile_photo')) {
            $newPhotoPath = $request->file('profile_photo')
                ->store('profile-photos', 'public');

            $oldPhotoPath = $user->profile_photo_path;

            $updateData['profile_photo_path'] = $newPhotoPath;

            if ($oldPhotoPath) {
                Storage::disk('public')->delete($oldPhotoPath);
            }
        }

        $user->forceFill($updateData)->save();

        return redirect()
            ->to(localized_route('profile.index') . '?tab=profile')
            ->with(
                'success',
                'Informasi profil berhasil diperbarui.'
            );
    }

    /**
     * Mengubah password pengguna.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user, 401);

        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->numbers(),
            ],
        ]);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return redirect()
            ->to(localized_route('profile.index') . '?tab=security')
            ->with(
                'success',
                'Password berhasil diperbarui.'
            );
    }

    /**
     * Mengatur notifikasi WhatsApp.
     */
    public function updateNotifications(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user, 401);

        $enabled = $request->boolean(
            'whatsapp_notifications_enabled'
        );

        if ($enabled && ! filled($user->whatsapp)) {
            return back()
                ->withErrors([
                    'notification' => 'Tambahkan nomor WhatsApp terlebih dahulu.',
                ])
                ->with('activeTab', 'notifications');
        }

        $user->forceFill([
            'whatsapp_notifications_enabled' => $enabled,
        ])->save();

        return redirect()
            ->to(localized_route('profile.index') . '?tab=notifications')
            ->with(
                'success',
                'Preferensi notifikasi berhasil diperbarui.'
            );
    }
}
