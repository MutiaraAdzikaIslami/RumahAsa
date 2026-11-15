<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Models\AuditLog;
use App\Modules\Core\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SessionController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    /**
     * FR-02: Logout manual.
     * - Tulis audit LOGOUT
     * - Logout user (invalidate session)
     * - Regenerate CSRF token
     * - Redirect ke halaman login
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $this->auditLogger->catat(
                aksi:      AuditLog::AKSI['LOGOUT'],
                entitas:   'users',
                entitasId: $user->id,
            );
        }

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah keluar dari sistem.');
    }

    /**
     * FR-02: Ping session untuk extend timeout.
     * Dipanggil frontend setiap 5 menit untuk menandai user masih aktif.
     */
    public function ping()
    {
        return response()->json([
            'ok' => true,
            'ts' => now()->timestamp,
        ]);
    }
}