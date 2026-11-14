<?php

namespace App\Modules\Core\Exceptions;

/**
 * Throw ketika transisi status rumah tidak diizinkan oleh matriks StatusWorkflow.
 *
 * Contoh: Draf -> Selesai (skip semua tahap) -> throw ini.
 */
class InvalidStatusTransitionException extends \RuntimeException
{
    public static function make(string $from, string $to): self
    {
        return new self("Transisi status tidak diizinkan: dari '{$from}' ke '{$to}'.");
    }
}