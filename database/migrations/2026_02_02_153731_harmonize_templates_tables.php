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
        Schema::table('surat_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_templates', 'template_html')) {
                $table->longText('template_html')->nullable()->after('keterangan');
            }
            if (!Schema::hasColumn('surat_templates', 'header_image_path')) {
                $table->string('header_image_path')->nullable()->after('template_html');
            }
            if (!Schema::hasColumn('surat_templates', 'paper_size')) {
                $table->string('paper_size')->default('A4')->after('header_image_path');
            }
            if (!Schema::hasColumn('surat_templates', 'qr_code_enabled')) {
                $table->boolean('qr_code_enabled')->default(false)->after('paper_size');
            }
            if (!Schema::hasColumn('surat_templates', 'qr_code_position')) {
                $table->string('qr_code_position')->default('bottom-right')->after('qr_code_enabled');
            }
            if (!Schema::hasColumn('surat_templates', 'qr_code_size')) {
                $table->integer('qr_code_size')->default(100)->after('qr_code_position');
            }
        });

        Schema::table('document_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('document_templates', 'template_html')) {
                $table->longText('template_html')->nullable()->after('keterangan');
            }
            if (!Schema::hasColumn('document_templates', 'header_image_path')) {
                $table->string('header_image_path')->nullable()->after('template_html');
            }
            if (!Schema::hasColumn('document_templates', 'paper_size')) {
                $table->string('paper_size')->default('A4')->after('header_image_path');
            }
            if (!Schema::hasColumn('document_templates', 'qr_code_enabled')) {
                $table->boolean('qr_code_enabled')->default(false)->after('paper_size');
            }
            if (!Schema::hasColumn('document_templates', 'qr_code_position')) {
                $table->string('qr_code_position')->default('bottom-right')->after('qr_code_enabled');
            }
            if (!Schema::hasColumn('document_templates', 'qr_code_size')) {
                $table->integer('qr_code_size')->default(100)->after('qr_code_position');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_templates', function (Blueprint $table) {
            $table->dropColumn(['template_html', 'header_image_path', 'paper_size', 'qr_code_enabled', 'qr_code_position', 'qr_code_size']);
        });

        Schema::table('document_templates', function (Blueprint $table) {
            $table->dropColumn(['template_html', 'header_image_path', 'paper_size', 'qr_code_enabled', 'qr_code_position', 'qr_code_size']);
        });
    }
};
