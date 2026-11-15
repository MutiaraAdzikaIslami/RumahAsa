<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $adminRole = Role::where('kode', 'admin')->first();
        $this->admin = User::factory()->create([
            'role_id'              => $adminRole->id,
            'username'             => 'adminutama',
            'must_change_password' => false,
            'is_aktif'             => true,
        ]);
    }

    private function makeWilayahRt(): Wilayah
    {
        $kec = Wilayah::create(['kode' => 'KEC-T', 'nama' => 'Kec Test', 'tingkat' => 'kecamatan', 'parent_id' => null]);
        $kec->update(['path' => '/' . $kec->id]);
        $rt  = Wilayah::create(['kode' => 'RT-T', 'nama' => 'RT 01', 'tingkat' => 'rt', 'parent_id' => $kec->id]);
        $rt->update(['path' => $kec->path . '/' . $rt->id]);
        return $rt->fresh();
    }

    private function makeNonSurveyorUser(string $roleKode = 'verifikator', string $username = 'usertarget'): User
    {
        $role = Role::where('kode', $roleKode)->first();
        return User::factory()->create([
            'role_id'              => $role->id,
            'wilayah_id'           => null,
            'nama'                 => 'User Target',
            'username'             => $username,
            'email'                => "{$username}@example.com",
            'must_change_password' => false,
            'is_aktif'             => true,
        ]);
    }

    // =========================================================================
    // AC-01: Admin melihat daftar user -> Diizinkan
    // =========================================================================

    public function test_ac01_admin_melihat_daftar_user_diizinkan(): void
    {
        $this->makeNonSurveyorUser('verifikator', 'verifikator_list');

        $response = $this->actingAs($this->admin)->getJson('/users');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'nama', 'username', 'email', 'is_aktif', 'role']
            ]
        ]);
    }

    // =========================================================================
    // AC-02: Pengguna non-Admin melihat daftar user -> Ditolak
    // =========================================================================

    public function test_ac02_pengguna_non_admin_melihat_daftar_user_ditolak(): void
    {
        $surveyorRole = Role::where('kode', 'surveyor')->first();
        $surveyor = User::factory()->create([
            'role_id'              => $surveyorRole->id,
            'wilayah_id'           => $this->makeWilayahRt()->id,
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($surveyor)->getJson('/users');

        $response->assertStatus(403);
    }

    // =========================================================================
    // AC-03: Admin menambahkan user dengan data valid -> Berhasil dibuat
    // =========================================================================

    public function test_ac03_admin_menambahkan_user_dengan_data_valid_berhasil(): void
    {
        $surveyorRole = Role::where('kode', 'surveyor')->first();
        $rt = $this->makeWilayahRt();

        $response = $this->actingAs($this->admin)->postJson('/users', [
            'nama'       => 'Surveyor Baru',
            'username'   => 'surveyorbaru',
            'email'      => 'surveyorbaru@example.com',
            'role_id'    => $surveyorRole->id,
            'wilayah_id' => $rt->id,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['message', 'user', 'password_sementara']);

        $this->assertDatabaseHas('users', [
            'username'             => 'surveyorbaru',
            'must_change_password' => true,
        ]);
    }

    // =========================================================================
    // AC-04: Admin menambahkan user dengan email/username duplikat -> Ditolak
    // =========================================================================

    public function test_ac04_admin_menambahkan_user_dengan_username_duplikat_ditolak(): void
    {
        $target = $this->makeNonSurveyorUser('verifikator', 'existinguser');
        $role = Role::where('kode', 'verifikator')->first();

        $response = $this->actingAs($this->admin)->postJson('/users', [
            'nama'     => 'User Duplikat',
            'username' => 'existinguser',
            'email'    => 'unique_email@example.com',
            'role_id'  => $role->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['username']);
    }

    public function test_ac04_admin_menambahkan_user_dengan_email_duplikat_ditolak(): void
    {
        $target = $this->makeNonSurveyorUser('verifikator', 'existinguser2');
        $role = Role::where('kode', 'verifikator')->first();

        $response = $this->actingAs($this->admin)->postJson('/users', [
            'nama'     => 'User Duplikat Email',
            'username' => 'newusername',
            'email'    => $target->email,
            'role_id'  => $role->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    // =========================================================================
    // AC-05: Admin menambahkan user dengan role tidak valid -> Ditolak
    // =========================================================================

    public function test_ac05_admin_menambahkan_user_dengan_role_tidak_valid_ditolak(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/users', [
            'nama'     => 'User Role Invalid',
            'username' => 'roleinvalid',
            'role_id'  => 999999, // ID tidak ada
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role_id']);
    }

    // =========================================================================
    // AC-06: Admin mengubah data user -> Berhasil
    // =========================================================================

    public function test_ac06_admin_mengubah_data_user_berhasil(): void
    {
        $target = $this->makeNonSurveyorUser('verifikator', 'userupdate');

        $response = $this->actingAs($this->admin)->putJson("/users/{$target->id}", [
            'nama'       => 'Nama Diperbarui',
            'username'   => 'userupdate',
            'email'      => 'update@example.com',
            'role_id'    => $target->role_id,
            'wilayah_id' => null,
            'is_aktif'   => true,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'id'    => $target->id,
            'nama'  => 'Nama Diperbarui',
            'email' => 'update@example.com',
        ]);
    }

    // =========================================================================
    // AC-07: Admin mengubah email/username menjadi milik akun lain -> Ditolak
    // =========================================================================

    public function test_ac07_admin_mengubah_username_menjadi_milik_akun_lain_ditolak(): void
    {
        $userA = $this->makeNonSurveyorUser('verifikator', 'usera');
        $userB = $this->makeNonSurveyorUser('verifikator', 'userb');

        $response = $this->actingAs($this->admin)->putJson("/users/{$userA->id}", [
            'nama'     => $userA->nama,
            'username' => 'userb', // coba ganti jadi username userB
            'role_id'  => $userA->role_id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['username']);
    }

    // =========================================================================
    // AC-08: Admin menonaktifkan akun user -> Status berubah menjadi nonaktif
    // =========================================================================

    public function test_ac08_admin_menonaktifkan_akun_user_status_berubah_nonaktif(): void
    {
        $target = $this->makeNonSurveyorUser('verifikator', 'usertoggle');

        $response = $this->actingAs($this->admin)->patchJson("/users/{$target->id}/toggle-aktif");

        $response->assertOk();
        $this->assertFalse($response->json('is_aktif'));

        $target->refresh();
        $this->assertFalse($target->is_aktif);
    }

    // =========================================================================
    // AC-09: Akun nonaktif mencoba login -> Ditolak
    // =========================================================================

    public function test_ac09_akun_nonaktif_mencoba_login_ditolak(): void
    {
        $role = Role::where('kode', 'verifikator')->first();
        User::factory()->withPassword('password123')->create([
            'role_id'  => $role->id,
            'username' => 'nonaktiflogin',
            'is_aktif' => false,
        ]);

        $response = $this->post('/login', [
            'username' => 'nonaktiflogin',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    // =========================================================================
    // AC-10: Admin mengaktifkan kembali akun nonaktif -> Berhasil
    // =========================================================================

    public function test_ac10_admin_mengaktifkan_kembali_akun_nonaktif_berhasil(): void
    {
        $target = $this->makeNonSurveyorUser('verifikator', 'userreaktif');
        $target->update(['is_aktif' => false]);

        $response = $this->actingAs($this->admin)->patchJson("/users/{$target->id}/toggle-aktif");

        $response->assertOk();
        $this->assertTrue($response->json('is_aktif'));

        $target->refresh();
        $this->assertTrue($target->is_aktif);
    }

    // =========================================================================
    // AC-11: Admin melakukan Soft Delete user -> deleted_at terisi
    // =========================================================================

    public function test_ac11_admin_melakukan_soft_delete_user_berhasil(): void
    {
        $target = $this->makeNonSurveyorUser('verifikator', 'usersoftdel');

        $response = $this->actingAs($this->admin)->deleteJson("/users/{$target->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('users', ['id' => $target->id]);
    }

    // =========================================================================
    // AC-12: User yang telah dihapus mencoba login -> Ditolak
    // =========================================================================

    public function test_ac12_user_yang_telah_dihapus_mencoba_login_ditolak(): void
    {
        $role = Role::where('kode', 'verifikator')->first();
        $deletedUser = User::factory()->withPassword('password123')->create([
            'role_id'  => $role->id,
            'username' => 'deletedlogin',
        ]);
        $deletedUser->delete(); // Soft delete

        $response = $this->post('/login', [
            'username' => 'deletedlogin',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    // =========================================================================
    // AC-13: User yang telah dihapus tidak muncul di daftar aktif
    // =========================================================================

    public function test_ac13_user_yang_telah_dihapus_tidak_muncul_di_daftar_aktif(): void
    {
        $target = $this->makeNonSurveyorUser('verifikator', 'userhiddenfromlist');
        $target->delete();

        $response = $this->actingAs($this->admin)->getJson('/users');

        $response->assertOk();
        $usernames = collect($response->json('data'))->pluck('username');
        $this->assertFalse($usernames->contains('userhiddenfromlist'));
    }

    // =========================================================================
    // AC-14: Pengguna non-Admin mencoba mengelola user -> Ditolak (403)
    // =========================================================================

    public function test_ac14_pengguna_non_admin_mencoba_mengelola_user_ditolak(): void
    {
        $surveyorRole = Role::where('kode', 'surveyor')->first();
        $surveyor = User::factory()->create([
            'role_id'              => $surveyorRole->id,
            'wilayah_id'           => $this->makeWilayahRt()->id,
            'must_change_password' => false,
        ]);

        $target = $this->makeNonSurveyorUser('verifikator', 'usertargetnonadmin');

        // Surveyor mencoba POST
        $this->actingAs($surveyor)->postJson('/users', [
            'nama'     => 'Hacker',
            'username' => 'hacker',
            'role_id'  => $surveyorRole->id,
        ])->assertStatus(403);

        // Surveyor mencoba PUT
        $this->actingAs($surveyor)->putJson("/users/{$target->id}", [
            'nama'     => 'Modified',
            'username' => $target->username,
            'role_id'  => $target->role_id,
        ])->assertStatus(403);

        // Surveyor mencoba DELETE
        $this->actingAs($surveyor)->deleteJson("/users/{$target->id}")->assertStatus(403);

        // Surveyor mencoba toggle-aktif
        $this->actingAs($surveyor)->patchJson("/users/{$target->id}/toggle-aktif")->assertStatus(403);
    }

    // =========================================================================
    // AC-15: Admin mencoba menonaktifkan dirinya sendiri -> Ditolak
    // =========================================================================

    public function test_ac15_admin_mencoba_menonaktifkan_dirinya_sendiri_ditolak(): void
    {
        $response = $this->actingAs($this->admin)->patchJson("/users/{$this->admin->id}/toggle-aktif");

        $response->assertStatus(422);
        $response->assertJsonPath('message', fn ($m) => str_contains($m, 'tidak dapat menonaktifkan akun sendiri'));
    }

    // =========================================================================
    // AC-16: Admin mencoba menghapus dirinya sendiri -> Ditolak
    // =========================================================================

    public function test_ac16_admin_mencoba_menghapus_dirinya_sendiri_ditolak(): void
    {
        // Buat admin kedua agar bukan admin terakhir
        $adminRole = Role::where('kode', 'admin')->first();
        User::factory()->create([
            'role_id'              => $adminRole->id,
            'username'             => 'adminlain',
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($this->admin)->deleteJson("/users/{$this->admin->id}");

        $response->assertStatus(422);
        $response->assertJsonPath('message', fn ($m) => str_contains($m, 'tidak dapat menghapus akun sendiri'));
    }

    // =========================================================================
    // AC-17: Admin terakhir mencoba dinonaktifkan atau dihapus -> Ditolak
    // =========================================================================

    public function test_ac17_admin_terakhir_mencoba_dihapus_ditolak(): void
    {
        // Hanya ada $this->admin sebagai satu-satunya admin
        $adminCount = User::whereHas('role', fn ($q) => $q->where('kode', 'admin'))->count();
        $this->assertEquals(1, $adminCount);

        $response = $this->actingAs($this->admin)->deleteJson("/users/{$this->admin->id}");

        $response->assertStatus(422);
    }

    public function test_ac17_admin_terakhir_mencoba_dinonaktifkan_oleh_admin_lain_dicegah(): void
    {
        $adminRole = Role::where('kode', 'admin')->first();
        // Ada admin target (satu-satunya admin aktif kedua)
        $adminTarget = User::factory()->create([
            'role_id'  => $adminRole->id,
            'is_aktif' => true,
        ]);

        // Nonaktifkan admin pertama sehingga $adminTarget adalah admin aktif terakhir
        $this->admin->update(['is_aktif' => false]);

        // Coba nonaktifkan admin aktif terakhir oleh admin penyerang (acting as admin)
        $response = $this->actingAs($this->admin)->patchJson("/users/{$adminTarget->id}/toggle-aktif");

        $response->assertStatus(422);
        $response->assertJsonPath('message', fn ($m) => str_contains($m, 'admin terakhir yang aktif'));
    }

    // =========================================================================
    // AC-18: Password akun baru disimpan dalam bentuk hash, bukan plaintext
    // =========================================================================

    public function test_ac18_password_akun_baru_disimpan_dalam_bentuk_hash(): void
    {
        $surveyorRole = Role::where('kode', 'surveyor')->first();
        $rt = $this->makeWilayahRt();

        $response = $this->actingAs($this->admin)->postJson('/users', [
            'nama'       => 'Hash Check',
            'username'   => 'hashcheck',
            'role_id'    => $surveyorRole->id,
            'wilayah_id' => $rt->id,
        ]);

        $response->assertStatus(201);
        $passwordSementara = $response->json('password_sementara');

        $newUser = User::where('username', 'hashcheck')->first();
        $this->assertNotNull($newUser);

        // Password tersimpan TIDAK sama dengan plaintext
        $this->assertNotEquals($passwordSementara, $newUser->password);
        // Password dapat diverifikasi dengan Hash::check
        $this->assertTrue(Hash::check($passwordSementara, $newUser->password));
    }

    // =========================================================================
    // AC-19: Akun baru login pertama kali mengikuti wajib ganti password Scrum 12
    // =========================================================================

    public function test_ac19_akun_baru_login_pertama_kali_wajib_ganti_password(): void
    {
        $surveyorRole = Role::where('kode', 'surveyor')->first();
        $rt = $this->makeWilayahRt();

        $createResponse = $this->actingAs($this->admin)->postJson('/users', [
            'nama'       => 'Akun Baru Wajib Ganti',
            'username'   => 'akunbaru_fr05',
            'role_id'    => $surveyorRole->id,
            'wilayah_id' => $rt->id,
        ]);

        $passwordSementara = $createResponse->json('password_sementara');

        // Logout admin
        $this->post('/logout');

        // Akun baru mencoba login
        $loginResponse = $this->post('/login', [
            'username' => 'akunbaru_fr05',
            'password' => $passwordSementara,
        ]);

        // Harus dialihkan ke halaman ganti password (Scrum 12)
        $loginResponse->assertRedirect(route('password.change'));
        $loginResponse->assertSessionHas('warning');
    }

    // =========================================================================
    // AC-20: Response daftar dan detail user tidak mengandung password hash atau token
    // =========================================================================

    public function test_ac20_response_daftar_dan_detail_user_tidak_mengandung_password_atau_token(): void
    {
        $target = $this->makeNonSurveyorUser('verifikator', 'sensitivetest');

        // Cek response daftar (index)
        $indexResponse = $this->actingAs($this->admin)->getJson('/users');
        $indexResponse->assertOk();
        $firstItem = $indexResponse->json('data.0');
        $this->assertArrayNotHasKey('password', $firstItem);
        $this->assertArrayNotHasKey('remember_token', $firstItem);

        // Cek response detail (show)
        $showResponse = $this->actingAs($this->admin)->getJson("/users/{$target->id}");
        $showResponse->assertOk();
        $this->assertArrayNotHasKey('password', $showResponse->json());
        $this->assertArrayNotHasKey('remember_token', $showResponse->json());
    }

    // =========================================================================
    // Pengujian Validasi Bisnis & Audit Log
    // =========================================================================

    public function test_surveyor_wajib_punya_wilayah_tugas(): void
    {
        $surveyorRole = Role::where('kode', 'surveyor')->first();

        $response = $this->actingAs($this->admin)->postJson('/users', [
            'nama'     => 'Surveyor Tanpa Wilayah',
            'username' => 'surveyortanpawilayah',
            'role_id'  => $surveyorRole->id,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Surveyor wajib memiliki wilayah tugas.']);
    }

    public function test_role_bukan_surveyor_tidak_boleh_punya_wilayah(): void
    {
        $verifikatorRole = Role::where('kode', 'verifikator')->first();
        $rt = $this->makeWilayahRt();

        $response = $this->actingAs($this->admin)->postJson('/users', [
            'nama'       => 'Verifikator',
            'username'   => 'verifikator1',
            'role_id'    => $verifikatorRole->id,
            'wilayah_id' => $rt->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', fn ($m) => str_contains($m, 'tidak boleh memiliki wilayah'));
    }

    public function test_create_user_mencatat_audit_log(): void
    {
        $surveyorRole = Role::where('kode', 'surveyor')->first();
        $rt = $this->makeWilayahRt();

        $this->actingAs($this->admin)->postJson('/users', [
            'nama'       => 'Audit Test',
            'username'   => 'audittest',
            'role_id'    => $surveyorRole->id,
            'wilayah_id' => $rt->id,
        ]);

        $this->assertDatabaseHas('audit_log', [
            'user_id' => $this->admin->id,
            'aksi'    => AuditLog::AKSI['USER_CREATE'],
            'entitas' => 'users',
        ]);
    }
}
