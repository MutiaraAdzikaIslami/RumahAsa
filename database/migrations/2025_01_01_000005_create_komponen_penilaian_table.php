<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master komponen penilaian (7 komponen sesuai panduan RTLH)
        Schema::create('komponen_penilaian', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();      // struktur, atap, dinding, lantai, luas_ruang, pencahayaan, sanitasi
            $table->string('nama', 100);
            $table->unsignedTinyInteger('urutan');     // 1..7
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komponen_penilaian');
    }
};
