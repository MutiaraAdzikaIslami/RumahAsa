<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            ['id' => 1, 'kode' => 'ADM', 'nama' => 'Administrator', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'kode' => 'SRV', 'nama' => 'Surveyor', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'kode' => 'VRF', 'nama' => 'Verifikator', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
