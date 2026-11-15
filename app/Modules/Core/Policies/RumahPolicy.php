<?php

namespace App\Modules\Core\Policies;

use App\Models\RumahWarga;
use App\Models\User;

/**
 * FR-04: Policy pembatasan akses rumah.
 *
 * Update: Input rumah sekarang dilakukan oleh Admin (bukan Surveyor)
 */
class RumahPolicy
{
    /**
     * AC-03, AC-04, AC-07, AC-08: Hak akses melihat data rumah.
     * - Verifikator, Admin, Pimpinan: Dapat melihat semua data di seluruh wilayah.
     * - Surveyor: Hanya dapat melihat data di dalam wilayah tugasnya (hierarkis).
     * - Surveyor tanpa wilayah tugas: Ditolak (tidak mendapat akses luas).
     */
    public function view(User $user, RumahWarga $rumah): bool
    {
        if ($user->isVerifikator() || $user->isAdmin() || $user->isPimpinan()) {
            return true;
        }

        if ($user->isSurveyor()) {
            return $this->rumahDalamWilayahSurveyor($user, $rumah);
        }

        return false;
    }

    /**
     * FIX: Hanya Admin yang bisa input rumah (bukan Surveyor)
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * AC-05, AC-06, AC-07, AC-10: Hak akses mengubah data rumah.
     * - Hanya Surveyor yang berwenang di wilayah tugas rumah tersebut.
     * - Status rumah harus bisa diedit (Draf atau Perlu Perbaikan).
     * - Pimpinan: Ditolak (read-only).
     */
    public function update(User $user, RumahWarga $rumah): bool
    {
        if ($user->isPimpinan()) {
            return false;
        }

        if (!$user->isSurveyor()) {
            return false;
        }

        if (!$this->rumahDalamWilayahSurveyor($user, $rumah)) {
            return false;
        }

        if (!$rumah->bisaDiedit()) {
            return false;
        }

        return true;
    }

    /**
     * AC-11: Hak akses menghapus data rumah.
     * - Hanya Admin yang boleh menghapus rumah berstatus Draf.
     * - Pimpinan: Ditolak (read-only).
     * - Surveyor, Verifikator: Ditolak.
     */
    public function delete(User $user, RumahWarga $rumah): bool
    {
        if ($user->isPimpinan()) {
            return false;
        }

        if ($user->isAdmin() && $rumah->status === RumahWarga::STATUS_DRAF) {
            return true;
        }

        return false;
    }

    /**
     * Hak akses menilai kelayakan (hanya Verifikator).
     * - Pimpinan: Ditolak.
     */
    public function nilai(User $user, RumahWarga $rumah): bool
    {
        if ($user->isPimpinan()) {
            return false;
        }

        return $user->isVerifikator();
    }

    /**
     * Hak akses melihat NIK lengkap (Verifikator dan Admin).
     * - Pimpinan & Surveyor melihat NIK tersamar.
     */
    public function lihatNikPenuh(User $user): bool
    {
        return $user->isVerifikator() || $user->isAdmin();
    }

    /**
     * Memeriksa apakah data rumah berada dalam lingkup wilayah tugas Surveyor.
     * Mendukung hierarki wilayah (misal: tugas di Kelurahan mencakup seluruh RW dan RT di bawahnya).
     */
    public function rumahDalamWilayahSurveyor(User $surveyor, RumahWarga $rumah): bool
    {
        $wilayahSurveyor = $surveyor->wilayah;

        // AC-07: Surveyor tanpa wilayah tugas tidak mendapat akses
        if (!$wilayahSurveyor) {
            return false;
        }

        // Jika ID wilayah tugas sama persis dengan wilayah rumah
        if ($rumah->wilayah_id === $wilayahSurveyor->id) {
            return true;
        }

        // Jika path wilayah tugas kosong, hanya cocokkan exact ID
        if (empty($wilayahSurveyor->path)) {
            return false;
        }

        // Cek hierarki wilayah: path wilayah rumah harus diawali dengan path wilayah tugas surveyor
        $wilayahRumah = $rumah->wilayah;
        if (!$wilayahRumah || empty($wilayahRumah->path)) {
            return false;
        }

        return str_starts_with($wilayahRumah->path, rtrim($wilayahSurveyor->path, '/') . '/');
    }
}