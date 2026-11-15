<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Role $adminRole;
    private Role $surveyorRole;
    private Role $pimpinanRole;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\WilayahContohSeeder::class);

        $this->adminRole = Role::where('kode', 'admin')->first();
        $this->surveyorRole = Role::where('kode', 'surveyor')->first();
        $this->pimpinanRole = Role::where('kode', 'pimpinan')->first();

        $this->admin = User::factory()->create([
            'role_id'              => $this->adminRole->id,
            'must_change_password' => false,
            'is_aktif'             => true,
        ]);
    }

    /**
     * AC-01: Admin mereset password user aktif -> Berhasil (200 OK, return password_sementara).
     */
    public function test_ac01_admin_mereset_password_user_aktif_berhasil(): void
    {
        $target = User::factory()->create([
            'role_id'              => $this->surveyorRole->id,
            'is_aktif'             => true,
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($this->admin)->postJson("/users/{$target->id}/reset-password");

        $response->assertOk();
        $response->assertJsonStructure([
            'message',
            'user' => ['id', 'nama', 'username', 'role', 'wilayah'],
            'password_sementara',
        ]);

        $passwordSementara = $response->json('password_sementara');
        $this->assertNotEmpty($passwordSementara);
        $this->assertSame(12, strlen($passwordSementara));
        $this->assertMatchesRegularExpression('/[A-Z]/', $passwordSementara);
        $this->assertMatchesRegularExpression('/[a-z]/', $passwordSementara);
        $this->assertMatchesRegularExpression('/[0-9]/', $passwordSementara);
    }

    /**
     * AC-02: Non-Admin (Surveyor, Pimpinan, Guest) mencoba mereset password -> Ditolak (403/401).
     */
    public function test_ac02_pengguna_non_admin_mencoba_mereset_password_ditolak(): void
    {
        $target = User::factory()->create();

        $surveyor = User::factory()->create([
            'role_id'              => $this->surveyorRole->id,
            'must_change_password' => false,
        ]);

        $pimpinan = User::factory()->create([
            'role_id'              => $this->pimpinanRole->id,
            'must_change_password' => false,
        ]);

        // 1. Surveyor -> 403 Forbidden
        $responseSurveyor = $this->actingAs($surveyor)->postJson("/users/{$target->id}/reset-password");
        $responseSurveyor->assertForbidden();

        // 2. Pimpinan -> 403 Forbidden
        $responsePimpinan = $this->actingAs($pimpinan)->postJson("/users/{$target->id}/reset-password");
        $responsePimpinan->assertForbidden();

        // 3. Guest (tanpa login) -> 401 Unauthorized
        auth()->logout();
        $responseGuest = $this->postJson("/users/{$target->id}/reset-password");
        $responseGuest->assertUnauthorized();
    }

    /**
     * AC-03: Reset password dengan ID user tidak ditemukan -> Ditolak (404 Not Found).
     */
    public function test_ac03_reset_password_dengan_id_user_tidak_ditemukan_ditolak(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/users/999999/reset-password');
        $response->assertNotFound();
    }

    /**
     * AC-04: Reset password pada user yang telah di-soft-delete -> Ditolak (404 Not Found).
     */
    public function test_ac04_reset_password_pada_user_soft_deleted_ditolak(): void
    {
        $target = User::factory()->create();
        $target->delete();

        $this->assertSoftDeleted('users', ['id' => $target->id]);

        $response = $this->actingAs($this->admin)->postJson("/users/{$target->id}/reset-password");
        $response->assertNotFound();
    }

    /**
     * AC-05: Reset password pada user nonaktif (is_aktif = false) -> Diizinkan, tetapi status akun tetap nonaktif.
     */
    public function test_ac05_reset_password_pada_user_nonaktif_diizinkan_tetapi_tetap_nonaktif_dan_tidak_bisa_login(): void
    {
        $target = User::factory()->create([
            'is_aktif'             => false,
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($this->admin)->postJson("/users/{$target->id}/reset-password");

        $response->assertOk();
        $target->refresh();

        // Status is_aktif tetap false (tidak otomatis aktif)
        $this->assertFalse($target->is_aktif);
        $this->assertTrue($target->must_change_password);

        // User nonaktif tetap tidak bisa login meskipun menggunakan password sementara yang baru
        $passwordSementara = $response->json('password_sementara');
        auth()->logout();

        $loginResponse = $this->post('/login', [
            'username' => $target->username,
            'password' => $passwordSementara,
        ]);

        $loginResponse->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    /**
     * AC-06: Password baru tersimpan dalam bentuk hash (bcrypt), bukan plaintext.
     */
    public function test_ac06_password_baru_tersimpan_dalam_bentuk_hash(): void
    {
        $target = User::factory()->create();

        $response = $this->actingAs($this->admin)->postJson("/users/{$target->id}/reset-password");

        $response->assertOk();
        $passwordSementara = $response->json('password_sementara');

        $target->refresh();
        $this->assertNotSame($passwordSementara, $target->password);
        $this->assertTrue(Hash::check($passwordSementara, $target->password));
    }

    /**
     * AC-07: Flag must_change_password bernilai true, password_changed_at null, dan akun terkunci dibuka.
     */
    public function test_ac07_flag_must_change_password_bernilai_true_dan_akun_terkunci_dibuka(): void
    {
        $target = User::factory()->create([
            'must_change_password' => false,
            'password_changed_at'  => now(),
            'locked_until'         => now()->addMinutes(15),
            'failed_login_count'   => 5,
        ]);

        $response = $this->actingAs($this->admin)->postJson("/users/{$target->id}/reset-password");

        $response->assertOk();
        $target->refresh();

        $this->assertTrue($target->must_change_password);
        $this->assertNull($target->password_changed_at);
        $this->assertNull($target->locked_until);
        $this->assertSame(0, $target->failed_login_count);
    }

    /**
     * AC-08: User login dengan password sementara -> Berhasil autentikasi, diarahkan ke form ganti password.
     */
    public function test_ac08_user_login_dengan_password_sementara_diarahkan_ke_form_ganti_password(): void
    {
        $target = User::factory()->create([
            'is_aktif'             => true,
            'must_change_password' => false,
        ]);

        $resetResponse = $this->actingAs($this->admin)->postJson("/users/{$target->id}/reset-password");
        $passwordSementara = $resetResponse->json('password_sementara');

        auth()->logout();

        $loginResponse = $this->post('/login', [
            'username' => $target->username,
            'password' => $passwordSementara,
        ]);

        $loginResponse->assertRedirect(route('password.change'));
        $loginResponse->assertSessionHas('warning');
        $this->assertAuthenticatedAs($target);
    }

    /**
     * AC-09: User dengan status wajib ganti password mencoba akses fitur lain -> Dicegah oleh middleware.
     */
    public function test_ac09_user_dengan_status_wajib_ganti_password_mencoba_akses_fitur_lain_dicegah(): void
    {
        $target = User::factory()->create([
            'must_change_password' => true,
            'is_aktif'             => true,
        ]);

        // Akses halaman web protected
        $webResponse = $this->actingAs($target)->get('/dashboard');
        $webResponse->assertRedirect(route('password.change'));

        // Akses JSON request protected
        $jsonResponse = $this->actingAs($target)->getJson('/dashboard');
        $jsonResponse->assertStatus(403);
        $jsonResponse->assertJson(['message' => 'Anda wajib mengganti password sebelum dapat mengakses fitur ini.']);
    }

    /**
     * AC-10: User mengganti password sementara ke password baru yang valid via PUT /password -> Berhasil.
     */
    public function test_ac10_user_mengganti_password_sementara_ke_password_baru_yang_valid_berhasil(): void
    {
        $target = User::factory()->create([
            'is_aktif'             => true,
            'must_change_password' => false,
        ]);

        $resetResponse = $this->actingAs($this->admin)->postJson("/users/{$target->id}/reset-password");
        $passwordSementara = $resetResponse->json('password_sementara');

        // Pastikan target refresh agar model memegang hash password sementara
        $target->refresh();

        $changeResponse = $this->actingAs($target)->put('/password', [
            'current_password'      => $passwordSementara,
            'password'              => 'BaruAman12345',
            'password_confirmation' => 'BaruAman12345',
        ]);

        $changeResponse->assertRedirect(route('dashboard'));
        $target->refresh();

        $this->assertTrue(Hash::check('BaruAman12345', $target->password));
        $this->assertFalse($target->must_change_password);
        $this->assertNotNull($target->password_changed_at);
    }

    /**
     * AC-11: Setelah ganti password selesai -> must_change_password false, akses normal terbuka kembali.
     */
    public function test_ac11_setelah_ganti_password_selesai_akses_fitur_normal_terbuka(): void
    {
        $target = User::factory()->create([
            'role_id'              => $this->surveyorRole->id,
            'is_aktif'             => true,
            'must_change_password' => true,
        ]);

        // Simulasikan password berhasil diganti
        $target->update([
            'password'             => Hash::make('PasswordBaruValid123'),
            'must_change_password' => false,
            'password_changed_at'  => now(),
        ]);

        $response = $this->actingAs($target)->get('/dashboard');
        $response->assertOk();
    }

    /**
     * AC-12: Password lama sebelum reset tidak dapat digunakan lagi.
     */
    public function test_ac12_password_lama_sebelum_reset_tidak_dapat_digunakan_lagi(): void
    {
        $passwordLama = 'PasswordLama123';
        $target = User::factory()->withPassword($passwordLama)->create([
            'is_aktif' => true,
        ]);

        $this->actingAs($this->admin)->postJson("/users/{$target->id}/reset-password");

        auth()->logout();

        $loginResponse = $this->post('/login', [
            'username' => $target->username,
            'password' => $passwordLama,
        ]);

        $loginResponse->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    /**
     * AC-13: Transaksi database rollback jika terjadi kegagalan sistem saat proses reset password.
     */
    public function test_ac13_kegagalan_di_tengah_proses_reset_memicu_rollback_transaksi(): void
    {
        $passwordSemula = 'PasswordAsal123';
        $target = User::factory()->withPassword($passwordSemula)->create([
            'must_change_password' => false,
            'locked_until'         => null,
        ]);

        // Simulasikan kegagalan transaksi dengan mock / listener yang melempar exception saat saving
        User::saving(function ($user) use ($target) {
            if ($user->id === $target->id && $user->must_change_password === true) {
                throw new \Exception('Simulasi kegagalan database');
            }
        });

        try {
            $service = app(\App\Modules\Users\Services\UserService::class);
            $service->resetPassword($target);
            $this->fail('Harusnya melempar Exception');
        } catch (\Exception $e) {
            $this->assertSame('Simulasi kegagalan database', $e->getMessage());
        }

        // Pastikan tidak ada data yang berubah secara parsial di database
        $target->refresh();
        $this->assertFalse($target->must_change_password);
        $this->assertTrue(Hash::check($passwordSemula, $target->password));
    }

    /**
     * AC-14: Reset password tidak mengubah role_id, wilayah_id, atau status is_aktif target.
     */
    public function test_ac14_reset_password_tidak_mengubah_role_wilayah_atau_status_aktif(): void
    {
        $wilayah = Wilayah::first();
        $target = User::factory()->create([
            'role_id'              => $this->surveyorRole->id,
            'wilayah_id'           => $wilayah->id,
            'is_aktif'             => true,
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($this->admin)->postJson("/users/{$target->id}/reset-password");

        $response->assertOk();
        $target->refresh();

        $this->assertSame($this->surveyorRole->id, $target->role_id);
        $this->assertSame($wilayah->id, $target->wilayah_id);
        $this->assertTrue($target->is_aktif);
    }

    /**
     * AC-15: Remember token lama dibatalkan / diubah saat password direset oleh Admin.
     */
    public function test_ac15_remember_token_lama_dibatalkan_saat_reset_password(): void
    {
        $oldToken = 'old_remember_token_string_1234567890';
        $target = User::factory()->create([
            'remember_token' => $oldToken,
        ]);

        $response = $this->actingAs($this->admin)->postJson("/users/{$target->id}/reset-password");

        $response->assertOk();
        $target->refresh();

        $this->assertNotEmpty($target->remember_token);
        $this->assertNotSame($oldToken, $target->remember_token);
    }

    /**
     * Security: Admin tidak dapat mereset password akun sendiri (harus lewat menu ganti password).
     */
    public function test_keamanan_admin_tidak_bisa_reset_password_diri_sendiri(): void
    {
        $response = $this->actingAs($this->admin)->postJson("/users/{$this->admin->id}/reset-password");

        $response->assertStatus(422);
        $response->assertJsonPath('message', fn ($m) => str_contains($m, 'tidak dapat mereset password akun sendiri'));
    }

    /**
     * Audit: Reset password mencatat audit log dengan aksi PASSWORD_RESET_BY_ADMIN.
     */
    public function test_reset_password_mencatat_audit_log(): void
    {
        $target = User::factory()->create();

        $this->actingAs($this->admin)->postJson("/users/{$target->id}/reset-password");

        $this->assertDatabaseHas('audit_log', [
            'user_id'    => $this->admin->id,
            'aksi'       => AuditLog::AKSI['PASSWORD_RESET_BY_ADMIN'],
            'entitas'    => 'users',
            'entitas_id' => $target->id,
        ]);
    }
}
