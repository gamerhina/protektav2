<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seminar_jenis', function (Blueprint $table) {
            $table->json('berkas_syarat_items')->nullable()->after('berkas_syarat_max_size_kb');
        });
    }

    public function down(): void
    {
        Schema::table('seminar_jenis', function (Blueprint $table) {
            $table->dropColumn('berkas_syarat_items');
        });
    }
};
