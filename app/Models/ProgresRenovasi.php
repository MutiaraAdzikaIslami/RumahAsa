<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgresRenovasi extends Model
{
    protected $table = 'progres_renovasi';

    protected $fillable = [
        'penerima_bantuan_id', 'tahap_pekerjaan',
        'persentase', 'tanggal_progres', 'catatan',
    ];

    protected $casts = ['persentase' => 'integer'];

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(PenerimaBantuan::class, 'penerima_bantuan_id');
    }
}
