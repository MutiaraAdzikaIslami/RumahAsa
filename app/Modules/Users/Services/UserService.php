<?php

namespace App\Modules\Users\Services;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Modules\Core\Services\AuditLogger;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * FR-05: User management service.
 */
class UserService
{
    public function __construct(
        private readonly Hasher $hasher,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function generatePassword(): string
    {
        $alphabet = (string) config('rumahasa.password_alphabet',
            'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789');

        $length = (int) config('rumahasa.password_length', 12);
        $length = max(8, min(64, $length));

        $alphabetLen = strlen($alphabet);
        if ($alphabetLen < 8) {
            $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
            $alphabetLen = strlen($alphabet);
        }

        do {
            $plain = '';
            $bytes = random_bytes($length);
            for ($i = 0; $i < $length; $i++) {
                $plain .= $alphabet[ord($bytes[$i]) % $alphabetLen];
            }
        } while (
            !preg_match('/[A-Z]/', $plain)
            || !preg_match('/[a-z]/', $plain)
            || !preg_match('/[0-9]/', $plain)
        );

        return $plain;
    }

    public function hashPassword(string $plaintext): string
    {
        return $this->hasher->make($plaintext);
    }

    public function normalizeUsername(string $username): string
    {
        return strtolower(trim($username));
    }

    public function createUser(array $data): array
    {
        $plain = $this->generatePassword();
        $hashed = $this->hashPassword($plain);

        $payload = [
            'role_id'              => (int) $data['role_id'],
            'wilayah_id'           => $data['wilayah_id'] ?? null,
            'nama'                 => trim((string) $data['nama']),
            'username'             => $this->normalizeUsername((string) $data['username']),
            'email'                => isset($data['email']) && $data['email'] !== ''
                ? strtolower(trim((string) $data['email']))
                : null,
            'password'             => $hashed,
            'must_change_password' => true,
            'is_aktif'             => (bool) ($data['is_aktif'] ?? true),
            'failed_login_count'   => 0,
            'locked_until'         => null,
        ];

        $user = DB::transaction(function () use ($payload) {
            return User::create($payload);
        });

        $this->auditLogger->catat(
            aksi:      AuditLog::AKSI['USER_CREATE'],
            entitas:   'users',
            entitasId: $user->id,
            dataBaru:  [
                'role_id'  => $user->role_id,
                'username' => $user->username,
                'nama'     => $user->nama,
                'is_aktif' => $user->is_aktif,
            ],
        );

        return [
            'user'               => $user,
            'password_plaintext' => $plain,
        ];
    }

    public function updateUser(User $user, array $data): User
    {
        $dataLama = $user->only(['role_id', 'wilayah_id', 'nama', 'username', 'email', 'is_aktif']);

        $user->forceFill([
            'role_id'    => (int) $data['role_id'],
            'wilayah_id' => array_key_exists('wilayah_id', $data) ? $data['wilayah_id'] : $user->wilayah_id,
            'nama'       => trim((string) $data['nama']),
            'username'   => $this->normalizeUsername((string) $data['username']),
            'email'      => isset($data['email']) && $data['email'] !== ''
                ? strtolower(trim((string) $data['email']))
                : null,
            'is_aktif'   => (bool) ($data['is_aktif'] ?? $user->is_aktif),
        ])->save();

        $this->auditLogger->catat(
            aksi:      AuditLog::AKSI['USER_UPDATE'],
            entitas:   'users',
            entitasId: $user->id,
            dataBaru:  $user->fresh()->only(['role_id', 'wilayah_id', 'nama', 'username', 'email', 'is_aktif']),
            dataLama:  $dataLama,
        );

        return $user->fresh();
    }

    public function resetPassword(User $user): array
    {
        $plain  = $this->generatePassword();
        $hashed = $this->hashPassword($plain);

        $user->forceFill([
            'password'             => $hashed,
            'must_change_password' => true,
            'password_changed_at'  => null,
            'failed_login_count'   => 0,
            'locked_until'         => null,
        ]);
        $user->setRememberToken(Str::random(60));
        $user->save();

        $this->auditLogger->catat(
            aksi:      AuditLog::AKSI['PASSWORD_RESET_BY_ADMIN'],
            entitas:   'users',
            entitasId: $user->id,
            dataBaru:  ['reset_at' => now()->toDateTimeString()],
        );

        return [
            'user'               => $user->fresh(),
            'password_plaintext' => $plain,
        ];
    }

    public function deleteUser(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->delete();

            $this->auditLogger->catat(
                aksi:      AuditLog::AKSI['USER_DELETE'],
                entitas:   'users',
                entitasId: $user->id,
                dataLama:  ['username' => $user->username, 'nama' => $user->nama],
            );
        });
    }

    public function restoreUser(int $userId): ?User
    {
        $user = User::withTrashed()->find($userId);
        if (!$user) {
            return null;
        }

        $user->restore();

        $this->auditLogger->catat(
            aksi:      AuditLog::AKSI['USER_RESTORE'],
            entitas:   'users',
            entitasId: $user->id,
        );

        return $user->fresh();
    }

    public function toggleAktif(User $user): User
    {
        return DB::transaction(function () use ($user) {
            if ($user->isAdmin() && $user->is_aktif) {
                $adminAktifCount = User::whereHas('role', fn ($q) => $q->where('kode', 'admin'))
                    ->where('is_aktif', true)
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->get(['id'])
                    ->count();

                if ($adminAktifCount <= 1) {
                    throw new \RuntimeException('Tidak dapat menonaktifkan admin terakhir yang aktif.');
                }
            }

            $baru = !$user->is_aktif;
            $user->forceFill(['is_aktif' => $baru])->save();

            $this->auditLogger->catat(
                aksi:      $baru ? AuditLog::AKSI['USER_ACTIVATE'] : AuditLog::AKSI['USER_DEACTIVATE'],
                entitas:   'users',
                entitasId: $user->id,
                dataBaru:  ['is_aktif' => $baru],
            );

            return $user->fresh();
        });
    }

    public function listRoles(): array
    {
        return Role::orderBy('id')->get(['id', 'kode', 'nama'])->toArray();
    }
}