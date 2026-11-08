<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FR-11a: Penilaian per komponen (7 baris per survei)
        Schema::create('survei_komponen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survei_id')->constrained('survei_kelayakan')->cascadeOnDelete();
            $table->foreignId('komponen_id')->constrained('komponen_penilaian')->restrictOnDelete();
            $table->foreignId('opsi_kondisi_id')->nullable()->constrained('opsi_kondisi')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['survei_id', 'komponen_id']);  // 1 baris per komponen per survei
            $table->index('komponen_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survei_komponen');
    }
};
