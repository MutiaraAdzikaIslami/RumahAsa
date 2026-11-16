<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveiKelayakan extends Model
{
    protected $table = 'survei_kelayakan';

    protected $fillable = [
        'rumah_id',
        'surveyor_id',
        'tanggal_survei',
        'dikirim_at',
        'latitude_survei',
        'longitude_survei',
        'lokasi_dicatat_at',
    ];

    protected $casts = [
        'tanggal_survei'    => 'date',
        'dikirim_at'        => 'datetime',
        'lokasi_dicatat_at' => 'datetime',
        'latitude_survei'   => 'decimal:7',
        'longitude_survei'  => 'decimal:7',
    ];

    public function rumah(): BelongsTo
    {
        return $this->belongsTo(RumahWarga::class, 'rumah_id');
    }

    public function surveyor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'surveyor_id');
    }

    public function komponenSurvei(): HasMany
    {
        return $this->hasMany(SurveiKomponen::class, 'survei_id');
    }

    public function apakahLengkap(): bool
    {
        $komponen = $this->komponenSurvei()->withCount(['foto as foto_count'])->get();
        if ($komponen->count() !== 7) return false;
        foreach ($komponen as $k) {
            if ($k->opsi_kondisi_id === null) return false;
            if ($k->foto_count < 1) return false;
        }
        return true;
    }
}
