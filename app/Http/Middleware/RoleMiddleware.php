<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (empty($roles) || in_array($user->role, $roles)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk tindakan ini.'
            ], 403);
        }

        // Friendly redirect based on role
        if ($user->isKasir()) {
            return redirect()->route('kasir.index')->with('error', 'Akses ditolak! Halaman tersebut hanya dapat diakses oleh Admin.');
        }

        abort(403, 'Akses tidak diizinkan.');
    }
}
