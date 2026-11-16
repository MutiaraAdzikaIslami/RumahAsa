<?php

namespace Tests\Feature;

use App\Models\KomponenPenilaian;
use App\Models\OpsiKondisi;
use App\Models\Role;
use App\Models\RumahWarga;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveiManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $surveyor;
    private RumahWarga $rumah;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\KomponenPenilaianSeeder::class);
        $this->seed(\Database\Seeders\OpsiKondisiSeeder::class);

        $kec = Wilayah::create(['kode' => 'KEC-T', 'nama' => 'Kec Test', 'tingkat' => 'kecamatan', 'parent_id' => null]);
        $kec->update(['path' => '/' . $kec->id]);
        $rt = Wilayah::create(['kode' => 'RT-T', 'nama' => 'RT 01', 'tingkat' => 'rt', 'parent_id' => $kec->id]);
        $rt->update(['path' => $kec->path . '/' . $rt->id]);

        $role = Role::where('kode', 'surveyor')->first();
        $this->surveyor = User::factory()->create([
            'role_id'              => $role->id,
            'wilayah_id'           => $rt->id,
            'must_change_password' => false,
        ]);

        $this->rumah = RumahWarga::factory()->create([
            'surveyor_id' => $this->surveyor->id,
            'wilayah_id'  => $rt->id,
            'status'      => RumahWarga::STATUS_MENUNGGU_SURVEI,
        ]);
    }

    public function test_buka_form_survei_otomatis_bikin_7_komponen_kosong(): void
    {
        $response = $this->actingAs($this->surveyor)->get("/survei/{$this->rumah->id}/create");

        $response->assertOk();
        $response->assertViewHas('komponenList', fn ($list) => $list->count() === 7);
    }

    public function test_surveyor_bisa_simpan_draf_survei(): void
    {
        $komponenList = KomponenPenilaian::all();
        $opsiPertama = OpsiKondisi::orderBy('urutan')->first();

        $kondisi = [];
        foreach ($komponenList as $k) {
            $kondisi[$k->id] = $opsiPertama->id;
        }

        $response = $this->actingAs($this->surveyor)->post("/survei/{$this->rumah->id}", [
            'tanggal_survei' => now()->toDateString(),
            'kondisi'        => $kondisi,
        ]);

        $response->assertRedirect(route('survei.index'));
        $this->assertDatabaseHas('survei_kelayakan', ['rumah_id' => $this->rumah->id]);
        $this->assertDatabaseCount('survei_komponen', 7);
        $this->assertSame('Diajukan', $this->rumah->fresh()->status);
    }

    public function test_submit_survei_tanpa_kelengkapan_ditolak(): void
    {
        $response = $this->actingAs($this->surveyor)->post("/survei/{$this->rumah->id}", [
            'tanggal_survei' => now()->toDateString(),
            'kondisi'        => [],
        ]);

        $response->assertSessionHasErrors('kondisi');
        $this->assertSame('Menunggu Survei', $this->rumah->fresh()->status);
    }
}