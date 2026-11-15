<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatStatus extends Model
{
    // Append-only: tidak ada updated_at / deleted_at
    public const UPDATED_AT = null;
    public const DELETED_AT = null;

    protected $table = 'riwayat_status';

    protected $fillable = [
        'rumah_id', 'user_id',
        'status_dari', 'status_ke',
        'catatan',
    ];

    public $timestamps = false; // hanya created_at

    public function rumah(): BelongsTo
    {
        return $this->belongsTo(RumahWarga::class, 'rumah_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
