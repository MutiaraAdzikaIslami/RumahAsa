<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survei_foto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survei_komponen_id')->constrained('survei_komponen')->cascadeOnDelete(); //[cite: 32]
            $table->string('path_file'); //[cite: 36]
            $table->string('mime_type')->nullable(); //[cite: 36]
            $table->integer('ukuran_byte')->nullable(); //[cite: 36]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survei_foto');
    }
};