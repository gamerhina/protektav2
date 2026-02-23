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
        Schema::create('surat_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->constrained('surats')->onDelete('cascade');
            $table->foreignId('surat_role_id')->constrained('surat_roles')->onDelete('cascade');
            $table->enum('user_type', ['dosen', 'admin']);
            $table->unsignedBigInteger('user_id');
            $table->enum('status', ['pending', 'approved', 'rejected', 'revision'])->default('pending');
            $table->text('catatan')->nullable(); // Catatan approval/rejection
            $table->text('tanda_tangan')->nullable(); // Path ke signature image
            $table->text('qr_code_path')->nullable(); // Path ke QR code
            $table->string('verification_token')->unique()->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // Expiry time (optional)
            $table->timestamps();
            
            $table->index(['surat_id', 'surat_role_id']);
            $table->index(['status']);
            $table->index(['user_type', 'user_id']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_approvals');
    }
};
