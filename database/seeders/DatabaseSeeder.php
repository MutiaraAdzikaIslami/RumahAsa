<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            WilayahKemendagriSeeder::class,
            AdminAwalSeeder::class,
            KomponenPenilaianSeeder::class,
            OpsiKondisiSeeder::class,
        ]);
    }
}