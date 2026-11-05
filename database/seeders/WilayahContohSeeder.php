<?php

namespace Database\Seeders;

use App\Models\Wilayah;
use Illuminate\Database\Seeder;

class WilayahContohSeeder extends Seeder
{
    /**
     * Seed contoh hierarki wilayah berjenjang.
     * Di produksi, Admin input manual via modul Wilayah (FR-08).
     */
    public function run(): void
    {
        // Kecamatan
        $kec = Wilayah::create([
            'kode'      => 'KEC-001',
            'nama'      => 'Kecamatan Contoh',
            'tingkat'   => Wilayah::TINGKAT_KECAMATAN,
            'parent_id' => null,
            'path'      => null,  // akan di-set setelah create
            'is_aktif'  => true,
        ]);
        $kec->update(['path' => '/' . $kec->id]);

        // Kelurahan di bawah kecamatan
        $kel = Wilayah::create([
            'kode'      => 'KEL-001',
            'nama'      => 'Kelurahan Contoh',
            'tingkat'   => Wilayah::TINGKAT_KELURAHAN,
            'parent_id' => $kec->id,
            'path'      => null,
            'is_aktif'  => true,
        ]);
        $kel->update(['path' => $kec->path . '/' . $kel->id]);

        // RW di bawah kelurahan
        $rw = Wilayah::create([
            'kode'      => 'RW-001',
            'nama'      => 'RW 01',
            'tingkat'   => Wilayah::TINGKAT_RW,
            'parent_id' => $kel->id,
            'path'      => null,
            'is_aktif'  => true,
        ]);
        $rw->update(['path' => $kel->path . '/' . $rw->id]);

        // 3 RT di bawah RW
        foreach (['RT 01', 'RT 02', 'RT 03'] as $i => $namaRt) {
            $rt = Wilayah::create([
                'kode'      => 'RT-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'nama'      => $namaRt,
                'tingkat'   => Wilayah::TINGKAT_RT,
                'parent_id' => $rw->id,
                'path'      => null,
                'is_aktif'  => true,
            ]);
            $rt->update(['path' => $rw->path . '/' . $rt->id]);
        }
    }
}
