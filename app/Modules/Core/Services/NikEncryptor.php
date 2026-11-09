<?php

namespace App\Modules\Core\Services;

use Illuminate\Support\Facades\Crypt;

/**
 * FR-10: Perlindungan data pribadi warga (NIK & No. KK).
 *
 * Strategi:
 *  - Enkripsi (AES-256-GCM via Laravel Crypt) untuk menyimpan nilai asli
 *  - HMAC SHA-256 untuk kolom hash (cek duplikat & pencarian tanpa reveal nilai asli)
 */
class NikEncryptor
{
    public function __construct(private readonly string $hmacKey)
    {
    }

    public function encryptNik(string $nik): string
    {
        $nik = preg_replace('/\D/', '', $nik);
        return Crypt::encryptString($nik);
    }

    public function decryptNik(string $encrypted): string
    {
        return Crypt::decryptString($encrypted);
    }

    public function hashNik(string $nik): string
    {
        $nik = preg_replace('/\D/', '', $nik);
        return hash_hmac('sha256', $nik, $this->hmacKey);
    }

    public function encryptNoKk(string $noKk): string
    {
        $noKk = preg_replace('/\D/', '', $noKk);
        return Crypt::encryptString($noKk);
    }

    public function decryptNoKk(string $encrypted): string
    {
        return Crypt::decryptString($encrypted);
    }

    public function hashNoKk(string $noKk): string
    {
        $noKk = preg_replace('/\D/', '', $noKk);
        return hash_hmac('sha256', $noKk, $this->hmacKey);
    }

    public function tersamar(string $encrypted, int $prefix = 6, int $suffix = 4): string
    {
        try {
            $plain = $this->decryptNik($encrypted);
        } catch (\Throwable) {
            return '******';
        }

        $len = strlen($plain);
        if ($len <= ($prefix + $suffix)) return str_repeat('*', $len);

        return substr($plain, 0, $prefix)
             . str_repeat('*', $len - $prefix - $suffix)
             . substr($plain, -$suffix);
    }
}