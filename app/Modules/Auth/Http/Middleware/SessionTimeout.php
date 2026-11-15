<?php

namespace App\Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * FR-02: Auto-timeout sesi.
 *
 * Strategi:
 *  - Track last_activity di session
 *  - Jika idle > session.lifetime (default 60 menit) -> force logout
 *  - Setiap request yang lewat -> update last_activity
 *
 * Frontend polling /session/ping setiap 5 menit untuk extend (menandai user masih aktif).
 */
class SessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $lifetimeMinutes = (int) config('session.lifetime', 60);
        $lastActivity    = $request->session()->get('last_activity', now()->timestamp);
        $idleSeconds     = now()->timestamp - (int) $lastActivity;

        // Jika idle lebih dari lifetime -> force logout
        if ($idleSeconds > ($lifetimeMinutes * 60)) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('warning', 'Sesi berakhir karena tidak ada aktivitas selama ' . $lifetimeMinutes . ' menit. Silakan login kembali.');
        }

        // Update last_activity
        $request->session()->put('last_activity', now()->timestamp);

        return $next($request);
    }
}