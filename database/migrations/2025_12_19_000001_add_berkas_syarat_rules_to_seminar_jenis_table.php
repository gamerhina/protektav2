<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seminar_jenis', function (Blueprint $table) {
            $table->json('berkas_syarat_extensions')->nullable()->after('syarat_seminar');
            $table->unsignedInteger('berkas_syarat_max_size_kb')->default(5120)->after('berkas_syarat_extensions');
        });
    }

    public function down(): void
    {
        Schema::table('seminar_jenis', function (Blueprint $table) {
            $table->dropColumn(['berkas_syarat_extensions', 'berkas_syarat_max_size_kb']);
        });
    }
};
