<?php

namespace App\Models;

use App\Modules\Core\Services\NikEncryptor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RumahWarga extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rumah_warga';

    protected $fillable = [
        'kode_registrasi',
        'nama_kepala_keluarga',
        'nik_enc', 'nik_hash',
        'no_kk_enc', 'no_kk_hash',
        'jumlah_anggota',
        'luas_rumah',
        'pekerjaan', 'penghasilan', 'status_kepemilikan',
        'alamat',
        'latitude', 'longitude',
        'wilayah_id', 'surveyor_id',
        'status',
    ];

    protected $casts = [
        'luas_rumah' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'penghasilan' => 'integer',
        'jumlah_anggota' => 'integer',
    ];

    public const STATUS_DRAF = 'Draf';

    public const STATUS_DIAJUKAN = 'Diajukan';

    public const STATUS_PERLU_PERBAIKAN = 'Perlu Perbaikan';

    public const STATUS_LAYAK_HUNI = 'Layak Huni';

    public const STATUS_PENERIMA_BANTUAN = 'Penerima Bantuan';

    public const STATUS_DALAM_RENOVASI = 'Dalam Renovasi';

    public const STATUS_SELESAI = 'Selesai';

    public const STATUS_LIST = [
        self::STATUS_DRAF,
        self::STATUS_DIAJUKAN,
        self::STATUS_PERLU_PERBAIKAN,
        self::STATUS_LAYAK_HUNI,
        self::STATUS_PENERIMA_BANTUAN,
        self::STATUS_DALAM_RENOVASI,
        self::STATUS_SELESAI,
    ];

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function surveyor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'surveyor_id');
    }

    public function survei(): HasMany
    {
        return $this->hasMany(SurveiKelayakan::class, 'rumah_id');
    }

    public function surveiAktif()
    {
        return $this->survei()->first();
    }

    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatus::class, 'rumah_id')->orderBy('id', 'asc');
    }

    public function bisaDiedit(): bool
    {
        return in_array($this->status, [self::STATUS_DRAF, self::STATUS_PERLU_PERBAIKAN]);
    }

    public function nikTersamar(): string
    {
        $nik = app(NikEncryptor::class)->decryptNik($this->nik_enc);
        if (strlen($nik) < 16) {
            return $nik;
        }

        return substr($nik, 0, 6).str_repeat('*', 6).substr($nik, -4);
    }

    public function noKkTersamar(): string
    {
        $noKk = app(NikEncryptor::class)->decryptNoKk($this->no_kk_enc);
        if (strlen($noKk) < 16) {
            return $noKk;
        }

        return substr($noKk, 0, 4).str_repeat('*', 8).substr($noKk, -4);
    }

    public function scopeStatus(Builder $q, string $status): Builder
    {
        return $q->where('status', $status);
    }
}
