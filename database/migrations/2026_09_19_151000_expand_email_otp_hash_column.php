<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('email_otps')) {
            Schema::table('email_otps', function (Blueprint $table): void {
                $table->string('otp', 255)->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('email_otps')) {
            Schema::table('email_otps', function (Blueprint $table): void {
                $table->string('otp', 6)->change();
            });
        }
    }
};