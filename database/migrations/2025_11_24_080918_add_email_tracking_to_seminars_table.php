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
        Schema::table('seminars', function (Blueprint $table) {
            $table->timestamp('undangan_sent_at')->nullable()->after('status');
            $table->json('undangan_recipients')->nullable()->after('undangan_sent_at');
            $table->timestamp('nilai_sent_at')->nullable()->after('undangan_recipients');
            $table->json('nilai_recipients')->nullable()->after('nilai_sent_at');
            $table->timestamp('borang_sent_at')->nullable()->after('nilai_recipients');
            $table->json('borang_recipients')->nullable()->after('borang_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seminars', function (Blueprint $table) {
            $table->dropColumn([
                'undangan_sent_at',
                'undangan_recipients',
                'nilai_sent_at',
                'nilai_recipients',
                'borang_sent_at',
                'borang_recipients',
            ]);
        });
    }
};
