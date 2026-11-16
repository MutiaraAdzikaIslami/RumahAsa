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
}
