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
        Schema::table('surat_approvals', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_approvals', 'urutan')) {
                $table->integer('urutan')->default(1)->after('dosen_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_approvals', function (Blueprint $table) {
            if (Schema::hasColumn('surat_approvals', 'urutan')) {
                $table->dropColumn('urutan');
            }
        });
    }
};
