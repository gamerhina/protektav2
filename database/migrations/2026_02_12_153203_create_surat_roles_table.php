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
        Schema::create('surat_roles', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // "Ketua Jurusan", "Wakil Dekan"
            $table->string('kode', 100)->unique(); // "ketua_jurusan", "wakil_dekan"
            $table->text('deskripsi')->nullable();
            $table->integer('urutan')->default(0); // Order untuk multi-approval
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            
            $table->index(['aktif', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_roles');
    }
};
