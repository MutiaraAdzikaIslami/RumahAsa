<?php

namespace Tests\Concerns;

use App\Models\PenerimaBantuan;
use App\Models\ProgresRenovasi;
use App\Models\Role;
use App\Models\RumahWarga;
use App\Models\User;
use App\Models\Wilayah;
use App\Modules\Core\Services\NikEncryptor;

/**
 * Pembantu tes untuk modul bantuan dan renovasi.
 */
trait BuatDataBantuan
{
    private int $urutanData = 0;

    protected function siapkanRole(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    protected function buatUser(string $role): User
    {
        return User::factory()->create([
            'role_id'              => Role::where('kode', $role)->first()->id,
            'must_change_password' => false,
        ]);
    }

    protected function buatRumah(string $status, string $nama = 'Warga Uji'): RumahWarga
    {
        $n   = ++$this->urutanData;
        $enc = app(NikEncryptor::class);
        $nik = '35780101019' . str_pad((string) $n, 5, '0', STR_PAD_LEFT);
        $kk  = '3578010101' . str_pad((string) $n, 6, '0', STR_PAD_LEFT);

        $kec = Wilayah::create(['kode' => "KEC-U{$n}", 'nama' => "Kec Uji {$n}", 'tingkat' => 'kecamatan', 'parent_id' => null]);
        $kec->update(['path' => '/' . $kec->id]);
        $rt = Wilayah::create(['kode' => "RT-U{$n}", 'nama' => "RT Uji {$n}", 'tingkat' => 'rt', 'parent_id' => $kec->id]);
        $rt->update(['path' => $kec->path . '/' . $rt->id]);

        $surveyor = User::factory()->create([
            'role_id'              => Role::where('kode', 'surveyor')->first()->id,
            'wilayah_id'           => $rt->id,
            'must_change_password' => false,
        ]);

        return RumahWarga::create([
            'kode_registrasi'      => "UJI-{$n}",
            'nama_kepala_keluarga' => $nama,
            'nik_enc'              => $enc->encryptNik($nik),
            'nik_hash'             => $enc->hashNik($nik),
            'no_kk_enc'            => $enc->encryptNoKk($kk),
            'no_kk_hash'           => $enc->hashNoKk($kk),
            'jumlah_anggota'       => 4,
            'alamat'               => 'Jl. Uji No. ' . $n,
            'wilayah_id'           => $rt->id,
            'surveyor_id'          => $surveyor->id,
            'status'               => $status,
        ]);
    }

    protected function buatBantuan(RumahWarga $rumah, string $tanggal = '2026-10-01'): PenerimaBantuan
    {
        return PenerimaBantuan::create([
            'rumah_id'        => $rumah->id,
            'status_bantuan'  => 'Disalurkan',
            'tanggal_bantuan' => $tanggal,
            'jenis_bantuan'   => 'Bantuan Stimulan',
            'sumber_program'  => 'APBD',
            'nominal'         => 20000000,
            'nomor_sk'        => 'SK/UJI/' . $rumah->id,
        ]);
    }

    protected function buatProgres(PenerimaBantuan $bantuan, int $persen, string $tanggal): ProgresRenovasi
    {
        return ProgresRenovasi::create([
            'penerima_bantuan_id' => $bantuan->id,
            'tahap_pekerjaan'     => 'Tahap uji',
            'persentase'          => $persen,
            'tanggal_progres'     => $tanggal,
        ]);
    }
}
