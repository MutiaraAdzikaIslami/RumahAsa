<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenerimaBantuan extends Model
{
    protected $table = 'penerima_bantuan';

    protected $fillable = [
        'rumah_id', 'status_bantuan', 'tanggal_bantuan',
        'jenis_bantuan', 'sumber_program', 'nominal', 'nomor_sk',
    ];

    protected $casts = [
        'nominal'         => 'decimal:2',
        'tanggal_bantuan' => 'date',
    ];

    public const SUMBER_BADGE_CLASSES = [
        'APBN'    => 'bg-emerald-100 text-emerald-800 border border-emerald-300',
        'APBD'    => 'bg-amber-100 text-amber-800 border border-amber-300',
        'CSR'     => 'bg-blue-100 text-blue-700 border border-blue-300',
        'Swadaya' => 'bg-purple-100 text-purple-700 border border-purple-300',
    ];

    public static function badgeSumberClass(?string $sumber): string
    {
        return self::SUMBER_BADGE_CLASSES[$sumber ?? 'APBD'] ?? 'bg-slate-100 text-slate-700 border border-slate-300';
    }

    public function rumah(): BelongsTo
    {
        return $this->belongsTo(RumahWarga::class, 'rumah_id');
    }

    public function progres(): HasMany
    {
        return $this->hasMany(ProgresRenovasi::class, 'penerima_bantuan_id')
            ->orderBy('tanggal_progres')
            ->orderBy('id');
    }

    /**
     * Persentase progres tertinggi. Karena progres tidak boleh turun,
     * nilai ini sama dengan progres terbaru.
     */
    public function progresTerakhir(): int
    {
        return (int) ($this->progres->max('persentase') ?? 0);
    }
}
