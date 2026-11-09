<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Tambahkan Facade ini
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user sudah login DAN memiliki role_id = 1 (Administrator)
        if (Auth::check() && Auth::user()->role_id == 1) {
            return $next($request);
        }

        // Jika bukan Admin, kembalikan ke halaman dashboard dengan pesan error
        return redirect()->route('dashboard')->with('error', 'Akses Ditolak! Halaman ini khusus untuk Administrator.');
    }
}
