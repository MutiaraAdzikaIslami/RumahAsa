<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survei_kelayakan', function (Blueprint $table) {
            $table->id(); 
            // Constraint UNIQUE: 1 rumah = 1 survei aktif[cite: 9, 32]
            $table->foreignId('rumah_id')->unique()->constrained('rumah_warga')->cascadeOnDelete();
            $table->date('tanggal_survei')->nullable(); //[cite: 35]
            $table->timestamp('dikirim_at')->nullable(); //[cite: 35]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survei_kelayakan');
    }
};