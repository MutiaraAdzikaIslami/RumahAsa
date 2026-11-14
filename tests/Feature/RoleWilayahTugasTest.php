<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleWilayahTugasTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\WilayahContohSeeder::class);
        $adminRole = Role::where('kode', 'admin')->first();
        $this->admin = User::factory()->create([
            'role_id'              => $adminRole->id,
            'must_change_password' => false,
        ]);
    }

    public function test_endpoint_roles_mengembalikan_list_role_untuk_dropdown(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/users/roles');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => ['*' => ['id', 'kode', 'nama', 'deskripsi']],
            'roles_wajib_wilayah',
        ]);

        $this->assertCount(4, $response->json('data'));
        $this->assertContains('surveyor', $response->json('roles_wajib_wilayah'));
    }

    public function test_endpoint_form_data_mengembalikan_roles_dan_wilayah_tree(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/users/form-data');

        $response->assertOk();
        $response->assertJsonStructure([
            'roles' => ['*' => ['id', 'kode', 'nama']],
            'wilayah_tree',
            'rules' => ['roles_wajib_wilayah', 'roles_lihat_semua_wilayah'],
        ]);

        $this->assertCount(4, $response->json('roles'));
        $this->assertNotEmpty($response->json('wilayah_tree'));
    }

    public function test_validasi_surveyor_wajib_punya_wilayah_saat_create(): void
    {
        $surveyorRole = Role::where('kode', 'surveyor')->first();

        $response = $this->actingAs($this->admin)->postJson('/users', [
            'nama'     => 'Surveyor Test',
            'username' => 'surveyortest',
            'role_id'  => $surveyorRole->id,
            // wilayah_id kosong
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Surveyor wajib memiliki wilayah tugas.');
    }

    public function test_validasi_role_bukan_surveyor_wilayah_harus_null(): void
    {
        $verifikatorRole = Role::where('kode', 'verifikator')->first();
        $rt = Wilayah::where('tingkat', 'rt')->first();

        $response = $this->actingAs($this->admin)->postJson('/users', [
            'nama'       => 'Verifikator Test',
            'username'   => 'verifikatortest',
            'role_id'    => $verifikatorRole->id,
            'wilayah_id' => $rt->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', fn ($m) => str_contains($m, 'tidak boleh memiliki wilayah'));
    }

    public function test_update_role_ke_surveyor_wajib_set_wilayah(): void
    {
        // Bikin user verifikator dulu (wilayah null)
        $verifikatorRole = Role::where('kode', 'verifikator')->first();
        $target = User::factory()->create([
            'role_id'    => $verifikatorRole->id,
            'wilayah_id' => null,
        ]);

        // Update ke surveyor tapi tanpa wilayah -> harus ditolak
        $surveyorRole = Role::where('kode', 'surveyor')->first();
        $response = $this->actingAs($this->admin)->putJson("/users/{$target->id}", [
            'nama'       => $target->nama,
            'username'   => $target->username,
            'role_id'    => $surveyorRole->id,
            'wilayah_id' => null,
            'is_aktif'   => true,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Surveyor wajib memiliki wilayah tugas.');
    }

    public function test_update_role_dari_surveyor_ke_admin_clear_wilayah(): void
    {
        // Bikin user surveyor dengan wilayah
        $surveyorRole = Role::where('kode', 'surveyor')->first();
        $rt = Wilayah::where('tingkat', 'rt')->first();
        $target = User::factory()->create([
            'role_id'    => $surveyorRole->id,
            'wilayah_id' => $rt->id,
        ]);

        // Update ke admin + clear wilayah
        $adminRole = Role::where('kode', 'admin')->first();
        $response = $this->actingAs($this->admin)->putJson("/users/{$target->id}", [
            'nama'       => $target->nama,
            'username'   => $target->username,
            'role_id'    => $adminRole->id,
            'wilayah_id' => null,
            'is_aktif'   => true,
        ]);

        $response->assertOk();
        $target->refresh();
        $this->assertSame($adminRole->id, $target->role_id);
        $this->assertNull($target->wilayah_id);
    }
}