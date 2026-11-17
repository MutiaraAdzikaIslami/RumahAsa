<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgresRenovasi extends Model
{
    use SoftDeletes;

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

    public function fotos(): HasMany
    {
        return $this->hasMany(ProgresFoto::class, 'progres_renovasi_id');
    }
}
