<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuatDataBantuan;
use Tests\TestCase;

class PemantauanProgresTest extends TestCase
{
    use RefreshDatabase, BuatDataBantuan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanRole();
    }

    public function test_pimpinan_bisa_memantau_progres(): void
    {
        $rumah   = $this->buatRumah('Dalam Renovasi', 'Budi Pantau');
        $bantuan = $this->buatBantuan($rumah);
        $this->buatProgres($bantuan, 40, '2026-10-10');

        $this->actingAs($this->buatUser('pimpinan'))
            ->get("/bantuan/{$rumah->id}")
            ->assertOk()
            ->assertSee('Budi Pantau')
            ->assertSee('40%');
    }

    public function test_rumah_yang_belum_penerima_bantuan_tidak_ditemukan(): void
    {
        $rumah = $this->buatRumah('Layak Huni');

        $this->actingAs($this->buatUser('verifikator'))
            ->get("/bantuan/{$rumah->id}")
            ->assertNotFound();
    }

    public function test_verifikator_menetapkan_selesai_saat_progres_100(): void
    {
        $rumah   = $this->buatRumah('Dalam Renovasi');
        $bantuan = $this->buatBantuan($rumah);
        $this->buatProgres($bantuan, 100, '2026-10-30');

        $this->actingAs($this->buatUser('verifikator'))
            ->post("/bantuan/{$rumah->id}/selesai")
            ->assertRedirect(route('bantuan.show', $rumah));

        $this->assertSame('Selesai', $rumah->fresh()->status);
        $this->assertDatabaseHas('riwayat_status', ['rumah_id' => $rumah->id, 'status_ke' => 'Selesai']);
    }

    public function test_selesai_ditolak_jika_progres_belum_100(): void
    {
        $rumah   = $this->buatRumah('Dalam Renovasi');
        $bantuan = $this->buatBantuan($rumah);
        $this->buatProgres($bantuan, 60, '2026-10-20');

        $this->actingAs($this->buatUser('verifikator'))
            ->post("/bantuan/{$rumah->id}/selesai")
            ->assertSessionHas('warning');

        $this->assertSame('Dalam Renovasi', $rumah->fresh()->status);
    }

    public function test_pimpinan_tidak_bisa_menetapkan_selesai(): void
    {
        $rumah   = $this->buatRumah('Dalam Renovasi');
        $bantuan = $this->buatBantuan($rumah);
        $this->buatProgres($bantuan, 100, '2026-10-30');

        $this->actingAs($this->buatUser('pimpinan'))
            ->post("/bantuan/{$rumah->id}/selesai")
            ->assertForbidden();

        $this->assertSame('Dalam Renovasi', $rumah->fresh()->status);
    }

    public function test_verifikator_tidak_melihat_tombol_edit_progres_di_review_penyelesaian(): void
    {
        $rumah   = $this->buatRumah('Dalam Renovasi');
        $bantuan = $this->buatBantuan($rumah);
        $this->buatProgres($bantuan, 100, '2026-10-30');

        $this->actingAs($this->buatUser('verifikator'))
            ->get("/bantuan/{$rumah->id}")
            ->assertOk()
            ->assertSee('REVIEW PENYELESAIAN RENOVASI')
            ->assertSee('Tetapkan Selesai')
            ->assertDontSee('Kelola Progres');
    }

    public function test_halaman_detail_penerima_bantuan_dan_review_penyelesaian_terpisah(): void
    {
        $rumah = $this->buatRumah('Penerima Bantuan', 'Warga Belum Dicatat');
        $verifikator = $this->buatUser('verifikator');

        // 1. Halaman Detail Penerima Bantuan (/bantuan/{id}/detail-penerima) menampilkan DETAIL PENERIMA BANTUAN & modal Catat Bantuan
        $this->actingAs($verifikator)
            ->get("/bantuan/{$rumah->id}/detail-penerima")
            ->assertOk()
            ->assertSee('DETAIL PENERIMA BANTUAN')
            ->assertSee('Catat Bantuan')
            ->assertDontSee('REVIEW PENYELESAIAN RENOVASI');

        // 2. Halaman Review Penyelesaian Renovasi (/bantuan/{id}) SELALU menampilkan REVIEW PENYELESAIAN RENOVASI
        $this->actingAs($verifikator)
            ->get("/bantuan/{$rumah->id}")
            ->assertOk()
            ->assertSee('REVIEW PENYELESAIAN RENOVASI')
            ->assertDontSee('DETAIL PENERIMA BANTUAN');
    }
}
