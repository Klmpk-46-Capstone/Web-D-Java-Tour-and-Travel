<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Mengarahkan pengguna ke Google untuk Login.
     */
    public function redirect(): RedirectResponse
    {
        /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
        $driver = Socialite::driver('google');

        return $driver
            ->redirectUrl(localized_route('google.callback'))
            ->redirect();
    }

    /**
     * Mengarahkan pengguna ke Google untuk Register.
     */
    public function registerRedirect(): RedirectResponse
    {
        /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
        $driver = Socialite::driver('google');

        return $driver
            ->redirectUrl(localized_route('google.register.callback'))
            ->redirect();
    }

    /**
     * Memproses callback Google untuk Login.
     */
    public function callback(): RedirectResponse
    {
        return $this->authenticate(false);
    }

    /**
     * Memproses callback Google untuk Register.
     */
    public function registerCallback(): RedirectResponse
    {
        return $this->authenticate(true);
    }

    /**
     * Memproses autentikasi Google.
     */
    private function authenticate(bool $isRegister): RedirectResponse
    {
        $destination = $isRegister ? 'register' : 'login';

        try {
            $callbackRoute = $isRegister
                ? 'google.register.callback'
                : 'google.callback';

            /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
            $driver = Socialite::driver('google');

            $googleUser = $driver
                ->redirectUrl(localized_route($callbackRoute))
                ->user();

            $email = Str::lower(trim((string) $googleUser->getEmail()));
            $googleId = (string) $googleUser->getId();

            $emailVerified = filter_var(
                $googleUser->user['email_verified'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );

            // Validasi identitas Google.
            if (
                $email === ''
                || $googleId === ''
                || ! $emailVerified
            ) {
                return $this->redirectWithError(
                    $destination,
                    'Email Google tidak valid atau belum terverifikasi.',
                    'Google email is invalid or has not been verified.'
                );
            }

            // Periksa apakah Google ID sudah terhubung.
            $user = User::query()
                ->where('google_id', $googleId)
                ->first();

            if ($user) {
                return $this->loginUser($user, $destination);
            }

            // Periksa apakah email sudah terdaftar.
            $user = User::query()
                ->whereRaw('LOWER(email) = ?', [$email])
                ->first();

            if ($user) {
                // Tolak jika email sudah terhubung dengan Google lain.
                if (
                    filled($user->google_id)
                    && $user->google_id !== $googleId
                ) {
                    return $this->redirectWithError(
                        $destination,
                        'Akun ini sudah terhubung dengan akun Google lain.',
                        'This account is already linked to another Google account.'
                    );
                }

                // Hubungkan Google dengan akun yang sudah tersedia.
                $user->forceFill([
                    'google_id' => $googleId,
                ])->save();

                return $this->loginUser($user, $destination);
            }

            // Login Google tidak boleh membuat akun baru.
            if (! $isRegister) {
                return $this->redirectWithError(
                    'login',
                    'Email Google belum terdaftar. Silakan daftar terlebih dahulu menggunakan formulir registrasi.',
                    'This Google email is not registered. Please create an account first.'
                );
            }

            // Buat akun baru untuk Register Google.
            $name = trim((string) $googleUser->getName());

            if ($name === '') {
                $name = Str::before($email, '@');
            }

            $user = new User();

            $user->forceFill([
                'name' => $name,
                'email' => $email,
                'google_id' => $googleId,
                'password' => Hash::make(Str::random(64)),
                'email_verified_at' => now(),
                'whatsapp' => null,
                'role' => 'customer',
            ])->save();

            return $this->loginUser($user, 'register');

        } catch (Throwable $exception) {
            report($exception);

            return $this->redirectWithError(
                $destination,
                'Autentikasi Google gagal. Silakan coba kembali.',
                'Google authentication failed. Please try again.'
            );
        }
    }

    /**
     * Login pengguna dan regenerasi session.
     */
    private function loginUser(
        User $user,
        string $destination
    ): RedirectResponse {
        Auth::login($user);

        request()->session()->regenerate();

        $message = $this->message(
            'Berhasil masuk menggunakan Google.',
            'Successfully signed in with Google.'
        );

        if ($destination === 'register') {
            $message = $this->message(
                'Registrasi menggunakan Google berhasil. Selamat datang!',
                'Google registration successful. Welcome!'
            );
        }

        return redirect()
            ->intended(localized_route('home'))
            ->with('success', $message);
    }

    /**
     * Menghasilkan pesan sesuai bahasa aplikasi.
     */
    private function message(
        string $indonesian,
        string $english
    ): string {
        return app()->getLocale() === 'en'
            ? $english
            : $indonesian;
    }

    /**
     * Mengembalikan pengguna ke halaman autentikasi.
     */
    private function redirectWithError(
        string $destination,
        string $indonesian,
        string $english
    ): RedirectResponse {
        $route = $destination === 'register'
            ? 'register'
            : 'login';

        return redirect()
            ->to(localized_route($route))
            ->withErrors([
                'google' => $this->message(
                    $indonesian,
                    $english
                ),
            ]);
    }
}
