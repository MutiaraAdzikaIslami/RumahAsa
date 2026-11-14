<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveiKomponen extends Model
{
    protected $table = 'survei_komponen';

    protected $fillable = [
        'survei_id',
        'komponen_id',
        'opsi_kondisi_id',
        'catatan',
    ];

    public function survei(): BelongsTo
    {
        return $this->belongsTo(SurveiKelayakan::class, 'survei_id');
    }

    public function komponen(): BelongsTo
    {
        return $this->belongsTo(KomponenPenilaian::class, 'komponen_id');
    }

    public function opsiKondisi(): BelongsTo
    {
        return $this->belongsTo(OpsiKondisi::class, 'opsi_kondisi_id');
    }

    public function foto(): HasMany
    {
        return $this->hasMany(SurveiFoto::class, 'survei_komponen_id');
    }
}
