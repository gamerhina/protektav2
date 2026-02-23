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
        Schema::table('surat_jenis', function (Blueprint $row) {
            $row->integer('upload_max_kb')->nullable()->default(10240)->after('is_uploaded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_jenis', function (Blueprint $row) {
            $row->dropColumn('upload_max_kb');
        });
    }
};
