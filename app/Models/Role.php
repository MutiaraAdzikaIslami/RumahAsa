<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['kode', 'nama', 'deskripsi'];

    // Konstanta untukhindari typo
    public const KODE = [
        'surveyor'    => 'surveyor',
        'verifikator' => 'verifikator',
        'admin'       => 'admin',
        'pimpinan'    => 'pimpinan',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Helper
    public function isSurveyor(): bool    { return $this->kode === self::KODE['surveyor']; }
    public function isVerifikator(): bool{ return $this->kode === self::KODE['verifikator']; }
    public function isAdmin(): bool      { return $this->kode === self::KODE['admin']; }
    public function isPimpinan(): bool  { return $this->kode === self::KODE['pimpinan']; }
}
