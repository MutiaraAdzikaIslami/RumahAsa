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
        Schema::create('wilayah', function (Blueprint $table) {
            // Gunakan id() yang otomatis bertipe BigInteger (wajib untuk kode BPS 10 digit)
            $table->id();

            // Gunakan foreignId untuk parent_id agar bertipe unsignedBigInteger
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('wilayah')
                ->restrictOnDelete();

            $table->enum('tingkat', ['Provinsi', 'Kabupaten', 'Kota', 'Kecamatan', 'Desa', 'Kelurahan']);
            $table->string('nama');
            $table->string('path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wilayah');
    }
};
