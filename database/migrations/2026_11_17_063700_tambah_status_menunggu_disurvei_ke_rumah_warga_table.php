<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE rumah_warga MODIFY COLUMN status ENUM('Menunggu Disurvei', 'Draf', 'Diajukan', 'Perlu Perbaikan', 'Layak Huni', 'Penerima Bantuan', 'Dalam Renovasi', 'Selesai') NOT NULL DEFAULT 'Draf'");
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE rumah_warga DROP CONSTRAINT IF EXISTS rumah_warga_status_check');
            DB::statement("ALTER TABLE rumah_warga ADD CONSTRAINT rumah_warga_status_check CHECK (status IN ('Menunggu Disurvei', 'Draf', 'Diajukan', 'Perlu Perbaikan', 'Layak Huni', 'Penerima Bantuan', 'Dalam Renovasi', 'Selesai'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE rumah_warga MODIFY COLUMN status ENUM('Draf', 'Diajukan', 'Perlu Perbaikan', 'Layak Huni', 'Penerima Bantuan', 'Dalam Renovasi', 'Selesai') NOT NULL DEFAULT 'Draf'");
        }
    }
};
