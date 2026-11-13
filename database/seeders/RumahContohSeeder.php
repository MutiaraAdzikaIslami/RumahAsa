<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RumahWarga;
use App\Models\User;
use App\Models\Wilayah;
use App\Modules\Core\Services\NikEncryptor;
use App\Modules\Workflow\Services\StatusWorkflow;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RumahContohSeeder extends Seeder
{
    public function run(): void
    {
        $nikEncryptor = app(NikEncryptor::class);
        $workflow = app(StatusWorkflow::class);

        $rt = Wilayah::where('kode', 'RT-001')->first() ?? Wilayah::where('tingkat', 'rt')->first();
        if (! $rt) {
            $this->command->error('Wilayah RT belum ada. Jalankan WilayahContohSeeder terlebih dahulu.');

            return;
        }

        // Surveyor
        $roleSurveyor = Role::where('kode', 'surveyor')->first();
        $surveyor = User::firstOrCreate(
            ['username' => 'surveyor01'],
            [
                'role_id' => $roleSurveyor->id,
                'wilayah_id' => $rt->id,
                'nama' => 'Budi Surveyor',
                'email' => 'surveyor@rumahasa.local',
                'password' => Hash::make('RumahAsa2025'),
                'must_change_password' => false,
                'is_aktif' => true,
            ]
        );

        // Verifikator
        $roleVerifikator = Role::where('kode', 'verifikator')->first();
        $verifikator = User::firstOrCreate(
            ['username' => 'verifikator01'],
            [
                'role_id' => $roleVerifikator->id,
                'wilayah_id' => null,
                'nama' => 'Dewi Verifikator',
                'email' => 'verifikator@rumahasa.local',
                'password' => Hash::make('RumahAsa2025'),
                'must_change_password' => false,
                'is_aktif' => true,
            ]
        );

        // Rumah 1: Sudah ada riwayat (Draf -> Diajukan -> Perlu Perbaikan)
        $nik1 = '3201011508800001';
        $noKk1 = '3201010101100001';
        $rumah1 = RumahWarga::updateOrCreate(
            ['kode_registrasi' => 'RUMAH-2026-0001'],
            [
                'nama_kepala_keluarga' => 'Slamet Riyadi',
                'nik_enc' => $nikEncryptor->encryptNik($nik1),
                'nik_hash' => $nikEncryptor->hashNik($nik1),
                'no_kk_enc' => $nikEncryptor->encryptNoKk($noKk1),
                'no_kk_hash' => $nikEncryptor->hashNoKk($noKk1),
                'jumlah_anggota' => 4,
                'luas_rumah' => 36.50,
                'pekerjaan' => 'Buruh Harian Lepas',
                'penghasilan' => 1500000,
                'status_kepemilikan' => 'milik_sendiri',
                'alamat' => 'Jl. Melati No. 12, RT 01 RW 01',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'wilayah_id' => $rt->id,
                'surveyor_id' => $surveyor->id,
                'status' => 'Draf',
            ]
        );

        // Bersihkan riwayat lama jika re-seed
        $rumah1->riwayatStatus()->delete();
        $workflow->ajukan($rumah1, $surveyor);
        $workflow->mintaPerbaikan($rumah1, 'Foto atap dan sanitasi kurang jelas, mohon diperbarui.', $verifikator);

        // Rumah 2: Penerima Bantuan -> Dalam Renovasi
        $nik2 = '3201012010850002';
        $noKk2 = '3201010101100002';
        $rumah2 = RumahWarga::updateOrCreate(
            ['kode_registrasi' => 'RUMAH-2026-0002'],
            [
                'nama_kepala_keluarga' => 'Siti Rohmah',
                'nik_enc' => $nikEncryptor->encryptNik($nik2),
                'nik_hash' => $nikEncryptor->hashNik($nik2),
                'no_kk_enc' => $nikEncryptor->encryptNoKk($noKk2),
                'no_kk_hash' => $nikEncryptor->hashNoKk($noKk2),
                'jumlah_anggota' => 5,
                'luas_rumah' => 42.00,
                'pekerjaan' => 'Pedagang Keliling',
                'penghasilan' => 1200000,
                'status_kepemilikan' => 'milik_sendiri',
                'alamat' => 'Gg. Kamboja RT 01 RW 01 No. 05',
                'latitude' => -6.2091,
                'longitude' => 106.8462,
                'wilayah_id' => $rt->id,
                'surveyor_id' => $surveyor->id,
                'status' => 'Draf',
            ]
        );

        $rumah2->riwayatStatus()->delete();
        $workflow->ajukan($rumah2, $surveyor);
        $workflow->tetapkanKategori($rumah2, 'tidak_layak_huni', 'Kondisi rumah rusak berat, memenuhi kriteria penerima bantuan.', $verifikator);
        $workflow->mulaiRenovasi($rumah2, 'Material tahap 1 sudah sampai di lokasi, renovasi dimulai.', $verifikator);

        // Rumah 3: Status Draf baru (tanpa riwayat status)
        $nik3 = '3201010503780003';
        $noKk3 = '3201010101100003';
        $rumah3 = RumahWarga::updateOrCreate(
            ['kode_registrasi' => 'RUMAH-2026-0003'],
            [
                'nama_kepala_keluarga' => 'Suparman',
                'nik_enc' => $nikEncryptor->encryptNik($nik3),
                'nik_hash' => $nikEncryptor->hashNik($nik3),
                'no_kk_enc' => $nikEncryptor->encryptNoKk($noKk3),
                'no_kk_hash' => $nikEncryptor->hashNoKk($noKk3),
                'jumlah_anggota' => 3,
                'luas_rumah' => 30.00,
                'pekerjaan' => 'Petani',
                'penghasilan' => 1000000,
                'status_kepemilikan' => 'menumpang',
                'alamat' => 'Jl. Mawar RT 01 RW 01 No. 08',
                'latitude' => -6.2085,
                'longitude' => 106.8450,
                'wilayah_id' => $rt->id,
                'surveyor_id' => $surveyor->id,
                'status' => 'Draf',
            ]
        );
        $rumah3->riwayatStatus()->delete();
    }
}
