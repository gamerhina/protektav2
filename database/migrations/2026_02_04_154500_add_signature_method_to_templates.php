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
        Schema::table('document_templates', function (Blueprint $table) {
            $table->enum('signature_method', ['manual', 'qr_code'])->default('qr_code')->after('paper_size');
        });

        Schema::table('surat_templates', function (Blueprint $table) {
            $table->enum('signature_method', ['manual', 'qr_code'])->default('qr_code')->after('header_repeat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_templates', function (Blueprint $table) {
            $table->dropColumn('signature_method');
        });
        
        Schema::table('surat_templates', function (Blueprint $table) {
            $table->dropColumn('signature_method');
        });
    }
};
