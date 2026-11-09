<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rumah_warga', function (Blueprint $table) {
            $table->id();
            $table->string('kode_registrasi', 20)->unique();
            $table->string('nama_kepala_keluarga', 150)->nullable();
            
            // NIK & No KK: nullable supaya bisa simpan draft kosong
            $table->text('nik_enc')->nullable();
            $table->char('nik_hash', 64)->nullable()->unique();
            $table->text('no_kk_enc')->nullable();
            $table->char('no_kk_hash', 64)->nullable()->index();
            
            $table->unsignedSmallInteger('jumlah_anggota')->nullable();
            $table->decimal('luas_rumah', 6, 2)->nullable();
            $table->string('pekerjaan', 100)->nullable();
            $table->bigInteger('penghasilan')->nullable();
            $table->enum('status_kepemilikan', ['milik_sendiri', 'sewa', 'menumpang', 'lainnya'])->nullable();
            $table->text('alamat')->nullable();
            
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            
            $table->foreignId('wilayah_id')->nullable()->constrained('wilayah')->nullOnDelete();
            $table->foreignId('surveyor_id')->constrained('users')->restrictOnDelete();
            
            $table->enum('status', [
                'Draf', 'Diajukan', 'Perlu Perbaikan', 'Layak Huni',
                'Penerima Bantuan', 'Dalam Renovasi', 'Selesai',
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