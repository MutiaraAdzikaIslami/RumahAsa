<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'must_change_password' => false,
        ]);
    }

    private function makeWilayahRt(): Wilayah
    {
        $kec = Wilayah::create(['kode' => 'KEC-T', 'nama' => 'Kec Test', 'tingkat' => 'kecamatan', 'parent_id' => null]);
        $kec->update(['path' => '/' . $kec->id]);
        $rt  = Wilayah::create(['kode' => 'RT-T', 'nama' => 'RT 01', 'tingkat' => 'rt', 'parent_id' => $kec->id]);
        $rt->update(['path' => $kec->path . '/' . $rt->id]);
        return $rt;
    }

    public function test_admin_bisa_buat_user_dengan_password_sementara(): void
    {
        $surveyorRole = Role::where('kode', 'surveyor')->first();
        $rt = $this->makeWilayahRt();

        $response = $this->actingAs($this->admin)->postJson('/users', [
            'nama'       => 'Surveyor Baru',
            'username'   => 'surveyorbaru',
            'email'      => 'surveyor@example.com',
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

    public function test_surveyor_wajib_punya_wilayah_tugas(): void
    {
        $surveyorRole = Role::where('kode', 'surveyor')->first();

        $response = $this->actingAs($this->admin)->postJson('/users', [
            'nama'     => 'Surveyor Tanpa Wilayah',
            'username' => 'surveyortanpawilayah',
            'role_id'  => $surveyorRole->id,
            // wilayah_id tidak diisi
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
            'wilayah_id' => $rt->id,  // salah: verifikator tidak boleh punya wilayah
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', fn ($m) => str_contains($m, 'tidak boleh memiliki wilayah'));
    }

    public function test_admin_bisa_update_user(): void
    {
        $target = User::factory()->create(['nama' => 'Nama Lama']);

        $response = $this->actingAs($this->admin)->putJson("/users/{$target->id}", [
            'nama'       => 'Nama Baru',
            'username'   => $target->username,
            'role_id'    => $target->role_id,
            'wilayah_id' => null,
            'is_aktif'   => true,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', ['id' => $target->id, 'nama' => 'Nama Baru']);
    }

    public function test_admin_bisa_menonaktifkan_user(): void
    {
        $target = User::factory()->create(['is_aktif' => true]);

        $response = $this->actingAs($this->admin)->patchJson("/users/{$target->id}/toggle-aktif");

        $response->assertOk();
        $this->assertFalse($response->json('is_aktif'));
    }

    public function test_admin_tidak_bisa_menonaktifkan_diri_sendiri(): void
    {
        $response = $this->actingAs($this->admin)->patchJson("/users/{$this->admin->id}/toggle-aktif");

        $response->assertStatus(422);
        $response->assertJsonPath('message', fn ($m) => str_contains($m, 'tidak dapat menonaktifkan akun sendiri'));
    }

    public function test_admin_tidak_bisa_menghapus_admin_terakhir(): void
    {
        $response = $this->actingAs($this->admin)->deleteJson("/users/{$this->admin->id}");

        $response->assertStatus(422);
        $response->assertJsonPath('message', fn ($m) => str_contains($m, 'admin terakhir') || str_contains($m, 'akun sendiri'));
    }

    public function test_admin_bisa_soft_delete_user_lain(): void
    {
        $target = User::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/users/{$target->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('users', ['id' => $target->id]);
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
            'user_id'  => $this->admin->id,
            'aksi'     => AuditLog::AKSI['USER_CREATE'],
            'entitas'  => 'users',
        ]);
    }
}