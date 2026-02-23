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
        Schema::table('surat_jenis', function (Blueprint $table) {
            $table->json('target_pemohon')->nullable()->after('nama')->comment('Roles allowed to apply: ["mahasiswa", "dosen"]');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_jenis', function (Blueprint $table) {
            $table->dropColumn('target_pemohon');
        });
    }
};
