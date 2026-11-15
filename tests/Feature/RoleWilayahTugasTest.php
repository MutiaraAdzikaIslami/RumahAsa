<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RumahWarga;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
            'username'             => 'adminscrum15',
            'must_change_password' => false,
            'is_aktif'             => true,
        ]);
    }

    private function getRtWilayah(): Wilayah
    {
        return Wilayah::where('tingkat', 'rt')->first();
    }

    private function makeRumah(Wilayah $wilayah, User $surveyor): RumahWarga
    {
        $uniqueNum = mt_rand(100000000000, 999999999999);
        $nik = '3201' . $uniqueNum;

        return RumahWarga::create([
            'kode_registrasi'      => 'TEST-' . uniqid(),
            'nama_kepala_keluarga' => 'Kepala Keluarga Test',
            'nik_enc'              => encrypt($nik),
            'nik_hash'             => hash_hmac('sha256', $nik, config('app.key')),
            'no_kk_enc'            => encrypt($nik),
            'no_kk_hash'           => hash_hmac('sha256', $nik, config('app.key')),
            'jumlah_anggota'       => 3,
            'luas_rumah'           => 36.0,
            'pekerjaan'            => 'Wiraswasta',
            'penghasilan'          => 2000000,
            'status_kepemilikan'   => 'milik_sendiri',
            'alamat'               => 'Jl. Test No. 1',
            'latitude'             => -6.2,
            'longitude'            => 106.8,
            'wilayah_id'           => $wilayah->id,
            'surveyor_id'          => $surveyor->id,
            'status'               => RumahWarga::STATUS_DRAF,
        ])->load('wilayah');
    }

    // =========================================================================
    // AC-01: Admin membuat user dengan role valid -> Berhasil
    // =========================================================================

    public function test_ac01_admin_membuat_user_dengan_role_valid_berhasil(): void
    {
        $verifikatorRole = Role::where('kode', 'verifikator')->first();

        $response = $this->actingAs($this->admin)->postJson('/users', [
            'nama'     => 'Verifikator Baru',
            'username' => 'verifikator_baru',
            'email'    => 'verifikator_baru@example.com',
            'role_id'  => $verifikatorRole->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'username' => 'verifikator_baru',
            'role_id'  => $verifikatorRole->id,
        ]);
    }

    // =========================================================================
    // AC-02: Admin membuat user dengan role tidak valid -> Ditolak
    // =========================================================================

    public function test_ac02_admin_membuat_user_dengan_role_tidak_valid_ditolak(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/users', [
            'nama'     => 'User Invalid Role',
            'username' => 'invalid_role_user',
            'role_id'  => 999999, // ID role tidak ada
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role_id']);
    }

    // =========================================================================
    // AC-03: Admin membuat user Surveyor dengan wilayah valid -> Berhasil
    // =========================================================================

    public function test_ac03_admin_membuat_user_surveyor_dengan_wilayah_valid_berhasil(): void
    {
        $surveyorRole = Role::where('kode', 'surveyor')->first();
        $rt = $this->getRtWilayah();

        $response = $this->actingAs($this->admin)->postJson('/users', [
            'nama'       => 'Surveyor Valid',
            'username'   => 'surveyor_valid',
            'email'      => 'surveyor_valid@example.com',
            'role_id'    => $surveyorRole->id,
            'wilayah_id' => $rt->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'username'   => 'surveyor_valid',
            'role_id'    => $surveyorRole->id,
            'wilayah_id' => $rt->id,
        ]);
    }

    // =========================================================================
    // AC-04: Admin membuat user Surveyor tanpa wilayah -> Ditolak
    // =========================================================================

    public function test_ac04_admin_membuat_user_surveyor_tanpa_wilayah_ditolak(): void
    {
        $surveyorRole = Role::where('kode', 'surveyor')->first();

        $response = $this->actingAs($this->admin)->postJson('/users', [
            'nama'     => 'Surveyor Tanpa Wilayah',
            'username' => 'surveyor_tanpa_wilayah',
            'role_id'  => $surveyorRole->id,
            // wilayah_id tidak diisi
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Surveyor wajib memiliki wilayah tugas.');
    }

    // =========================================================================
    // AC-05: Admin membuat user Surveyor dengan ID wilayah tidak valid -> Ditolak
    // =========================================================================

    public function test_ac05_admin_membuat_user_surveyor_dengan_id_wilayah_tidak_valid_ditolak(): void
    {
        $surveyorRole = Role::where('kode', 'surveyor')->first();

        $response = $this->actingAs($this->admin)->postJson('/users', [
            'nama'       => 'Surveyor Wilayah Invalid',
            'username'   => 'surveyor_invalid_wilayah',
            'role_id'    => $surveyorRole->id,
            'wilayah_id' => 999999, // Wilayah tidak ada
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['wilayah_id']);
    }

    // =========================================================================
    // AC-06: Admin mengubah role user menjadi Surveyor tanpa wilayah -> Ditolak
    // =========================================================================

    public function test_ac06_admin_mengubah_role_user_menjadi_surveyor_tanpa_wilayah_ditolak(): void
    {
        $verifikatorRole = Role::where('kode', 'verifikator')->first();
        $target = User::factory()->create([
            'role_id'    => $verifikatorRole->id,
            'wilayah_id' => null,
            'username'   => 'target_ubah_role',
        ]);

        $surveyorRole = Role::where('kode', 'surveyor')->first();

        $response = $this->actingAs($this->admin)->putJson("/users/{$target->id}", [
            'nama'       => $target->nama,
            'username'   => $target->username,
            'role_id'    => $surveyorRole->id,
            'wilayah_id' => null, // Tidak sertakan wilayah
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Surveyor wajib memiliki wilayah tugas.');
    }

    // =========================================================================
    // AC-07: Admin mengubah role user menjadi Surveyor sekaligus menetapkan wilayah valid -> Berhasil
    // =========================================================================

    public function test_ac07_admin_mengubah_role_menjadi_surveyor_dan_wilayah_valid_berhasil(): void
    {
        $verifikatorRole = Role::where('kode', 'verifikator')->first();
        $target = User::factory()->create([
            'role_id'    => $verifikatorRole->id,
            'wilayah_id' => null,
            'username'   => 'target_promosi',
        ]);

        $surveyorRole = Role::where('kode', 'surveyor')->first();
        $rt = $this->getRtWilayah();

        $response = $this->actingAs($this->admin)->putJson("/users/{$target->id}", [
            'nama'       => $target->nama,
            'username'   => $target->username,
            'role_id'    => $surveyorRole->id,
            'wilayah_id' => $rt->id,
        ]);

        $response->assertOk();
        $target->refresh();
        $this->assertEquals($surveyorRole->id, $target->role_id);
        $this->assertEquals($rt->id, $target->wilayah_id);
    }

    // =========================================================================
    // AC-08: Admin mengubah wilayah tugas Surveyor -> Berhasil
    // =========================================================================

    public function test_ac08_admin_mengubah_wilayah_tugas_surveyor_berhasil(): void
    {
        $surveyorRole = Role::where('kode', 'surveyor')->first();
        $wilayahAwal = Wilayah::where('tingkat', 'rt')->first();
        $wilayahBaru = Wilayah::where('tingkat', 'rt')->where('id', '!=', $wilayahAwal->id)->first();

        // Buat wilayah baru jika hanya ada satu
        if (!$wilayahBaru) {
            $wilayahBaru = Wilayah::create([
                'kode'      => 'RT-BARU',
                'nama'      => 'RT Baru',
                'tingkat'   => 'rt',
                'parent_id' => $wilayahAwal->parent_id,
            ]);
            $wilayahBaru->update(['path' => $wilayahAwal->parent?->path . '/' . $wilayahBaru->id]);
        }

        $surveyor = User::factory()->create([
            'role_id'    => $surveyorRole->id,
            'wilayah_id' => $wilayahAwal->id,
            'username'   => 'surveyor_pindah',
        ]);

        $response = $this->actingAs($this->admin)->putJson("/users/{$surveyor->id}", [
            'nama'       => $surveyor->nama,
            'username'   => $surveyor->username,
            'role_id'    => $surveyorRole->id,
            'wilayah_id' => $wilayahBaru->id,
        ]);

        $response->assertOk();
        $surveyor->refresh();
        $this->assertEquals($wilayahBaru->id, $surveyor->wilayah_id);
    }

    // =========================================================================
    // AC-09: Admin menghapus seluruh wilayah tugas Surveyor -> Ditolak
    // =========================================================================

    public function test_ac09_admin_menghapus_seluruh_wilayah_tugas_surveyor_ditolak(): void
    {
        $surveyorRole = Role::where('kode', 'surveyor')->first();
        $rt = $this->getRtWilayah();

        $surveyor = User::factory()->create([
            'role_id'    => $surveyorRole->id,
            'wilayah_id' => $rt->id,
            'username'   => 'surveyor_hapus_wilayah',
        ]);

        // Admin mencoba mengosongkan wilayah_id pada Surveyor
        $response = $this->actingAs($this->admin)->putJson("/users/{$surveyor->id}", [
            'nama'       => $surveyor->nama,
            'username'   => $surveyor->username,
            'role_id'    => $surveyorRole->id,
            'wilayah_id' => null,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Surveyor wajib memiliki wilayah tugas.');
    }

    // =========================================================================
    // AC-10: Admin menetapkan role dan wilayah pada user non-Surveyor -> Mengikuti aturan
    // =========================================================================

    public function test_ac10_admin_menetapkan_wilayah_pada_user_non_surveyor_ditolak(): void
    {
        $adminRole = Role::where('kode', 'admin')->first();
        $rt = $this->getRtWilayah();

        // Non-surveyor (Admin) tidak boleh diberi wilayah
        $response = $this->actingAs($this->admin)->postJson('/users', [
            'nama'       => 'Admin Baru',
            'username'   => 'admin_baru',
            'role_id'    => $adminRole->id,
            'wilayah_id' => $rt->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', fn ($m) => str_contains($m, 'tidak boleh memiliki wilayah'));
    }

    public function test_ac10_admin_menetapkan_non_surveyor_tanpa_wilayah_diizinkan(): void
    {
        $pimpinanRole = Role::where('kode', 'pimpinan')->first();

        // Pimpinan tanpa wilayah tugas diizinkan (lihat semua wilayah)
        $response = $this->actingAs($this->admin)->postJson('/users', [
            'nama'       => 'Pimpinan Baru',
            'username'   => 'pimpinan_baru',
            'role_id'    => $pimpinanRole->id,
            'wilayah_id' => null,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'username'   => 'pimpinan_baru',
            'wilayah_id' => null,
        ]);
    }

    // =========================================================================
    // AC-11: Pengguna non-Admin mencoba mengubah role -> Ditolak (403)
    // =========================================================================

    public function test_ac11_pengguna_non_admin_mencoba_mengubah_role_ditolak(): void
    {
        $surveyorRole = Role::where('kode', 'surveyor')->first();
        $adminRole = Role::where('kode', 'admin')->first();
        $rt = $this->getRtWilayah();

        $surveyor = User::factory()->create([
            'role_id'              => $surveyorRole->id,
            'wilayah_id'           => $rt->id,
            'must_change_password' => false,
        ]);

        // Surveyor mencoba mengubah role akun dirinya sendiri menjadi Admin
        $response = $this->actingAs($surveyor)->putJson("/users/{$surveyor->id}", [
            'nama'     => $surveyor->nama,
            'username' => $surveyor->username,
            'role_id'  => $adminRole->id,
        ]);

        $response->assertStatus(403);
    }

    // =========================================================================
    // AC-12: Pengguna non-Admin mencoba mengubah wilayah tugas -> Ditolak (403)
    // =========================================================================

    public function test_ac12_pengguna_non_admin_mencoba_mengubah_wilayah_tugas_ditolak(): void
    {
        $surveyorRole = Role::where('kode', 'surveyor')->first();
        $rt = $this->getRtWilayah();

        $surveyor = User::factory()->create([
            'role_id'              => $surveyorRole->id,
            'wilayah_id'           => $rt->id,
            'must_change_password' => false,
        ]);

        // Surveyor mencoba mengubah wilayah tugas via endpoint /users/{id}
        $response = $this->actingAs($surveyor)->putJson("/users/{$surveyor->id}", [
            'nama'       => $surveyor->nama,
            'username'   => $surveyor->username,
            'role_id'    => $surveyorRole->id,
            'wilayah_id' => 999,
        ]);

        $response->assertStatus(403);
    }

    // =========================================================================
    // AC-13 & AC-14: Surveyor mengakses wilayah tugas vs luar penugasan
    // =========================================================================

    public function test_ac13_dan_ac14_surveyor_akses_wilayah_tugas_diizinkan_dan_luar_penugasan_ditolak(): void
    {
        $surveyorRole = Role::where('kode', 'surveyor')->first();
        $wilayahA = Wilayah::where('tingkat', 'rt')->first();

        // Wilayah B di kecamatan lain
        $kecB = Wilayah::create(['kode' => 'KEC-B', 'nama' => 'Kec B', 'tingkat' => 'kecamatan']);
        $kecB->update(['path' => '/' . $kecB->id]);
        $wilayahB = Wilayah::create(['kode' => 'RT-B', 'nama' => 'RT B', 'tingkat' => 'rt', 'parent_id' => $kecB->id]);
        $wilayahB->update(['path' => $kecB->path . '/' . $wilayahB->id]);

        $surveyor = User::factory()->create([
            'role_id'              => $surveyorRole->id,
            'wilayah_id'           => $wilayahA->id,
            'must_change_password' => false,
        ]);

        $rumahA = $this->makeRumah($wilayahA, $surveyor);
        $rumahB = $this->makeRumah($wilayahB, $surveyor);

        // AC-13: Akses wilayah tugas diizinkan
        $this->assertTrue($surveyor->can('view', $rumahA));

        // AC-14: Akses di luar penugasan ditolak
        $this->assertFalse($surveyor->can('view', $rumahB));
    }

    // =========================================================================
    // AC-15: Perubahan wilayah tugas diterapkan pada pemeriksaan akses berikutnya
    // =========================================================================

    public function test_ac15_perubahan_wilayah_tugas_langsung_mengubah_cakupan_akses(): void
    {
        $surveyorRole = Role::where('kode', 'surveyor')->first();
        $wilayahA = Wilayah::where('tingkat', 'rt')->first();

        $kecB = Wilayah::create(['kode' => 'KEC-B2', 'nama' => 'Kec B2', 'tingkat' => 'kecamatan']);
        $kecB->update(['path' => '/' . $kecB->id]);
        $wilayahB = Wilayah::create(['kode' => 'RT-B2', 'nama' => 'RT B2', 'tingkat' => 'rt', 'parent_id' => $kecB->id]);
        $wilayahB->update(['path' => $kecB->path . '/' . $wilayahB->id]);

        $surveyor = User::factory()->create([
            'role_id'              => $surveyorRole->id,
            'wilayah_id'           => $wilayahA->id,
            'username'             => 'surveyor_dinamis',
            'must_change_password' => false,
        ]);

        $rumahA = $this->makeRumah($wilayahA, $surveyor);
        $rumahB = $this->makeRumah($wilayahB, $surveyor);

        // Sebelum pindah: boleh A, tidak boleh B
        $this->assertTrue($surveyor->can('view', $rumahA));
        $this->assertFalse($surveyor->can('view', $rumahB));

        // Admin memindahkan wilayah tugas Surveyor ke Wilayah B
        $this->actingAs($this->admin)->putJson("/users/{$surveyor->id}", [
            'nama'       => $surveyor->nama,
            'username'   => $surveyor->username,
            'role_id'    => $surveyorRole->id,
            'wilayah_id' => $wilayahB->id,
        ])->assertOk();

        // Refresh model surveyor
        $surveyor->refresh();

        // Setelah pindah: tidak boleh A, boleh B!
        $this->assertFalse($surveyor->can('view', $rumahA));
        $this->assertTrue($surveyor->can('view', $rumahB));
    }

    // =========================================================================
    // AC-16: Penetapan wilayah gagal -> Tidak meninggalkan data tidak konsisten
    // =========================================================================

    public function test_ac16_penetapan_wilayah_gagal_tidak_meninggalkan_data_inkonsisten(): void
    {
        $surveyorRole = Role::where('kode', 'surveyor')->first();
        $initialUserCount = User::count();

        // Coba buat Surveyor tanpa wilayah
        $response = $this->actingAs($this->admin)->postJson('/users', [
            'nama'     => 'Surveyor Gagal',
            'username' => 'surveyor_gagal_rollback',
            'role_id'  => $surveyorRole->id,
            // wilayah_id sengaja null
        ]);

        $response->assertStatus(422);

        // Pastikan tidak ada record yang tersimpan sebagian
        $this->assertEquals($initialUserCount, User::count());
        $this->assertDatabaseMissing('users', ['username' => 'surveyor_gagal_rollback']);
    }

    // =========================================================================
    // Endpoint Pendukung Form Data & Roles
    // =========================================================================

    public function test_endpoint_roles_mengembalikan_list_role(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/users/roles');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => ['*' => ['id', 'kode', 'nama', 'deskripsi']],
            'roles_wajib_wilayah',
        ]);
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
    }
}
