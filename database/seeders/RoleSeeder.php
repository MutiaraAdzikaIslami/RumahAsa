<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'kode'       => Role::KODE['surveyor'],
                'nama'       => 'Surveyor',
                'deskripsi'  => 'Petugas lapangan yang melakukan pendataan rumah dan input progres renovasi.',
            ],
            [
                'kode'       => Role::KODE['verifikator'],
                'nama'       => 'Verifikator',
                'deskripsi'  => 'Petugas pemerintah yang meninjau survei, menetapkan kategori, mencatat bantuan, dan menetapkan status Selesai.',
            ],
            [
                'kode'       => Role::KODE['admin'],
                'nama'       => 'Admin',
                'deskripsi'  => 'Pengelola sistem: akun pengguna, master wilayah, dan pemantauan audit log (read-only).',
            ],
            [
                'kode'       => Role::KODE['pimpinan'],
                'nama'       => 'Pimpinan',
                'deskripsi'  => 'Pemantau penyaluran bantuan: dashboard ringkasan, rekap dana per triwulan (read-only).',
            ],
        ];

        foreach ($roles as $r) {
            Role::updateOrCreate(['kode' => $r['kode']], $r);
        }
    }
}
