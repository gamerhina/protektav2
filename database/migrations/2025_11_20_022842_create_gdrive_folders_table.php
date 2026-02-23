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
        Schema::create('gdrive_folders', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // Folder name (e.g., "Semester Ganjil 2024/2025")
            $table->string('folder_id'); // Google Drive folder ID
            $table->string('link'); // Google Drive link
            $table->enum('semester', ['ganjil', 'genap']);
            $table->year('tahun_akademik');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gdrive_folders');
    }
};
