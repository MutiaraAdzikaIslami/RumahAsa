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

        $this->assertSoftDeleted('progres_renovasi', ['id' => $progres->id]);
        $this->assertDatabaseHas('audit_log', ['aksi' => 'PROGRES_DELETE']);
    }

    public function test_surveyor_menambah_mengubah_dan_menghapus_progres(): void
    {
        $rumah   = $this->buatRumah('Dalam Renovasi');
        $bantuan = $this->buatBantuan($rumah);

        $surveyor = $this->buatUser('surveyor');

        // Surveyor menambah progres (FR-22)
        $this->actingAs($surveyor)
            ->post("/bantuan/{$rumah->id}/progres", $this->dataProgres(35, '2026-10-12'))
            ->assertRedirect(route('bantuan.progres.index', $rumah));

        $this->assertDatabaseHas('progres_renovasi', ['persentase' => 35]);
        $progres = \App\Models\ProgresRenovasi::where('persentase', 35)->first();

        // Surveyor mengubah progres
        $this->actingAs($surveyor)
            ->put("/bantuan/{$rumah->id}/progres/{$progres->id}", $this->dataProgres(45, '2026-10-12'))
            ->assertRedirect(route('bantuan.progres.index', $rumah));

        $this->assertSame(45, $progres->fresh()->persentase);

        // Surveyor menghapus progres (Soft delete)
        $this->actingAs($surveyor)
            ->delete("/bantuan/{$rumah->id}/progres/{$progres->id}")
            ->assertRedirect(route('bantuan.progres.index', $rumah));

        $this->assertSoftDeleted('progres_renovasi', ['id' => $progres->id]);
    }

    public function test_unggah_foto_progres(): void
    {
        \Illuminate\Support\Facades\Storage::fake(config('rumahasa.foto_disk', 'private'));

        $rumah   = $this->buatRumah('Dalam Renovasi');
        $bantuan = $this->buatBantuan($rumah);

        $foto = \Illuminate\Http\UploadedFile::fake()->image('bukti_pondasi.jpg');

        $data = $this->dataProgres(30, '2026-10-11') + [
            'foto' => [$foto],
        ];

        $this->actingAs($this->buatUser('surveyor'))
            ->post("/bantuan/{$rumah->id}/progres", $data)
            ->assertRedirect(route('bantuan.progres.index', $rumah));

        $progres = \App\Models\ProgresRenovasi::where('persentase', 30)->first();
        $this->assertNotNull($progres);
        $this->assertDatabaseHas('progres_foto', [
            'progres_renovasi_id' => $progres->id,
        ]);
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
            $this->actingAs($user)->get("/bantuan/{$rumah->id}/progres")->assertOk()->assertSee('Tahap uji');
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
