<?php

namespace Tests\Feature;

use App\Models\PenerimaBantuan;
use App\Models\RiwayatStatus;
use App\Models\Role;
use App\Models\RumahWarga;
use App\Models\SurveiFoto;
use App\Models\SurveiKelayakan;
use App\Models\SurveiKomponen;
use App\Models\User;
use App\Models\Wilayah;
use Carbon\Carbon;
use Database\Seeders\KomponenPenilaianSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class HeatmapTest extends TestCase
{
    use RefreshDatabase;

    private Role $roleAdmin;

    private Role $roleSurveyor;

    private Role $roleVerifikator;

    private Role $rolePimpinan;

    private Wilayah $kecamatan;

    private Wilayah $kelurahan1;

    private Wilayah $kelurahan2;

    private Wilayah $rw1;

    private Wilayah $rw2;

    private User $admin;

    private User $surveyor;

    private User $verifikator;

    private User $pimpinan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(KomponenPenilaianSeeder::class);

        $this->roleAdmin = Role::where('kode', 'admin')->first();
        $this->roleSurveyor = Role::where('kode', 'surveyor')->first();
        $this->roleVerifikator = Role::where('kode', 'verifikator')->first();
        $this->rolePimpinan = Role::where('kode', 'pimpinan')->first();

        // Hierarki Wilayah
        $this->kecamatan = Wilayah::create([
            'kode' => 'KEC-TEST-'.uniqid(),
            'nama' => 'Kecamatan Uji',
            'tingkat' => Wilayah::TINGKAT_KECAMATAN,
            'is_aktif' => true,
        ]);
        $this->kecamatan->update(['path' => '/'.$this->kecamatan->id]);

        $this->kelurahan1 = Wilayah::create([
            'kode' => 'KEL-TEST-1-'.uniqid(),
            'nama' => 'Kelurahan Sukamaju',
            'tingkat' => Wilayah::TINGKAT_KELURAHAN,
            'parent_id' => $this->kecamatan->id,
            'is_aktif' => true,
        ]);
        $this->kelurahan1->update(['path' => $this->kecamatan->path.'/'.$this->kelurahan1->id]);

        $this->kelurahan2 = Wilayah::create([
            'kode' => 'KEL-TEST-2-'.uniqid(),
            'nama' => 'Kelurahan Cibiru',
            'tingkat' => Wilayah::TINGKAT_KELURAHAN,
            'parent_id' => $this->kecamatan->id,
            'is_aktif' => true,
        ]);
        $this->kelurahan2->update(['path' => $this->kecamatan->path.'/'.$this->kelurahan2->id]);

        $this->rw1 = Wilayah::create([
            'kode' => 'RW-TEST-1-'.uniqid(),
            'nama' => 'RW 01',
            'tingkat' => Wilayah::TINGKAT_RW,
            'parent_id' => $this->kelurahan1->id,
            'is_aktif' => true,
        ]);
        $this->rw1->update(['path' => $this->kelurahan1->path.'/'.$this->rw1->id]);

        $this->rw2 = Wilayah::create([
            'kode' => 'RW-TEST-2-'.uniqid(),
            'nama' => 'RW 02',
            'tingkat' => Wilayah::TINGKAT_RW,
            'parent_id' => $this->kelurahan2->id,
            'is_aktif' => true,
        ]);
        $this->rw2->update(['path' => $this->kelurahan2->path.'/'.$this->rw2->id]);

        // Users
        $this->admin = User::create([
            'nama' => 'Admin Uji',
            'username' => 'admin_test_'.uniqid(),
            'email' => 'admin_'.uniqid().'@test.local',
            'password' => bcrypt('password123'),
            'role_id' => $this->roleAdmin->id,
            'must_change_password' => false,
            'is_aktif' => true,
        ]);

        $this->surveyor = User::create([
            'nama' => 'Surveyor Uji',
            'username' => 'surveyor_test_'.uniqid(),
            'email' => 'surveyor_'.uniqid().'@test.local',
            'password' => bcrypt('password123'),
            'role_id' => $this->roleSurveyor->id,
            'wilayah_id' => $this->kelurahan1->id,
            'must_change_password' => false,
            'is_aktif' => true,
        ]);

        $this->verifikator = User::create([
            'nama' => 'Verifikator Uji',
            'username' => 'verifikator_test_'.uniqid(),
            'email' => 'verifikator_'.uniqid().'@test.local',
            'password' => bcrypt('password123'),
            'role_id' => $this->roleVerifikator->id,
            'must_change_password' => false,
            'is_aktif' => true,
        ]);

        $this->pimpinan = User::create([
            'nama' => 'Pimpinan Uji',
            'username' => 'pimpinan_test_'.uniqid(),
            'email' => 'pimpinan_'.uniqid().'@test.local',
            'password' => bcrypt('password123'),
            'role_id' => $this->rolePimpinan->id,
            'must_change_password' => false,
            'is_aktif' => true,
        ]);
    }

    private function createRumah(array $attrs = []): RumahWarga
    {
        $nik = '3273'.str_pad((string) rand(1, 99999999), 12, '0', STR_PAD_LEFT);

        return RumahWarga::create(array_merge([
            'kode_registrasi' => 'RW-UJI-'.uniqid(),
            'nama_kepala_keluarga' => 'Warga Uji '.uniqid(),
            'nik_enc' => Crypt::encryptString($nik),
            'nik_hash' => hash_hmac('sha256', $nik, 'test'),
            'latitude' => -6.9175,
            'longitude' => 107.6191,
            'wilayah_id' => $this->kelurahan1->id,
            'surveyor_id' => $this->surveyor->id,
            'status' => 'Draf',
        ], $attrs));
    }

    public function test_guest_tidak_bisa_mengakses_endpoint_heatmap(): void
    {
        $response = $this->getJson(route('heatmap.data'));
        $response->assertStatus(401);
    }

    public function test_admin_tidak_memiliki_akses_ke_endpoint_heatmap(): void
    {
        $response = $this->actingAs($this->admin)->getJson(route('heatmap.data'));
        $response->assertStatus(403);
    }

    public function test_verifikator_dapat_mengakses_endpoint_heatmap_semua_wilayah(): void
    {
        // Buat 1 rumah di kelurahan1 dan 1 di kelurahan2
        $this->createRumah(['wilayah_id' => $this->kelurahan1->id, 'status' => 'Diajukan']);
        $this->createRumah(['wilayah_id' => $this->kelurahan2->id, 'status' => 'Diajukan']);

        $response = $this->actingAs($this->verifikator)->getJson(route('heatmap.data'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'layers' => ['menunggu_disurvei', 'sedang_disurvei', 'sedang_renovasi', 'selesai'],
                'counts',
                'area_distribution',
            ]);

        $this->assertGreaterThanOrEqual(2, $response->json('counts.sedang_disurvei'));
    }

    public function test_pimpinan_dapat_mengakses_endpoint_heatmap_semua_wilayah(): void
    {
        $this->createRumah(['wilayah_id' => $this->kelurahan1->id, 'status' => 'Diajukan']);

        $response = $this->actingAs($this->pimpinan)->getJson(route('heatmap.data'));

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_surveyor_hanya_melihat_data_rumah_di_wilayah_tugasnya(): void
    {
        // Rumah di kelurahan1 (wilayah tugas surveyor)
        $r1 = $this->createRumah(['wilayah_id' => $this->kelurahan1->id, 'status' => 'Diajukan', 'latitude' => -6.91, 'longitude' => 107.61]);
        // Rumah di kelurahan2 (di luar wilayah tugas surveyor)
        $r2 = $this->createRumah(['wilayah_id' => $this->kelurahan2->id, 'status' => 'Diajukan', 'latitude' => -6.95, 'longitude' => 107.65]);

        $response = $this->actingAs($this->surveyor)->getJson(route('heatmap.data'));

        $response->assertStatus(200);
        $points = collect($response->json('layers.sedang_disurvei'));

        $this->assertTrue($points->contains(fn ($p) => round($p['lat'], 2) === -6.91));
        $this->assertFalse($points->contains(fn ($p) => round($p['lat'], 2) === -6.95));
    }

    public function test_surveyor_tanpa_wilayah_tugas_mengembalikan_data_kosong(): void
    {
        $surveyorNoWilayah = User::create([
            'nama' => 'Surveyor Tanpa Wilayah',
            'username' => 'surveyor_nowil_'.uniqid(),
            'email' => 'surveyor_nowil_'.uniqid().'@test.local',
            'password' => bcrypt('password123'),
            'role_id' => $this->roleSurveyor->id,
            'wilayah_id' => null,
            'must_change_password' => false,
            'is_aktif' => true,
        ]);

        $response = $this->actingAs($surveyorNoWilayah)->getJson(route('heatmap.data'));

        $response->assertStatus(200)
            ->assertJson([
                'counts' => [
                    'menunggu_disurvei' => 0,
                    'sedang_disurvei' => 0,
                    'sedang_renovasi' => 0,
                    'selesai' => 0,
                    'total' => 0,
                ],
            ]);
    }

    public function test_data_heatmap_tidak_menampilkan_data_pribadi_warga(): void
    {
        $this->createRumah(['status' => 'Diajukan']);

        $response = $this->actingAs($this->verifikator)->getJson(route('heatmap.data'));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringNotContainsString('nama_kepala_keluarga', $content);
        $this->assertStringNotContainsString('nik', $content);
        $this->assertStringNotContainsString('no_kk', $content);
        $this->assertStringNotContainsString('Warga Uji', $content);
    }

    public function test_rumah_tanpa_koordinat_tidak_muncul_di_heatmap(): void
    {
        $this->createRumah(['status' => 'Diajukan', 'latitude' => null, 'longitude' => null]);

        $response = $this->actingAs($this->verifikator)->getJson(route('heatmap.data'));

        $response->assertStatus(200);
        $points = collect($response->json('layers.sedang_disurvei'));
        $this->assertFalse($points->contains(fn ($p) => $p['lat'] === null || $p['lng'] === null));
    }

    public function test_lapisan_layak_huni_dan_penerima_bantuan_tidak_ditampilkan(): void
    {
        $this->createRumah(['status' => RumahWarga::STATUS_LAYAK_HUNI, 'latitude' => -6.991, 'longitude' => 107.691]);
        $this->createRumah(['status' => RumahWarga::STATUS_PENERIMA_BANTUAN, 'latitude' => -6.992, 'longitude' => 107.692]);

        $response = $this->actingAs($this->verifikator)->getJson(route('heatmap.data'));

        $response->assertStatus(200);
        $allPoints = collect($response->json('layers'))->flatten(1);

        $this->assertFalse($allPoints->contains(fn ($p) => round($p['lat'], 3) === -6.991));
        $this->assertFalse($allPoints->contains(fn ($p) => round($p['lat'], 3) === -6.992));
    }

    public function test_filter_sumber_program_hanya_memengaruhi_dua_lapisan_renovasi(): void
    {
        // 1. Sedang Disurvei (tidak terpengaruh sumber program)
        $rSurvey = $this->createRumah(['status' => 'Diajukan', 'latitude' => -6.801, 'longitude' => 107.801]);

        // 2. Dalam Renovasi dengan APBN
        $rRenovApbn = $this->createRumah(['status' => 'Dalam Renovasi', 'latitude' => -6.802, 'longitude' => 107.802]);
        PenerimaBantuan::create([
            'rumah_id' => $rRenovApbn->id,
            'sumber_program' => 'APBN',
            'nominal' => 15000000,
        ]);

        // 3. Selesai dengan APBD
        $rSelesaiApbd = $this->createRumah(['status' => 'Selesai', 'latitude' => -6.803, 'longitude' => 107.803]);
        PenerimaBantuan::create([
            'rumah_id' => $rSelesaiApbd->id,
            'sumber_program' => 'APBD',
            'nominal' => 10000000,
        ]);

        // Request filter sumber_program = APBN
        $response = $this->actingAs($this->verifikator)->getJson(route('heatmap.data', ['sumber_program' => 'APBN']));

        $response->assertStatus(200);

        // Lapisan Sedang Disurvei TETAP muncul (tidak terpengaruh)
        $disurvei = collect($response->json('layers.sedang_disurvei'));
        $this->assertTrue($disurvei->contains(fn ($p) => round($p['lat'], 3) === -6.801));

        // Lapisan Sedang Renovasi: yang APBN muncul
        $renov = collect($response->json('layers.sedang_renovasi'));
        $this->assertTrue($renov->contains(fn ($p) => round($p['lat'], 3) === -6.802));

        // Lapisan Selesai: yang APBD TIDAK muncul karena filter APBN
        $selesai = collect($response->json('layers.selesai'));
        $this->assertFalse($selesai->contains(fn ($p) => round($p['lat'], 3) === -6.803));
    }

    public function test_filter_triwulan_menyaring_berdasarkan_tanggal_status(): void
    {
        $rTriwulan1 = $this->createRumah(['status' => 'Diajukan', 'latitude' => -6.701, 'longitude' => 107.701]);
        RiwayatStatus::create([
            'rumah_id' => $rTriwulan1->id,
            'user_id' => $this->surveyor->id,
            'status_dari' => 'Draf',
            'status_ke' => 'Diajukan',
            'created_at' => Carbon::create(2026, 2, 15, 10, 0, 0), // Bulan 2 = Triwulan 1
        ]);

        $rTriwulan3 = $this->createRumah(['status' => 'Diajukan', 'latitude' => -6.703, 'longitude' => 107.703]);
        RiwayatStatus::create([
            'rumah_id' => $rTriwulan3->id,
            'user_id' => $this->surveyor->id,
            'status_dari' => 'Draf',
            'status_ke' => 'Diajukan',
            'created_at' => Carbon::create(2026, 8, 20, 10, 0, 0), // Bulan 8 = Triwulan 3
        ]);

        // Request filter triwulan 1 tahun 2026
        $response = $this->actingAs($this->verifikator)->getJson(route('heatmap.data', [
            'triwulan' => 1,
            'tahun' => 2026,
        ]));

        $response->assertStatus(200);
        $points = collect($response->json('layers.sedang_disurvei'));

        $this->assertTrue($points->contains(fn ($p) => round($p['lat'], 3) === -6.701));
        $this->assertFalse($points->contains(fn ($p) => round($p['lat'], 3) === -6.703));
    }

    public function test_dashboard_verifikator_merender_halaman_dan_komponen_heatmap(): void
    {
        $rumah = $this->createRumah(['status' => 'Diajukan']);
        $survei = SurveiKelayakan::create([
            'rumah_id' => $rumah->id,
            'surveyor_id' => $this->surveyor->id,
            'tanggal_survei' => now(),
        ]);
        $komponen = SurveiKomponen::create([
            'survei_id' => $survei->id,
            'komponen_id' => (\App\Models\KomponenPenilaian::first() ?? \App\Models\KomponenPenilaian::create(['kode' => 'struktur', 'nama' => 'Struktur Bangunan', 'urutan' => 1]))->id,
        ]);
        SurveiFoto::create([
            'survei_komponen_id' => $komponen->id,
            'path_file' => 'survei/test.jpg',
            'mime_type' => 'image/jpeg',
            'ukuran_byte' => 1024,
            'nama_asli' => 'test.jpg',
        ]);

        $response = $this->actingAs($this->verifikator)->get(route('dashboard'));

        $response->assertStatus(200)
            ->assertSee('Dashboard Verifikator')
            ->assertSee('Heatmap Status Rumah')
            ->assertSee('heatmap-map');
    }

    public function test_dashboard_surveyor_merender_halaman_dan_komponen_heatmap(): void
    {
        $response = $this->actingAs($this->surveyor)->get(route('dashboard'));

        $response->assertStatus(200)
            ->assertSee('Selamat datang, '.$this->surveyor->nama)
            ->assertSee('Heatmap Status Rumah')
            ->assertSee('heatmap-map');
    }

    public function test_dashboard_pimpinan_merender_halaman_dan_komponen_heatmap(): void
    {
        $response = $this->actingAs($this->pimpinan)->get(route('dashboard'));

        $response->assertStatus(200)
            ->assertSee('Dashboard Pimpinan')
            ->assertSee('Heatmap Status Rumah')
            ->assertSee('heatmap-map');
    }

    public function test_dashboard_admin_tidak_menampilkan_komponen_heatmap(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200)
            ->assertDontSee('Heatmap Status Rumah')
            ->assertDontSee('heatmap-map');
    }
}
