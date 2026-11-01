<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /**
     * Menampilkan halaman lupa password.
     */
    public function create(): View
    {
        return view('pages.auth.lupa_password');
    }

    /**
     * Mengirimkan tautan reset password ke email.
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $isEnglish = app()->getLocale() === 'en';

        // Validasi format email.
        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
            ],
        ], [
            'email.required' => $isEnglish
                ? 'Email address is required.'
                : 'Alamat email wajib diisi.',

            'email.email' => $isEnglish
                ? 'Please enter a valid email address.'
                : 'Masukkan alamat email yang valid.',

            'email.max' => $isEnglish
                ? 'Email address is too long.'
                : 'Alamat email terlalu panjang.',
        ]);

        // Normalisasi email.
        $email = Str::lower(trim($validated['email']));

        // Periksa apakah email sudah terdaftar.
        $userExists = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->exists();

        // Jika email belum terdaftar, tampilkan pesan.
        if (! $userExists) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => $isEnglish
                        ? 'Email is not registered. Please register first.'
                        : 'Email Belum Terdaftar. Silakan Registrasi terlebih dahulu.',
                ]);
        }

        // Kirim tautan reset password melalui email.
        $status = Password::sendResetLink([
            'email' => $email,
        ]);

        // Jika email berhasil dikirim.
        if ($status === Password::RESET_LINK_SENT) {
            return back()->with(
                'status',
                $isEnglish
                    ? 'Password reset link has been sent to your email. Please check your inbox.'
                    : 'Tautan reset password berhasil dikirim ke email Anda. Silakan periksa kotak masuk email.'
            );
        }

        // Jika terlalu sering meminta reset password.
        if ($status === Password::RESET_THROTTLED) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => $isEnglish
                        ? 'Please wait before requesting another password reset link.'
                        : 'Silakan tunggu beberapa saat sebelum meminta tautan reset password kembali.',
                ]);
        }

        // Jika terjadi kegagalan lainnya.
        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => $isEnglish
                    ? 'Unable to send the password reset link. Please try again.'
                    : 'Gagal mengirim tautan reset password. Silakan coba kembali.',
            ]);
    }

    /**
     * Menampilkan halaman pengaturan password baru.
     */
    public function edit(Request $request, string $token): View
    {
        return view('pages.auth.reset_password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /**
     * Memproses perubahan password.
     */
    public function update(Request $request): RedirectResponse
    {
        $isEnglish = app()->getLocale() === 'en';

        $validated = $request->validate([
            'token' => [
                'required',
                'string',
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
                PasswordRule::min(8)->letters()->numbers(),
            ],
        ], [
            'email.required' => $isEnglish
                ? 'Email address is required.'
                : 'Alamat email wajib diisi.',

            'email.email' => $isEnglish
                ? 'Please enter a valid email address.'
                : 'Masukkan alamat email yang valid.',

            'password.required' => $isEnglish
                ? 'New password is required.'
                : 'Kata sandi baru wajib diisi.',

            'password.confirmed' => $isEnglish
                ? 'Password confirmation does not match.'
                : 'Konfirmasi kata sandi tidak cocok.',

            'password.min' => $isEnglish
                ? 'Password must contain at least 8 characters.'
                : 'Kata sandi minimal 8 karakter.',

            'password.letters' => $isEnglish
                ? 'Password must contain letters.'
                : 'Kata sandi harus mengandung huruf.',

            'password.numbers' => $isEnglish
                ? 'Password must contain numbers.'
                : 'Kata sandi harus mengandung angka.',
        ]);

        // Memproses reset password.
        $status = Password::reset(
            [
                'email' => Str::lower(trim($validated['email'])),
                'password' => $validated['password'],
                'password_confirmation' => $request->input(
                    'password_confirmation'
                ),
                'token' => $validated['token'],
            ],
            function ($user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // Jika password berhasil diperbarui.
        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->to(localized_route('login'))
                ->with(
                    'success',
                    $isEnglish
                        ? 'Your password has been changed successfully. Please sign in.'
                        : 'Kata sandi berhasil diperbarui. Silakan masuk kembali.'
                );
        }

        // Jika token tidak valid atau kedaluwarsa.
        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => $isEnglish
                    ? 'The password reset link is invalid or has expired.'
                    : 'Tautan reset kata sandi tidak valid atau sudah kedaluwarsa.',
            ]);
    }
}
