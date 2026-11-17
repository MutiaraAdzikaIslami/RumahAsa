<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuatDataBantuan;
use Tests\TestCase;

class PenyaluranBantuanTest extends TestCase
{
    use RefreshDatabase, BuatDataBantuan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanRole();
    }

    private function dataValid(): array
    {
        return [
            'tanggal_bantuan' => '2026-10-05',
            'jenis_bantuan'   => 'Bantuan Stimulan',
            'sumber_program'  => 'APBD',
            'nominal'         => 20000000,
            'nomor_sk'        => 'SK/001/2026',
        ];
    }

    public function test_verifikator_bisa_membuka_form_penyaluran(): void
    {
        $rumah = $this->buatRumah('Penerima Bantuan');

        $this->actingAs($this->buatUser('verifikator'))
            ->get("/bantuan/{$rumah->id}/salurkan")
            ->assertOk()
            ->assertSee('Catat Bantuan');
    }

    public function test_penyaluran_membuat_catatan_dan_memulai_renovasi(): void
    {
        $rumah = $this->buatRumah('Penerima Bantuan');

        $this->actingAs($this->buatUser('verifikator'))
            ->post("/bantuan/{$rumah->id}/salurkan", $this->dataValid())
            ->assertRedirect(route('bantuan.index'));

        $this->assertDatabaseHas('penerima_bantuan', [
            'rumah_id'       => $rumah->id,
            'nomor_sk'       => 'SK/001/2026',
            'sumber_program' => 'APBD',
        ]);
        $this->assertSame('Dalam Renovasi', $rumah->fresh()->status);
        $this->assertDatabaseHas('riwayat_status', ['rumah_id' => $rumah->id, 'status_ke' => 'Dalam Renovasi']);
        $this->assertDatabaseHas('audit_log', ['aksi' => 'BANTUAN_CREATE', 'entitas' => 'penerima_bantuan']);
    }

    public function test_rumah_bukan_penerima_bantuan_ditolak(): void
    {
        $rumah = $this->buatRumah('Layak Huni');

        $this->actingAs($this->buatUser('verifikator'))
            ->post("/bantuan/{$rumah->id}/salurkan", $this->dataValid())
            ->assertRedirect(route('bantuan.index'));

        $this->assertDatabaseCount('penerima_bantuan', 0);
        $this->assertSame('Layak Huni', $rumah->fresh()->status);
    }

    public function test_penyaluran_ganda_ditolak(): void
    {
        $rumah = $this->buatRumah('Penerima Bantuan');
        $this->buatBantuan($rumah);

        $this->actingAs($this->buatUser('verifikator'))
            ->post("/bantuan/{$rumah->id}/salurkan", $this->dataValid())
            ->assertRedirect(route('bantuan.index'));

        $this->assertDatabaseCount('penerima_bantuan', 1);
    }

    public function test_validasi_wajib_diisi(): void
    {
        $rumah = $this->buatRumah('Penerima Bantuan');

        $this->actingAs($this->buatUser('verifikator'))
            ->post("/bantuan/{$rumah->id}/salurkan", [])
            ->assertSessionHasErrors(['tanggal_bantuan', 'jenis_bantuan', 'sumber_program', 'nominal', 'nomor_sk']);
    }

    public function test_admin_dan_pimpinan_tidak_bisa_mencatat_penyaluran(): void
    {
        $rumah = $this->buatRumah('Penerima Bantuan');

        $this->actingAs($this->buatUser('pimpinan'))
            ->post("/bantuan/{$rumah->id}/salurkan", $this->dataValid())
            ->assertForbidden();
        $this->actingAs($this->buatUser('admin'))
            ->post("/bantuan/{$rumah->id}/salurkan", $this->dataValid())
            ->assertForbidden();

        $this->assertDatabaseCount('penerima_bantuan', 0);
    }
}
