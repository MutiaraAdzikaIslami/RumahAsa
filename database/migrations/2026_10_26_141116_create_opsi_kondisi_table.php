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
        Schema::create('opsi_kondisi', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('komponen_id');
            $table->string('label');
            $table->unsignedSmallInteger('urutan');
            $table->timestamps();

            $table->foreign('komponen_id')
                ->references('id')->on('komponen_penilaian')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opsi_kondisi');
    }
};
