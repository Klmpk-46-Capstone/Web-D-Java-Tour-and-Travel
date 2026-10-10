<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            return redirect()->route('home');
        }

        return view('pages.auth.login');
    }

    /**
     * Menampilkan halaman registrasi.
     */
    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('pages.auth.register');
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
         * Periksa nomor dalam beberapa format untuk menghindari
         * pendaftaran ulang nomor yang sudah terdaftar.
         */
        $existingWhatsApp = User::query()
            ->whereIn(
                'whatsapp',
                $this->whatsappCandidates($whatsapp)
            )
            ->exists();

        if ($existingWhatsApp) {
            return back()
                ->withInput($request->except([
                    'password',
                    'password_confirmation',
                ]))
                ->withErrors([
                    'whatsapp' => 'Nomor WhatsApp sudah terdaftar.',
                ]);
        }

        /*
         * Email diperiksa sebelum pembuatan akun.
         */
        $existingEmail = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->exists();

        if ($existingEmail) {
            return back()
                ->withInput($request->except([
                    'password',
                    'password_confirmation',
                ]))
                ->withErrors([
                    'email' => 'Email sudah terdaftar.',
                ]);
        }

        $user = User::create([
            'name' => trim($validated['name']),
            'whatsapp' => $whatsapp,
            'email' => $email,
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('home')
            ->with(
                'success',
                'Pendaftaran berhasil. Selamat datang!'
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

        /*
         * Regenerasi session untuk mengurangi risiko
         * session fixation setelah autentikasi berhasil.
         */
        $request->session()->regenerate();

        return redirect()
            ->intended(route('home'))
            ->with(
                'success',
                'Berhasil masuk ke akun kamu.'
            );
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
            ->route('login')
            ->with(
                'success',
                'Kamu telah keluar dari akun.'
            );
    }
}
