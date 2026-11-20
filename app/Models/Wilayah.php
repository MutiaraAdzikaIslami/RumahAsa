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

    public const TINGKAT_PROVINSI       = 'provinsi';
    public const TINGKAT_KABUPATEN_KOTA = 'kabupaten';
    public const TINGKAT_KECAMATAN      = 'kecamatan';
    public const TINGKAT_KELURAHAN      = 'kelurahan';
    public const TINGKAT_RW             = 'rw';
    public const TINGKAT_RT             = 'rt';

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

    /**
     * Mengambil rantai hierarki wilayah ke atas (rt -> rw -> kelurahan -> kecamatan -> kabupaten -> provinsi).
     */
    public function hierarki(): array
    {
        $chain = [];
        $curr = $this;
        $visited = [];
        while ($curr && !in_array($curr->id, $visited, true)) {
            $visited[] = $curr->id;
            $chain[$curr->tingkat] = $curr;
            $curr = $curr->parent;
        }
        return $chain;
    }

    /**
     * Format RT/RW singkat, contoh: "RT 007/RW 007".
     */
    public function rtRwLabel(): ?string
    {
        $h = $this->hierarki();
        $parts = [];
        if (isset($h['rt'])) {
            $rtNama = trim($h['rt']->nama);
            $parts[] = str_starts_with(strtoupper($rtNama), 'RT') ? $rtNama : 'RT ' . $rtNama;
        }
        if (isset($h['rw'])) {
            $rwNama = trim($h['rw']->nama);
            $parts[] = str_starts_with(strtoupper($rwNama), 'RW') ? $rwNama : 'RW ' . $rwNama;
        }
        return !empty($parts) ? implode('/', $parts) : null;
    }

    /**
     * Format wilayah untuk kolom tabel (contoh: "Kel. Nyamplong, Kec. Gayam").
     */
    public function namaKelurahanAtauWilayah(): string
    {
        $h = $this->hierarki();
        $parts = [];
        if (isset($h['kelurahan'])) {
            $nama = $h['kelurahan']->nama;
            $parts[] = str_starts_with(strtolower($nama), 'kel') ? $nama : 'Kel. ' . $nama;
        }
        if (isset($h['kecamatan'])) {
            $nama = $h['kecamatan']->nama;
            $parts[] = str_starts_with(strtolower($nama), 'kec') ? $nama : 'Kec. ' . $nama;
        }
        if (!empty($parts)) {
            return implode(', ', $parts);
        }
        if (isset($h['kabupaten'])) {
            return ucwords(strtolower($h['kabupaten']->nama));
        }
        return $this->nama;
    }

    /**
     * Format wilayah lengkap dari tingkat terendah hingga kabupaten/kota.
     */
    public function namaLengkap(): string
    {
        $h = $this->hierarki();
        $parts = [];
        if ($rtRw = $this->rtRwLabel()) {
            $parts[] = $rtRw;
        }
        if (isset($h['kelurahan'])) {
            $nama = $h['kelurahan']->nama;
            $parts[] = str_starts_with(strtolower($nama), 'kel') ? $nama : 'Kel. ' . $nama;
        }
        if (isset($h['kecamatan'])) {
            $nama = $h['kecamatan']->nama;
            $parts[] = str_starts_with(strtolower($nama), 'kec') ? $nama : 'Kec. ' . $nama;
        }
        if (isset($h['kabupaten'])) {
            $parts[] = ucwords(strtolower($h['kabupaten']->nama));
        }
        return !empty($parts) ? implode(', ', $parts) : $this->nama;
    }

    /**
     * Format wilayah tugas untuk kartu sidebar kiri (contoh: "Kabupaten Sumenep" atau "Kec. Gayam, Kab. Sumenep").
     */
    public function namaWilayahTugasSidebar(): string
    {
        $h = $this->hierarki();
        if (isset($h['kecamatan'], $h['kabupaten'])) {
            return 'Kec. ' . ucwords(strtolower($h['kecamatan']->nama)) . ', ' . ucwords(strtolower($h['kabupaten']->nama));
        }
        if (isset($h['kabupaten'])) {
            return ucwords(strtolower($h['kabupaten']->nama));
        }
        if (isset($h['kecamatan'])) {
            return 'Kecamatan ' . ucwords(strtolower($h['kecamatan']->nama));
        }
        return $this->nama;
    }
}