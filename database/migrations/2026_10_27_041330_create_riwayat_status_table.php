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
    Schema::create('riwayat_status', function (Blueprint $table) {
        $table->id();
        $table->foreignId('rumah_id')->constrained('rumah_warga')->cascadeOnDelete(); //[cite: 33]
        $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); //[cite: 33]
        $table->string('status_dari'); //[cite: 36]
        $table->string('status_ke'); //[cite: 36]
        $table->text('catatan')->nullable(); //[cite: 36]
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_status');
    }
};
