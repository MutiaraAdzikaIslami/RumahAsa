<?php

namespace App\Modules\Bantuan\Services;

use App\Models\PenerimaBantuan;

class AturanProgres
{
    /** Kembalikan pesan galat jika progres tidak boleh diubah, atau null jika boleh. */
    public function periksaUbah(PenerimaBantuan $bantuan): ?string
    {
        return null;
    }

    /** Kembalikan pesan galat jika nilai progres melanggar aturan, atau null jika sah. */
    public function periksaNilai(PenerimaBantuan $bantuan, int $persentase, string $tanggal, ?int $abaikanId = null): ?string
    {
        return null;
    }
}
