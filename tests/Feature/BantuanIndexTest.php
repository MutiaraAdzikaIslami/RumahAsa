<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuatDataBantuan;
use Tests\TestCase;

class BantuanIndexTest extends TestCase
{
    use RefreshDatabase, BuatDataBantuan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanRole();
    }

    public function test_verifikator_melihat_hanya_rumah_penerima_bantuan(): void
    {
        $this->buatRumah('Penerima Bantuan', 'Budi Penerima');
        $this->buatRumah('Layak Huni', 'Warga Layak');

        $this->actingAs($this->buatUser('verifikator'))
            ->get('/bantuan')
            ->assertOk()
            ->assertSee('Budi Penerima')
            ->assertDontSee('Warga Layak');
    }

    public function test_admin_dan_pimpinan_bisa_melihat_daftar(): void
    {
        $this->actingAs($this->buatUser('admin'))->get('/bantuan')->assertOk();
        $this->actingAs($this->buatUser('pimpinan'))->get('/bantuan')->assertOk();
    }

    public function test_surveyor_ditolak(): void
    {
        $this->actingAs($this->buatUser('surveyor'))->get('/bantuan')->assertForbidden();
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/bantuan')->assertRedirect('/login');
    }

    public function test_nik_ditampilkan_tersamar(): void
    {
        $this->buatRumah('Penerima Bantuan', 'Budi Penerima');

        $this->actingAs($this->buatUser('pimpinan'))
            ->get('/bantuan')
            ->assertSee('357801******0001')
            ->assertDontSee('3578010101900001');
    }

    public function test_pencarian_menyaring_nama(): void
    {
        $this->buatRumah('Penerima Bantuan', 'Budi Penerima');
        $this->buatRumah('Penerima Bantuan', 'Siti Lain');

        $this->actingAs($this->buatUser('verifikator'))
            ->get('/bantuan?search=Budi')
            ->assertSee('Budi Penerima')
            ->assertDontSee('Siti Lain');
    }

    public function test_pencarian_menyaring_alamat_dan_kode(): void
    {
        $rumah1 = $this->buatRumah('Penerima Bantuan', 'Warga Satu');
        $rumah2 = $this->buatRumah('Penerima Bantuan', 'Warga Dua');

        $this->actingAs($this->buatUser('verifikator'))
            ->get("/bantuan?search={$rumah1->kode_registrasi}")
            ->assertSee('Warga Satu')
            ->assertDontSee('Warga Dua');

        $this->actingAs($this->buatUser('verifikator'))
            ->get("/bantuan?search={$rumah2->alamat}")
            ->assertSee('Warga Dua')
            ->assertDontSee('Warga Satu');
    }

    public function test_filter_status_menyaring_rumah(): void
    {
        $this->buatRumah('Penerima Bantuan', 'Budi Calon');
        $this->buatRumah('Dalam Renovasi', 'Agus Renovasi');
        $this->buatRumah('Selesai', 'Dewi Selesai');

        $response = $this->actingAs($this->buatUser('verifikator'))
            ->get('/bantuan?status=Dalam Renovasi');

        $response->assertOk()
            ->assertSee('Agus Renovasi')
            ->assertDontSee('Budi Calon')
            ->assertDontSee('Dewi Selesai');
    }

    public function test_filter_wilayah_menyaring_rumah(): void
    {
        $rumah1 = $this->buatRumah('Penerima Bantuan', 'Warga Wilayah A');
        $rumah2 = $this->buatRumah('Penerima Bantuan', 'Warga Wilayah B');

        $response = $this->actingAs($this->buatUser('verifikator'))
            ->get("/bantuan?wilayah_id={$rumah1->wilayah_id}");

        $response->assertOk()
            ->assertSee('Warga Wilayah A')
            ->assertDontSee('Warga Wilayah B');
    }

    public function test_tampilkan_status_bantuan_dan_persentase_progres_terakhir(): void
    {
        $rumah = $this->buatRumah('Dalam Renovasi', 'Warga Progres');
        $bantuan = $this->buatBantuan($rumah);
        $this->buatProgres($bantuan, 30, '2026-10-10');
        $this->buatProgres($bantuan, 65, '2026-10-20');

        $this->actingAs($this->buatUser('verifikator'))
            ->get('/bantuan')
            ->assertOk()
            ->assertSee('Progres Penerima Bantuan')
            ->assertSee('DETAIL MONITORING')
            ->assertSee('Review Penyelesaian Renovasi')
            ->assertSee('Warga Progres')
            ->assertSee('Dalam Renovasi')
            ->assertSee('65%');
    }

    public function test_verifikator_dan_pimpinan_bisa_melihat_rekap_dana(): void
    {
        $rumah = $this->buatRumah('Dalam Renovasi', 'Warga Rekap Dana');
        $this->buatBantuan($rumah, '2026-10-01');

        $this->actingAs($this->buatUser('verifikator'))
            ->get('/bantuan/rekap-dana')
            ->assertOk()
            ->assertSee('Rekap Dana Bantuan')
            ->assertSee('Ringkasan per Sumber Program')
            ->assertSee('Rumah Penerima Dana')
            ->assertSee('Warga Rekap Dana')
            ->assertSee('APBD');
    }

    public function test_ekspor_rekap_dana_excel_dan_pdf_mencatat_audit_log(): void
    {
        $rumah = $this->buatRumah('Dalam Renovasi', 'Warga Ekspor Dana');
        $this->buatBantuan($rumah, '2026-10-01');
        $verifikator = $this->buatUser('verifikator');

        $resExcel = $this->actingAs($verifikator)
            ->get('/bantuan/rekap-dana/ekspor?format=excel&tahun=2026');
        $resExcel->assertOk();
        $this->assertDatabaseHas('audit_log', [
            'aksi'    => 'EKSPOR_EXCEL',
            'entitas' => 'rekap_dana',
        ]);

        $resPdf = $this->actingAs($verifikator)
            ->get('/bantuan/rekap-dana/ekspor?format=pdf&tahun=2026');
        $resPdf->assertOk();
        $this->assertDatabaseHas('audit_log', [
            'aksi'    => 'EKSPOR_PDF',
            'entitas' => 'rekap_dana',
        ]);
    }

    public function test_ekspor_daftar_penerima_bantuan_excel_dan_pdf(): void
    {
        $rumah = $this->buatRumah('Penerima Bantuan', 'Warga Ekspor Daftar');
        $verifikator = $this->buatUser('verifikator');

        $this->actingAs($verifikator)
            ->get('/bantuan/ekspor?format=excel')
            ->assertOk();
        $this->assertDatabaseHas('audit_log', [
            'aksi'    => 'EKSPOR_EXCEL',
            'entitas' => 'penerima_bantuan',
        ]);

        $this->actingAs($verifikator)
            ->get('/bantuan/ekspor?format=pdf&mode=progres')
            ->assertOk();
        $this->assertDatabaseHas('audit_log', [
            'aksi'    => 'EKSPOR_PDF',
            'entitas' => 'progres_renovasi',
        ]);
    }
}
