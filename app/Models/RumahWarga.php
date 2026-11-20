<?php

namespace App\Models;

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
        'luas_rumah'     => 'decimal:2',
        'latitude'       => 'decimal:7',
        'longitude'      => 'decimal:7',
        'penghasilan'    => 'integer',
        'jumlah_anggota' => 'integer',
    ];

    public const STATUS_MENUNGGU_SURVEI   = 'Menunggu Disurvei';
    public const STATUS_DRAF              = 'Draf';
    public const STATUS_DIAJUKAN          = 'Diajukan';
    public const STATUS_PERLU_PERBAIKAN   = 'Perlu Perbaikan';
    public const STATUS_LAYAK_HUNI        = 'Layak Huni';
    public const STATUS_PENERIMA_BANTUAN  = 'Penerima Bantuan';
    public const STATUS_DALAM_RENOVASI    = 'Dalam Renovasi';
    public const STATUS_SELESAI           = 'Selesai';

    public const STATUS_LIST = [
        self::STATUS_DRAF,
        self::STATUS_DIAJUKAN,
        self::STATUS_PERLU_PERBAIKAN,
        self::STATUS_LAYAK_HUNI,
        self::STATUS_PENERIMA_BANTUAN,
        self::STATUS_DALAM_RENOVASI,
        self::STATUS_SELESAI,
    ];

    /**
     * 8 Status Alur Lengkap untuk kartu statistik dan filter tampilan semua role.
     */
    public const STATUS_ALUR_LIST = [
        self::STATUS_MENUNGGU_SURVEI,
        self::STATUS_DRAF,
        self::STATUS_DIAJUKAN,
        self::STATUS_PERLU_PERBAIKAN,
        self::STATUS_LAYAK_HUNI,
        self::STATUS_PENERIMA_BANTUAN,
        self::STATUS_DALAM_RENOVASI,
        self::STATUS_SELESAI,
    ];

    /**
     * Legenda warna status kategorik yang konsisten di seluruh role.
     */
    public const STATUS_BADGE_CLASSES = [
        self::STATUS_MENUNGGU_SURVEI  => 'bg-slate-100 text-slate-700 border border-slate-300',
        self::STATUS_DRAF             => 'bg-sky-100 text-sky-800 border border-sky-300',
        self::STATUS_DIAJUKAN         => 'bg-blue-100 text-blue-700 border border-blue-300',
        self::STATUS_PERLU_PERBAIKAN  => 'bg-amber-100 text-amber-800 border border-amber-300',
        self::STATUS_LAYAK_HUNI       => 'bg-emerald-100 text-emerald-800 border border-emerald-300',
        self::STATUS_PENERIMA_BANTUAN => 'bg-rose-100 text-rose-700 border border-rose-300',
        self::STATUS_DALAM_RENOVASI   => 'bg-purple-100 text-purple-700 border border-purple-300',
        self::STATUS_SELESAI          => 'bg-teal-100 text-teal-800 border border-teal-300',
    ];

    public const STATUS_DOT_COLORS = [
        self::STATUS_MENUNGGU_SURVEI  => '#64748B',
        self::STATUS_DRAF             => '#0284C7',
        self::STATUS_DIAJUKAN         => '#2563EB',
        self::STATUS_PERLU_PERBAIKAN  => '#D97706',
        self::STATUS_LAYAK_HUNI       => '#059669',
        self::STATUS_PENERIMA_BANTUAN => '#E11D48',
        self::STATUS_DALAM_RENOVASI   => '#7C3AED',
        self::STATUS_SELESAI          => '#0D9488',
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
        return $this->hasMany(RiwayatStatus::class, 'rumah_id');
    }

    public function bisaDiedit(): bool
    {
        return in_array($this->status, [self::STATUS_DRAF, self::STATUS_PERLU_PERBAIKAN]);
    }

    public function nikTersamar(): string
    {
        $nik = app(\App\Modules\Core\Services\NikEncryptor::class)->decryptNik($this->nik_enc);
        if (strlen($nik) < 16) return $nik;
        return substr($nik, 0, 6) . str_repeat('*', 6) . substr($nik, -4);
    }

    public function noKkTersamar(): string
    {
        $noKk = app(\App\Modules\Core\Services\NikEncryptor::class)->decryptNoKk($this->no_kk_enc);
        if (strlen($noKk) < 16) return $noKk;
        return substr($noKk, 0, 4) . str_repeat('*', 8) . substr($noKk, -4);
    }

    public function penerimaBantuan(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PenerimaBantuan::class, 'rumah_id');
    }

    public function scopeStatus(Builder $q, string $status): Builder
    {
        return $q->where('status', $status);
    }

    /**
     * Mengembalikan salah satu dari 8 status alur:
     * Bila status di DB adalah 'Draf' dan belum ada survei yang diisi, maka 'Menunggu Disurvei'.
     */
    public function statusAlur(): string
    {
        if ($this->status === self::STATUS_DRAF) {
            $survei = $this->relationLoaded('survei') ? $this->survei->first() : $this->survei()->first();
            if (!$survei) {
                return self::STATUS_MENUNGGU_SURVEI;
            }
            $hasFilledKomponen = $survei->komponenSurvei()
                ->where(function ($q) {
                    $q->whereNotNull('opsi_kondisi_id')->orWhereNotNull('catatan');
                })->exists();
            return $hasFilledKomponen ? self::STATUS_DRAF : self::STATUS_MENUNGGU_SURVEI;
        }
        return $this->status;
    }

    public function badgeStatusClass(?string $statusOverride = null): string
    {
        $st = $statusOverride ?? $this->statusAlur();
        return self::STATUS_BADGE_CLASSES[$st] ?? 'bg-slate-100 text-slate-700 border border-slate-300';
    }

    /**
     * Format alamat lengkap beserta RT/RW, contoh: "Jl. Griya Nyamplong Asri No. 76, RT 007/RW 007".
     */
    public function alamatLengkap(): string
    {
        $alamat = trim((string) ($this->alamat ?? ''));
        $rtRw = $this->wilayah?->rtRwLabel();
        if ($rtRw && $alamat !== '' && !str_contains(strtoupper($alamat), 'RT ')) {
            return rtrim($alamat, '., ') . ', ' . $rtRw;
        }
        if ($alamat !== '') {
            return $alamat;
        }
        return $rtRw ?: '-';
    }

    /**
     * Format nama wilayah singkat (Kelurahan & Kecamatan), contoh: "Kel. Nyamplong, Kec. Gayam".
     */
    public function wilayahSingkat(): string
    {
        return $this->wilayah?->namaKelurahanAtauWilayah() ?? '-';
    }

    /**
     * Format waktu terakhir diperbarui sesuai mockup: "Hari ini, 08.15", "Kemarin, 16.20", atau "18 Nov 2026".
     */
    public function terakhirDiperbaruiLabel(): string
    {
        $dt = $this->updated_at ?? $this->created_at;
        if (!$dt) {
            return '-';
        }
        $dt = $dt->timezone('Asia/Jakarta');
        if ($dt->isToday()) {
            return 'Hari ini, ' . $dt->format('H.i');
        }
        if ($dt->isYesterday()) {
            return 'Kemarin, ' . $dt->format('H.i');
        }
        $bulanSingkat = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        return $dt->day . ' ' . ($bulanSingkat[$dt->month] ?? $dt->format('M')) . ' ' . $dt->year;
    }
}