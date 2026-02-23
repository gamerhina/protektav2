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
        // Add to surats table
        Schema::table('surats', function (Blueprint $table) {
            if (!Schema::hasColumn('surats', 'uploaded_pdf_path')) {
                $table->string('uploaded_pdf_path')->nullable()->after('generated_file_path');
            }
        });

        // Add to surat_jenis table
        Schema::table('surat_jenis', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_jenis', 'is_uploaded')) {
                $table->boolean('is_uploaded')->default(false)->after('template_id');
            }
        });

        // Add to surat_approvals table
        Schema::table('surat_approvals', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_approvals', 'stamp_x')) {
                $table->integer('stamp_x')->nullable()->after('qr_code_url');
                $table->integer('stamp_y')->nullable()->after('stamp_x');
                $table->integer('stamp_page')->nullable()->after('stamp_y');
                $table->boolean('is_stamped')->default(false)->after('stamp_page');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            $table->dropColumn('uploaded_pdf_path');
        });

        Schema::table('surat_jenis', function (Blueprint $table) {
            $table->dropColumn('is_uploaded');
        });

        Schema::table('surat_approvals', function (Blueprint $table) {
            $table->dropColumn(['stamp_x', 'stamp_y', 'stamp_page', 'is_stamped']);
        });
    }
};
