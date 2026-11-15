<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\RumahWarga;
use App\Models\User;
use App\Models\Wilayah;
use App\Modules\Core\Exceptions\InvalidStatusTransitionException;
use App\Modules\Workflow\Services\StatusWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StatusWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private StatusWorkflow $workflow;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->workflow = app(StatusWorkflow::class);
    }

    private function makeRumah(string $status = 'Draf'): RumahWarga
    {
        $kec = Wilayah::create(['kode' => 'KEC-T', 'nama' => 'Kec Test', 'tingkat' => 'kecamatan', 'parent_id' => null]);
        $kec->update(['path' => '/' . $kec->id]);
        $rt  = Wilayah::create(['kode' => 'RT-T', 'nama' => 'RT 01', 'tingkat' => 'rt', 'parent_id' => $kec->id]);
        $rt->update(['path' => $kec->path . '/' . $rt->id]);

        $role = Role::where('kode', 'surveyor')->first();
        $surveyor = User::factory()->create(['role_id' => $role->id, 'wilayah_id' => $rt->id]);

        return RumahWarga::create([
            'kode_registrasi'      => 'TEST-' . uniqid(),
            'nama_kepala_keluarga' => 'Test KK',
            'nik_enc'              => encrypt('1234567890123456'),
            'nik_hash'             => hash_hmac('sha256', '1234567890123456', 'test'),
            'no_kk_enc'            => encrypt('1234567890123456'),
            'no_kk_hash'           => hash_hmac('sha256', '1234567890123456', 'test'),
            'jumlah_anggota'       => 4,
            'luas_rumah'           => 36.50,
            'status_kepemilikan'   => 'milik_sendiri',
            'alamat'               => 'Jl. Test',
            'wilayah_id'           => $rt->id,
            'surveyor_id'          => $surveyor->id,
            'status'               => $status,
        ]);
    }

    private function makeUser(string $roleKode): User
    {
        $role = Role::where('kode', $roleKode)->first();
        return User::factory()->create(['role_id' => $role->id]);
    }

    public function test_draf_bisa_diajukan(): void
    {
        $rumah = $this->makeRumah('Draf');
        $surveyor = $this->makeUser('surveyor');

        $this->workflow->ajukan($rumah, $surveyor);

        $this->assertSame('Diajukan', $rumah->fresh()->status);
    }

    public function test_diajukan_bisa_ditetapkan_layak_huni(): void
    {
        $rumah = $this->makeRumah('Diajukan');
        $verifikator = $this->makeUser('verifikator');

        $this->workflow->tetapkanKategori($rumah, 'layak_huni', 'Rumah layak huni', $verifikator);

        $this->assertSame('Layak Huni', $rumah->fresh()->status);
    }

    public function test_diajukan_bisa_ditetapkan_tidak_layak_huni(): void
    {
        $rumah = $this->makeRumah('Diajukan');
        $verifikator = $this->makeUser('verifikator');

        $this->workflow->tetapkanKategori($rumah, 'tidak_layak_huni', 'Rumah tidak layak huni', $verifikator);

        $this->assertSame('Penerima Bantuan', $rumah->fresh()->status);
    }

    public function test_penerima_bantuan_bisa_mulai_renovasi(): void
    {
        $rumah = $this->makeRumah('Penerima Bantuan');
        $verifikator = $this->makeUser('verifikator');

        $this->workflow->mulaiRenovasi($rumah, 'Bantuan disalurkan', $verifikator);

        $this->assertSame('Dalam Renovasi', $rumah->fresh()->status);
    }

    public function test_dalam_renovasi_bisa_selesai(): void
    {
        $rumah = $this->makeRumah('Dalam Renovasi');
        $verifikator = $this->makeUser('verifikator');

        $this->workflow->selesaikan($rumah, 'Renovasi 100%', $verifikator);

        $this->assertSame('Selesai', $rumah->fresh()->status);
    }

    public function test_layak_huni_tidak_bisa_transisi_apapun(): void
    {
        $this->expectException(InvalidStatusTransitionException::class);

        $rumah = $this->makeRumah('Layak Huni');
        $verifikator = $this->makeUser('verifikator');

        // Layak Huni adalah final state, tidak boleh berubah
        $this->workflow->mulaiRenovasi($rumah, 'Coba ubah', $verifikator);
    }

    public function test_selesai_tidak_bisa_transisi_apapun(): void
    {
        $this->expectException(InvalidStatusTransitionException::class);

        $rumah = $this->makeRumah('Selesai');
        $verifikator = $this->makeUser('verifikator');

        // Selesai adalah final state
        $this->workflow->ajukan($rumah, $verifikator);
    }

    public function test_setiap_transisi_mencatat_riwayat_status_dan_audit_log(): void
    {
        $rumah = $this->makeRumah('Draf');
        $surveyor = $this->makeUser('surveyor');

        // Gunakan DB transaction spy untuk pastikan semua query jalan
        DB::enableQueryLog();
        $this->workflow->ajukan($rumah, $surveyor);
        $queries = DB::getQueryLog();

        // Assert riwayat_status tercatat
        $this->assertDatabaseHas('riwayat_status', [
            'rumah_id'    => $rumah->id,
            'status_dari' => 'Draf',
            'status_ke'   => 'Diajukan',
            'user_id'     => $surveyor->id,
        ]);

        // Assert audit_log tercatat dengan aksi SURVEI_SUBMIT
        $this->assertDatabaseHas('audit_log', [
            'user_id'    => $surveyor->id,
            'aksi'       => AuditLog::AKSI['SURVEI_SUBMIT'],
            'entitas'    => 'rumah_warga',
            'entitas_id' => $rumah->id,
        ]);

        // Verify query count — harus ada: UPDATE rumah_warga, INSERT riwayat_status, INSERT audit_log
        $updateQueries = array_filter($queries, fn ($q) => str_starts_with($q['query'], 'update'));
        $insertQueries = array_filter($queries, fn ($q) => str_starts_with($q['query'], 'insert'));
        $this->assertGreaterThanOrEqual(3, count($updateQueries) + count($insertQueries));
    }

    public function test_can_transition_check_method(): void
    {
        $this->assertTrue($this->workflow->canTransition('Draf', 'Diajukan'));
        $this->assertTrue($this->workflow->canTransition('Diajukan', 'Penerima Bantuan'));
        $this->assertTrue($this->workflow->canTransition('Penerima Bantuan', 'Dalam Renovasi'));

        $this->assertFalse($this->workflow->canTransition('Draf', 'Selesai'));      // skip
        $this->assertFalse($this->workflow->canTransition('Layak Huni', 'Draf'));   // final
        $this->assertFalse($this->workflow->canTransition('Selesai', 'Draf'));      // final
    }
}