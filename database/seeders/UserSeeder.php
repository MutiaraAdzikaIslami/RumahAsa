<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Akun Admin
        User::factory()->create([
            'nama' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'role_id' => 1,
            'wilayah_id' => null, // Admin bebas
            'is_aktif' => true,
            'must_change_password' => false,
        ]);

        // (Opsional) Akun Surveyor untuk testing nanti
        User::factory()->create([
            'nama' => 'Surveyor Bojongsoang',
            'email' => 'surveyor@example.com',
            'password' => Hash::make('password'),
            'role_id' => 2,
            'wilayah_id' => 3204062001, // ID Desa Bojongsoang
            'is_aktif' => true,
            'must_change_password' => false,
        ]);
    }
}
