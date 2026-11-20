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
     * Hak akses mengubah data rumah:
     * - Hanya Admin yang berwenang.
     * - Status rumah harus bisa diedit (Menunggu Disurvei atau Draf).
     * - Surveyor, Verifikator, Pimpinan: Ditolak.
     */
    public function update(User $user, RumahWarga $rumah): bool
    {
        if (!$user->isAdmin()) {
            return false;
        }

        if (!$rumah->bisaDiedit()) {
            return false;
        }

        return true;
    }

    /**
     * Hak akses mengisi survei kelayakan:
     * - Hanya Surveyor yang berwenang di wilayah tugas rumah tersebut.
     * - Status rumah harus bisa disurvei (Menunggu Disurvei, Draf, atau Perlu Perbaikan).
     * - Admin, Verifikator, Pimpinan: Ditolak.
     */
    public function survei(User $user, RumahWarga $rumah): bool
    {
        if (!$user->isSurveyor()) {
            return false;
        }

        if (!$this->rumahDalamWilayahSurveyor($user, $rumah)) {
            return false;
        }

        if (!$rumah->bisaDisurvei()) {
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
     * Mendukung hierarki wilayah tingkat Kecamatan:
     * - Surveyor yang ditugaskan pada rumah terkait langsung memiliki hak akses.
     * - Surveyor di tingkat Kecamatan mencakup seluruh Kelurahan, RW, dan RT di bawahnya.
     * - Surveyor dalam lingkup Kecamatan yang sama dengan rumah dapat mengakses data rumah tersebut.
     */
    public function rumahDalamWilayahSurveyor(User $surveyor, RumahWarga $rumah): bool
    {
        // Jika surveyor ini yang secara eksplisit ditugaskan pada rumah tersebut
        if ($rumah->surveyor_id && (int) $rumah->surveyor_id === (int) $surveyor->id) {
            return true;
        }

        $wilayahSurveyor = $surveyor->wilayah;

        // AC-07: Surveyor tanpa wilayah tugas tidak mendapat akses
        if (!$wilayahSurveyor) {
            return false;
        }

        // Jika ID wilayah tugas sama persis dengan wilayah rumah
        if ($rumah->wilayah_id === $wilayahSurveyor->id) {
            return true;
        }

        // Cek hierarki wilayah: path wilayah rumah diawali dengan path wilayah tugas surveyor
        $wilayahRumah = $rumah->wilayah;
        if ($wilayahRumah && !empty($wilayahRumah->path) && !empty($wilayahSurveyor->path)) {
            if (str_starts_with($wilayahRumah->path, rtrim($wilayahSurveyor->path, '/') . '/')) {
                return true;
            }
        }

        // Cek apakah surveyor dan rumah berada dalam satu lingkup Kecamatan yang sama
        if ($wilayahRumah) {
            $kecamatanSurveyorId = $this->resolveKecamatanId($wilayahSurveyor);
            $kecamatanRumahId    = $this->resolveKecamatanId($wilayahRumah);

            if ($kecamatanSurveyorId !== null && $kecamatanSurveyorId === $kecamatanRumahId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mengambil ID Kecamatan dari suatu hierarki wilayah.
     */
    private function resolveKecamatanId(\App\Models\Wilayah $wilayah): ?int
    {
        if ($wilayah->tingkat === \App\Models\Wilayah::TINGKAT_KECAMATAN) {
            return $wilayah->id;
        }

        if (!empty($wilayah->path)) {
            $segments = array_values(array_filter(explode('/', $wilayah->path)));
            // Format path: /provinsi/kabupaten/kecamatan/... (indeks ke-2 adalah kecamatan)
            if (count($segments) >= 3) {
                return (int) $segments[2];
            }
        }

        $current = $wilayah;
        while ($current) {
            if ($current->tingkat === \App\Models\Wilayah::TINGKAT_KECAMATAN) {
                return $current->id;
            }
            $current = $current->parent;
        }

        return null;
    }
}