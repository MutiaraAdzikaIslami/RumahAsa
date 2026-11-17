<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenerimaBantuan extends Model
{
    use HasFactory;

    protected $table = 'penerima_bantuan';

    public const STATUS_MENUNGGU_PENYALURAN = 'Menunggu Penyaluran';

    public const STATUS_DISALURKAN = 'Disalurkan';

    public const STATUS_SELESAI = 'Selesai';

    protected $fillable = [
        'rumah_id',
        'status_bantuan',
        'tanggal_bantuan',
        'jenis_bantuan',
        'nominal',
        'sumber_program',
        'nomor_sk',
    ];

    protected $casts = [
        'tanggal_bantuan' => 'date',
        'nominal' => 'decimal:2',
    ];

    public function rumah(): BelongsTo
    {
        return $this->belongsTo(RumahWarga::class, 'rumah_id');
    }

    public function getTanggalAttribute(): ?string
    {
        return $this->tanggal_bantuan?->format('Y-m-d');
    }

    public function setTanggalAttribute($value): void
    {
        $this->attributes['tanggal_bantuan'] = $value;
    }

    public function getJenisAttribute(): ?string
    {
        return $this->jenis_bantuan;
    }

    public function setJenisAttribute($value): void
    {
        $this->attributes['jenis_bantuan'] = $value;
    }
}
