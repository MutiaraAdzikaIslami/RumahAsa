<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FR-24 / StatusWorkflow: Riwayat status rumah (append-only)
        Schema::create('riwayat_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rumah_id')->constrained('rumah_warga')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status_dari', 30);
            $table->string('status_ke', 30);
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['rumah_id', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_status');
    }
};
