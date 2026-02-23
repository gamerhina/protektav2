<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_jenis', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_jenis', 'form_fields')) {
                $table->json('form_fields')->nullable()->after('keterangan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surat_jenis', function (Blueprint $table) {
            if (Schema::hasColumn('surat_jenis', 'form_fields')) {
                $table->dropColumn('form_fields');
            }
        });
    }
};
