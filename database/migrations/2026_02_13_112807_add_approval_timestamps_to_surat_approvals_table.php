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
        Schema::table('surat_approvals', function (Blueprint $table) {
            // Check and add columns only if they don't exist
            if (!Schema::hasColumn('surat_approvals', 'role_id')) {
                $table->foreignId('role_id')->nullable()->after('surat_role_id')->constrained('surat_roles')->onDelete('cascade');
            }
            if (!Schema::hasColumn('surat_approvals', 'dosen_id')) {
                $table->foreignId('dosen_id')->nullable()->after('role_id')->constrained('dosen')->onDelete('set null');
            }
            
            // Add signature fields
            if (!Schema::hasColumn('surat_approvals', 'signature_type')) {
                $table->enum('signature_type', ['canvas', 'upload', 'qr'])->nullable()->after('catatan');
            }
            if (!Schema::hasColumn('surat_approvals', 'signature_path')) {
                $table->string('signature_path')->nullable()->after('signature_type');
            }
            if (!Schema::hasColumn('surat_approvals', 'qr_code_url')) {
                $table->string('qr_code_url')->nullable()->after('signature_path');
            }
            
            // Add approval timestamps
            if (!Schema::hasColumn('surat_approvals', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('signed_at');
            }
            if (!Schema::hasColumn('surat_approvals', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_approvals', function (Blueprint $table) {
            if (Schema::hasColumn('surat_approvals', 'role_id')) {
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            }
            if (Schema::hasColumn('surat_approvals', 'dosen_id')) {
                $table->dropForeign(['dosen_id']);
                $table->dropColumn('dosen_id');
            }
            $table->dropColumn([
                'signature_type',
                'signature_path',
                'qr_code_url',
                'approved_at',
                'rejected_at'
            ]);
        });
    }
};
