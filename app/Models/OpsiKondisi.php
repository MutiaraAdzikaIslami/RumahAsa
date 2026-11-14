<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpsiKondisi extends Model
{
    protected $table = 'opsi_kondisi';

    protected $fillable = ['komponen_id', 'kode', 'label', 'deskripsi', 'otomatis_rtlh', 'urutan'];

    protected $casts = ['otomatis_rtlh' => 'boolean'];

    public function komponen(): BelongsTo
    {
        return $this->belongsTo(KomponenPenilaian::class, 'komponen_id');
    }

    public function surveiKomponen(): HasMany
    {
        return $this->hasMany(SurveiKomponen::class, 'opsi_kondisi_id');
    }
}
