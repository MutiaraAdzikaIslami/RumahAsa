<?php

namespace Database\Seeders;

use App\Models\Wilayah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahKemendagriSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('seeders/data/wilayah.sql');
        
        if (!file_exists($file)) {
            $this->command->error('File wilayah.sql tidak ditemukan di database/seeders/data/');
            return;
        }

        // FIX: PostgreSQL tidak support SET FOREIGN_KEY_CHECKS, gunakan truncate CASCADE
        Wilayah::truncate();

        $this->command->info('Memproses data Kemendagri... (Ini bisa memakan waktu 1-3 menit)');

        $handle = fopen($file, 'r');
        $kodeToIdMap = []; 
        $count = 0;

        while (($line = fgets($handle)) !== false) {
            if (preg_match("/\('([^']+)',\s*'([^']+)'\)/", $line, $matches)) {
                $kode = $matches[1];
                $nama = stripslashes($matches[2]);
                
                $tingkat = $this->getTingkat($kode);
                $parentKode = $this->getParentKode($kode);
                $parentId = $parentKode ? ($kodeToIdMap[$parentKode] ?? null) : null;

                $id = DB::table('wilayah')->insertGetId([
                    'kode' => $kode,
                    'nama' => $nama,
                    'tingkat' => $tingkat,
                    'parent_id' => $parentId,
                    'is_aktif' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $kodeToIdMap[$kode] = $id;
                $count++;

                if ($count % 10000 === 0) {
                    $this->command->info("Processed $count rows... (Tingkat: $tingkat)");
                }
            }
        }
        fclose($handle);

        $this->command->info('Data utama berhasil dimasukkan. Menggenerate materialized path...');

        $this->generatePaths();

        $this->command->info('Seeding Wilayah Kemendagri selesai! Total: ' . $count . ' baris.');
    }

    private function getTingkat(string $kode): string
    {
        $len = strlen($kode);
        return match($len) {
            2 => 'provinsi',
            5 => 'kabupaten',
            8 => 'kecamatan',
            13 => 'kelurahan',
            default => 'wilayah'
        };
    }

    private function getParentKode(string $kode): ?string
    {
        $parts = explode('.', $kode);
        if (count($parts) <= 1) return null; 
        array_pop($parts); 
        return implode('.', $parts);
    }

    private function generatePaths(): void
    {
        $allWilayah = Wilayah::orderBy('id')->get(['id', 'parent_id']);
        $pathMap = [];

        foreach ($allWilayah as $w) {
            if (is_null($w->parent_id)) {
                $pathMap[$w->id] = '/' . $w->id;
            } else {
                $pathMap[$w->id] = ($pathMap[$w->parent_id] ?? '') . '/' . $w->id;
            }
        }

        foreach (array_chunk($pathMap, 1000, true) as $chunk) {
            foreach ($chunk as $id => $path) {
                DB::table('wilayah')->where('id', $id)->update(['path' => $path]);
            }
        }
    }
}