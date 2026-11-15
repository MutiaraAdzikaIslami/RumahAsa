<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FR-11b: Foto bukti per komponen (1 komponen bisa banyak foto)
        Schema::create('survei_foto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survei_komponen_id')->constrained('survei_komponen')->cascadeOnDelete();
            // Path file di disk private (di luar public/) — nama acak
            $table->string('path_file', 255);
            $table->string('mime_type', 50);
            $table->unsignedInteger('ukuran_byte');
            $table->string('nama_asli', 255);
            $table->timestamps();
            $table->softDeletes();

            $table->index('survei_komponen_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survei_foto');
    }
};
