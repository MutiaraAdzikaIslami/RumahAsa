<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wilayah', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();        
            $table->string('nama', 100);
            // FIX: Ganti enum jadi string supaya support 6 level (provinsi, kabupaten, dll)
            $table->string('tingkat', 30); 
            $table->foreignId('parent_id')->nullable()->constrained('wilayah')->nullOnDelete();
            $table->string('path', 255)->nullable()->index();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tingkat', 'parent_id']);
            $table->index('path', 'wilayah_path_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah');
    }
};