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
        Schema::create('seminars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mahasiswa_id');
            $table->unsignedBigInteger('seminar_jenis_id');
            $table->string('no_surat');
            $table->string('judul');
            $table->date('tanggal');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->string('lokasi');
            $table->unsignedBigInteger('p1_dosen_id'); // Pembimbing 1
            $table->unsignedBigInteger('p2_dosen_id'); // Pembimbing 2
            $table->unsignedBigInteger('pembahas_dosen_id'); // Pembahas
            $table->json('berkas_syarat'); // JSON array of file paths
            $table->enum('status', ['diajukan', 'disetujui', 'ditolak', 'selesai'])->default('diajukan');
            $table->timestamp('tanggal_nilai')->nullable(); // When nilai was submitted
            $table->string('folder_gdrive')->nullable(); // Google Drive folder ID
            $table->timestamps();

            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswa')->onDelete('cascade');
            $table->foreign('seminar_jenis_id')->references('id')->on('seminar_jenis')->onDelete('cascade');
            $table->foreign('p1_dosen_id')->references('id')->on('dosen')->onDelete('cascade');
            $table->foreign('p2_dosen_id')->references('id')->on('dosen')->onDelete('cascade');
            $table->foreign('pembahas_dosen_id')->references('id')->on('dosen')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seminars');
    }
};
