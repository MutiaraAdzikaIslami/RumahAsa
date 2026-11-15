<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminAwalSeeder extends Seeder
{
    /**
     * FR-05 prasyarat: minimal satu akun admin harus tersedia dari data awal.
     * Password sementara: "RumahAsa2025" (alphanumeric).
     * User wajib ganti password setelah login pertama (must_change_password=true).
     */
    public function run(): void
    {
        $adminRole = Role::where('kode', Role::KODE['admin'])->first();
        if (!$adminRole) {
            throw new \RuntimeException('Role admin belum di-seed. Jalankan RoleSeeder lebih dulu.');
        }

        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'role_id'               => $adminRole->id,
                'wilayah_id'            => null, // Admin lihat semua wilayah
                'nama'                  => 'Administrator Awal',
                'email'                 => 'admin@rumahasa.local',
                'password'              => Hash::make('RumahAsa2025'),
                'must_change_password'  => true,
                'is_aktif'              => true,
            ]
        );

        // Bonus: 1 Surveyor contoh dengan wilayah tugas RT 01
        $surveyorRole = Role::where('kode', Role::KODE['surveyor'])->first();
        $rt01 = Wilayah::where('kode', 'RT-001')->first();

        if ($surveyorRole && $rt01) {
            User::updateOrCreate(
                ['username' => 'surveyor01'],
                [
                    'role_id'               => $surveyorRole->id,
                    'wilayah_id'            => $rt01->id,
                    'nama'                  => 'Surveyor Contoh',
                    'email'                 => 'surveyor@rumahasa.local',
                    'password'              => Hash::make('RumahAsa2025'),
                    'must_change_password'  => true,
                    'is_aktif'              => true,
                ]
            );
        }
    }
}
