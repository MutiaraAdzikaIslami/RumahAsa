<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RumahWarga;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RumahPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        // Daftarkan route testing untuk verifikasi middleware role & policy via HTTP
        Route::middleware(['web', 'role:admin'])->get('/_test/role-admin-only', function () {
            return response()->json(['message' => 'Admin authorized']);
        });

        Route::middleware(['web', 'role:pimpinan|admin'])->get('/_test/multi-role', function () {
            return response()->json(['message' => 'Multi-role authorized']);
        });

        Route::middleware(['web', 'auth'])->get('/_test/rumah/{rumah}', function (RumahWarga $rumah) {
            Gate::authorize('view', $rumah);
            return response()->json(['id' => $rumah->id, 'nama' => $rumah->nama_kepala_keluarga]);
        });

        Route::middleware(['web', 'auth'])->put('/_test/rumah/{rumah}', function (RumahWarga $rumah) {
            Gate::authorize('update', $rumah);
            return response()->json(['message' => 'Update allowed']);
        });

        Route::middleware(['web', 'auth'])->delete('/_test/rumah/{rumah}', function (RumahWarga $rumah) {
            Gate::authorize('delete', $rumah);
            return response()->json(['message' => 'Delete allowed']);
        });
    }

    private function getUser(string $roleKode, ?Wilayah $wilayah = null): User
    {
        $role = Role::where('kode', $roleKode)->first();
        return User::factory()->create([
            'role_id'    => $role->id,
            'wilayah_id' => $roleKode === 'surveyor' ? $wilayah?->id : null,
        ]);
    }

    private function getWilayahKelurahanDanRt(): array
    {
        $kec = Wilayah::create(['kode' => 'KEC-T', 'nama' => 'Kecamatan Test', 'tingkat' => 'kecamatan', 'parent_id' => null]);
        $kec->update(['path' => '/' . $kec->id]);

        $kel = Wilayah::create(['kode' => 'KEL-T', 'nama' => 'Kelurahan Test', 'tingkat' => 'kelurahan', 'parent_id' => $kec->id]);
        $kel->update(['path' => $kec->path . '/' . $kel->id]);

        $rw  = Wilayah::create(['kode' => 'RW-T', 'nama' => 'RW 01', 'tingkat' => 'rw', 'parent_id' => $kel->id]);
        $rw->update(['path' => $kel->path . '/' . $rw->id]);

        $rt  = Wilayah::create(['kode' => 'RT-T', 'nama' => 'RT 01', 'tingkat' => 'rt', 'parent_id' => $rw->id]);
        $rt->update(['path' => $rw->path . '/' . $rt->id]);

        return [$kel->fresh(), $rt->fresh()];
    }

    private function getWilayahRt(): Wilayah
    {
        [$kel, $rt] = $this->getWilayahKelurahanDanRt();
        return $rt;
    }

    private function getWilayahRtLain(): Wilayah
    {
        $kec = Wilayah::create(['kode' => 'KEC-T2', 'nama' => 'Kec Test 2', 'tingkat' => 'kecamatan', 'parent_id' => null]);
        $kec->update(['path' => '/' . $kec->id]);

        $rt  = Wilayah::create(['kode' => 'RT-T2', 'nama' => 'RT 02', 'tingkat' => 'rt', 'parent_id' => $kec->id]);
        $rt->update(['path' => $kec->path . '/' . $rt->id]);

        return $rt->fresh();
    }

    private function getRumah(Wilayah $wilayah, User $surveyor, string $status = RumahWarga::STATUS_DRAF): RumahWarga
    {
        $uniqueNum = mt_rand(100000000000, 999999999999);
        $nik = '3201' . $uniqueNum;
        $noKk = '3201' . $uniqueNum;

        return RumahWarga::create([
            'kode_registrasi'      => 'TEST-' . uniqid(),
            'nama_kepala_keluarga' => 'Bapak Kepala Keluarga',
            'nik_enc'              => encrypt($nik),
            'nik_hash'             => hash_hmac('sha256', $nik, config('app.key')),
            'no_kk_enc'            => encrypt($noKk),
            'no_kk_hash'           => hash_hmac('sha256', $noKk, config('app.key')),
            'jumlah_anggota'       => 4,
            'luas_rumah'           => 36.50,
            'pekerjaan'            => 'Buruh',
            'penghasilan'          => 1500000,
            'status_kepemilikan'   => 'milik_sendiri',
            'alamat'               => 'Jl. Contoh Alamat No. 123',
            'latitude'             => -6.200000,
            'longitude'            => 106.816666,
            'wilayah_id'           => $wilayah->id,
            'surveyor_id'          => $surveyor->id,
            'status'               => $status,
        ])->load('wilayah');
    }

    // =========================================================================
    // AC-01: Pengguna belum login mengakses endpoint terlindungi -> Ditolak
    // =========================================================================

    public function test_ac01_pengguna_belum_login_mengakses_endpoint_terlindungi_dialihkan_ke_login(): void
    {
        $response = $this->get('/_test/role-admin-only');

        $response->assertRedirect(route('login'));
    }

    public function test_ac01_pengguna_belum_login_request_json_mengakses_endpoint_terlindungi_mendapat_401(): void
    {
        $response = $this->getJson('/_test/role-admin-only');

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    // =========================================================================
    // AC-02: Role tidak sesuai mengakses endpoint -> Ditolak (403 Forbidden)
    // =========================================================================

    public function test_ac02_role_tidak_sesuai_mengakses_endpoint_ditolak_403(): void
    {
        $surveyor = $this->getUser('surveyor', $this->getWilayahRt());

        // Surveyor mencoba mengakses endpoint khusus role admin
        $response = $this->actingAs($surveyor)->get('/_test/role-admin-only');

        $response->assertStatus(403);
    }

    public function test_ac02_role_sesuai_mengakses_endpoint_diizinkan_200(): void
    {
        $admin = $this->getUser('admin');

        $response = $this->actingAs($admin)->get('/_test/role-admin-only');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Admin authorized']);
    }

    public function test_ac02_multi_role_pipe_diizinkan_untuk_salah_satu_role(): void
    {
        $pimpinan = $this->getUser('pimpinan');
        $surveyor = $this->getUser('surveyor', $this->getWilayahRt());

        // Pimpinan termasuk dalam 'pimpinan|admin' -> Diizinkan
        $this->actingAs($pimpinan)->get('/_test/multi-role')->assertStatus(200);

        // Surveyor tidak termasuk dalam 'pimpinan|admin' -> Ditolak
        $this->actingAs($surveyor)->get('/_test/multi-role')->assertStatus(403);
    }

    // =========================================================================
    // AC-03: Surveyor mengakses wilayah tugasnya -> Diizinkan
    // =========================================================================

    public function test_ac03_surveyor_mengakses_wilayah_tugasnya_diizinkan(): void
    {
        $rt = $this->getWilayahRt();
        $surveyor = $this->getUser('surveyor', $rt);
        $rumah = $this->getRumah($rt, $surveyor);

        $this->assertTrue($surveyor->can('view', $rumah));
    }

    public function test_ac03_surveyor_mengakses_wilayah_turunan_hierarki_diizinkan(): void
    {
        [$kelurahan, $rt] = $this->getWilayahKelurahanDanRt();
        // Surveyor ditugaskan di tingkat Kelurahan
        $surveyor = $this->getUser('surveyor', $kelurahan);
        // Rumah berada di tingkat RT di bawah Kelurahan tersebut
        $rumah = $this->getRumah($rt, $surveyor);

        $this->assertTrue($surveyor->can('view', $rumah));
    }

    // =========================================================================
    // AC-04: Surveyor mengakses wilayah lain -> Ditolak
    // =========================================================================

    public function test_ac04_surveyor_mengakses_wilayah_lain_ditolak(): void
    {
        $rt1 = $this->getWilayahRt();
        $rt2 = $this->getWilayahRtLain();

        $surveyor = $this->getUser('surveyor', $rt1);
        $rumahLain = $this->getRumah($rt2, $surveyor);

        $this->assertFalse($surveyor->can('view', $rumahLain));
    }

    // =========================================================================
    // AC-05: Hak akses update data rumah (hanya Admin)
    // =========================================================================

    public function test_admin_mengubah_data_berstatus_menunggu_disurvei_diizinkan(): void
    {
        $rt = $this->getWilayahRt();
        $surveyor = $this->getUser('surveyor', $rt);
        $admin = $this->getUser('admin');
        $rumah = $this->getRumah($rt, $surveyor, RumahWarga::STATUS_MENUNGGU_DISURVEI);

        $this->assertTrue($admin->can('update', $rumah));
    }

    public function test_admin_mengubah_data_berstatus_draf_diizinkan(): void
    {
        $rt = $this->getWilayahRt();
        $surveyor = $this->getUser('surveyor', $rt);
        $admin = $this->getUser('admin');
        $rumah = $this->getRumah($rt, $surveyor, RumahWarga::STATUS_DRAF);

        $this->assertTrue($admin->can('update', $rumah));
    }

    public function test_admin_mengubah_data_yang_sudah_diajukan_ditolak(): void
    {
        $rt = $this->getWilayahRt();
        $surveyor = $this->getUser('surveyor', $rt);
        $admin = $this->getUser('admin');
        $rumah = $this->getRumah($rt, $surveyor, RumahWarga::STATUS_DIAJUKAN);

        $this->assertFalse($admin->can('update', $rumah));
    }

    public function test_surveyor_mengubah_data_rumah_ditolak(): void
    {
        $rt = $this->getWilayahRt();
        $surveyor = $this->getUser('surveyor', $rt);
        $rumah = $this->getRumah($rt, $surveyor, RumahWarga::STATUS_DRAF);

        $this->assertFalse($surveyor->can('update', $rumah));
    }

    public function test_verifikator_mengubah_data_rumah_ditolak(): void
    {
        $rt = $this->getWilayahRt();
        $surveyor = $this->getUser('surveyor', $rt);
        $verifikator = $this->getUser('verifikator');
        $rumah = $this->getRumah($rt, $surveyor, RumahWarga::STATUS_DRAF);

        $this->assertFalse($verifikator->can('update', $rumah));
    }

    // =========================================================================
    // Hak akses mengisi survei (hanya Surveyor pada wilayah tugasnya)
    // =========================================================================

    public function test_surveyor_mengisi_survei_wilayah_tugasnya_status_menunggu_disurvei_diizinkan(): void
    {
        $rt = $this->getWilayahRt();
        $surveyor = $this->getUser('surveyor', $rt);
        $rumah = $this->getRumah($rt, $surveyor, RumahWarga::STATUS_MENUNGGU_DISURVEI);

        $this->assertTrue($surveyor->can('survei', $rumah));
    }

    public function test_surveyor_mengisi_survei_wilayah_tugasnya_status_draf_diizinkan(): void
    {
        $rt = $this->getWilayahRt();
        $surveyor = $this->getUser('surveyor', $rt);
        $rumah = $this->getRumah($rt, $surveyor, RumahWarga::STATUS_DRAF);

        $this->assertTrue($surveyor->can('survei', $rumah));
    }

    public function test_surveyor_mengisi_survei_wilayah_tugasnya_status_perlu_perbaikan_diizinkan(): void
    {
        $rt = $this->getWilayahRt();
        $surveyor = $this->getUser('surveyor', $rt);
        $rumah = $this->getRumah($rt, $surveyor, RumahWarga::STATUS_PERLU_PERBAIKAN);

        $this->assertTrue($surveyor->can('survei', $rumah));
    }

    public function test_surveyor_mengisi_survei_wilayah_lain_ditolak(): void
    {
        $rt1 = $this->getWilayahRt();
        $rt2 = $this->getWilayahRtLain();

        $surveyor = $this->getUser('surveyor', $rt1);
        $rumahLain = $this->getRumah($rt2, $surveyor, RumahWarga::STATUS_DRAF);

        $this->assertFalse($surveyor->can('survei', $rumahLain));
    }

    public function test_surveyor_tanpa_wilayah_tidak_bisa_survei(): void
    {
        $rt = $this->getWilayahRt();
        $surveyorTanpaWilayah = $this->getUser('surveyor', null);
        $rumah = $this->getRumah($rt, $surveyorTanpaWilayah, RumahWarga::STATUS_DRAF);

        $this->assertFalse($surveyorTanpaWilayah->can('survei', $rumah));
    }

    public function test_admin_dan_verifikator_tidak_bisa_mengisi_survei(): void
    {
        $rt = $this->getWilayahRt();
        $surveyor = $this->getUser('surveyor', $rt);
        $admin = $this->getUser('admin');
        $verifikator = $this->getUser('verifikator');
        $rumah = $this->getRumah($rt, $surveyor, RumahWarga::STATUS_DRAF);

        $this->assertFalse($admin->can('survei', $rumah));
        $this->assertFalse($verifikator->can('survei', $rumah));
    }

    // =========================================================================
    // AC-07: Surveyor tanpa wilayah tugas mengakses data -> Tidak mendapat akses luas
    // =========================================================================

    public function test_ac07_surveyor_tanpa_wilayah_tugas_tidak_mendapat_akses_melihat_data(): void
    {
        $rt = $this->getWilayahRt();
        // Surveyor tanpa wilayah tugas (wilayah_id = null)
        $surveyorTanpaWilayah = $this->getUser('surveyor', null);
        $rumah = $this->getRumah($rt, $surveyorTanpaWilayah);

        $this->assertFalse($surveyorTanpaWilayah->can('view', $rumah));
    }

    public function test_ac07_surveyor_tanpa_wilayah_tugas_tidak_boleh_membuat_data(): void
    {
        $surveyorTanpaWilayah = $this->getUser('surveyor', null);

        $this->assertFalse($surveyorTanpaWilayah->can('create', RumahWarga::class));
    }

    public function test_ac07_surveyor_tanpa_wilayah_tugas_tidak_boleh_mengubah_data(): void
    {
        $rt = $this->getWilayahRt();
        $surveyorTanpaWilayah = $this->getUser('surveyor', null);
        $rumah = $this->getRumah($rt, $surveyorTanpaWilayah, RumahWarga::STATUS_DRAF);

        $this->assertFalse($surveyorTanpaWilayah->can('update', $rumah));
    }

    // =========================================================================
    // AC-08: Pimpinan melihat data seluruh wilayah -> Diizinkan
    // =========================================================================

    public function test_ac08_pimpinan_melihat_data_seluruh_wilayah_diizinkan(): void
    {
        $rt1 = $this->getWilayahRt();
        $rt2 = $this->getWilayahRtLain();

        $surveyor1 = $this->getUser('surveyor', $rt1);
        $surveyor2 = $this->getUser('surveyor', $rt2);

        $rumah1 = $this->getRumah($rt1, $surveyor1);
        $rumah2 = $this->getRumah($rt2, $surveyor2);

        $pimpinan = $this->getUser('pimpinan');

        $this->assertTrue($pimpinan->can('view', $rumah1));
        $this->assertTrue($pimpinan->can('view', $rumah2));
    }

    // =========================================================================
    // AC-09: Pimpinan membuat data -> Ditolak (read-only)
    // =========================================================================

    public function test_ac09_pimpinan_membuat_data_ditolak(): void
    {
        $pimpinan = $this->getUser('pimpinan');

        $this->assertFalse($pimpinan->can('create', RumahWarga::class));
    }

    // =========================================================================
    // AC-10: Pimpinan mengubah data -> Ditolak (read-only)
    // =========================================================================

    public function test_ac10_pimpinan_mengubah_data_ditolak(): void
    {
        $rt = $this->getWilayahRt();
        $surveyor = $this->getUser('surveyor', $rt);
        $rumah = $this->getRumah($rt, $surveyor, RumahWarga::STATUS_DRAF);

        $pimpinan = $this->getUser('pimpinan');

        $this->assertFalse($pimpinan->can('update', $rumah));
    }

    // =========================================================================
    // AC-11: Pimpinan menghapus data -> Ditolak (read-only)
    // =========================================================================

    public function test_ac11_pimpinan_menghapus_data_ditolak(): void
    {
        $rt = $this->getWilayahRt();
        $surveyor = $this->getUser('surveyor', $rt);
        $rumah = $this->getRumah($rt, $surveyor, RumahWarga::STATUS_DRAF);

        $pimpinan = $this->getUser('pimpinan');

        $this->assertFalse($pimpinan->can('delete', $rumah));
        $this->assertFalse($pimpinan->can('nilai', $rumah));
    }

    // =========================================================================
    // AC-12: Pengguna memanipulasi ID untuk mengakses data wilayah lain -> Ditolak
    // =========================================================================

    public function test_ac12_pengguna_memanipulasi_id_untuk_mengakses_data_wilayah_lain_ditolak(): void
    {
        $rt1 = $this->getWilayahRt();
        $rt2 = $this->getWilayahRtLain();

        $surveyor1 = $this->getUser('surveyor', $rt1);
        $surveyor2 = $this->getUser('surveyor', $rt2);

        $rumahWilayah2 = $this->getRumah($rt2, $surveyor2);

        // Surveyor1 mencoba mengakses URL endpoint /_test/rumah/{id} dengan ID milik rumah di wilayah RT2
        $response = $this->actingAs($surveyor1)->getJson("/_test/rumah/{$rumahWilayah2->id}");

        // Harus ditolak dengan 403 Forbidden
        $response->assertStatus(403);
    }

    public function test_ac12_pimpinan_mencoba_mengubah_atau_menghapus_data_lewat_endpoint_ditolak(): void
    {
        $rt = $this->getWilayahRt();
        $surveyor = $this->getUser('surveyor', $rt);
        $rumah = $this->getRumah($rt, $surveyor, RumahWarga::STATUS_DRAF);

        $pimpinan = $this->getUser('pimpinan');

        // Pimpinan mencoba update via HTTP
        $updateResponse = $this->actingAs($pimpinan)->putJson("/_test/rumah/{$rumah->id}");
        $updateResponse->assertStatus(403);

        // Pimpinan mencoba delete via HTTP
        $deleteResponse = $this->actingAs($pimpinan)->deleteJson("/_test/rumah/{$rumah->id}");
        $deleteResponse->assertStatus(403);
    }

    // =========================================================================
    // Verifikasi Tambahan: Admin dan Verifikator
    // =========================================================================

    public function test_verifikator_dan_admin_boleh_view_semua_rumah(): void
    {
        $rt = $this->getWilayahRt();
        $surveyor = $this->getUser('surveyor', $rt);
        $verifikator = $this->getUser('verifikator');
        $admin = $this->getUser('admin');
        $rumah = $this->getRumah($rt, $surveyor);

        $this->assertTrue($verifikator->can('view', $rumah));
        $this->assertTrue($admin->can('view', $rumah));
    }
}
