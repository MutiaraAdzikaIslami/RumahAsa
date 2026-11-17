<?php

namespace Database\Seeders;

use App\Models\PenerimaBantuan;
use App\Models\Role;
use App\Models\RumahWarga;
use App\Models\User;
use App\Models\Wilayah;
use App\Modules\Core\Services\NikEncryptor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data contoh untuk demo modul bantuan dan renovasi (khusus lokal).
 * Jalankan: php artisan db:seed --class=BantuanContohSeeder
 * Akun contoh: verifikator01, pimpinan01, surveyor01 (password RumahAsa2025).
 */
class BantuanContohSeeder extends Seeder
{
    public function run(): void
    {
        $nik = app(NikEncryptor::class);

        $rt = Wilayah::firstOrCreate(
            ['kode' => 'RT-DEMO'],
            ['nama' => 'RT Demo', 'tingkat' => 'rt', 'parent_id' => null, 'is_aktif' => true]
        );
        if (!$rt->path) {
            $rt->update(['path' => '/' . $rt->id]);
        }

        $surveyor = $this->akun('surveyor01', 'surveyor', 'Surveyor Contoh', $rt->id);
        $this->akun('verifikator01', 'verifikator', 'Verifikator Contoh', null);
        $this->akun('pimpinan01', 'pimpinan', 'Pimpinan Contoh', null);

        $contoh = [
            ['RUMAH-DEMO-0001', 'Budi Santoso',  '3578010101900001', RumahWarga::STATUS_PENERIMA_BANTUAN, []],
            ['RUMAH-DEMO-0002', 'Siti Aminah',   '3578010101900002', RumahWarga::STATUS_PENERIMA_BANTUAN, []],
            ['RUMAH-DEMO-0003', 'Agus Setiawan', '3578010101900003', RumahWarga::STATUS_DALAM_RENOVASI,   [30, 60]],
            ['RUMAH-DEMO-0004', 'Dewi Lestari',  '3578010101900004', RumahWarga::STATUS_SELESAI,          [50, 100]],
        ];

        foreach ($contoh as $i => [$kode, $nama, $nomor, $status, $progres]) {
            $rumah = RumahWarga::updateOrCreate(['kode_registrasi' => $kode], [
                'nama_kepala_keluarga' => $nama,
                'nik_enc'              => $nik->encryptNik($nomor),
                'nik_hash'             => $nik->hashNik($nomor),
                'no_kk_enc'            => $nik->encryptNoKk($nomor),
                'no_kk_hash'           => $nik->hashNoKk($nomor),
                'jumlah_anggota'       => 4,
                'alamat'               => 'Jl. Contoh No. ' . ($i + 1),
                'wilayah_id'           => $rt->id,
                'surveyor_id'          => $surveyor->id,
                'status'               => $status,
            ]);

            if ($status === RumahWarga::STATUS_PENERIMA_BANTUAN) {
                continue;
            }

            $bantuan = PenerimaBantuan::updateOrCreate(['rumah_id' => $rumah->id], [
                'status_bantuan'  => 'Disalurkan',
                'tanggal_bantuan' => '2026-10-01',
                'jenis_bantuan'   => 'Bantuan Stimulan Perumahan Swadaya',
                'sumber_program'  => ($i % 2 === 0) ? 'APBD' : 'APBN',
                'nominal'         => 20000000,
                'nomor_sk'        => 'SK/RTLH/2026/' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
            ]);

            $bantuan->progres()->delete();
            foreach ($progres as $j => $persen) {
                $bantuan->progres()->create([
                    'tahap_pekerjaan' => 'Tahap pekerjaan ' . ($j + 1),
                    'persentase'      => $persen,
                    'tanggal_progres' => '2026-10-' . str_pad((string) (10 + $j * 10), 2, '0', STR_PAD_LEFT),
                ]);
            }
        }
    }

    private function akun(string $username, string $role, string $nama, ?int $wilayahId): User
    {
        return User::updateOrCreate(['username' => $username], [
            'role_id'              => Role::where('kode', $role)->value('id'),
            'wilayah_id'           => $wilayahId,
            'nama'                 => $nama,
            'email'                => $username . '@rumahasa.local',
            'password'             => Hash::make('RumahAsa2025'),
            'must_change_password' => false,
            'is_aktif'             => true,
        ]);
    }
}
