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
        Schema::create('dosen_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dosen_id');
            $table->unsignedBigInteger('mahasiswa_id');
            $table->enum('jenis_pembimbing', ['p1', 'p2'])->default('p1'); // p1 = pembimbing utama, p2 = pembimbing pendamping
            $table->timestamps();

            $table->foreign('dosen_id')->references('id')->on('dosen')->onDelete('cascade');
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswa')->onDelete('cascade');

            // Ensure a dosen can't be the same type of pembimbing for the same mahasiswa twice
            $table->unique(['dosen_id', 'mahasiswa_id', 'jenis_pembimbing']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosen_mahasiswa');
    }
};
