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
        Schema::table('seminar_signatures', function (Blueprint $table) {
            $table->string('verification_token')->nullable()->unique()->after('jenis_penilai');
            $table->string('qr_code_path')->nullable()->after('tanda_tangan');
            $table->enum('signature_type', ['manual', 'qr_code'])->default('manual')->after('qr_code_path');
        });

        Schema::table('surats', function (Blueprint $table) {
            $table->string('verification_token')->nullable()->unique()->after('status');
            $table->string('qr_code_path')->nullable()->after('generated_file_path');
            $table->enum('signature_type', ['manual', 'qr_code'])->default('manual')->after('qr_code_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seminar_signatures', function (Blueprint $table) {
            $table->dropColumn(['verification_token', 'qr_code_path', 'signature_type']);
        });

        Schema::table('surats', function (Blueprint $table) {
            $table->dropColumn(['verification_token', 'qr_code_path', 'signature_type']);
        });
    }
};
