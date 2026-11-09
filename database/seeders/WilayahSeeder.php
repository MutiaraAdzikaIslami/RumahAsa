<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // TINGKAT 1: PROVINSI
            ['id' => 32, 'parent_id' => null, 'tingkat' => 'Provinsi', 'nama' => 'Jawa Barat', 'created_at' => now(), 'updated_at' => now()],

            // TINGKAT 2: KABUPATEN / KOTA
            ['id' => 3204, 'parent_id' => 32, 'tingkat' => 'Kabupaten', 'nama' => 'Kabupaten Bandung', 'created_at' => now(), 'updated_at' => now()],

            // TINGKAT 3: KECAMATAN
            ['id' => 320406, 'parent_id' => 3204, 'tingkat' => 'Kecamatan', 'nama' => 'Bojongsoang', 'created_at' => now(), 'updated_at' => now()],

            // TINGKAT 4: DESA / KELURAHAN
            ['id' => 3204062001, 'parent_id' => 320406, 'tingkat' => 'Desa', 'nama' => 'Bojongsoang', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3204062002, 'parent_id' => 320406, 'tingkat' => 'Desa', 'nama' => 'Bojongsari', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('wilayah')->insert($data);
    }
}
