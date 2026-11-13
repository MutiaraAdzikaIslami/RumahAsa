<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RumahWarga;
use App\Models\User;
use App\Models\Wilayah;
use App\Modules\Workflow\Services\StatusWorkflow;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiwayatStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $surveyor;

    private User $verifikator;

    private Wilayah $rt;

    private StatusWorkflow $workflow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $kec = Wilayah::create(['kode' => 'KEC-01', 'nama' => 'Kecamatan Contoh', 'tingkat' => 'kecamatan', 'parent_id' => null]);
        $kec->update(['path' => '/'.$kec->id]);
        $this->rt = Wilayah::create(['kode' => 'RT-01', 'nama' => 'RT 001', 'tingkat' => 'rt', 'parent_id' => $kec->id]);
        $this->rt->update(['path' => $kec->path.'/'.$this->rt->id]);

        $roleSurveyor = Role::where('kode', 'surveyor')->first();
        $this->surveyor = User::factory()->create([
            'nama' => 'Ahmad Surveyor',
            'role_id' => $roleSurveyor->id,
            'wilayah_id' => $this->rt->id,
            'must_change_password' => false,
        ]);

        $roleVerifikator = Role::where('kode', 'verifikator')->first();
        $this->verifikator = User::factory()->create([
            'nama' => 'Siti Verifikator',
            'role_id' => $roleVerifikator->id,
            'must_change_password' => false,
        ]);

        $this->workflow = app(StatusWorkflow::class);
    }

    private function buatRumah(string $status = 'Draf'): RumahWarga
    {
        return RumahWarga::create([
            'kode_registrasi' => 'RUMAH-2026-0001',
            'nama_kepala_keluarga' => 'Bapak Budi',
            'nik_enc' => encrypt('1234567890123456'),
            'nik_hash' => hash_hmac('sha256', '1234567890123456', 'test'),
            'no_kk_enc' => encrypt('1234567890123456'),
            'no_kk_hash' => hash_hmac('sha256', '1234567890123456', 'test'),
            'jumlah_anggota' => 4,
            'luas_rumah' => 45.0,
            'status_kepemilikan' => 'milik_sendiri',
            'alamat' => 'Jl. Merdeka No. 10',
            'wilayah_id' => $this->rt->id,
            'surveyor_id' => $this->surveyor->id,
            'status' => $status,
        ]);
    }

    /**
     * SCRUM-108: Tulis riwayat di setiap perubahan status (append-only)
     */
    public function test_riwayat_status_ditulis_secara_append_only_di_setiap_perubahan_status(): void
    {
        $rumah = $this->buatRumah('Draf');

        // Transisi 1: Draf -> Diajukan oleh Surveyor
        $this->workflow->ajukan($rumah, $this->surveyor);

        $this->assertDatabaseHas('riwayat_status', [
            'rumah_id' => $rumah->id,
            'user_id' => $this->surveyor->id,
            'status_dari' => 'Draf',
            'status_ke' => 'Diajukan',
            'catatan' => 'Survei diajukan ke Verifikator',
        ]);

        // Transisi 2: Diajukan -> Perlu Perbaikan oleh Verifikator
        $this->workflow->mintaPerbaikan($rumah, 'Foto dinding perlu diperjelas', $this->verifikator);

        $this->assertDatabaseHas('riwayat_status', [
            'rumah_id' => $rumah->id,
            'user_id' => $this->verifikator->id,
            'status_dari' => 'Diajukan',
            'status_ke' => 'Perlu Perbaikan',
            'catatan' => 'Foto dinding perlu diperjelas',
        ]);

        // Total 2 record riwayat (append-only)
        $this->assertCount(2, $rumah->fresh()->riwayatStatus);
    }

    /**
     * SCRUM-109 & FR-24: Tampilan riwayat (siapa, kapan, dari-ke, catatan)
     */
    public function test_tampilan_riwayat_status_menampilkan_siapa_kapan_dari_ke_dan_catatan(): void
    {
        $rumah = $this->buatRumah('Draf');

        // Lakukan 2 transisi status
        $this->workflow->ajukan($rumah, $this->surveyor);
        $this->workflow->mintaPerbaikan($rumah, 'Foto atap kurang jelas, harap unggah ulang', $this->verifikator);

        // Surveyor mengakses detail rumah
        $response = $this->actingAs($this->surveyor)->get(route('rumah.show', $rumah));

        $response->assertStatus(200);

        // Memastikan heading riwayat status muncul
        $response->assertSee('Riwayat Status');
        $response->assertSee('2 Perubahan');

        // Memastikan SIAPA muncul (nama user & rolenya)
        $response->assertSee('Ahmad Surveyor');
        $response->assertSee('Siti Verifikator');

        // Memastikan DARI-KE muncul
        $response->assertSee('Draf');
        $response->assertSee('Diajukan');
        $response->assertSee('Perlu Perbaikan');

        // Memastikan CATATAN muncul
        $response->assertSee('Survei diajukan ke Verifikator');
        $response->assertSee('Foto atap kurang jelas, harap unggah ulang');

        // Memastikan KAPAN (format tanggal d/m/Y) muncul
        $response->assertSee(now()->format('d/m/Y'));
    }

    /**
     * Tampilan jika belum ada perubahan status
     */
    public function test_rumah_tanpa_riwayat_menampilkan_pesan_kosong(): void
    {
        $rumah = $this->buatRumah('Draf');

        $response = $this->actingAs($this->surveyor)->get(route('rumah.show', $rumah));

        $response->assertStatus(200);
        $response->assertSee('Belum ada riwayat perubahan status.');
    }
}
