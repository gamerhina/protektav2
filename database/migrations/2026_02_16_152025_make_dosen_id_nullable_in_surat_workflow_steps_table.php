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
        Schema::table('surat_workflow_steps', function (Blueprint $table) {
            $table->unsignedBigInteger('dosen_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_workflow_steps', function (Blueprint $table) {
            // Warning: changing back to non-nullable might fail if NULLs exist
            $table->unsignedBigInteger('dosen_id')->nullable(false)->change();
        });
    }
};
