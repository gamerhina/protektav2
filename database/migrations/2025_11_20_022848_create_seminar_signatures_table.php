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
        Schema::create('seminar_signatures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seminar_id');
            $table->unsignedBigInteger('dosen_id'); // Dosen who signed
            $table->enum('jenis_penilai', ['p1', 'p2', 'pembahas']); // Who signed
            $table->text('tanda_tangan'); // Base64 encoded signature image
            $table->timestamp('tanggal_ttd')->nullable(); // When signature was made
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
        Schema::dropIfExists('seminar_signatures');
    }
};
