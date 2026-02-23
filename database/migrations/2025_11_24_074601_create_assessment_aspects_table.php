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
        Schema::create('assessment_aspects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seminar_jenis_id');
            $table->enum('evaluator_type', ['p1', 'p2', 'pembahas']);
            $table->string('nama_aspek');
            $table->decimal('persentase', 5, 2)->default(0);
            $table->integer('urutan')->default(0);
            $table->timestamps();

            $table->foreign('seminar_jenis_id')->references('id')->on('seminar_jenis')->onDelete('cascade');
            $table->index(['seminar_jenis_id', 'evaluator_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_aspects');
    }
};
