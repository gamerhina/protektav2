<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surat_jenis_id');
            $table->string('nama');
            $table->string('file_path');
            $table->text('keterangan')->nullable();
            $table->json('available_tags')->nullable();
            $table->json('tag_mappings')->nullable();
            $table->json('tag_types')->nullable();
            $table->json('tag_properties')->nullable();
            $table->string('email_subject_template')->nullable();
            $table->text('email_body_template')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->foreign('surat_jenis_id')->references('id')->on('surat_jenis')->onDelete('cascade');
        });

        Schema::table('surat_jenis', function (Blueprint $table) {
            $table->foreign('template_id')->references('id')->on('surat_templates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('surat_jenis', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
        });
        Schema::dropIfExists('surat_templates');
    }
};
