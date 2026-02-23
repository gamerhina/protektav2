<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surat_jenis_id');
            $table->unsignedBigInteger('pemohon_dosen_id');
            $table->unsignedBigInteger('mahasiswa_id')->nullable();
            $table->enum('untuk_type', ['mahasiswa', 'dosen', 'umum']);

            $table->string('no_surat')->nullable();
            $table->date('tanggal_surat');
            $table->string('tujuan')->nullable();
            $table->string('perihal')->nullable();
            $table->text('isi')->nullable();
            $table->string('penerima_email')->nullable();

            $table->enum('status', ['diajukan', 'diproses', 'dikirim', 'ditolak'])->default('diajukan');
            $table->string('generated_file_path')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('surat_jenis_id')->references('id')->on('surat_jenis')->onDelete('cascade');
            $table->foreign('pemohon_dosen_id')->references('id')->on('dosen')->onDelete('cascade');
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswa')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surats');
    }
};
