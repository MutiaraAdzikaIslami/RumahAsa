<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FR-11a: Header survei kelayakan (1 survei aktif per rumah — batasan #5)
        Schema::create('survei_kelayakan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rumah_id')->unique()->constrained('rumah_warga')->cascadeOnDelete();
            $table->foreignId('surveyor_id')->constrained('users')->restrictOnDelete();
            $table->date('tanggal_survei');
            $table->timestamp('dikirim_at')->nullable();
            $table->timestamps();

            $table->index('surveyor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survei_kelayakan');
    }
};
