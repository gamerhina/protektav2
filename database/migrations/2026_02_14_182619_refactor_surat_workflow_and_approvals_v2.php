<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Refactor surat_workflow_steps
        // We drop and recreate to simplify the shift from template_id to surat_jenis_id
        Schema::dropIfExists('surat_workflow_steps');
        Schema::create('surat_workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_jenis_id')->constrained('surat_jenis')->onDelete('cascade');
            $table->string('role_nama')->nullable(); // Label for the signer, e.g. "Kajur"
            $table->foreignId('dosen_id')->constrained('dosen')->onDelete('cascade');
            $table->integer('urutan')->default(1);
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });

        // 2. Update surat_approvals to support capturing role_nama directly
        Schema::table('surat_approvals', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_approvals', 'role_nama')) {
                $table->string('role_nama')->nullable()->after('surat_id');
            }
            
            // Adjust existing column to be nullable if it isn't, so we can use role_nama instead
            // Using raw SQL for change() since it might require doctrine/dbal or have issues with enums
            // But let's try the standard way first.
        });
        
        // Ensure surat_role_id is nullable
        Schema::table('surat_approvals', function (Blueprint $table) {
            $table->unsignedBigInteger('surat_role_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::dropIfExists('surat_workflow_steps');
        Schema::table('surat_approvals', function (Blueprint $table) {
            $table->dropColumn('role_nama');
        });
    }
};
