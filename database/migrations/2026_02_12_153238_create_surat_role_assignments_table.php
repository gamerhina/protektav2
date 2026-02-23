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
        Schema::create('surat_role_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_role_id')->constrained('surat_roles')->onDelete('cascade');
            $table->enum('user_type', ['dosen', 'admin']);
            $table->unsignedBigInteger('user_id');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            
            $table->index(['user_type', 'user_id']);
            $table->index(['surat_role_id', 'aktif']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_role_assignments');
    }
};
