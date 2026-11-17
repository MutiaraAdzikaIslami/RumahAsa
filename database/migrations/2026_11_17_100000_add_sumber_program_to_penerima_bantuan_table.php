<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('penerima_bantuan') && !Schema::hasColumn('penerima_bantuan', 'sumber_program')) {
            Schema::table('penerima_bantuan', function (Blueprint $table) {
                $table->string('sumber_program')->nullable()->after('jenis_bantuan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('penerima_bantuan') && Schema::hasColumn('penerima_bantuan', 'sumber_program')) {
            Schema::table('penerima_bantuan', function (Blueprint $table) {
                $table->dropColumn('sumber_program');
            });
        }
    }
};
