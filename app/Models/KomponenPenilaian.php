<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KomponenPenilaian extends Model
{
    protected $table = 'komponen_penilaian';

    protected $fillable = ['kode', 'nama', 'urutan', 'deskripsi'];

    public function opsiKondisi(): HasMany
    {
        return $this->hasMany(OpsiKondisi::class, 'komponen_id');
    }

    public function surveiKomponen(): HasMany
    {
        return $this->hasMany(SurveiKomponen::class, 'komponen_id');
    }
}
