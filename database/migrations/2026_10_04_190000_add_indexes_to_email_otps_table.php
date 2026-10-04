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
        if (Schema::hasTable('email_otps')) {
            Schema::table('email_otps', function (Blueprint $table): void {
                $table->index(['email', 'action', 'expires_at'], 'email_otps_lookup_idx');
                $table->index(['email', 'action', 'created_at'], 'email_otps_ratelimit_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('email_otps')) {
            Schema::table('email_otps', function (Blueprint $table): void {
                $table->dropIndex('email_otps_lookup_idx');
                $table->dropIndex('email_otps_ratelimit_idx');
            });
        }
    }
};
