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
        Schema::table('surat_roles', function (Blueprint $table) {
            if (Schema::hasColumn('surat_roles', 'aktif')) {
                $table->renameColumn('aktif', 'is_active');
            }
            if (!Schema::hasColumn('surat_roles', 'warna')) {
                $table->string('warna', 7)->nullable()->after('urutan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_roles', function (Blueprint $table) {
            if (Schema::hasColumn('surat_roles', 'is_active')) {
                $table->renameColumn('is_active', 'aktif');
            }
            if (Schema::hasColumn('surat_roles', 'warna')) {
                $table->dropColumn('warna');
            }
        });
    }
};
