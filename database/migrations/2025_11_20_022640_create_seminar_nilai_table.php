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
        Schema::create('seminar_nilai', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seminar_id');
            $table->unsignedBigInteger('dosen_id'); // Dosen penilai (P1, P2, or Pembahas)
            $table->enum('jenis_penilai', ['p1', 'p2', 'pembahas']); // Who is doing the evaluation
            $table->decimal('nilai_angka', 5, 2); // Numeric grade (0-100.00)
            $table->json('komponen_nilai')->nullable(); // Components like {materi: 25, metodologi: 25, presentasi: 25, diskusi: 25}
            $table->text('catatan')->nullable(); // Notes from the evaluator
            $table->timestamps();

            $table->foreign('seminar_id')->references('id')->on('seminars')->onDelete('cascade');
            $table->foreign('dosen_id')->references('id')->on('dosen')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seminar_nilai');
    }
};
