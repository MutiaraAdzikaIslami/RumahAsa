<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Panggil seeder secara berurutan. 
        // Urutan SANGAT PENTING: Role dan Wilayah harus ada dulu sebelum User dibuat!
        $this->call([
            RoleSeeder::class,
            WilayahSeeder::class,
            UserSeeder::class,
        ]);
    }
}
