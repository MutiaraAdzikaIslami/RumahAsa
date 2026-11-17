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

    protected $casts = ['nominal' => 'decimal:2'];

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
