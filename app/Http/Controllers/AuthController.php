<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to(localized_route('home'));
        }

        return view('pages.auth.login');
    }

    /**
     * Menampilkan halaman registrasi.
     */
    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to(localized_route('home'));
        }

        return view('pages.auth.register');
    }

    /**
     * Mendeteksi alur login/registrasi yang berasal dari halaman Inggris.
     */
    private function prefersEnglish(Request $request): bool
    {
        if (app()->getLocale() === 'en') {
            return true;
        }

        $intended = (string) session('url.intended', '');
        $referer = (string) $request->headers->get('referer', '');

        return str_starts_with($intended, url('/en'))
            || str_starts_with($referer, url('/en'));
    }

    /**
     * Menormalisasi nomor WhatsApp Indonesia ke format 62.
     *
     * 081234567890   -> 6281234567890
     * 6281234567890  -> 6281234567890
     * +6281234567890 -> 6281234567890
     */
    private function normalizeWhatsApp(string $number): string
    {
        $number = preg_replace('/\D+/', '', trim($number)) ?? '';

        if (str_starts_with($number, '08')) {
            $number = '62' . substr($number, 1);
        }

        return $number;
    }

    /**
     * Menghasilkan beberapa format nomor untuk kompatibilitas
     * dengan akun yang mungkin sudah tersimpan dalam format lama.
     */
    private function whatsappCandidates(string $number): array
    {
        $normalized = $this->normalizeWhatsApp($number);

        $candidates = [$normalized];

        if (str_starts_with($normalized, '62')) {
            $candidates[] = '+' . $normalized;
            $candidates[] = '0' . substr($normalized, 2);
        }

        return array_values(array_unique($candidates));
    }

    /**
     * Mendaftarkan pengguna baru.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
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
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->numbers(),
            ],
            'terms' => [
                'accepted',
            ],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 100 karakter.',

            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp.max' => 'Nomor WhatsApp maksimal 20 karakter.',
            'whatsapp.regex' => 'Format nomor WhatsApp tidak valid.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Masukkan alamat email yang valid.',
            'email.max' => 'Email maksimal 255 karakter.',

            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.letters' => 'Kata sandi harus memiliki huruf.',
            'password.numbers' => 'Kata sandi harus memiliki angka.',

            'terms.accepted' =>
                'Kamu harus menyetujui syarat dan ketentuan.',
        ]);

        $whatsapp = $this->normalizeWhatsApp(
            $validated['whatsapp']
        );

        $email = Str::lower(trim($validated['email']));

        /*
         * Validasi panjang dan format nomor hasil normalisasi.
         * Pemeriksaan ini mencegah input yang hanya berisi simbol.
         */
        if (
            ! preg_match('/^628[0-9]{7,11}$/', $whatsapp)
        ) {
            return back()
                ->withInput($request->except([
                    'password',
                    'password_confirmation',
                ]))
                ->withErrors([
                    'whatsapp' =>
                        'Masukkan nomor WhatsApp Indonesia yang valid, '
                        . 'misalnya 081234567890 atau +6281234567890.',
                ]);
        }

        /*
         * Email diperiksa sebelum pembuatan akun.
         * Jika akun sebelumnya pernah di-blacklist, lakukan RESET TOTAL data lama.
         */
        $existingUserByEmail = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if ($existingUserByEmail) {
            if ($existingUserByEmail->is_blacklisted) {
                $this->purgeAndResetBlacklistedUser($existingUserByEmail, $email);
            } else {
                return back()
                    ->withInput($request->except([
                        'password',
                        'password_confirmation',
                    ]))
                    ->withErrors([
                        'email' => 'Email sudah terdaftar.',
                    ]);
            }
        }

        /*
         * Periksa nomor dalam beberapa format untuk menghindari
         * pendaftaran ulang nomor yang sudah terdaftar.
         */
        $existingUserByWhatsApp = User::query()
            ->whereIn(
                'whatsapp',
                $this->whatsappCandidates($whatsapp)
            )
            ->first();

        if ($existingUserByWhatsApp) {
            if ($existingUserByWhatsApp->is_blacklisted) {
                $this->purgeAndResetBlacklistedUser($existingUserByWhatsApp, $existingUserByWhatsApp->email);
            } else {
                return back()
                    ->withInput($request->except([
                        'password',
                        'password_confirmation',
                    ]))
                    ->withErrors([
                        'whatsapp' => 'Nomor WhatsApp sudah terdaftar.',
                    ]);
            }
        }

        $user = User::create([
            'name' => trim($validated['name']),
            'whatsapp' => $whatsapp,
            'email' => $email,
            'password' => Hash::make($validated['password']),
            'last_login_at' => now(),
            'last_seen_at' => now(),
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->to(localized_route('home'))
            ->with(
                'success',
                $this->prefersEnglish($request)
                    ? 'Registration successful. Welcome!'
                    : 'Pendaftaran berhasil. Selamat datang!'
            );
    }

    /**
     * Login menggunakan email atau nomor WhatsApp.
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => [
                'required',
                'string',
                'max:255',
            ],
            'password' => [
                'required',
                'string',
            ],
        ], [
            'login.required' =>
                'Email atau nomor WhatsApp wajib diisi.',
            'login.max' =>
                'Email atau nomor WhatsApp terlalu panjang.',
            'password.required' =>
                'Kata sandi wajib diisi.',
        ]);

        $loginValue = trim($validated['login']);

        $isEmail = filter_var(
            $loginValue,
            FILTER_VALIDATE_EMAIL
        ) !== false;

        /*
         * Gunakan kunci rate limiter yang konsisten.
         * Variasi format nomor yang sama akan menggunakan
         * kunci pembatasan percobaan yang sama.
         */
        $normalizedLogin = $isEmail
            ? Str::lower($loginValue)
            : $this->normalizeWhatsApp($loginValue);

        $throttleKey = Str::lower($normalizedLogin)
            . '|'
            . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput($request->only('login'))
                ->withErrors([
                    'login' =>
                        "Terlalu banyak percobaan. "
                        . "Coba lagi dalam {$seconds} detik.",
                ]);
        }

        /*
         * Tentukan kolom pencarian berdasarkan jenis input.
         */
        if ($isEmail) {
            $user = User::query()
                ->whereRaw(
                    'LOWER(email) = ?',
                    [Str::lower($loginValue)]
                )
                ->first();
        } else {
            $user = User::query()
                ->whereIn(
                    'whatsapp',
                    $this->whatsappCandidates($loginValue)
                )
                ->first();
        }

        if ($user && $user->is_blacklisted) {
            RateLimiter::hit($throttleKey, 60);

            $reason = $user->blacklist_reason ? " Alasan: {$user->blacklist_reason}." : '';

            return back()
                ->withInput($request->only('login'))
                ->withErrors([
                    'login' => $this->prefersEnglish($request)
                        ? "Your account has been blacklisted by administrator.{$reason} Please contact customer support."
                        : "Akun Anda telah dinonaktifkan/masuk daftar hitam oleh administrator.{$reason} Silakan hubungi customer service kami.",
                ]);
        }

        /*
         * Gunakan nilai yang benar-benar tersimpan pada akun
         * untuk Auth::attempt. Ini membantu akun lama yang
         * nomornya belum dinormalisasi.
         */
        $credentials = [
            'password' => $validated['password'],
        ];

        if ($user) {
            $credentials[$isEmail ? 'email' : 'whatsapp'] =
                $isEmail ? $user->email : $user->whatsapp;
        }

        if (
            ! $user
            || ! Auth::attempt(
                $credentials,
                $request->boolean('remember')
            )
        ) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withInput($request->only('login'))
                ->withErrors([
                    'login' =>
                        'Email/nomor WhatsApp atau kata sandi '
                        . 'tidak sesuai.',
                ]);
        }

        RateLimiter::clear($throttleKey);

        $authUser = Auth::user();
        if ($authUser instanceof User) {
            $authUser->forceFill([
                'last_login_at' => now(),
                'last_seen_at' => now(),
            ])->saveQuietly();
        }

        /*
         * Regenerasi session untuk mengurangi risiko
         * session fixation setelah autentikasi berhasil.
         */
        $request->session()->regenerate();

        return redirect()
            ->intended(localized_route('home'))
            ->with(
                'success',
                $this->prefersEnglish($request)
                    ? 'You are now signed in.'
                    : 'Berhasil masuk ke akun kamu.'
            );
    }

    /**
     * Reset total data lama pada akun yang pernah di-blacklist:
     * - Putuskan relasi booking lama (set user_id = null)
     * - Ubah customer_email pada booking lama menjadi archived agar akun baru tidak mewarisi riwayat lama
     * - Hapus data ulasan / review
     * - Hapus model user lama agar akun baru terdaftar benar-benar bersih.
     */
    protected function purgeAndResetBlacklistedUser(User $oldUser, string $email): void
    {
        DB::transaction(function () use ($oldUser, $email) {
            Booking::where('user_id', $oldUser->id)
                ->orWhereRaw('LOWER(customer_email) = ?', [Str::lower($email)])
                ->update([
                    'user_id' => null,
                    'customer_email' => 'archived_' . time() . '_' . Str::lower($email),
                ]);

            $oldUser->reviews()->delete();
            $oldUser->delete();
        });
    }

    /**
     * Logout pengguna dan mengakhiri session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->to(localized_route('login'))
            ->with(
                'success',
                $this->prefersEnglish($request)
                    ? 'You have been signed out.'
                    : 'Kamu telah keluar dari akun.'
            );
    }
}
