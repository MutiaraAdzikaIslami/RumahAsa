<?php

namespace App\Modules\Bantuan\Services;

use App\Models\PenerimaBantuan;
use App\Models\ProgresRenovasi;
use App\Models\RumahWarga;

/**
 * Aturan progres renovasi:
 *  1. Progres hanya boleh diubah saat rumah berstatus Dalam Renovasi (terkunci setelah Selesai).
 *  2. Tanggal progres tidak boleh sebelum tanggal penyaluran bantuan.
 *  3. Persentase tidak boleh turun: tidak lebih rendah dari progres pada tanggal yang sama atau
 *     sebelumnya, dan tidak lebih tinggi dari progres pada tanggal sesudahnya.
 */
class AturanProgres
{
    public function periksaUbah(PenerimaBantuan $bantuan): ?string
    {
        if ($bantuan->rumah->status !== RumahWarga::STATUS_DALAM_RENOVASI) {
            return 'Progres hanya dapat diubah selama rumah berstatus Dalam Renovasi.';
        }

        return null;
    }

    public function periksaNilai(PenerimaBantuan $bantuan, int $persentase, string $tanggal, ?int $abaikanId = null): ?string
    {
        if ($bantuan->tanggal_bantuan && $tanggal < $bantuan->tanggal_bantuan) {
            return "Tanggal progres tidak boleh sebelum tanggal penyaluran bantuan ({$bantuan->tanggal_bantuan}).";
        }

        $lain = ProgresRenovasi::where('penerima_bantuan_id', $bantuan->id)
            ->when($abaikanId, fn ($q) => $q->where('id', '!=', $abaikanId));

        $batasBawah = (clone $lain)->where('tanggal_progres', '<=', $tanggal)->max('persentase');
        if ($batasBawah !== null && $persentase < (int) $batasBawah) {
            return "Persentase tidak boleh lebih rendah dari progres sebelumnya ({$batasBawah}%).";
        }

        $batasAtas = (clone $lain)->where('tanggal_progres', '>', $tanggal)->min('persentase');
        if ($batasAtas !== null && $persentase > (int) $batasAtas) {
            return "Persentase tidak boleh melebihi progres pada tanggal berikutnya ({$batasAtas}%).";
        }

        return null;
    }
}
