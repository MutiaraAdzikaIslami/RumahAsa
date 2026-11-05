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
        Schema::create('rumah_warga', function (Blueprint $table) {
            $table->id();
            $table->string('kode_registrasi')->unique(); // Kode registrasi unik rumah
            $table->foreignId('wilayah_id')->constrained('wilayah')->onDelete('cascade'); // Relasi ke tabel wilayah
            $table->string('nama_kepala_keluarga');
            $table->text('alamat')->nullable();
            
            // Kolom enkripsi untuk privasi data warga sesuai standar ERD
            $table->text('nik_enc'); 
            $table->text('no_kk_enc')->nullable(); 
            
            // Status tahapan verifikasi/renovasi rumah
            $table->enum('status', [
                'draft', 
                'belum_verifikasi', 
                'menunggu_persetujuan', 
                'dalam_renovasi', 
                'selesai'
            ])->default('draft');
            
            // Titik Koordinat GPS (Latitude & Longitude) untuk peta/heatmap
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Referensi pengguna (pencatat/surveyor)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rumah_warga');
    }
};