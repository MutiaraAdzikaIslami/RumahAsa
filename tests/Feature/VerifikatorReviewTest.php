<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\KomponenPenilaian;
use App\Models\Role;
use App\Models\RumahWarga;
use App\Models\SurveiFoto;
use App\Models\SurveiKelayakan;
use App\Models\SurveiKomponen;
use App\Models\User;
use App\Models\Wilayah;
use App\Modules\Core\Services\NikEncryptor;
use Database\Seeders\KomponenPenilaianSeeder;
use Database\Seeders\OpsiKondisiSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifikatorReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $verifikator;

    private User $surveyor;

    private Wilayah $rt;

    private Wilayah $rt2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(KomponenPenilaianSeeder::class);
        $this->seed(OpsiKondisiSeeder::class);

        $kec = Wilayah::create(['kode' => 'KEC-A', 'nama' => 'Kecamatan A', 'tingkat' => 'kecamatan', 'parent_id' => null]);
        $kec->update(['path' => '/'.$kec->id]);

        $kel = Wilayah::create(['kode' => 'KEL-A', 'nama' => 'Kelurahan A', 'tingkat' => 'kelurahan', 'parent_id' => $kec->id]);
        $kel->update(['path' => $kec->path.'/'.$kel->id]);

        $this->rt = Wilayah::create(['kode' => 'RT-01', 'nama' => 'RT 01', 'tingkat' => 'rt', 'parent_id' => $kel->id]);
        $this->rt->update(['path' => $kel->path.'/'.$this->rt->id]);

        $this->rt2 = Wilayah::create(['kode' => 'RT-02', 'nama' => 'RT 02', 'tingkat' => 'rt', 'parent_id' => $kel->id]);
        $this->rt2->update(['path' => $kel->path.'/'.$this->rt2->id]);

        $verifikatorRole = Role::where('kode', 'verifikator')->first();
        $this->verifikator = User::factory()->create([
            'role_id' => $verifikatorRole->id,
            'wilayah_id' => null,
            'nama' => 'Verifikator Utama',
            'must_change_password' => false,
            'is_aktif' => true,
        ]);

        $surveyorRole = Role::where('kode', 'surveyor')->first();
        $this->surveyor = User::factory()->create([
            'role_id' => $surveyorRole->id,
            'wilayah_id' => $this->rt->id,
            'nama' => 'Surveyor Lapangan',
            'must_change_password' => false,
            'is_aktif' => true,
        ]);
    }

    private function createRumahDiajukan(string $namaKk = 'Warga Diajukan', ?Wilayah $wilayah = null): RumahWarga
    {
        $nikEncryptor = app(NikEncryptor::class);
        $nik = '320101'.rand(1000000000, 9999999999);
        $targetWilayah = $wilayah ?? $this->rt;

        $rumah = RumahWarga::create([
            'kode_registrasi' => 'RA-'.uniqid(),
            'nama_kepala_keluarga' => $namaKk,
            'nik_enc' => $nikEncryptor->encryptNik($nik),
            'nik_hash' => $nikEncryptor->hashNik($nik),
            'no_kk_enc' => $nikEncryptor->encryptNoKk($nik),
            'no_kk_hash' => $nikEncryptor->hashNoKk($nik),
            'jumlah_anggota' => 4,
            'luas_rumah' => 45.0,
            'pekerjaan' => 'Buruh',
            'penghasilan' => 1500000,
            'status_kepemilikan' => 'milik_sendiri',
            'alamat' => 'Jl. Contoh No. 123',
            'latitude' => -6.2000,
            'longitude' => 106.8166,
            'wilayah_id' => $targetWilayah->id,
            'surveyor_id' => $this->surveyor->id,
            'status' => RumahWarga::STATUS_DIAJUKAN,
        ]);

        $survei = SurveiKelayakan::create([
            'rumah_id' => $rumah->id,
            'surveyor_id' => $this->surveyor->id,
            'tanggal_survei' => now()->subDay(),
            'dikirim_at' => now(),
        ]);

        $komponenList = KomponenPenilaian::with('opsiKondisi')->orderBy('urutan')->get();
        foreach ($komponenList as $k) {
            $opsi = $k->opsiKondisi->first();
            $sk = SurveiKomponen::create([
                'survei_id' => $survei->id,
                'komponen_id' => $k->id,
                'opsi_kondisi_id' => $opsi?->id,
                'catatan' => 'Catatan untuk '.$k->nama,
            ]);

            SurveiFoto::create([
                'survei_komponen_id' => $sk->id,
                'path_file' => 'survei-foto/test-'.uniqid().'.jpg',
                'mime_type' => 'image/jpeg',
                'ukuran_byte' => 204800,
                'nama_asli' => 'bukti-'.$k->id.'.jpg',
            ]);
        }

        return $rumah;
    }

    public function test_guest_di_redirect_ke_login(): void
    {
        $response = $this->get('/verifikator/survei');
        $response->assertRedirect('/login');
    }

    public function test_surveyor_tidak_dapat_mengakses_antrean_verifikator(): void
    {
        $response = $this->actingAs($this->surveyor)->get('/verifikator/survei');
        $response->assertStatus(403);
    }

    public function test_verifikator_dapat_melihat_halaman_antrean_survei_diajukan(): void
    {
        $rumahDiajukan = $this->createRumahDiajukan('Keluarga Pak Sukirman');

        // Bikin juga rumah yang masih Draf
        $rumahDraf = RumahWarga::create([
            'kode_registrasi' => 'RA-DRAF-001',
            'nama_kepala_keluarga' => 'Keluarga Draf Rahasia',
            'wilayah_id' => $this->rt->id,
            'surveyor_id' => $this->surveyor->id,
            'status' => RumahWarga::STATUS_DRAF,
        ]);

        $response = $this->actingAs($this->verifikator)->get('/verifikator/survei');

        $response->assertOk();
        $response->assertSee('Antrean Survei Diajukan');
        $response->assertSee('Keluarga Pak Sukirman');
        $response->assertSee($rumahDiajukan->kode_registrasi);
        // Memastikan rumah draf tidak masuk ke antrean verifikator
        $response->assertDontSee('Keluarga Draf Rahasia');
    }

    public function test_verifikator_bisa_filter_antrean_berdasarkan_pencarian(): void
    {
        $rumah1 = $this->createRumahDiajukan('Bambang Pamungkas');
        $rumah2 = $this->createRumahDiajukan('Siti Khadijah');

        $response = $this->actingAs($this->verifikator)->get('/verifikator/survei?search=Bambang');

        $response->assertOk();
        $response->assertSee('Bambang Pamungkas');
        $response->assertDontSee('Siti Khadijah');
    }

    public function test_verifikator_bisa_filter_antrean_berdasarkan_wilayah(): void
    {
        $rumah1 = $this->createRumahDiajukan('Warga RT 01', $this->rt);
        $rumah2 = $this->createRumahDiajukan('Warga RT 02', $this->rt2);

        $response = $this->actingAs($this->verifikator)->get("/verifikator/survei?wilayah_id={$this->rt->id}");

        $response->assertOk();
        $response->assertSee('Warga RT 01');
        $response->assertDontSee('Warga RT 02');
    }

    public function test_verifikator_dapat_melihat_halaman_review_dengan_detail_data_dan_galeri_foto(): void
    {
        $rumah = $this->createRumahDiajukan('Agus Salim');

        $response = $this->actingAs($this->verifikator)->get("/verifikator/survei/{$rumah->id}");

        $response->assertOk();
        $response->assertSee('Review Survei');
        $response->assertSee('Agus Salim');
        $response->assertSee($rumah->kode_registrasi);
        $response->assertSee('Detail Penilaian 7 Komponen dan Galeri Foto Bukti');
        $response->assertSee('Foto Bukti Lapangan');
        $response->assertSee('Minta Perbaikan');
        $response->assertSee('Tetapkan Kategori');
    }

    public function test_verifikator_bisa_minta_perbaikan_dengan_catatan(): void
    {
        $rumah = $this->createRumahDiajukan('Warga Butuh Perbaikan');

        $response = $this->actingAs($this->verifikator)->post("/verifikator/survei/{$rumah->id}/minta-perbaikan", [
            'catatan' => 'Foto atap dan lantai kurang jelas, mohon diperbaiki dan difoto ulang.',
        ]);

        $response->assertRedirect('/verifikator/survei');
        $response->assertSessionHas('success');

        $rumah->refresh();
        $this->assertEquals(RumahWarga::STATUS_PERLU_PERBAIKAN, $rumah->status);

        $this->assertDatabaseHas('riwayat_status', [
            'rumah_id' => $rumah->id,
            'user_id' => $this->verifikator->id,
            'status_dari' => RumahWarga::STATUS_DIAJUKAN,
            'status_ke' => RumahWarga::STATUS_PERLU_PERBAIKAN,
            'catatan' => 'Foto atap dan lantai kurang jelas, mohon diperbaiki dan difoto ulang.',
        ]);

        $this->assertDatabaseHas('audit_log', [
            'user_id' => $this->verifikator->id,
            'aksi' => AuditLog::AKSI['STATUS_CHANGE'],
            'entitas' => 'rumah_warga',
            'entitas_id' => $rumah->id,
        ]);
    }

    public function test_minta_perbaikan_gagal_jika_catatan_kurang_dari_5_karakter(): void
    {
        $rumah = $this->createRumahDiajukan('Warga Butuh Perbaikan');

        $response = $this->actingAs($this->verifikator)->post("/verifikator/survei/{$rumah->id}/minta-perbaikan", [
            'catatan' => 'Kur',
        ]);

        $response->assertSessionHasErrors('catatan');

        $rumah->refresh();
        $this->assertEquals(RumahWarga::STATUS_DIAJUKAN, $rumah->status);
    }

    public function test_verifikator_bisa_tetapkan_kategori_tidak_layak_huni(): void
    {
        $rumah = $this->createRumahDiajukan('Warga Tidak Layak Huni');

        $response = $this->actingAs($this->verifikator)->post("/verifikator/survei/{$rumah->id}/tetapkan-kategori", [
            'kategori' => 'tidak_layak_huni',
            'catatan' => 'Struktur atap dan dinding mengalami kerusakan parah.',
        ]);

        $response->assertRedirect('/verifikator/survei');
        $response->assertSessionHas('success');

        $rumah->refresh();
        $this->assertEquals(RumahWarga::STATUS_PENERIMA_BANTUAN, $rumah->status);

        $this->assertDatabaseHas('riwayat_status', [
            'rumah_id' => $rumah->id,
            'status_ke' => RumahWarga::STATUS_PENERIMA_BANTUAN,
        ]);
    }

    public function test_verifikator_bisa_tetapkan_kategori_layak_huni(): void
    {
        $rumah = $this->createRumahDiajukan('Warga Rumah Bagus');

        $response = $this->actingAs($this->verifikator)->post("/verifikator/survei/{$rumah->id}/tetapkan-kategori", [
            'kategori' => 'layak_huni',
            'catatan' => 'Semua komponen dalam kondisi baik dan aman.',
        ]);

        $response->assertRedirect('/verifikator/survei');
        $response->assertSessionHas('success');

        $rumah->refresh();
        $this->assertEquals(RumahWarga::STATUS_LAYAK_HUNI, $rumah->status);

        $this->assertDatabaseHas('riwayat_status', [
            'rumah_id' => $rumah->id,
            'status_ke' => RumahWarga::STATUS_LAYAK_HUNI,
        ]);
    }
}
