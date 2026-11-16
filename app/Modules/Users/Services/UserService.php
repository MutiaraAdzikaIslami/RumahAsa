<?php

namespace App\Modules\Users\Services;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Modules\Core\Services\AuditLogger;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\DB;

/**
 * FR-05: User management service.
 *
 * --------------------------------------------------------------------------
 *  PENTING: PENYEBAB UMUM "password generate tidak cocok dengan DB"
 * --------------------------------------------------------------------------
 *  1. Double-hash. Model User TIDAK boleh menambah cast `password => 'hashed'`
 *     bila service sudah memanggil Hash::make(). Di project ini hashing
 *     HANYA terjadi di method hashPassword() di bawah - sekali saja.
 *  2. Username case-mismatch. LoginController memakai strtolower() pada
 *     input username. Karena itu setiap username wajib disimpan lowercase
 *     di sini agar pencarian saat login selalu match.
 *  3. Plaintext ditampilkan berbeda dengan yang di-hash. Method createUser()
 *     mengembalikan objek berisi `password_plaintext` yang PERSIS sama dengan
 *     string yang di-hash - tidak ada re-generate di tengah jalan.
 * --------------------------------------------------------------------------
 */
class UserService
{
    public function __construct(
        private readonly Hasher $hasher,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    /**
     * Generate satu plaintext password alphanumeric.
     *
     * Karakter ambigu (O, 0, I, l, 1) dihilangkan dari alphabet default
     * supaya tidak keliru saat user mengetik manual. Panjang & alphabet
     * bisa dikonfigurasi via config('rumahasa.password_*').
     *
     * @return string plaintext password (belum di-hash)
     */
    public function generatePassword(): string
    {
        $alphabet = (string) config('rumahasa.password_alphabet',
            'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789');

        $length = (int) config('rumahasa.password_length', 10);
        $length = max(8, min(64, $length)); // enforce rule AlphanumericPassword 8-64

        $alphabetLen = strlen($alphabet);

        // Fallback jika alphabet kosong / tidak valid
        if ($alphabetLen < 8) {
            $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
            $alphabetLen = strlen($alphabet);
        }

        // Generate dengan random_bytes (CSPRNG) - aman secara kriptografis.
        $plain = '';
        $bytes = random_bytes($length);
        for ($i = 0; $i < $length; $i++) {
            $plain .= $alphabet[ord($bytes[$i]) % $alphabetLen];
        }

        return $plain;
    }

    /**
     * Hash sebuah plaintext. Dilakukan TEPAT SEKALI di seluruh siklus hidup
     * password (create maupun reset). Jangan hash ulang di tempat lain.
     */
    public function hashPassword(string $plaintext): string
    {
        return $this->hasher->make($plaintext);
    }

    /**
     * Normalisasi username: lowercase + trim. WAJIB dipanggil sebelum simpan
     * agar cocok dengan LoginController yang juga melakukan strtolower().
     */
    public function normalizeUsername(string $username): string
    {
        return strtolower(trim($username));
    }

    /**
     * FR-05: Buat user baru.
     *
     * @param array $data  keys: role_id, wilayah_id?, nama, username, email?,
     *                     is_aktif. Password digenerate otomatis.
     *
     * @return array{user: User, password_plaintext: string}
     *         Plaintext dikembalikan sekali untuk ditampilkan ke admin -
     *         TIDAK disimpan di log maupun DB.
     */
    public function createUser(array $data): array
    {
        // 1. Generate plaintext SATU KALI.
        $plain = $this->generatePassword();

        // 2. Hash plaintext yang PERSIS sama.
        $hashed = $this->hashPassword($plain);

        // 3. Siapkan data - username wajib lowercase.
        $payload = [
            'role_id'              => (int) $data['role_id'],
            'wilayah_id'           => $data['wilayah_id'] ?? null,
            'nama'                 => trim((string) $data['nama']),
            'username'             => $this->normalizeUsername((string) $data['username']),
            'email'                => isset($data['email']) && $data['email'] !== ''
                ? strtolower(trim((string) $data['email']))
                : null,
            'password'             => $hashed,            // sudah di-hash
            'must_change_password' => true,                // FR-03: wajib ganti saat login pertama
            'is_aktif'             => (bool) ($data['is_aktif'] ?? true),
            'failed_login_count'   => 0,
            'locked_until'         => null,
        ];

        // 4. Simpan dalam transaction.
        $user = DB::transaction(function () use ($payload) {
            return User::create($payload);
        });

        // 5. Audit log.
        $this->auditLogger->catat(
            aksi:      AuditLog::AKSI['USER_CREATE'],
            entitas:   'users',
            entitasId: $user->id,
            dataBaru:  [
                'role_id'  => $user->role_id,
                'username' => $user->username,
                'nama'     => $user->nama,
                'is_aktif' => $user->is_aktif,
                // password_plaintext SENGAJA tidak dicatat demi keamanan.
            ],
        );

        // 6. Kembalikan user + plaintext (untuk ditampilkan sekali).
        return [
            'user'                => $user,
            'password_plaintext'  => $plain,
        ];
    }

    /**
     * FR-05: Update user (tanpa menyentuh password).
     */
    public function updateUser(User $user, array $data): User
    {
        $dataLama = $user->only(['role_id', 'wilayah_id', 'nama', 'username', 'email', 'is_aktif']);

        $user->forceFill([
            'role_id'     => (int) $data['role_id'],
            'wilayah_id'  => $data['wilayah_id'] ?? null,
            'nama'        => trim((string) $data['nama']),
            'username'    => $this->normalizeUsername((string) $data['username']),
            'email'       => isset($data['email']) && $data['email'] !== ''
                ? strtolower(trim((string) $data['email']))
                : null,
            'is_aktif'    => (bool) ($data['is_aktif'] ?? $user->is_aktif),
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

    /**
     * FR-03/FR-05: Reset password user ke nilai acak baru.
     * Flag must_change_password diset true agar user wajib ganti setelah login.
     *
     * @return array{user: User, password_plaintext: string}
     */
    public function resetPassword(User $user): array
    {
        $plain  = $this->generatePassword();
        $hashed = $this->hashPassword($plain);

        $user->forceFill([
            'password'             => $hashed,
            'must_change_password'  => true,
            'password_changed_at'   => null,
            'failed_login_count'   => 0,
            'locked_until'         => null,   // bersihkan lock lama
        ])->save();

        $this->auditLogger->catat(
            aksi:      AuditLog::AKSI['PASSWORD_RESET_BY_ADMIN'],
            entitas:   'users',
            entitasId: $user->id,
            dataBaru:  ['reset_at' => now()->toDateTimeString()],
        );

        return [
            'user'                => $user->fresh(),
            'password_plaintext'  => $plain,
        ];
    }

    /**
     * FR-05: Soft-delete user (tidak benar-benar dihapus, supaya audit utuh).
     */
    public function deleteUser(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->delete(); // soft delete

            $this->auditLogger->catat(
                aksi:      AuditLog::AKSI['USER_DELETE'],
                entitas:   'users',
                entitasId: $user->id,
                dataLama:  ['username' => $user->username, 'nama' => $user->nama],
            );
        });
    }

    /**
     * FR-05: Restore user yang di-soft-delete.
     */
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

    /**
     * FR-05: Toggle status aktif (nonaktifkan tanpa hapus).
     */
    public function toggleAktif(User $user): User
    {
        $baru = !$user->is_aktif;

        $user->forceFill(['is_aktif' => $baru])->save();

        $this->auditLogger->catat(
            aksi:      $baru ? AuditLog::AKSI['USER_ACTIVATE'] : AuditLog::AKSI['USER_DEACTIVATE'],
            entitas:   'users',
            entitasId: $user->id,
            dataBaru:  ['is_aktif' => $baru],
        );

        return $user->fresh();
    }

    /**
     * Helper: list role untuk dropdown form user.
     *
     * @return array<int, array{id: int, kode: string, nama: string}>
     */
    public function listRoles(): array
    {
        return Role::orderBy('id')->get(['id', 'kode', 'nama'])->toArray();
    }
}