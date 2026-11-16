<?php

namespace Tests\Feature;

use App\Models\RumahWarga;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuatDataBantuan;
use Tests\TestCase;

class AturanProgresTest extends TestCase
{
    use RefreshDatabase, BuatDataBantuan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanRole();
    }

    private function kirim(RumahWarga $rumah, int $persen, string $tanggal)
    {
        return $this->actingAs($this->buatUser('verifikator'))
            ->post("/bantuan/{$rumah->id}/progres", [
                'tahap_pekerjaan' => 'Tahap uji',
                'persentase'      => $persen,
                'tanggal_progres' => $tanggal,
            ]);
    }

    public function test_progres_tidak_boleh_turun(): void
    {
        $rumah   = $this->buatRumah('Dalam Renovasi');
        $bantuan = $this->buatBantuan($rumah);
        $this->buatProgres($bantuan, 50, '2026-10-10');

        $this->kirim($rumah, 40, '2026-10-15')->assertSessionHasErrors('persentase');

        $this->assertDatabaseCount('progres_renovasi', 1);
    }

    public function test_progres_naik_atau_sama_diterima(): void
    {
        $rumah   = $this->buatRumah('Dalam Renovasi');
        $bantuan = $this->buatBantuan($rumah);
        $this->buatProgres($bantuan, 50, '2026-10-10');

        $this->kirim($rumah, 50, '2026-10-15')->assertSessionHasNoErrors();
        $this->kirim($rumah, 70, '2026-10-20')->assertSessionHasNoErrors();

        $this->assertDatabaseCount('progres_renovasi', 3);
    }

    public function test_progres_tidak_boleh_melebihi_tanggal_berikutnya(): void
    {
        $rumah   = $this->buatRumah('Dalam Renovasi');
        $bantuan = $this->buatBantuan($rumah);
        $this->buatProgres($bantuan, 80, '2026-10-20');

        $this->kirim($rumah, 90, '2026-10-15')->assertSessionHasErrors('persentase');

        $this->assertDatabaseCount('progres_renovasi', 1);
    }

    public function test_tanggal_sebelum_penyaluran_ditolak(): void
    {
        $rumah = $this->buatRumah('Dalam Renovasi');
        $this->buatBantuan($rumah, '2026-10-01');

        $this->kirim($rumah, 20, '2026-09-30')->assertSessionHasErrors('persentase');

        $this->assertDatabaseCount('progres_renovasi', 0);
    }

    public function test_pengubahan_tidak_dibandingkan_dengan_dirinya_sendiri(): void
    {
        $rumah   = $this->buatRumah('Dalam Renovasi');
        $bantuan = $this->buatBantuan($rumah);
        $progres = $this->buatProgres($bantuan, 50, '2026-10-10');

        $this->actingAs($this->buatUser('verifikator'))
            ->put("/bantuan/{$rumah->id}/progres/{$progres->id}", [
                'tahap_pekerjaan' => 'Tahap uji',
                'persentase'      => 60,
                'tanggal_progres' => '2026-10-10',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(60, $progres->fresh()->persentase);
    }

    public function test_progres_terkunci_setelah_selesai(): void
    {
        $rumah   = $this->buatRumah('Selesai');
        $bantuan = $this->buatBantuan($rumah);
        $progres = $this->buatProgres($bantuan, 100, '2026-10-30');

        $this->kirim($rumah, 100, '2026-11-01')->assertSessionHasErrors('persentase');

        $this->actingAs($this->buatUser('verifikator'))
            ->delete("/bantuan/{$rumah->id}/progres/{$progres->id}")
            ->assertSessionHasErrors('progres');

        $this->assertDatabaseCount('progres_renovasi', 1);
    }
}
