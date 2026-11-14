<?php

namespace App\Modules\Core\Exceptions;

class FileValidationException extends \RuntimeException
{
    public static function tooLarge(int $actual, int $max): self
    {
        return new self("Ukuran file melebihi batas maksimal. Diterima: {$actual} bytes, Maks: {$max} bytes.");
    }

    public static function invalidType(string $detected): self
    {
        return new self("Tipe file tidak diizinkan. Hanya JPG/PNG/WebP yang diperbolehkan. Terdeteksi: {$detected}.");
    }

    public static function maxPerKomponen(int $max): self
    {
        return new self("Jumlah foto per komponen sudah mencapai maksimum ({$max}).");
    }
}