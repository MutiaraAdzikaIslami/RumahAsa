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
    Schema::create('progres_renovasi', function (Blueprint $table) {
        $table->id();
        $table->foreignId('penerima_bantuan_id')->constrained('penerima_bantuan')->cascadeOnDelete(); //[cite: 32]
        $table->string('tahap_pekerjaan'); //[cite: 36]
        $table->integer('persentase'); //[cite: 36]
        $table->date('tanggal_progres'); //[cite: 36]
        $table->text('catatan')->nullable(); //[cite: 36]
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progres_renovasi');
    }
};
