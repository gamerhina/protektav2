<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('surat_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->constrained()->onDelete('cascade');
            // Polymorphic relation for the commenter (Admin, Dosen, Mahasiswa)
            $table->nullableMorphs('user'); 
            $table->text('message');
            // Is this an internal note only for admins/approvers?
            $table->boolean('is_internal')->default(false); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_comments');
    }
};
