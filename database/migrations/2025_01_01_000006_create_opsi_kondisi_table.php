<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master opsi kondisi per komponen (33 opsi total — lihat seeder)
        Schema::create('opsi_kondisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('komponen_id')->constrained('komponen_penilaian')->cascadeOnDelete();
            $table->string('kode', 30);                  // baik, rusak_ringan, kritis_dimensi, dst
            $table->string('label', 100);
            $table->text('deskripsi');                   // panduan teknis untuk Surveyor/Verifikator
            $table->boolean('otomatis_rtlh')->default(false);  // penanda opsi yang otomatis TLH menurut panduan
            $table->unsignedTinyInteger('urutan');
            $table->timestamps();

            $table->unique(['komponen_id', 'kode']);
            $table->index('komponen_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opsi_kondisi');
    }
};
