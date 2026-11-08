<?php

namespace App\Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * FR-04: Middleware batasi akses route berdasarkan role via Gate.
 *
 * Penggunaan di route:
 *   ->middleware('role:access-admin')
 *   ->middleware('role:access-admin|access-verifikator')  // multi-gate
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $gate): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $gates = explode('|', $gate);

        foreach ($gates as $g) {
            if (Gate::forUser($user)->allows($g)) {
                return $next($request);
            }
        }

        abort(403, 'Anda tidak memiliki kewenangan untuk mengakses halaman ini.');
    }
}
