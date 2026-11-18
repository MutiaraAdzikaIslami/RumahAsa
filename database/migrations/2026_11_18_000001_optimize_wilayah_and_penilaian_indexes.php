<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Hapus indeks duplikat pada wilayah.path dan tambahkan indeks pattern_ops untuk query LIKE 'prefix%'
        DB::statement('DROP INDEX IF EXISTS wilayah_path_idx;');
        DB::statement('CREATE INDEX IF NOT EXISTS wilayah_path_pattern_idx ON wilayah (path varchar_pattern_ops);');

        // 2. Tambahkan indeks selektif (parent_id, tingkat) untuk endpoint cascading wilayah
        DB::statement('CREATE INDEX IF NOT EXISTS wilayah_parent_id_tingkat_idx ON wilayah (parent_id, tingkat);');

        // 3. Tambahkan indeks composite untuk relasi RumahWarga::penilaianAktif() & histori penilaian
        DB::statement('CREATE INDEX IF NOT EXISTS penilaian_rumah_id_is_aktif_idx ON penilaian (rumah_id, is_aktif);');

        if (Schema::hasColumn('penilaian', 'verifikator_id')) {
            DB::statement('CREATE INDEX IF NOT EXISTS penilaian_verifikator_id_idx ON penilaian (verifikator_id);');
        }
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS penilaian_verifikator_id_idx;');
        DB::statement('DROP INDEX IF EXISTS penilaian_rumah_id_is_aktif_idx;');
        DB::statement('DROP INDEX IF EXISTS wilayah_parent_id_tingkat_idx;');
        DB::statement('DROP INDEX IF EXISTS wilayah_path_pattern_idx;');
        DB::statement('CREATE INDEX IF NOT EXISTS wilayah_path_idx ON wilayah (path);');
    }
};