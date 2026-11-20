<?php

namespace Database\Seeders;

use App\Models\PenerimaBantuan;
use App\Models\RiwayatStatus;
use App\Models\Role;
use App\Models\RumahWarga;
use App\Models\SurveiKelayakan;
use App\Models\User;
use App\Models\Wilayah;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class HeatmapSampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Pastikan Role tersedia
        $roleSurveyor = Role::firstOrCreate(['kode' => 'surveyor'], ['nama' => 'Surveyor', 'deskripsi' => 'Petugas surveyor']);
        $roleVerifikator = Role::firstOrCreate(['kode' => 'verifikator'], ['nama' => 'Verifikator', 'deskripsi' => 'Petugas verifikator']);
        $rolePimpinan = Role::firstOrCreate(['kode' => 'pimpinan'], ['nama' => 'Pimpinan', 'deskripsi' => 'Pimpinan instansi']);

        // 2. Buat Master Wilayah Sukamaju & Sekitarnya
        $kecamatan = Wilayah::firstOrCreate(
            ['kode' => 'KEC-SUKAMAJU'],
            [
                'nama' => 'Kecamatan Sukamaju',
                'tingkat' => Wilayah::TINGKAT_KECAMATAN,
                'parent_id' => null,
                'is_aktif' => true,
            ]
        );
        $kecamatan->update(['path' => '/'.$kecamatan->id]);

        $kelurahans = [
            'Kel. Sukamaju' => 'KEL-SUKAMAJU',
            'Kel. Cibiru' => 'KEL-CIBIRU',
            'Kel. Mekarjaya' => 'KEL-MEKARJAYA',
            'Kel. Sukasari' => 'KEL-SUKASARI',
        ];

        $kelurahanModels = [];
        $rwModels = [];

        foreach ($kelurahans as $nama => $kode) {
            $kel = Wilayah::firstOrCreate(
                ['kode' => $kode],
                [
                    'nama' => $nama,
                    'tingkat' => Wilayah::TINGKAT_KELURAHAN,
                    'parent_id' => $kecamatan->id,
                    'is_aktif' => true,
                ]
            );
            $kel->update(['path' => $kecamatan->path.'/'.$kel->id]);
            $kelurahanModels[$nama] = $kel;

            // Buat RW 01 s/d RW 04 di bawah kelurahan
            for ($rwNum = 1; $rwNum <= 4; $rwNum++) {
                $rwKode = $kode.'-RW0'.$rwNum;
                $rwNama = 'RW 0'.$rwNum;
                $rw = Wilayah::firstOrCreate(
                    ['kode' => $rwKode],
                    [
                        'nama' => $rwNama,
                        'tingkat' => Wilayah::TINGKAT_RW,
                        'parent_id' => $kel->id,
                        'is_aktif' => true,
                    ]
                );
                $rw->update(['path' => $kel->path.'/'.$rw->id]);
                $rwModels[$nama][$rwNama] = $rw;

                // Buat RT 01 s/d RT 03 jika Kel. Sukamaju
                if ($nama === 'Kel. Sukamaju') {
                    for ($rtNum = 1; $rtNum <= 3; $rtNum++) {
                        $rtKode = $rwKode.'-RT0'.$rtNum;
                        $rtNama = 'RT 0'.$rtNum;
                        $rt = Wilayah::firstOrCreate(
                            ['kode' => $rtKode],
                            [
                                'nama' => $rtNama,
                                'tingkat' => Wilayah::TINGKAT_RT,
                                'parent_id' => $rw->id,
                                'is_aktif' => true,
                            ]
                        );
                        $rt->update(['path' => $rw->path.'/'.$rt->id]);
                    }
                }
            }
        }

        $kelSukamaju = $kelurahanModels['Kel. Sukamaju'];

        // 3. Buat / Update Pengguna
        // Verifikator (Dewi Lestari)
        $verifikator = User::updateOrCreate(
            ['username' => 'verifikator01'],
            [
                'role_id' => $roleVerifikator->id,
                'nama' => 'Dewi Lestari',
                'email' => 'verifikator@rumahasa.local',
                'password' => Hash::make('RumahAsa2025'),
                'must_change_password' => false,
                'is_aktif' => true,
            ]
        );

        // Surveyor (Rizky Aditya) - Ditugaskan ke Kel. Sukamaju
        $surveyor = User::updateOrCreate(
            ['username' => 'surveyor01'],
            [
                'role_id' => $roleSurveyor->id,
                'wilayah_id' => $kelSukamaju->id,
                'nama' => 'Rizky Aditya',
                'email' => 'surveyor@rumahasa.local',
                'password' => Hash::make('RumahAsa2025'),
                'must_change_password' => false,
                'is_aktif' => true,
            ]
        );

        // Pimpinan (Drs. Hendra Wijaya)
        $pimpinan = User::updateOrCreate(
            ['username' => 'pimpinan01'],
            [
                'role_id' => $rolePimpinan->id,
                'nama' => 'Drs. Hendra Wijaya',
                'email' => 'pimpinan@rumahasa.local',
                'password' => Hash::make('RumahAsa2025'),
                'must_change_password' => false,
                'is_aktif' => true,
            ]
        );

        // 4. Buat Rumah Warga dengan Koordinat Realistis
        // Center Sukamaju: -6.9175, 107.6191
        $baseLat = -6.9175;
        $baseLng = 107.6191;

        // Distribusi rumah:
        // Total 147 rumah berkoordinat:
        // - 37 Sedang Disurvei (Diajukan, Perlu Perbaikan, Draf)
        // - 24 Sedang Renovasi (Dalam Renovasi)
        // - 86 Selesai (Selesai)
        // - 9 Menunggu Disurvei
        //
        // Wilayah Sukamaju mendapat 44 rumah (RW 01: 14, RW 02: 12, RW 03: 10, RW 04: 8)
        // Kel. Cibiru: 37, Kel. Mekarjaya: 30, Kel. Sukasari: 22, Lainnya: 14
        $specs = [
            ['nama' => 'Kel. Sukamaju', 'target' => 44, 'latOffset' => 0.000, 'lngOffset' => 0.000],
            ['nama' => 'Kel. Cibiru', 'target' => 37, 'latOffset' => 0.012, 'lngOffset' => 0.015],
            ['nama' => 'Kel. Mekarjaya', 'target' => 30, 'latOffset' => -0.015, 'lngOffset' => 0.008],
            ['nama' => 'Kel. Sukasari', 'target' => 22, 'latOffset' => 0.008, 'lngOffset' => -0.016],
            ['nama' => 'Lainnya', 'target' => 14, 'latOffset' => -0.018, 'lngOffset' => -0.012],
        ];

        // Hitung rumah yang sudah ada
        $existingCount = RumahWarga::whereNotNull('latitude')->whereNotNull('longitude')->count();
        if ($existingCount >= 100) {
            $this->command?->info('Data rumah berkoordinat sudah cukup ('.$existingCount.' rumah).');

            return;
        }

        $namaWargaList = [
            'Siti Aminah', 'Bambang Sutrisno', 'Nurhayati', 'Asep Kurnia', 'Dedi Irawan',
            'Maman Suryaman', 'Euis Kartini', 'Yayah Rokayah', 'Hendra Gunawan', 'Rina Marlina',
            'Agus Santoso', 'Budi Hartono', 'Sri Rahayu', 'Dewi Susanti', 'Ujang Koswara',
            'Cecep Supriatna', 'Endang Suherman', 'Neneng Hasanah', 'Dadang Hidayat', 'Imas Masitoh',
        ];

        $globalIndex = 1;
        $totalSedangDisurvei = 0;
        $totalSedangRenovasi = 0;
        $totalSelesai = 0;
        $totalMenunggu = 0;

        foreach ($specs as $spec) {
            $kelNama = $spec['nama'];
            $target = $spec['target'];

            for ($i = 1; $i <= $target; $i++) {
                // Tentukan wilayah
                if ($kelNama === 'Lainnya') {
                    $wilayahObj = $kecamatan;
                } else {
                    $rwList = array_values($rwModels[$kelNama]);
                    $rwIndex = ($i % count($rwList));
                    $wilayahObj = $rwList[$rwIndex];
                }

                // Tentukan status agar proporsinya pas (37 Sedang Disurvei, 24 Renovasi, 86 Selesai, 9 Menunggu)
                $rnd = $i % 10;
                if ($rnd === 0) {
                    $status = 'Menunggu Disurvei';
                    $totalMenunggu++;
                } elseif ($rnd === 1 || $rnd === 2 || $rnd === 3) {
                    $status = ($rnd === 1) ? 'Diajukan' : (($rnd === 2) ? 'Perlu Perbaikan' : 'Draf');
                    $totalSedangDisurvei++;
                } elseif ($rnd === 4 || $rnd === 5) {
                    $status = 'Dalam Renovasi';
                    $totalSedangRenovasi++;
                } else {
                    $status = 'Selesai';
                    $totalSelesai++;
                }

                // Koordinat sedikit diacak di sekitar pusat wilayah
                $lat = $baseLat + $spec['latOffset'] + ((mt_rand(-100, 100) / 10000));
                $lng = $baseLng + $spec['lngOffset'] + ((mt_rand(-100, 100) / 10000));

                $namaWarga = $namaWargaList[($globalIndex - 1) % count($namaWargaList)].' '.$globalIndex;
                $nik = '3273'.str_pad($globalIndex, 12, '0', STR_PAD_LEFT);

                // Buat rumah
                $rumah = RumahWarga::create([
                    'kode_registrasi' => 'RW-'.date('Y').'-'.str_pad($globalIndex, 4, '0', STR_PAD_LEFT),
                    'nama_kepala_keluarga' => $namaWarga,
                    'nik_enc' => Crypt::encryptString($nik),
                    'nik_hash' => hash_hmac('sha256', $nik, config('rumahasa.nik_hmac_key', 'test')),
                    'no_kk_enc' => Crypt::encryptString('3273'.str_pad($globalIndex + 1000, 12, '0', STR_PAD_LEFT)),
                    'no_kk_hash' => hash_hmac('sha256', '3273'.str_pad($globalIndex + 1000, 12, '0', STR_PAD_LEFT), config('rumahasa.nik_hmac_key', 'test')),
                    'jumlah_anggota' => rand(2, 6),
                    'luas_rumah' => rand(30, 90),
                    'pekerjaan' => 'Buruh Harian Lepas',
                    'penghasilan' => rand(1500000, 3000000),
                    'status_kepemilikan' => 'milik_sendiri',
                    'alamat' => 'Jl. Mawar No. '.$globalIndex.', '.$wilayahObj->nama,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'wilayah_id' => $wilayahObj->id,
                    'surveyor_id' => $surveyor->id,
                    'status' => $status,
                ]);

                // Buat survei jika draf/diajukan/perlu perbaikan/renovasi/selesai
                if ($status !== 'Menunggu Disurvei') {
                    SurveiKelayakan::create([
                        'rumah_id' => $rumah->id,
                        'surveyor_id' => $surveyor->id,
                        'tanggal_survei' => Carbon::now()->subDays(rand(5, 60)),
                        'dikirim_at' => Carbon::now()->subDays(rand(1, 5)),
                    ]);
                }

                // Tentukan tanggal status di triwulan (Triwulan 1, 2, 3, 4 tahun 2026)
                $month = ($globalIndex % 12) + 1; // 1 s/d 12
                $statusDate = Carbon::create(2026, $month, rand(1, 28), 10, 0, 0);

                // Buat riwayat status
                RiwayatStatus::create([
                    'rumah_id' => $rumah->id,
                    'user_id' => ($status === 'Diajukan') ? $surveyor->id : $verifikator->id,
                    'status_dari' => 'Draf',
                    'status_ke' => $status,
                    'catatan' => 'Perubahan status ke '.$status,
                    'created_at' => $statusDate,
                ]);

                // Jika status renovasi atau selesai, buat data penerima_bantuan
                if (in_array($status, ['Dalam Renovasi', 'Selesai'])) {
                    $sumberProgram = ($globalIndex % 3 === 0) ? 'APBD' : 'APBN';
                    $nominal = ($sumberProgram === 'APBN') ? 15000000 : 10000000;

                    PenerimaBantuan::create([
                        'rumah_id' => $rumah->id,
                        'status_bantuan' => 'Disalurkan',
                        'tanggal_bantuan' => $statusDate->copy()->subDays(rand(1, 10)),
                        'jenis_bantuan' => 'Material dan Tukang',
                        'nominal' => $nominal,
                        'sumber_program' => $sumberProgram,
                        'nomor_sk' => 'SK/'.date('Y').'/'.str_pad($globalIndex, 4, '0', STR_PAD_LEFT),
                    ]);
                }

                $globalIndex++;
            }
        }
    }
}
