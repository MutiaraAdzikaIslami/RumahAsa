<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuatDataBantuan;
use Tests\TestCase;

class ProgresRenovasiTest extends TestCase
{
    use RefreshDatabase, BuatDataBantuan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanRole();
    }

    private function dataProgres(int $persen = 25, string $tanggal = '2026-10-10'): array
    {
        return [
            'tahap_pekerjaan' => 'Pondasi',
            'persentase'      => $persen,
            'tanggal_progres' => $tanggal,
            'catatan'         => 'Selesai digali',
        ];
    }

    public function test_verifikator_menambah_progres(): void
    {
        $rumah = $this->buatRumah('Dalam Renovasi');
        $this->buatBantuan($rumah);

        $this->actingAs($this->buatUser('verifikator'))
            ->post("/bantuan/{$rumah->id}/progres", $this->dataProgres())
            ->assertRedirect(route('bantuan.progres.index', $rumah));

        $this->assertDatabaseHas('progres_renovasi', ['tahap_pekerjaan' => 'Pondasi', 'persentase' => 25]);
        $this->assertDatabaseHas('audit_log', ['aksi' => 'PROGRES_CREATE']);
    }

    public function test_verifikator_mengubah_progres(): void
    {
        $rumah   = $this->buatRumah('Dalam Renovasi');
        $bantuan = $this->buatBantuan($rumah);
        $progres = $this->buatProgres($bantuan, 25, '2026-10-10');

        $this->actingAs($this->buatUser('verifikator'))
            ->put("/bantuan/{$rumah->id}/progres/{$progres->id}", $this->dataProgres(40))
            ->assertRedirect(route('bantuan.progres.index', $rumah));

        $this->assertSame(40, $progres->fresh()->persentase);
        $this->assertDatabaseHas('audit_log', ['aksi' => 'PROGRES_UPDATE']);
    }

    public function test_verifikator_menghapus_progres(): void
    {
        $rumah   = $this->buatRumah('Dalam Renovasi');
        $bantuan = $this->buatBantuan($rumah);
        $progres = $this->buatProgres($bantuan, 25, '2026-10-10');

        $this->actingAs($this->buatUser('verifikator'))
            ->delete("/bantuan/{$rumah->id}/progres/{$progres->id}")
            ->assertRedirect(route('bantuan.progres.index', $rumah));

        $this->assertDatabaseMissing('progres_renovasi', ['id' => $progres->id]);
        $this->assertDatabaseHas('audit_log', ['aksi' => 'PROGRES_DELETE']);
    }

    public function test_validasi_persentase_di_luar_rentang(): void
    {
        $rumah = $this->buatRumah('Dalam Renovasi');
        $this->buatBantuan($rumah);

        $this->actingAs($this->buatUser('verifikator'))
            ->post("/bantuan/{$rumah->id}/progres", $this->dataProgres(120))
            ->assertSessionHasErrors('persentase');
    }

    public function test_admin_dan_pimpinan_hanya_bisa_melihat(): void
    {
        $rumah   = $this->buatRumah('Dalam Renovasi');
        $bantuan = $this->buatBantuan($rumah);
        $this->buatProgres($bantuan, 25, '2026-10-10');

        foreach (['admin', 'pimpinan'] as $role) {
            $user = $this->buatUser($role);
            $this->actingAs($user)->get("/bantuan/{$rumah->id}/progres")->assertOk()->assertSee('Pondasi');
            $this->actingAs($user)->post("/bantuan/{$rumah->id}/progres", $this->dataProgres())->assertForbidden();
        }
    }

    public function test_rumah_tanpa_bantuan_tidak_ditemukan(): void
    {
        $rumah = $this->buatRumah('Penerima Bantuan');

        $this->actingAs($this->buatUser('verifikator'))
            ->get("/bantuan/{$rumah->id}/progres")
            ->assertNotFound();
    }
}
