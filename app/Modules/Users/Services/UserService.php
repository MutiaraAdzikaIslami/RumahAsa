<?php

namespace App\Modules\Users\Services;

use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * FR-05: Service untuk manage user oleh Admin.
 *
 * Aturan:
 *  - Create: generate password sementara acak (12 char), set must_change_password=true
 *  - Update: hash password baru bila diisi
 *  - Nonaktifkan: toggle is_aktif (tidak hapus)
 *  - Delete: soft delete, cek constraint (tidak boleh hapus admin terakhir)
 *  - Validasi: Surveyor wajib punya wilayah, role lain wilayah kosong
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
        $this->validateRoleWilayah($data['role_id'] ?? null, $data['wilayah_id'] ?? null);

        $passwordSementara = $this->generatePasswordSementara();

        $user = User::create([
            'role_id'              => $data['role_id'],
            'wilayah_id'           => $data['wilayah_id'],
            'nama'                 => $data['nama'],
            'username'             => $data['username'],
            'email'                 => $data['email'] ?? null,
            'password'              => Hash::make($passwordSementara),
            'must_change_password'  => true,
            'is_aktif'              => $data['is_aktif'] ?? true,
        ]);

        return ['user' => $user, 'password_sementara' => $passwordSementara];
    }

    /**
     * Update user. Password baru di-hash bila diisi.
     */
    public function update(User $user, array $data): User
    {
        $this->validateRoleWilayah(
            $data['role_id'] ?? $user->role_id,
            $data['wilayah_id'] ?? $user->wilayah_id,
            $user
        );

        $updateData = [
            'role_id'    => $data['role_id'] ?? $user->role_id,
            'wilayah_id' => $data['wilayah_id'] ?? $user->wilayah_id,
            'nama'       => $data['nama'] ?? $user->nama,
            'username'   => $data['username'] ?? $user->username,
            'email'      => array_key_exists('email', $data) ? $data['email'] : $user->email,
            'is_aktif'   => $data['is_aktif'] ?? $user->is_aktif,
        ];

        // Hash password baru bila diisi
        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
            $updateData['must_change_password'] = true;
        }

        $user->update($updateData);

        return $user->fresh();
    }

    /**
     * Toggle is_aktif (nonaktifkan / aktifkan kembali).
     */
    public function toggleAktif(User $user): User
    {
        // Cegah admin menonaktifkan diri sendiri
        if (auth()->id() === $user->id) {
            throw new \RuntimeException('Anda tidak dapat menonaktifkan akun sendiri.');
        }

        // Cegah menonaktifkan admin terakhir yang aktif
        if ($user->isAdmin() && $user->is_aktif) {
            $adminAktifCount = User::whereHas('role', fn ($q) => $q->where('kode', 'admin'))
                ->where('is_aktif', true)
                ->whereNull('deleted_at')
                ->count();
            if ($adminAktifCount <= 1) {
                throw new \RuntimeException('Tidak dapat menonaktifkan admin terakhir yang aktif.');
            }
        }

        $user->update(['is_aktif' => !$user->is_aktif]);

        return $user->fresh();
    }

    /**
     * Soft delete user.
     */
    public function delete(User $user): void
    {
        if (auth()->id() === $user->id) {
            throw new \RuntimeException('Anda tidak dapat menghapus akun sendiri.');
        }

        if ($user->isAdmin()) {
            $adminCount = User::whereHas('role', fn ($q) => $q->where('kode', 'admin'))
                ->whereNull('deleted_at')
                ->count();
            if ($adminCount <= 1) {
                throw new \RuntimeException('Tidak dapat menghapus admin terakhir.');
            }
        }

        $user->delete();
    }

    /**
     * Restore user yang di-soft delete.
     */
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
    private function validateRoleWilayah(?int $roleId, ?int $wilayahId, ?User $exceptUser = null): void
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