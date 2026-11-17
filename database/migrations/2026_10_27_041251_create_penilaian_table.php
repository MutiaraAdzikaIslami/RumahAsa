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
        if (! Schema::hasTable('penilaian')) {
            Schema::create('penilaian', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rumah_id')->constrained('rumah_warga')->cascadeOnDelete();
                $table->string('kategori');
                $table->text('catatan');
                $table->text('alasan_revisi')->nullable();
                $table->boolean('is_aktif')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian');
    }
};
