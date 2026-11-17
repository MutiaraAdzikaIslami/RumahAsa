<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penilaian extends Model
{
    use HasFactory;

    protected $table = 'penilaian';

    public const KATEGORI_LAYAK_HUNI = 'layak_huni';

    public const KATEGORI_TIDAK_LAYAK_HUNI = 'tidak_layak_huni';

    protected $fillable = [
        'rumah_id',
        'user_id',
        'kategori',
        'catatan',
        'alasan_revisi',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    public function rumah(): BelongsTo
    {
        return $this->belongsTo(RumahWarga::class, 'rumah_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
