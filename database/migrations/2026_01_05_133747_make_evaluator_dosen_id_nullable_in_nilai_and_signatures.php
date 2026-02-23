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
        Schema::table('seminar_nilai', function (Blueprint $table) {
            $table->unsignedBigInteger('dosen_id')->nullable()->change();
        });
        Schema::table('seminar_signatures', function (Blueprint $table) {
            $table->unsignedBigInteger('dosen_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seminar_nilai', function (Blueprint $table) {
            $table->unsignedBigInteger('dosen_id')->nullable(false)->change();
        });
        Schema::table('seminar_signatures', function (Blueprint $table) {
            $table->unsignedBigInteger('dosen_id')->nullable(false)->change();
        });
    }
};
