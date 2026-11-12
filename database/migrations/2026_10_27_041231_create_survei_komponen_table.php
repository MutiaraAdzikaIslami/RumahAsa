<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survei_komponen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survei_kelayakan_id')->constrained('survei_kelayakan')->cascadeOnDelete(); //[cite: 32]
            $table->foreignId('komponen_id')->constrained('komponen_penilaian')->restrictOnDelete(); //[cite: 36]
            $table->foreignId('opsi_kondisi_id')->constrained('opsi_kondisi')->restrictOnDelete(); //[cite: 36]
            $table->text('catatan')->nullable(); //[cite: 36]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survei_komponen');
    }
};