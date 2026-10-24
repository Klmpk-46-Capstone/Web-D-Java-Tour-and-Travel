<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('admin.login')->with('warning', 'Akses dibatasi. Silakan otentikasi kredensial CMS OPS Console.');
        }

        if (!auth()->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized access.'], 403);
            }
            return redirect()->route('home')->with('error', 'Akses ditolak. Akun Anda tidak memiliki hak akses CMS Console.');
        }

        if ($role === 'super_admin' && !auth()->user()->isSuperAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses dibatasi. Fitur ini hanya dapat diakses oleh Super Admin.'], 403);
            }
            return redirect()->route('admin.dashboard')->with('error', 'Akses ditolak. Data transaksi & keuangan sensitif hanya dapat diakses oleh Super Admin.');
        }

        return $next($request);
    }
}
