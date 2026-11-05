<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    
    public function run(): void
    {
        // 1. Buat role ADMIN terlebih dahulu agar foreign key terpenuhi
        $adminRoleId = DB::table('roles')->insertGetId([
            'kode' => 'ADMIN',
            'nama' => 'Administrator',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Buat akun Admin menggunakan kredensial dari file .env
        User::create([
            'nama' => env('ADMIN_NAME', 'Admin RumahAsa'),
            'email' => env('ADMIN_EMAIL', 'admin@rumahasa.test'),
            'password' => Hash::make(env('ADMIN_PASSWORD', 'Admin12345')),
            'role_id' => $adminRoleId,
            'wilayah_id' => null, 
            'must_change_password' => true, 
            'password_changed_at' => null,
        ]);
    }
}