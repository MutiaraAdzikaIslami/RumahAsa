<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WilayahManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $role = Role::where('kode', 'admin')->first();
        $this->admin = User::factory()->create(['role_id' => $role->id, 'must_change_password' => false]);
    }

    private function makeWilayahTree(): array
    {
        $kec = Wilayah::create(['kode' => 'KEC-1', 'nama' => 'Kec 1', 'tingkat' => 'kecamatan', 'parent_id' => null]);
        $kec->update(['path' => '/' . $kec->id]);
        $kel = Wilayah::create(['kode' => 'KEL-1', 'nama' => 'Kel 1', 'tingkat' => 'kelurahan', 'parent_id' => $kec->id]);
        $kel->update(['path' => $kec->path . '/' . $kel->id]);
        $rw  = Wilayah::create(['kode' => 'RW-1', 'nama' => 'RW 01', 'tingkat' => 'rw', 'parent_id' => $kel->id]);
        $rw->update(['path' => $kel->path . '/' . $rw->id]);
        $rt  = Wilayah::create(['kode' => 'RT-1', 'nama' => 'RT 01', 'tingkat' => 'rt', 'parent_id' => $rw->id]);
        $rt->update(['path' => $rw->path . '/' . $rt->id]);
        return [$kec, $kel, $rw, $rt];
    }

    public function test_admin_bisa_buat_wilayah_kecamatan_dengan_path_tergenerate(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/wilayah', [
            'kode'    => 'KEC-NEW',
            'nama'    => 'Kecamatan Baru',
            'tingkat' => 'kecamatan',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('wilayah', ['kode' => 'KEC-NEW']);
        $wilayah = Wilayah::where('kode', 'KEC-NEW')->first();
        $this->assertSame('/' . $wilayah->id, $wilayah->path);
    }

    public function test_buat_wilayah_rt_otomatis_dapat_path_berjenjang(): void
    {
        [$kec, $kel, $rw, $rt] = $this->makeWilayahTree();

        $response = $this->actingAs($this->admin)->postJson('/wilayah', [
            'kode'      => 'RT-NEW',
            'nama'      => 'RT Baru',
            'tingkat'   => 'rt',
            'parent_id' => $rw->id,
        ]);

        $response->assertStatus(201);
        $new = Wilayah::where('kode', 'RT-NEW')->first();
        $expectedPath = $rw->path . '/' . $new->id;
        $this->assertSame($expectedPath, $new->path);
    }

    public function test_validasi_parent_harus_1_jenjang_di_atas(): void
    {
        [$kec, $kel, $rw, $rt] = $this->makeWilayahTree();

        $response = $this->actingAs($this->admin)->postJson('/wilayah', [
            'kode'      => 'RT-FAIL',
            'nama'      => 'RT Fail',
            'tingkat'   => 'rt',
            'parent_id' => $kec->id,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('wilayah', ['kode' => 'RT-FAIL']);
    }

    public function test_update_parent_re_generate_path_descendant(): void
    {
        [$kec, $kel, $rw, $rt] = $this->makeWilayahTree();

        $kel2 = Wilayah::create(['kode' => 'KEL-2', 'nama' => 'Kel 2', 'tingkat' => 'kelurahan', 'parent_id' => $kec->id]);
        $kel2->update(['path' => $kec->path . '/' . $kel2->id]);

        $response = $this->actingAs($this->admin)->putJson('/wilayah/' . $rw->id, [
            'kode'      => $rw->kode,
            'nama'      => $rw->nama,
            'parent_id' => $kel2->id,
        ]);

        $response->assertOk();
        $rw->refresh();
        $rt->refresh();

        $this->assertSame($kel2->path . '/' . $rw->id, $rw->path);
        $this->assertSame($rw->path . '/' . $rt->id, $rt->path);
    }

    public function test_hapus_wilayah_dengan_anak_ditolak(): void
    {
        [$kec, $kel, $rw, $rt] = $this->makeWilayahTree();

        $response = $this->actingAs($this->admin)->deleteJson('/wilayah/' . $kec->id);

        $response->assertStatus(422);
        $this->assertDatabaseHas('wilayah', ['id' => $kec->id]);
    }

    public function test_hapus_wilayah_tanpa_anak_dan_tanpa_relasi_berhasil(): void
    {
        [$kec, $kel, $rw, $rt] = $this->makeWilayahTree();

        $this->actingAs($this->admin)->deleteJson('/wilayah/' . $rt->id)->assertStatus(204);
        $this->assertSoftDeleted('wilayah', ['id' => $rt->id]);
    }

    public function test_get_tree_mengembalikan_hierarki(): void
    {
        [$kec, $kel, $rw, $rt] = $this->makeWilayahTree();

        $response = $this->actingAs($this->admin)->getJson('/wilayah/tree');

        $response->assertOk();

        // Cek top-level array punya minimal 1 node (kecamatan)
        $tree = $response->json();
        $this->assertGreaterThanOrEqual(1, count($tree));

        // Cek root node (kecamatan) punya key children
        $rootNode = collect($tree)->first(fn ($node) => $node['kode'] === 'KEC-1');
        $this->assertNotNull($rootNode);
        $this->assertArrayHasKey('children', $rootNode);
        $this->assertGreaterThanOrEqual(1, count($rootNode['children']));

        // Cek kelurahan punya children (rw)
        $kelNode = collect($rootNode['children'])->first(fn ($n) => $n['kode'] === 'KEL-1');
        $this->assertNotNull($kelNode);
        $this->assertArrayHasKey('children', $kelNode);
    }

    public function test_get_descendants_mengembalikan_semua_anak_berjenjang(): void
    {
        [$kec, $kel, $rw, $rt] = $this->makeWilayahTree();

        $response = $this->actingAs($this->admin)->getJson("/wilayah/{$kec->id}/descendants");

        $response->assertOk();
        $descendants = $response->json();
        $this->assertCount(3, $descendants);
    }

    public function test_role_bukan_admin_tidak_bisa_akses_wilayah(): void
    {
        $role = Role::where('kode', 'surveyor')->first();
        $surveyor = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($surveyor)->getJson('/wilayah/tree');

        $response->assertForbidden();
    }
}