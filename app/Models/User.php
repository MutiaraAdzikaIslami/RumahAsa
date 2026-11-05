<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as AuthenticatableBase;
use Illuminate\Notifications\Notifiable;

class User extends AuthenticatableBase
{
    use Notifiable, SoftDeletes;

    protected $fillable = [
        'role_id',
        'wilayah_id',
        'nama',
        'username',
        'email',
        'password',
        'must_change_password',
        'password_changed_at',
        'locked_until',
        'failed_login_count',
        'is_aktif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'must_change_password'  => 'boolean',
        'is_aktif'              => 'boolean',
        'password_changed_at'  => 'datetime',
        'locked_until'          => 'datetime',
    ];

    // === Relasi ===
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function rumahDiinput(): HasMany
    {
        return $this->hasMany(RumahWarga::class, 'surveyor_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatus::class);
    }

    // === Helper role check ===
    public function isSurveyor(): bool    { return $this->role?->isSurveyor() ?? false; }
    public function isVerifikator(): bool { return $this->role?->isVerifikator() ?? false; }
    public function isAdmin(): bool       { return $this->role?->isAdmin() ?? false; }
    public function isPimpinan(): bool   { return $this->role?->isPimpinan() ?? false; }

    // === FR-01: lock status ===
    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function canLogin(): bool
    {
        return $this->is_aktif && !$this->isLocked() && $this->deleted_at === null;
    }

    // === FR-03: harus ganti password? ===
    public function mustChangePassword(): bool
    {
        return (bool) $this->must_change_password;
    }

    // === Scope aktif ===
    public function scopeAktif(Builder $q): Builder
    {
        return $q->where('is_aktif', true)->whereNull('deleted_at');
    }

    // Survei yang diinput oleh surveyor ini (relasi tidak langsung)
    public function surveiDiinput(): HasMany
    {
        return $this->hasMany(SurveiKelayakan::class, 'surveyor_id');
    }
}
