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

    protected $table = 'wilayah';

    protected $fillable = ['kode', 'nama', 'tingkat', 'parent_id', 'path', 'is_aktif'];

    protected $casts = ['is_aktif' => 'boolean'];

    public const TINGKAT_KECAMATAN = 'kecamatan';
    public const TINGKAT_KELURAHAN = 'kelurahan';
    public const TINGKAT_RW        = 'rw';
    public const TINGKAT_RT        = 'rt';

    public const URUTAN_TINGKAT = [
        self::TINGKAT_KECAMATAN => 1,
        self::TINGKAT_KELURAHAN => 2,
        self::TINGKAT_RW        => 3,
        self::TINGKAT_RT        => 4,
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

    public static function isValidParent(?int $parentId, string $childTingkat): bool
    {
        if ($childTingkat === self::TINGKAT_KECAMATAN) {
            return $parentId === null;
        }
        if ($parentId === null) return false;

        $parent = self::find($parentId);
        if (!$parent) return false;

        $parentUrutan = self::URUTAN_TINGKAT[$parent->tingkat] ?? 0;
        $childUrutan  = self::URUTAN_TINGKAT[$childTingkat] ?? 0;

        return $parentUrutan === ($childUrutan - 1);
    }
}
