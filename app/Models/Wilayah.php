<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wilayah extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'wilayah'; // PENTING: nama tabel di DB adalah 'wilayah', bukan 'wilayahs'

    protected $fillable = ['kode', 'nama', 'tingkat', 'parent_id', 'path', 'is_aktif'];

    protected $casts = ['is_aktif' => 'boolean'];

    // === Konstanta tingkat hierarki (sesuai data Kemendagri yang ada di DB) ===
    public const TINGKAT_PROVINSI       = 'provinsi';
    public const TINGKAT_KABUPATEN_KOTA = 'kabupaten';  // ← DIUBAH: dari 'kabupaten_kota' jadi 'kabupaten' (match data DB)
    public const TINGKAT_KECAMATAN      = 'kecamatan';
    public const TINGKAT_KELURAHAN      = 'kelurahan';
    public const TINGKAT_RW             = 'rw';
    public const TINGKAT_RT             = 'rt';

    public const URUTAN_TINGKAT = [
        self::TINGKAT_PROVINSI       => 1,
        self::TINGKAT_KABUPATEN_KOTA => 2,
        self::TINGKAT_KECAMATAN      => 3,
        self::TINGKAT_KELURAHAN      => 4,
        self::TINGKAT_RW             => 5,
        self::TINGKAT_RT             => 6,
    ];

    public const LABEL_TINGKAT = [
        self::TINGKAT_PROVINSI       => 'Provinsi',
        self::TINGKAT_KABUPATEN_KOTA => 'Kabupaten/Kota',
        self::TINGKAT_KECAMATAN      => 'Kecamatan',
        self::TINGKAT_KELURAHAN      => 'Kelurahan/Desa',
        self::TINGKAT_RW             => 'RW',
        self::TINGKAT_RT             => 'RT',
    ];

    public const TINGKAT_DROPDOWN = [
        self::TINGKAT_KABUPATEN_KOTA,
        self::TINGKAT_KECAMATAN,
        self::TINGKAT_KELURAHAN,
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Wilayah::class, 'parent_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function rumahWarga(): HasMany
    {
        return $this->hasMany(RumahWarga::class);
    }

    public function scopeDescendants(Builder $q, self $parent): Builder
    {
        return $q->where('path', 'like', $parent->path . '/%');
    }

    public function scopeDropdownTingkat(Builder $q): Builder
    {
        return $q->whereIn('tingkat', self::TINGKAT_DROPDOWN);
    }

    public static function isValidParent(?int $parentId, string $childTingkat): bool
    {
        $childUrutan = self::URUTAN_TINGKAT[$childTingkat] ?? 0;

        if ($childUrutan === 1) {
            return $parentId === null;
        }
        if ($parentId === null) return false;

        $parent = self::find($parentId);
        if (!$parent) return false;

        $parentUrutan = self::URUTAN_TINGKAT[$parent->tingkat] ?? 0;

        return $parentUrutan === ($childUrutan - 1);
    }

    public function labelTingkat(): string
    {
        return self::LABEL_TINGKAT[$this->tingkat] ?? ucfirst($this->tingkat);
    }
}
