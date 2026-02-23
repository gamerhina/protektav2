<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('seminars', function (Blueprint $table) {
            $table->dropForeign(['p1_dosen_id']);
            $table->dropForeign(['p2_dosen_id']);
            $table->dropForeign(['pembahas_dosen_id']);
        });

        // Avoid doctrine/dbal dependency by using raw SQL for column changes.
        DB::statement('ALTER TABLE seminars MODIFY p1_dosen_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE seminars MODIFY p2_dosen_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE seminars MODIFY pembahas_dosen_id BIGINT UNSIGNED NULL');

        Schema::table('seminars', function (Blueprint $table) {
            $table->foreign('p1_dosen_id')->references('id')->on('dosen')->onDelete('cascade');
            $table->foreign('p2_dosen_id')->references('id')->on('dosen')->onDelete('cascade');
            $table->foreign('pembahas_dosen_id')->references('id')->on('dosen')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seminars', function (Blueprint $table) {
            $table->dropForeign(['p1_dosen_id']);
            $table->dropForeign(['p2_dosen_id']);
            $table->dropForeign(['pembahas_dosen_id']);
        });

        DB::statement('ALTER TABLE seminars MODIFY p1_dosen_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE seminars MODIFY p2_dosen_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE seminars MODIFY pembahas_dosen_id BIGINT UNSIGNED NOT NULL');

        Schema::table('seminars', function (Blueprint $table) {
            $table->foreign('p1_dosen_id')->references('id')->on('dosen')->onDelete('cascade');
            $table->foreign('p2_dosen_id')->references('id')->on('dosen')->onDelete('cascade');
            $table->foreign('pembahas_dosen_id')->references('id')->on('dosen')->onDelete('cascade');
        });
    }
};
