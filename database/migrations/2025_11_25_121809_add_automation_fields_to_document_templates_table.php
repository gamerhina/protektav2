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
            $table->foreignId('seminar_jenis_id')->nullable()->after('kode')->constrained('seminar_jenis')->onDelete('set null');
            $table->json('available_tags')->nullable()->after('mapping_fields');
            $table->json('tag_mappings')->nullable()->after('available_tags');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_templates', function (Blueprint $table) {
            $table->dropForeign(['seminar_jenis_id']);
            $table->dropColumn(['seminar_jenis_id', 'available_tags', 'tag_mappings']);
        });
    }
};
