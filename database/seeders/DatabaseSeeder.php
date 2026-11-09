<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat data Role (Harus menyertakan kolom 'kode' dan 'nama')
        DB::table('roles')->insert([
            'id' => 1,
            'kode' => 'ADM',
            'nama' => 'Administrator',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Buat data Wilayah (Sebagai antisipasi relasi wilayah_id di tabel users)
        DB::table('wilayah')->insert([
            'id' => 1,
            'parent_id' => null,
            'tingkat' => 'Kecamatan', // Sesuai enum: 'Kecamatan', 'Kelurahan', 'RW', 'RT'
            'nama' => 'Kecamatan Bojongsoang',
            'path' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Buat data User dengan menyambungkan role_id dan wilayah_id
        User::factory()->create([
            'nama' => 'Test User',
            'email' => 'test@example.com',
            'role_id' => 1,
            'wilayah_id' => 1,
        ]);
    }
}
