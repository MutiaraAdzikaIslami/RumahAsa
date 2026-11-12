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
    Schema::create('penilaian', function (Blueprint $table) {
        $table->id();
        $table->foreignId('rumah_id')->constrained('rumah_warga')->cascadeOnDelete(); //[cite: 32]
        $table->string('kategori'); //[cite: 36]
        $table->text('catatan'); //[cite: 36]
        $table->text('alasan_revisi')->nullable(); //[cite: 36]
        $table->boolean('is_aktif')->default(true); //[cite: 36]
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian');
    }
};
