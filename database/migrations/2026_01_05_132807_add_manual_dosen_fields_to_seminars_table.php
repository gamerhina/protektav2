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
        Schema::table('seminars', function (Blueprint $table) {
            $table->string('p1_nama')->nullable()->after('p1_dosen_id');
            $table->string('p1_nip')->nullable()->after('p1_nama');
            $table->string('p2_nama')->nullable()->after('p2_dosen_id');
            $table->string('p2_nip')->nullable()->after('p2_nama');
            $table->string('pembahas_nama')->nullable()->after('pembahas_dosen_id');
            $table->string('pembahas_nip')->nullable()->after('pembahas_nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seminars', function (Blueprint $table) {
            $table->dropColumn(['p1_nama', 'p1_nip', 'p2_nama', 'p2_nip', 'pembahas_nama', 'pembahas_nip']);
        });
    }
};
