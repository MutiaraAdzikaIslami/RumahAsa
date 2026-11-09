<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RumahWarga;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RumahPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function getUser(string $roleKode, ?Wilayah $wilayah = null): User
    {
        $role = Role::where('kode', $roleKode)->first();
        return User::factory()->create([
            'role_id'    => $role->id,
            'wilayah_id' => $roleKode === 'surveyor' ? $wilayah?->id : null,
        ]);
    }

    private function getWilayahRt(): Wilayah
    {
        $kec = Wilayah::create(['kode' => 'KEC-T', 'nama' => 'Kec Test', 'tingkat' => 'kecamatan', 'parent_id' => null]);
        $kec->update(['path' => '/' . $kec->id]);
        $kel = Wilayah::create(['kode' => 'KEL-T', 'nama' => 'Kel Test', 'tingkat' => 'kelurahan', 'parent_id' => $kec->id]);
        $kel->update(['path' => $kec->path . '/' . $kel->id]);
        $rw  = Wilayah::create(['kode' => 'RW-T', 'nama' => 'RW 01', 'tingkat' => 'rw', 'parent_id' => $kel->id]);
        $rw->update(['path' => $kel->path . '/' . $rw->id]);
        $rt  = Wilayah::create(['kode' => 'RT-T', 'nama' => 'RT 01', 'tingkat' => 'rt', 'parent_id' => $rw->id]);
        $rt->update(['path' => $rw->path . '/' . $rt->id]);
        return $rt->fresh();
    }

    private function getWilayahRtLain(): Wilayah
    {
        $kec = Wilayah::create(['kode' => 'KEC-T2', 'nama' => 'Kec Test 2', 'tingkat' => 'kecamatan', 'parent_id' => null]);
        $kec->update(['path' => '/' . $kec->id]);
        $rt  = Wilayah::create(['kode' => 'RT-T2', 'nama' => 'RT 02', 'tingkat' => 'rt', 'parent_id' => $kec->id]);
        $rt->update(['path' => $kec->path . '/' . $rt->id]);
        return $rt->fresh();
    }

    private function getRumah(Wilayah $wilayah, User $surveyor, string $status = 'Draf'): RumahWarga
    {
        return RumahWarga::create([
            'kode_registrasi'      => 'TEST-' . uniqid(),
            'nama_kepala_keluarga' => 'Test KK',
            'nik_enc'              => encrypt('1234567890123456'),
            'nik_hash'             => hash_hmac('sha256', '1234567890123456', 'test'),
            'no_kk_enc'            => encrypt('1234567890123456'),
            'no_kk_hash'           => hash_hmac('sha256', '1234567890123456', 'test'),
            'jumlah_anggota'       => 4,
            'luas_rumah'           => 36.50,
            'pekerjaan'            => 'Petani',
            'penghasilan'          => 1500000,
            'status_kepemilikan'   => 'milik_sendiri',
            'alamat'               => 'Jl. Test',
            'latitude'             => -6.200000,
            'longitude'            => 106.816666,
            'wilayah_id'           => $wilayah->id,
            'surveyor_id'          => $surveyor->id,
            'status'               => $status,
        ])->load('wilayah');  // eager load supaya policy bisa akses path
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_surveyor_boleh_view_rumah_di_wilayahnya_sendiri(): void
    {
        $rt = $this->getWilayahRt();
        $surveyor = $this->getUser('surveyor', $rt);
        $rumah = $this->getRumah($rt, $surveyor);

        $this->assertTrue($surveyor->can('view', $rumah));
    }

    public function test_surveyor_tidak_boleh_view_rumah_di_luar_wilayahnya(): void
    {
        $rt1 = $this->getWilayahRt();
        $rt2 = $this->getWilayahRtLain();
        $surveyor = $this->getUser('surveyor', $rt1);
        $rumah = $this->getRumah($rt2, $surveyor);

        $this->assertFalse($surveyor->can('view', $rumah));
    }

    public function test_verifikator_boleh_view_semua_rumah(): void
    {
        $rt = $this->getWilayahRt();
        $surveyor = $this->getUser('surveyor', $rt);
        $verifikator = $this->getUser('verifikator');
        $rumah = $this->getRumah($rt, $surveyor);

        $this->assertTrue($verifikator->can('view', $rumah));
    }

    public function test_admin_boleh_view_semua_rumah(): void
    {
        $rt = $this->getWilayahRt();
        $surveyor = $this->getUser('surveyor', $rt);
        $admin = $this->getUser('admin');
        $rumah = $this->getRumah($rt, $surveyor);

        $this->assertTrue($admin->can('view', $rumah));
    }

    public function test_pimpinan_boleh_view_semua_rumah(): void
    {
        $rt = $this->getWilayahRt();
        $surveyor = $this->getUser('surveyor', $rt);
        $pimpinan = $this->getUser('pimpinan');
        $rumah = $this->getRumah($rt, $surveyor);

        $this->assertTrue($pimpinan->can('view', $rumah));
    }

    public function test_pimpinan_tidak_boleh_update_rumah(): void
    {
        $rt = $this->getWilayahRt();
        $surveyor = $this->getUser('surveyor', $rt);
        $pimpinan = $this->getUser('pimpinan');
        $rumah = $this->getRumah($rt, $surveyor);

        $this->assertFalse($pimpinan->can('update', $rumah));
    }

    public function test_hanya_surveyor_yang_boleh_create_rumah(): void
    {
        $surveyor = $this->getUser('surveyor', $this->getWilayahRt());
        $verifikator = $this->getUser('verifikator');
        $admin = $this->getUser('admin');
        $pimpinan = $this->getUser('pimpinan');

        $this->assertTrue($surveyor->can('create', RumahWarga::class));
        $this->assertFalse($verifikator->can('create', RumahWarga::class));
        $this->assertFalse($admin->can('create', RumahWarga::class));
        $this->assertFalse($pimpinan->can('create', RumahWarga::class));
    }

    public function test_surveyor_tidak_boleh_update_rumah_yang_sudah_diajukan(): void
    {
        $rt = $this->getWilayahRt();
        $surveyor = $this->getUser('surveyor', $rt);
        $rumah = $this->getRumah($rt, $surveyor, 'Diajukan');

        $this->assertFalse($surveyor->can('update', $rumah));
    }
}