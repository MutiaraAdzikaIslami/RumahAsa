<?php

namespace App\Modules\Core\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * FR-03: Password policy — alphanumeric (huruf + angka), tanpa simbol/spasi.
 * Min 8, max 64 karakter.
 *
 * Contoh valid: "RumahAsa2025", "password123", "AdminBdm21"
 * Contoh invalid: "pass word" (spasi), "pass@word" (simbol), "short" (<8)
 */
class AlphanumericPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (empty($value)) {
            $fail('Password wajib diisi.');
            return;
        }

        if (!is_string($value)) {
            $fail('Format password tidak valid.');
            return;
        }

        if (!preg_match('/^[A-Za-z0-9]{8,64}$/', $value)) {
            $fail('Password harus 8-64 karakter, hanya huruf dan angka (tanpa simbol/spasi).');
            return;
        }
    }
}