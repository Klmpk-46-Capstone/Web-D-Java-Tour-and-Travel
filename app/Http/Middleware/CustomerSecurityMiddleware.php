<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerSecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();

            if ($user->is_blacklisted && ! $user->isAdmin()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $reason = $user->blacklist_reason ? " Alasan: {$user->blacklist_reason}." : '';

                return redirect()
                    ->to(localized_route('login'))
                    ->withErrors([
                        'login' => "Akun Anda telah dinonaktifkan/masuk daftar hitam oleh administrator.{$reason} Silakan hubungi customer service kami.",
                    ]);
            }

            // Update real-time activity (throttle setiap 2 menit agar efisien)
            if (! $user->last_seen_at || $user->last_seen_at->lt(now()->subMinutes(2))) {
                $user->forceFill(['last_seen_at' => now()])->saveQuietly();
            }
        }

        return $next($request);
    }
}

