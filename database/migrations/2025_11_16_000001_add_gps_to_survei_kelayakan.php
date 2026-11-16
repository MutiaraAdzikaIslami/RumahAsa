<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survei_kelayakan', function (Blueprint $table) {
            $table->decimal('latitude_survei', 10, 7)->nullable()->after('tanggal_survei');
            $table->decimal('longitude_survei', 10, 7)->nullable()->after('latitude_survei');
            $table->timestamp('lokasi_dicatat_at')->nullable()->after('longitude_survei');
        });
    }

    public function down(): void
    {
        Schema::table('survei_kelayakan', function (Blueprint $table) {
            $table->dropColumn(['latitude_survei', 'longitude_survei', 'lokasi_dicatat_at']);
        });
    }
};
