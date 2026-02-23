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
        Schema::create('assessment_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seminar_nilai_id');
            $table->unsignedBigInteger('assessment_aspect_id');
            $table->decimal('nilai', 5, 2);
            $table->timestamps();

            $table->foreign('seminar_nilai_id')->references('id')->on('seminar_nilai')->onDelete('cascade');
            $table->foreign('assessment_aspect_id')->references('id')->on('assessment_aspects')->onDelete('cascade');
            $table->unique(['seminar_nilai_id', 'assessment_aspect_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_scores');
    }
};
