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
    Schema::create('penerima_bantuan', function (Blueprint $table) {
        $table->id();
        // Constraint UNIQUE: 1 rumah = 1 catatan bantuan[cite: 32]
        $table->foreignId('rumah_id')->unique()->constrained('rumah_warga')->cascadeOnDelete();
        $table->string('status_bantuan')->nullable(); //[cite: 36]
        $table->date('tanggal_bantuan')->nullable(); //[cite: 36]
        $table->string('jenis_bantuan')->nullable(); //[cite: 36]
        $table->decimal('nominal', 15, 2)->nullable(); //[cite: 36]
        $table->string('nomor_sk')->nullable(); //[cite: 36]
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerima_bantuan');
    }
};
