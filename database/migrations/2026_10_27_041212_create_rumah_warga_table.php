<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rumah_warga', function (Blueprint $table) {
            $table->id();
            $table->string('kode_registrasi')->unique(); //[cite: 32]
            $table->foreignId('wilayah_id')->constrained('wilayah')->restrictOnDelete(); //[cite: 32]
            $table->string('nama_kepala_keluarga'); //[cite: 35]
            $table->string('status')->default('Draf'); //[cite: 35]
            $table->decimal('latitude', 10, 8)->nullable(); //[cite: 35]
            $table->decimal('longitude', 11, 8)->nullable(); //[cite: 35]
            $table->text('nik_terenkripsi'); //[cite: 34, 35]
            $table->string('hash_nik')->index(); //[cite: 34]
            $table->timestamps();
            $table->softDeletes(); //[cite: 28]
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rumah_warga');
    }
};