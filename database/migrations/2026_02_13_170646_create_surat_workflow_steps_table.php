<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('surat_workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('surat_templates')->onDelete('cascade');
            $table->foreignId('surat_role_id')->constrained('surat_roles')->onDelete('cascade');
            $table->foreignId('dosen_id')->nullable()->constrained('dosen')->onDelete('set null');
            $table->integer('urutan')->default(1);
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('surat_workflow_steps');
    }
};
