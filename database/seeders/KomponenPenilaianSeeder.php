<?php

namespace Database\Seeders;

use App\Models\KomponenPenilaian;
use Illuminate\Database\Seeder;

class KomponenPenilaianSeeder extends Seeder
{
    public function run(): void
    {
        // 7 komponen sesuai panduan RTLH pemerintah (bukan proposal awal)
        $komponen = [
            [
                'kode'        => 'struktur',
                'nama'        => 'Struktur Bangunan (Keselamatan)',
                'urutan'      => 1,
                'deskripsi'   => 'Fondasi, kolom, dan balok. Komponen dengan bobot tertinggi dalam penilaian.',
            ],
            [
                'kode'        => 'atap',
                'nama'        => 'Atap',
                'urutan'      => 2,
                'deskripsi'   => 'Penutup atap dan struktur penyangga. Pertimbangkan persentase kerusakan dan kelayakan material.',
            ],
            [
                'kode'        => 'dinding',
                'nama'        => 'Dinding',
                'urutan'      => 3,
                'deskripsi'   => 'Material dinding dan tingkat kerusakan (retak struktural, bolong, rapuh).',
            ],
            [
                'kode'        => 'lantai',
                'nama'        => 'Lantai',
                'urutan'      => 4,
                'deskripsi'   => 'Jenis material lantai (tanah, kayu, semen/ubin) dan tingkat kerusakan.',
            ],
            [
                'kode'        => 'luas_ruang',
                'nama'        => 'Kecukupan Luas Ruang',
                'urutan'      => 5,
                'deskripsi'   => 'Luas lantai per penghuni (≥9 m²/orang standar SDG) dan tinggi plafon (≥2,8 m).',
            ],
            [
                'kode'        => 'pencahayaan',
                'nama'        => 'Pencahayaan & Penghawaan (Ventilasi)',
                'urutan'      => 6,
                'deskripsi'   => 'Ventilasi minimal 5% luas lantai, pencahayaan alami minimal 10% luas lantai.',
            ],
            [
                'kode'        => 'sanitasi',
                'nama'        => 'Fasilitas Sanitasi',
                'urutan'      => 7,
                'deskripsi'   => 'Jarak sumur-septic tank, tipe kloset, dan ketersediaan MCK.',
            ],
        ];

        foreach ($komponen as $k) {
            KomponenPenilaian::updateOrCreate(['kode' => $k['kode']], $k);
        }
    }
}
