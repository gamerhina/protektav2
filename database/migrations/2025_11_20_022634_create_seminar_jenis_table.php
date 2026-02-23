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
        Schema::create('seminar_jenis', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // Seminar Usul, Seminar Hasil, Ujian Skripsi
            $table->string('kode'); // SUSUL, SHAS, USKRP
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seminar_jenis');
    }
};
