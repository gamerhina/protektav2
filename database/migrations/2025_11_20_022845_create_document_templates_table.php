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
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // Template name (e.g., "Borang Nilai", "Undangan Seminar")
            $table->string('kode'); // Template code (e.g., "BRNG_NILAI", "UND_SEMINAR")
            $table->string('file_path'); // Path to the .docx file
            $table->text('keterangan')->nullable();
            $table->json('mapping_fields')->nullable(); // Available fields like {nama: "Nama Mahasiswa", npm: "NPM Mahasiswa", etc.}
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
