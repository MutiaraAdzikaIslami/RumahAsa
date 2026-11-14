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

    private User $surveyor;
    private Wilayah $rt;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        
        // Bikin wilayah RT
        $kec = Wilayah::create(['kode' => 'KEC-T', 'nama' => 'Kec Test', 'tingkat' => 'kecamatan', 'parent_id' => null]);
        $kec->update(['path' => '/' . $kec->id]);
        $this->rt = Wilayah::create(['kode' => 'RT-T', 'nama' => 'RT 01', 'tingkat' => 'rt', 'parent_id' => $kec->id]);
        $this->rt->update(['path' => $kec->path . '/' . $this->rt->id]);

        // Bikin surveyor
        $role = Role::where('kode', 'surveyor')->first();
        $this->surveyor = User::factory()->create([
            'role_id'              => $role->id,
            'wilayah_id'           => $this->rt->id,
            'must_change_password' => false,
        ]);
    }

    public function test_surveyor_bisa_buat_rumah_lengkap(): void
    {
        $response = $this->actingAs($this->surveyor)->post('/rumah', [
            'nama_kepala_keluarga'  => 'Budi Santoso',
            'nik'                   => '1234567890123456',
            'no_kk'                 => '6543210987654321',
            'jumlah_anggota'        => 4,
            'alamat'                => 'Jl. Test No. 123',
            'wilayah_id'            => $this->rt->id,
            'action'                => 'save',
        ]);

        $response->assertRedirect(); // redirect ke rumah.show
        $this->assertDatabaseHas('rumah_warga', ['nama_kepala_keluarga' => 'Budi Santoso']);
    }

    public function test_surveyor_bisa_simpan_draf_kosong(): void
    {
        $response = $this->actingAs($this->surveyor)->post('/rumah', [
            'action' => 'draft',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('rumah_warga', ['status' => 'Draf']);
    }

    public function test_cek_duplikat_nik_ditolak(): void
    {
        // Bikin rumah dengan NIK tertentu secara manual (tanpa factory)
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
            'alamat'               => 'Jl. Existing',
            'wilayah_id'           => $this->rt->id,
            'surveyor_id'          => $this->surveyor->id,
            'status'               => RumahWarga::STATUS_DRAF,
        ]);

        // Coba input rumah lagi dengan NIK yang sama
        $response = $this->actingAs($this->surveyor)->post('/rumah', [
            'nama_kepala_keluarga'  => 'Duplicate User',
            'nik'                   => '1111222233334444',
            'no_kk'                 => '9999888877776666',
            'jumlah_anggota'        => 2,
            'alamat'                => 'Jl. Duplicate',
            'wilayah_id'            => $this->rt->id,
            'action'                => 'save',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('nik');
    }

    public function test_surveyor_tidak_bisa_lihat_rumah_di_luar_wilayahnya(): void
    {
        // Bikin wilayah lain
        $kec2 = Wilayah::create(['kode' => 'KEC-T2', 'nama' => 'Kec Test 2', 'tingkat' => 'kecamatan', 'parent_id' => null]);
        $kec2->update(['path' => '/' . $kec2->id]);
        $rt2 = Wilayah::create(['kode' => 'RT-T2', 'nama' => 'RT 02', 'tingkat' => 'rt', 'parent_id' => $kec2->id]);
        $rt2->update(['path' => $kec2->path . '/' . $rt2->id]);

        // Bikin rumah di wilayah lain
        $nikEncryptor = app(\App\Modules\Core\Services\NikEncryptor::class);
        $rumahLain = RumahWarga::create([
            'kode_registrasi'      => 'RUMAH-2026-8888',
            'nama_kepala_keluarga' => 'User Wilayah Lain',
            'nik_enc'              => $nikEncryptor->encryptNik('1234567890123456'),
            'nik_hash'             => $nikEncryptor->hashNik('1234567890123456'),
            'no_kk_enc'            => $nikEncryptor->encryptNoKk('6543210987654321'),
            'no_kk_hash'           => $nikEncryptor->hashNoKk('6543210987654321'),
            'jumlah_anggota'       => 3,
            'alamat'               => 'Jl. Lain',
            'wilayah_id'           => $rt2->id,
            'surveyor_id'          => $this->surveyor->id,
            'status'               => RumahWarga::STATUS_DRAF,
        ]);

        // Surveyor akses daftar rumah
        $response = $this->actingAs($this->surveyor)->get('/rumah');

        // Pastikan rumah di wilayah lain TIDAK muncul
        $response->assertDontSee($rumahLain->nama_kepala_keluarga);
    }

    public function test_input_rumah_mencatat_audit_log(): void
    {
        $this->actingAs($this->surveyor)->post('/rumah', [
            'nama_kepala_keluarga'  => 'Audit Test',
            'nik'                   => '1234567890123456',
            'no_kk'                 => '6543210987654321',
            'jumlah_anggota'        => 4,
            'alamat'                => 'Jl. Audit',
            'wilayah_id'            => $this->rt->id,
            'action'                => 'save',
        ]);

        $this->assertDatabaseHas('audit_log', [
            'user_id'  => $this->surveyor->id,
            'aksi'     => 'RUMAH_CREATE',
            'entitas'  => 'rumah_warga',
        ]);
    }
}