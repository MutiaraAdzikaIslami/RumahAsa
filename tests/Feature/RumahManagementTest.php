<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RumahWarga;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RumahManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $surveyor;
    private Wilayah $kelurahan;
    private Wilayah $rt;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $prov = Wilayah::create(['kode' => '35', 'nama' => 'Jawa Timur', 'tingkat' => 'provinsi', 'parent_id' => null]);
        $prov->update(['path' => '/' . $prov->id]);

        $kab = Wilayah::create(['kode' => '35.78', 'nama' => 'Kota Surabaya', 'tingkat' => 'kabupaten', 'parent_id' => $prov->id]);
        $kab->update(['path' => $prov->path . '/' . $kab->id]);

        $kec = Wilayah::create(['kode' => '35.78.01', 'nama' => 'Kec Test', 'tingkat' => 'kecamatan', 'parent_id' => $kab->id]);
        $kec->update(['path' => $kab->path . '/' . $kec->id]);

        $this->kelurahan = Wilayah::create(['kode' => '35.78.01.1001', 'nama' => 'Kel Test', 'tingkat' => 'kelurahan', 'parent_id' => $kec->id]);
        $this->kelurahan->update(['path' => $kec->path . '/' . $this->kelurahan->id]);

        $rw = Wilayah::create(['kode' => 'RW-T', 'nama' => 'RW 01', 'tingkat' => 'rw', 'parent_id' => $this->kelurahan->id]);
        $rw->update(['path' => $this->kelurahan->path . '/' . $rw->id]);

        $this->rt = Wilayah::create(['kode' => 'RT-T', 'nama' => 'RT 01', 'tingkat' => 'rt', 'parent_id' => $rw->id]);
        $this->rt->update(['path' => $rw->path . '/' . $this->rt->id]);

        $adminRole = Role::where('kode', 'admin')->first();
        $this->admin = User::factory()->create([
            'role_id'              => $adminRole->id,
            'must_change_password' => false,
        ]);

        $surveyorRole = Role::where('kode', 'surveyor')->first();
        $this->surveyor = User::factory()->create([
            'role_id'              => $surveyorRole->id,
            'wilayah_id'           => $this->rt->id,
            'must_change_password' => false,
        ]);
    }

    public function test_admin_bisa_buat_rumah_lengkap(): void
    {
        $response = $this->actingAs($this->admin)->post('/rumah', [
            'nama_kepala_keluarga' => 'Budi Santoso',
            'nik'                  => '1234567890123456',
            'no_kk'                => '6543210987654321',
            'jumlah_anggota'       => 4,
            'status_kepemilikan'   => 'milik_sendiri',
            'rt_text'              => '01',
            'rw_text'              => '01',
            'alamat'               => 'Jl. Test No. 123',
            'wilayah_id'           => $this->kelurahan->id,
            'surveyor_id'          => $this->surveyor->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('rumah_warga', [
            'nama_kepala_keluarga' => 'Budi Santoso',
            'status'               => 'Menunggu Survei',
        ]);
    }

    public function test_admin_bisa_simpan_draf_kosong(): void
    {
        $response = $this->actingAs($this->admin)->post('/rumah', [
            'surveyor_id' => $this->surveyor->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['nama_kepala_keluarga', 'nik', 'no_kk', 'wilayah_id']);
    }

    public function test_cek_duplikat_nik_ditolak(): void
    {
        $nikEncryptor = app(\App\Modules\Core\Services\NikEncryptor::class);
        $nik = '1111222233334444';

        RumahWarga::create([
            'kode_registrasi'      => 'RUMAH-2026-9999',
            'nama_kepala_keluarga' => 'Existing User',
            'nik_enc'              => $nikEncryptor->encryptNik($nik),
            'nik_hash'             => $nikEncryptor->hashNik($nik),
            'no_kk_enc'            => $nikEncryptor->encryptNoKk('9999888877776666'),
            'no_kk_hash'           => $nikEncryptor->hashNoKk('9999888877776666'),
            'jumlah_anggota'       => 2,
            'status_kepemilikan'   => 'milik_sendiri',
            'alamat'               => 'Jl. Existing',
            'wilayah_id'           => $this->rt->id,
            'surveyor_id'          => $this->surveyor->id,
            'status'               => RumahWarga::STATUS_MENUNGGU_SURVEI,
        ]);

        $response = $this->actingAs($this->admin)->post('/rumah', [
            'nama_kepala_keluarga' => 'Duplicate User',
            'nik'                  => '1111222233334444',
            'no_kk'                => '9999888877776666',
            'jumlah_anggota'       => 2,
            'status_kepemilikan'   => 'milik_sendiri',
            'rt_text'              => '01',
            'rw_text'              => '01',
            'alamat'               => 'Jl. Duplicate',
            'wilayah_id'           => $this->kelurahan->id,
            'surveyor_id'          => $this->surveyor->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('nik');
    }

    public function test_surveyor_tidak_bisa_lihat_rumah_di_luar_wilayahnya(): void
    {
        $kec2 = Wilayah::create(['kode' => 'KEC-T2', 'nama' => 'Kec Test 2', 'tingkat' => 'kecamatan', 'parent_id' => null]);
        $kec2->update(['path' => '/' . $kec2->id]);
        $rt2 = Wilayah::create(['kode' => 'RT-T2', 'nama' => 'RT 02', 'tingkat' => 'rt', 'parent_id' => $kec2->id]);
        $rt2->update(['path' => $kec2->path . '/' . $rt2->id]);

        $nikEncryptor = app(\App\Modules\Core\Services\NikEncryptor::class);
        $rumahLain = RumahWarga::create([
            'kode_registrasi'      => 'RUMAH-2026-8888',
            'nama_kepala_keluarga' => 'User Wilayah Lain',
            'nik_enc'              => $nikEncryptor->encryptNik('1234567890123456'),
            'nik_hash'             => $nikEncryptor->hashNik('1234567890123456'),
            'no_kk_enc'            => $nikEncryptor->encryptNoKk('6543210987654321'),
            'no_kk_hash'           => $nikEncryptor->hashNoKk('6543210987654321'),
            'jumlah_anggota'       => 3,
            'status_kepemilikan'   => 'milik_sendiri',
            'alamat'               => 'Jl. Lain',
            'wilayah_id'           => $rt2->id,
            'surveyor_id'          => $this->surveyor->id,
            'status'               => RumahWarga::STATUS_MENUNGGU_SURVEI,
        ]);

        $this->assertFalse($this->surveyor->can('view', $rumahLain));

        $response = $this->actingAs($this->surveyor)->get('/rumah');
        $response->assertStatus(403);
    }

    public function test_input_rumah_mencatat_audit_log(): void
    {
        $this->actingAs($this->admin)->post('/rumah', [
            'nama_kepala_keluarga' => 'Audit Test',
            'nik'                  => '1234567890123456',
            'no_kk'                => '6543210987654321',
            'jumlah_anggota'       => 4,
            'status_kepemilikan'   => 'milik_sendiri',
            'rt_text'              => '01',
            'rw_text'              => '01',
            'alamat'               => 'Jl. Audit',
            'wilayah_id'           => $this->kelurahan->id,
            'surveyor_id'          => $this->surveyor->id,
        ]);

        $this->assertDatabaseHas('audit_log', [
            'user_id' => $this->admin->id,
            'aksi'    => 'RUMAH_CREATE',
            'entitas' => 'rumah_warga',
        ]);
    }
}