<?php

namespace App\Modules\Users\Services;

use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * FR-05, FR-06 & FR-07: Service untuk manage user, role, wilayah tugas, dan reset password oleh Admin.
 *
 * Aturan:
 *  - Create: generate password sementara acak (12 char), set must_change_password=true
 *  - Update: hash password baru bila diisi
 *  - Reset Password: Admin generate password sementara baru (12 char), set must_change_password=true, clear lock
 *  - Nonaktifkan: toggle is_aktif (tidak hapus)
 *  - Delete: soft delete, cek constraint (tidak boleh hapus admin terakhir)
 *  - Validasi: Surveyor wajib punya wilayah, role lain wilayah kosong (null)
 */
class UserService
{
    /**
     * Bikin user baru + generate password sementara.
     *
     * @return array{user: User, password_sementara: string}
     */
    public function create(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $this->validateRoleWilayah($data['role_id'] ?? null, $data['wilayah_id'] ?? null);

            $passwordSementara = $this->generatePasswordSementara();

            $user = User::create([
                'role_id'              => $data['role_id'],
                'wilayah_id'           => $data['wilayah_id'] ?? null,
                'nama'                 => $data['nama'],
                'username'             => $data['username'],
                'email'                => $data['email'] ?? null,
                'password'             => Hash::make($passwordSementara),
                'must_change_password' => true,
                'is_aktif'             => $data['is_aktif'] ?? true,
            ]);

            return ['user' => $user, 'password_sementara' => $passwordSementara];
        });
    }

    /**
     * Update user. Password baru di-hash bila diisi. Pakai array_key_exists() untuk handle explicit null.
     */
    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $targetRoleId = array_key_exists('role_id', $data) ? $data['role_id'] : $user->role_id;
            $targetWilayahId = array_key_exists('wilayah_id', $data) ? $data['wilayah_id'] : $user->wilayah_id;

            // Cegah admin mengubah role diri sendiri menjadi non-admin
            if (auth()->id() === $user->id && $targetRoleId !== $user->role_id) {
                $targetRole = Role::find($targetRoleId);
                if ($user->isAdmin() && $targetRole && !$targetRole->isAdmin()) {
                    throw new \RuntimeException('Anda tidak dapat mengubah role akun sendiri menjadi non-admin.');
                }
            }

            $this->validateRoleWilayah($targetRoleId, $targetWilayahId, $user);

            $updateData = [
                'role_id'    => $targetRoleId,
                'wilayah_id' => $targetWilayahId,
                'nama'       => array_key_exists('nama', $data) ? $data['nama'] : $user->nama,
                'username'   => array_key_exists('username', $data) ? $data['username'] : $user->username,
                'email'      => array_key_exists('email', $data) ? $data['email'] : $user->email,
                'is_aktif'   => array_key_exists('is_aktif', $data) ? $data['is_aktif'] : $user->is_aktif,
            ];

            // Hash password baru bila diisi
            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
                $updateData['must_change_password'] = true;
            }

            $user->update($updateData);

            return $user->fresh();
        });
    }

    /**
     * FR-07: Reset password user oleh Admin.
     * - Bungkus dalam atomic DB transaction
     * - Cegah Admin mereset password akun sendiri
     * - Generate password sementara baru (12 char alphanumeric)
     * - Set must_change_password = true (wajib ganti saat login berikutnya)
     * - Clear locked_until & failed_login_count (unlock akun kalau ke-lock)
     * - Clear password_changed_at = null
     * - Invalidate remember token lama
     * - Pertahankan role_id, wilayah_id, dan is_aktif
     *
     * @return array{user: User, password_sementara: string}
     */
    public function resetPassword(User $user): array
    {
        return DB::transaction(function () use ($user) {
            if (auth()->id() === $user->id) {
                throw new \RuntimeException('Anda tidak dapat mereset password akun sendiri. Gunakan menu Ganti Password.');
            }

            $passwordSementara = $this->generatePasswordSementara();

            $user->forceFill([
                'password'              => Hash::make($passwordSementara),
                'must_change_password'  => true,
                'password_changed_at'    => null,
                'locked_until'           => null,
                'failed_login_count'     => 0,
            ])->save();

            // Invalidate remember token
            $user->setRememberToken(Str::random(60));
            $user->save();

            return ['user' => $user->fresh(), 'password_sementara' => $passwordSementara];
        });
    }

    /**
     * Toggle is_aktif.
     */
    public function toggleAktif(User $user): User
    {
        return DB::transaction(function () use ($user) {
            // Cegah admin menonaktifkan diri sendiri
            if (auth()->id() === $user->id) {
                throw new \RuntimeException('Anda tidak dapat menonaktifkan akun sendiri.');
            }

            // Cegah menonaktifkan admin terakhir yang aktif
            if ($user->isAdmin() && $user->is_aktif) {
                $adminAktifCount = User::whereHas('role', fn ($q) => $q->where('kode', 'admin'))
                    ->where('is_aktif', true)
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->count();
                if ($adminAktifCount <= 1) {
                    throw new \RuntimeException('Tidak dapat menonaktifkan admin terakhir yang aktif.');
                }
            }

            $user->update(['is_aktif' => !$user->is_aktif]);

            return $user->fresh();
        });
    }

    /**
     * Soft delete user.
     */
    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            // Cegah admin menghapus diri sendiri
            if (auth()->id() === $user->id) {
                throw new \RuntimeException('Anda tidak dapat menghapus akun sendiri.');
            }

            if ($user->isAdmin()) {
                $adminCount = User::whereHas('role', fn ($q) => $q->where('kode', 'admin'))
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->count();
                if ($adminCount <= 1) {
                    throw new \RuntimeException('Tidak dapat menghapus admin terakhir.');
                }
            }

            $user->delete();
        });
    }

    public function restore(int $userId): User
    {
        $user = User::withTrashed()->findOrFail($userId);
        $user->restore();
        return $user->fresh();
    }

    /**
     * Generate password sementara: 12 char alphanumeric (sesuai AlphanumericPassword rule).
     */
    private function generatePasswordSementara(): string
    {
        // Pastikan ada kombinasi huruf besar, kecil, dan angka
        do {
            $pwd = Str::random(12);
        } while (
            !preg_match('/[A-Z]/', $pwd)
            || !preg_match('/[a-z]/', $pwd)
            || !preg_match('/[0-9]/', $pwd)
        );
        return $pwd;
    }

    /**
     * Validasi: Surveyor wajib punya wilayah, role lain wilayah harus null.
     */
    public function validateRoleWilayah(?int $roleId, ?int $wilayahId, ?User $exceptUser = null): void
    {
        if (!$roleId) {
            throw new \InvalidArgumentException('Role wajib dipilih.');
        }

        $role = Role::find($roleId);
        if (!$role) {
            throw new \InvalidArgumentException('Role tidak ditemukan.');
        }

        if ($role->kode === 'surveyor') {
            if (!$wilayahId) {
                throw new \InvalidArgumentException('Surveyor wajib memiliki wilayah tugas.');
            }
            if (!Wilayah::find($wilayahId)) {
                throw new \InvalidArgumentException('Wilayah tugas tidak ditemukan.');
            }
        } else {
            // Verifikator / Admin / Pimpinan: wilayah harus null
            if ($wilayahId) {
                throw new \InvalidArgumentException(
                    "Role {$role->nama} tidak boleh memiliki wilayah tugas (lihat semua wilayah)."
                );
            }
        }
    }
}
