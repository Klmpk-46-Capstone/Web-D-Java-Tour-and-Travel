<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('pages.auth.login');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('pages.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'whatsapp' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/',
                'unique:users,whatsapp',
            ],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->numbers(),
            ],
            'terms' => ['accepted'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp.regex' => 'Format nomor WhatsApp tidak valid.',
            'whatsapp.unique' => 'Nomor WhatsApp sudah terdaftar.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Masukkan alamat email yang valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.letters' => 'Kata sandi harus memiliki huruf.',
            'password.numbers' => 'Kata sandi harus memiliki angka.',
            'terms.accepted' => 'Kamu harus menyetujui syarat dan ketentuan.',
        ]);

        $user = User::create([
            'name' => trim($validated['name']),
            'whatsapp' => preg_replace(
                '/[\s()\-]/',
                '',
                $validated['whatsapp']
            ),
            'email' => Str::lower(trim($validated['email'])),
            'password' => $validated['password'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('home')
            ->with('success', 'Pendaftaran berhasil. Selamat datang!');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Email atau nomor WhatsApp wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $loginValue = trim($validated['login']);
        $throttleKey = Str::lower($loginValue).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput($request->only('login'))
                ->withErrors([
                    'login' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
                ]);
        }

        $field = filter_var($loginValue, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'whatsapp';

        $loginValue = $field === 'email'
            ? Str::lower($loginValue)
            : preg_replace('/[\s()\-]/', '', $loginValue);

        $user = User::query()
            ->where($field, $loginValue)
            ->first();

        if (
            ! $user ||
            ! Auth::attempt([
                $field => $loginValue,
                'password' => $validated['password'],
            ], $request->boolean('remember'))
        ) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withInput($request->only('login'))
                ->withErrors([
                    'login' => 'Email/nomor WhatsApp atau kata sandi tidak sesuai.',
                ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()
            ->intended(route('home'))
            ->with('success', 'Berhasil masuk ke akun kamu.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Kamu telah keluar dari akun.');
    }
}
