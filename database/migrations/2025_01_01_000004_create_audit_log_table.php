<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FR-09: Audit log (append-only, tidak ada updated_at / deleted_at)
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aksi', 80)->index();        // LOGIN_SUCCESS, USER_CREATE, RUMAH_SUBMIT, dll
            $table->string('entitas', 60)->nullable();  // users, rumah_warga, survei_kelayakan, dst
            $table->unsignedBigInteger('entitas_id')->nullable();
            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['aksi', 'created_at']);
            $table->index(['entitas', 'entitas_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};
