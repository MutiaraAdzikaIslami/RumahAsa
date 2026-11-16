<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE rumah_warga DROP CONSTRAINT IF EXISTS rumah_warga_status_check;");

        DB::table('rumah_warga')
            ->where('status', 'Draf')
            ->update(['status' => 'Menunggu Survei']);

        DB::statement("
            ALTER TABLE rumah_warga
            ADD CONSTRAINT rumah_warga_status_check
            CHECK (status IN (
                'Menunggu Survei',
                'Diajukan',
                'Perlu Perbaikan',
                'Layak Huni',
                'Penerima Bantuan',
                'Dalam Renovasi',
                'Selesai'
            ));
        ");

        DB::statement("ALTER TABLE rumah_warga ALTER COLUMN status SET DEFAULT 'Menunggu Survei';");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE rumah_warga DROP CONSTRAINT IF EXISTS rumah_warga_status_check;");

        DB::table('rumah_warga')
            ->where('status', 'Menunggu Survei')
            ->update(['status' => 'Draf']);

        DB::statement("
            ALTER TABLE rumah_warga
            ADD CONSTRAINT rumah_warga_status_check
            CHECK (status IN (
                'Draf', 'Diajukan', 'Perlu Perbaikan', 'Layak Huni',
                'Penerima Bantuan', 'Dalam Renovasi', 'Selesai'
            ));
        ");

        DB::statement("ALTER TABLE rumah_warga ALTER COLUMN status SET DEFAULT 'Draf';");
    }
};
