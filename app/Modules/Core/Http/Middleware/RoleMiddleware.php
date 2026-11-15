<?php

namespace App\Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Membatasi akses endpoint berdasarkan role pengguna atau Gate permission.
     * Mendukung:
     * - Single role: 'admin', 'surveyor', 'pimpinan', 'verifikator'
     * - Multi role dipisah pipe: 'admin|pimpinan'
     * - Multi role dipisah koma: 'admin', 'pimpinan'
     * - Gate permission: 'manage-users', 'manage-wilayah', 'input-rumah'
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // AC-01: Pengguna belum login mengakses endpoint terlindungi ditolak
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect()->route('login');
        }

        // Kumpulkan semua token role/gate yang diizinkan (pecah pipe '|' dan koma)
        $allowedTokens = [];
        foreach ($roles as $roleParam) {
            foreach (explode('|', $roleParam) as $token) {
                $trimmed = trim($token);
                if ($trimmed !== '') {
                    $allowedTokens[] = $trimmed;
                }
            }
        }

        $userRoleKode = $user->role?->kode;

        // AC-02: Pengguna dengan role yang memiliki kewenangan diizinkan
        foreach ($allowedTokens as $token) {
            // 1. Cocokkan langsung dengan kode role user
            if ($userRoleKode && $userRoleKode === $token) {
                return $next($request);
            }

            // 2. Evaluasi Gate langsung
            if (Gate::forUser($user)->allows($token)) {
                return $next($request);
            }

            // 3. Evaluasi Gate dengan prefix 'access-'
            if (Gate::forUser($user)->allows('access-' . $token)) {
                return $next($request);
            }
        }

        // AC-02: Role tidak sesuai ditolak (403 Forbidden)
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Anda tidak memiliki kewenangan untuk mengakses halaman ini.',
            ], 403);
        }

        abort(403, 'Anda tidak memiliki kewenangan untuk mengakses halaman ini.');
    }
}
