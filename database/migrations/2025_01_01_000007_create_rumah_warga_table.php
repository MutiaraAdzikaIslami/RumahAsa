<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FR-10: Data rumah & warga
        Schema::create('rumah_warga', function (Blueprint $table) {
            $table->id();
            $table->string('kode_registrasi', 20)->unique();
            $table->string('nama_kepala_keluarga', 150);
            // NIK & No KK: dienkripsi (AES-256-GCM via Crypt::encryptString)
            $table->text('nik_enc');
            // HMAC SHA-256 dari NIK untuk cek duplikat & pencarian tanpa reveal nilai asli
            $table->char('nik_hash', 64)->unique();
            $table->text('no_kk_enc');
            $table->char('no_kk_hash', 64)->index();
            $table->unsignedSmallInteger('jumlah_anggota');
            // Untuk komponen 5 (Kecukupan Luas) & 6 (Pencahayaan)
            $table->decimal('luas_rumah', 6, 2)->nullable();   // m²
            // Data sosial-ekonomi
            $table->string('pekerjaan', 100)->nullable();
            $table->bigInteger('penghasilan')->nullable();
            $table->enum('status_kepemilikan', ['milik_sendiri', 'sewa', 'menumpang', 'lainnya'])->default('milik_sendiri');
            $table->text('alamat');
            // GPS
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            // Relasi
            $table->foreignId('wilayah_id')->constrained('wilayah')->restrictOnDelete();
            $table->foreignId('surveyor_id')->constrained('users')->restrictOnDelete();
            // FR-10 / StatusWorkflow
            $table->enum('status', [
                'Draf',
                'Diajukan',
                'Perlu Perbaikan',
                'Layak Huni',
                'Penerima Bantuan',
                'Dalam Renovasi',
                'Selesai',
            ])->default('Draf');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['wilayah_id', 'status']);
            $table->index('surveyor_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rumah_warga');
    }
};
