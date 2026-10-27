<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('progres_foto', function (Blueprint $table) {
        $table->id();
        $table->foreignId('progres_renovasi_id')->constrained('progres_renovasi')->cascadeOnDelete(); //[cite: 32]
        $table->string('path_file'); //[cite: 36]
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progres_foto');
    }
};
