<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('email_otps') && ! Schema::hasColumn('email_otps', 'failed_attempts')) {
            Schema::table('email_otps', function (Blueprint $table): void {
                $table->unsignedTinyInteger('failed_attempts')->default(0)->after('expires_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('email_otps', 'failed_attempts')) {
            Schema::table('email_otps', function (Blueprint $table): void {
                $table->dropColumn('failed_attempts');
            });
        }
    }
};