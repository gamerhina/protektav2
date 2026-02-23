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
            $table->boolean('requires_approval')->default(false)->after('signature_method');
            $table->json('approval_flow')->nullable()->after('requires_approval'); // Array of role IDs in sequence
            $table->integer('approval_expiry_days')->nullable()->after('approval_flow'); // Expiry in days (null = no expiry)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_templates', function (Blueprint $table) {
            $table->dropColumn(['requires_approval', 'approval_flow', 'approval_expiry_days']);
        });
    }
};
