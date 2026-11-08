<?php

namespace App\Modules\Core\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * FR-09: Service pencatat audit log (append-only).
 * Dipanggil dari modul lain untuk menulis jejak aktivitas penting.
 */
class AuditLogger
{
    public function __construct(private Request $request)
    {
    }

    public function catat(
        string $aksi,
        ?string $entitas = null,
        ?int $entitasId = null,
        ?array $dataBaru = null,
        ?array $dataLama = null,
        ?User $user = null,
    ): AuditLog {
        $authUser = Auth::user();

        return AuditLog::create([
            'user_id'      => $user?->id ?? $authUser?->id,
            'aksi'         => $aksi,
            'entitas'      => $entitas,
            'entitas_id'   => $entitasId,
            'data_baru'    => $dataBaru,
            'data_lama'    => $dataLama,
            'ip_address'   => $this->request->ip(),
            'user_agent'   => substr((string) $this->request->userAgent(), 0, 255),
        ]);
    }

    public function catatLoginGagal(?string $username): AuditLog
    {
        $user = $username ? User::where('username', $username)->first() : null;

        return AuditLog::create([
            'user_id'    => $user?->id,
            'aksi'       => AuditLog::AKSI['LOGIN_FAILED'],
            'entitas'    => 'users',
            'entitas_id' => $user?->id,
            'data_baru'  => ['username_input' => $username],
            'ip_address' => $this->request->ip(),
            'user_agent' => substr((string) $this->request->userAgent(), 0, 255),
        ]);
    }
}
