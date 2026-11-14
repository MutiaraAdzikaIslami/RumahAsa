<?php

namespace App\Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * FR-03: Paksa user ganti password bila must_change_password = true.
 *
 * Allow-list route yang tetap bisa diakses:
 *  - password.change (GET form)
 *  - password.update (PUT)
 *  - logout
 *  - session.ping
 *
 * Selain itu -> redirect ke password.change dengan warning.
 */
class MustChangePassword
{
    /** Route name allow-list */
    private const ALLOWED_ROUTES = [
        'password.change',
        'password.update',
        'logout',
        'session.ping',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->must_change_password) {
            return $next($request);
        }

        $currentRoute = $request->route()?->getName();

        if (in_array($currentRoute, self::ALLOWED_ROUTES, true)) {
            return $next($request);
        }

        // JSON request -> 403
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Anda wajib mengganti password sebelum dapat mengakses fitur ini.',
            ], 403);
        }

        return redirect()
            ->route('password.change')
            ->with('warning', 'Anda menggunakan password sementara. Wajib ganti password sebelum dapat mengakses fitur lain.');
    }
}