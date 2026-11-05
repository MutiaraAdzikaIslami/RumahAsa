<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    // FR-09: append-only — tidak ada updated_at / deleted_at
    public const UPDATED_AT = null;
    public const DELETED_AT = null;

    protected $table = 'audit_log';

    protected $fillable = [
        'user_id', 'aksi', 'entitas', 'entitas_id',
        'data_lama', 'data_baru',
        'ip_address', 'user_agent',
    ];

    protected $casts = [
        'data_lama' => 'array',
        'data_baru' => 'array',
    ];

    public $timestamps = false; // hanya created_at

    // Konstanta aksi (FR-09)
    public const AKSI = [
        // Auth
        'LOGIN_SUCCESS'           => 'LOGIN_SUCCESS',
        'LOGIN_FAILED'            => 'LOGIN_FAILED',
        'LOGOUT'                   => 'LOGOUT',
        'PASSWORD_CHANGE'          => 'PASSWORD_CHANGE',
        'PASSWORD_RESET_BY_ADMIN'  => 'PASSWORD_RESET_BY_ADMIN',
        // User management
        'USER_CREATE'              => 'USER_CREATE',
        'USER_UPDATE'               => 'USER_UPDATE',
        'USER_DEACTIVATE'           => 'USER_DEACTIVATE',
        'USER_ACTIVATE'             => 'USER_ACTIVATE',
        'USER_DELETE'               => 'USER_DELETE',
        'USER_RESTORE'              => 'USER_RESTORE',
        // Wilayah
        'WILAYAH_CREATE'            => 'WILAYAH_CREATE',
        'WILAYAH_UPDATE'             => 'WILAYAH_UPDATE',
        'WILAYAH_DELETE'             => 'WILAYAH_DELETE',
        // Rumah & Survei
        'RUMAH_DRAFT_SAVE'           => 'RUMAH_DRAFT_SAVE',
        'RUMAH_CREATE'               => 'RUMAH_CREATE',
        'RUMAH_UPDATE'               => 'RUMAH_UPDATE',
        'RUMAH_DELETE'               => 'RUMAH_DELETE',
        'SURVEI_DRAFT_SAVE'          => 'SURVEI_DRAFT_SAVE',
        'SURVEI_SUBMIT'              => 'SURVEI_SUBMIT',
        'FOTO_UPLOAD'                => 'FOTO_UPLOAD',
        'FOTO_DELETE'                => 'FOTO_DELETE',
        'FOTO_VIEW'                  => 'FOTO_VIEW',
        // Workflow
        'STATUS_CHANGE'              => 'STATUS_CHANGE',
        // (akan ditambah saat modul lain dibuat: VERIFIKASI_*, BANTUAN_*, PROGRES_*, EKSPOR_*)
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
