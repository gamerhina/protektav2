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
        Schema::table('surats', function (Blueprint $table) {
            $table->enum('approval_status', ['none', 'pending', 'in_progress', 'approved', 'rejected', 'revision'])->default('none')->after('status');
            $table->unsignedBigInteger('current_approval_step')->default(0)->after('approval_status'); // Current step in approval flow
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'current_approval_step']);
        });
    }
};
