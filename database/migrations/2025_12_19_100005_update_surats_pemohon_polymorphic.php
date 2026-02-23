<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make pemohon support mahasiswa/dosen.
        Schema::table('surats', function (Blueprint $table) {
            if (!Schema::hasColumn('surats', 'pemohon_type')) {
                $table->enum('pemohon_type', ['dosen', 'mahasiswa'])->default('dosen')->after('surat_jenis_id');
            }
            if (!Schema::hasColumn('surats', 'pemohon_mahasiswa_id')) {
                $table->unsignedBigInteger('pemohon_mahasiswa_id')->nullable()->after('pemohon_dosen_id');
            }
        });

        // Drop FK first, then make pemohon_dosen_id nullable using raw SQL (avoids doctrine/dbal requirement).
        try {
            Schema::table('surats', function (Blueprint $table) {
                $table->dropForeign(['pemohon_dosen_id']);
            });
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            DB::statement('ALTER TABLE surats MODIFY pemohon_dosen_id BIGINT UNSIGNED NULL');
        } catch (\Throwable $e) {
            // ignore
        }

        // Re-add foreign keys
        Schema::table('surats', function (Blueprint $table) {
            try {
                $table->foreign('pemohon_dosen_id')->references('id')->on('dosen')->nullOnDelete();
            } catch (\Throwable $e) {
                // ignore
            }
            try {
                $table->foreign('pemohon_mahasiswa_id')->references('id')->on('mahasiswa')->nullOnDelete();
            } catch (\Throwable $e) {
                // ignore
            }
        });

        DB::table('surats')->whereNull('pemohon_type')->update(['pemohon_type' => 'dosen']);
    }

    public function down(): void
    {
        try {
            Schema::table('surats', function (Blueprint $table) {
                $table->dropForeign(['pemohon_mahasiswa_id']);
            });
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            Schema::table('surats', function (Blueprint $table) {
                $table->dropForeign(['pemohon_dosen_id']);
            });
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            DB::statement('ALTER TABLE surats MODIFY pemohon_dosen_id BIGINT UNSIGNED NOT NULL');
        } catch (\Throwable $e) {
            // ignore
        }

        Schema::table('surats', function (Blueprint $table) {
            if (Schema::hasColumn('surats', 'pemohon_mahasiswa_id')) {
                $table->dropColumn('pemohon_mahasiswa_id');
            }
            if (Schema::hasColumn('surats', 'pemohon_type')) {
                $table->dropColumn('pemohon_type');
            }

            try {
                $table->foreign('pemohon_dosen_id')->references('id')->on('dosen')->onDelete('cascade');
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }
};
