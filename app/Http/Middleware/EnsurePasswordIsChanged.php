<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Pastikan user sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 2. Jika status must_change_password adalah true
        if (Auth::user()->must_change_password) {
            
            // Cegah infinite loop: Biarkan user mengakses rute terkait ganti password dan logout
            if ($request->routeIs('password.change') || $request->routeIs('password.update') || $request->routeIs('logout')) {
                return $next($request);
            }

            // Arahkan paksa ke halaman ganti password jika mencoba mengakses halaman lain
            return redirect()->route('password.change');
        }

        // 3. Jika aman, lanjutkan ke halaman yang dituju (misal: Dashboard)
        return $next($request);
    }
}