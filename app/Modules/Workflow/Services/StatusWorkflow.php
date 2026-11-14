<?php

namespace App\Modules\Workflow\Services;

use App\Models\AuditLog;
use App\Models\RiwayatStatus;
use App\Models\RumahWarga;
use App\Models\User;
use App\Modules\Core\Exceptions\InvalidStatusTransitionException;
use App\Modules\Core\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Dev Story: StatusWorkflow adalah SATU-SATUNYA pintu perubahan status rumah.
 *
 * Prinsip:
 *  - Setiap transisi status HANYA boleh lewat class ini
 *  - Setiap method memvalidasi transisi valid -> throw jika tidak
 *  - Wrap dalam DB::transaction()
 *  - Insert RiwayatStatus + AuditLog dalam transaksi yang sama
 *
 * Matriks transisi (status_dari => [status_ke_yg_diizinkan, ...]):
 *  Draf             -> Diajukan
 *  Diajukan         -> Perlu Perbaikan | Layak Huni | Penerima Bantuan
 *  Perlu Perbaikan  -> Diajukan
 *  Penerima Bantuan -> Dalam Renovasi
 *  Dalam Renovasi   -> Selesai
 *  Layak Huni       -> [] (final)
 *  Selesai          -> [] (final)
 */
class StatusWorkflow
{
    /**
     * Matriks transisi status yang diizinkan.
     */
    private const TRANSITIONS = [
        RumahWarga::STATUS_DRAF => [
            RumahWarga::STATUS_DIAJUKAN,
        ],
        RumahWarga::STATUS_DIAJUKAN => [
            RumahWarga::STATUS_PERLU_PERBAIKAN,
            RumahWarga::STATUS_LAYAK_HUNI,
            RumahWarga::STATUS_PENERIMA_BANTUAN,
        ],
        RumahWarga::STATUS_PERLU_PERBAIKAN => [
            RumahWarga::STATUS_DIAJUKAN,
        ],
        RumahWarga::STATUS_PENERIMA_BANTUAN => [
            RumahWarga::STATUS_DALAM_RENOVASI,
        ],
        RumahWarga::STATUS_DALAM_RENOVASI => [
            RumahWarga::STATUS_SELESAI,
        ],
        // Final states — tidak ada transisi keluar
        RumahWarga::STATUS_LAYAK_HUNI => [],
        RumahWarga::STATUS_SELESAI    => [],
    ];

    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {
    }

    /**
     * Cek apakah transisi dari $from ke $to diizinkan.
     */
    public function canTransition(string $from, string $to): bool
    {
        $allowed = self::TRANSITIONS[$from] ?? [];
        return in_array($to, $allowed, true);
    }

    /**
     * UC-05: Surveyor mengajukan survei (Draf/Perlu Perbaikan -> Diajukan).
     */
    public function ajukan(RumahWarga $rumah, ?User $user = null): void
    {
        $this->transition(
            rumah:     $rumah,
            to:        RumahWarga::STATUS_DIAJUKAN,
            user:      $user,
            catatan:   'Survei diajukan ke Verifikator',
            auditAksi: AuditLog::AKSI['SURVEI_SUBMIT'],
        );
    }

    /**
     * Verifikator minta perbaikan survei (Diajukan -> Perlu Perbaikan).
     */
    public function mintaPerbaikan(RumahWarga $rumah, string $catatan, ?User $user = null): void
    {
        $this->transition(
            rumah:     $rumah,
            to:        RumahWarga::STATUS_PERLU_PERBAIKAN,
            user:      $user,
            catatan:   $catatan,
            auditAksi: AuditLog::AKSI['STATUS_CHANGE'],
        );
    }

    /**
     * UC-09: Verifikator menetapkan kategori manual (Diajukan -> Layak Huni / Penerima Bantuan).
     *
     * @param string $kategori 'layak_huni' atau 'tidak_layak_huni'
     */
    public function tetapkanKategori(RumahWarga $rumah, string $kategori, string $catatan, ?User $user = null): void
    {
        $to = $kategori === 'tidak_layak_huni'
            ? RumahWarga::STATUS_PENERIMA_BANTUAN
            : RumahWarga::STATUS_LAYAK_HUNI;

        $this->transition(
            rumah:     $rumah,
            to:        $to,
            user:      $user,
            catatan:   $catatan,
            auditAksi: AuditLog::AKSI['STATUS_CHANGE'],
        );
    }

    /**
     * Verifikator merevisi keputusan sebelum renovasi dimulai.
     * Toggle antara Layak Huni <-> Penerima Bantuan.
     */
    public function revisiKeputusan(RumahWarga $rumah, string $catatan, ?User $user = null): void
    {
        $to = $rumah->status === RumahWarga::STATUS_LAYAK_HUNI
            ? RumahWarga::STATUS_PENERIMA_BANTUAN
            : RumahWarga::STATUS_LAYAK_HUNI;

        $this->transition(
            rumah:     $rumah,
            to:        $to,
            user:      $user,
            catatan:   $catatan,
            auditAksi: AuditLog::AKSI['STATUS_CHANGE'],
        );
    }

    /**
     * UC-13: Verifikator mencatat penyaluran bantuan (Penerima Bantuan -> Dalam Renovasi).
     */
    public function mulaiRenovasi(RumahWarga $rumah, string $catatan, ?User $user = null): void
    {
        $this->transition(
            rumah:     $rumah,
            to:        RumahWarga::STATUS_DALAM_RENOVASI,
            user:      $user,
            catatan:   $catatan,
            auditAksi: AuditLog::AKSI['STATUS_CHANGE'],
        );
    }

    /**
     * Verifikator menetapkan status Selesai (Dalam Renovasi -> Selesai).
     * Syarat: progres terakhir 100%. Pengecekan dilakukan di controller modul Renovasi.
     */
    public function selesaikan(RumahWarga $rumah, ?string $catatan = null, ?User $user = null): void
    {
        $this->transition(
            rumah:     $rumah,
            to:        RumahWarga::STATUS_SELESAI,
            user:      $user,
            catatan:   $catatan ?? 'Renovasi selesai (100%)',
            auditAksi: AuditLog::AKSI['STATUS_CHANGE'],
        );
    }

    /**
     * Jalankan transisi status dalam satu transaksi DB.
     * Menulis: rumah_warga.status + riwayat_status + audit_log.
     *
     * @throws InvalidStatusTransitionException
     */
    private function transition(
        RumahWarga $rumah,
        string $to,
        ?User $user,
        string $catatan,
        string $auditAksi,
    ): void {
        $from = $rumah->status;
        $user = $user ?? Auth::user();

        if (!$this->canTransition($from, $to)) {
            throw InvalidStatusTransitionException::make($from, $to);
        }

        DB::transaction(function () use ($rumah, $from, $to, $user, $catatan, $auditAksi) {
            // 1. Update status rumah
            $rumah->status = $to;
            $rumah->save();

            // 2. Tulis riwayat status (append-only)
            RiwayatStatus::create([
                'rumah_id'    => $rumah->id,
                'user_id'     => $user?->id,
                'status_dari' => $from,
                'status_ke'   => $to,
                'catatan'     => $catatan,
            ]);

            // 3. Tulis audit log
            $this->auditLogger->catat(
                aksi:      $auditAksi,
                entitas:   'rumah_warga',
                entitasId: $rumah->id,
                dataBaru:  ['status' => $to, 'catatan' => $catatan],
                dataLama:  ['status' => $from],
                user:      $user,
            );
        });
    }

    /**
     * Daftar transisi yang diizinkan dari status tertentu.
     */
    public function allowedTransitions(string $from): array
    {
        return self::TRANSITIONS[$from] ?? [];
    }
}