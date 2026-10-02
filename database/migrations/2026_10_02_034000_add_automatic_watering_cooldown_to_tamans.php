<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tamans', 'last_auto_watered_at')) {
            Schema::table('tamans', function (Blueprint $table): void {
                $table->timestamp('last_auto_watered_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tamans', 'last_auto_watered_at')) {
            Schema::table('tamans', function (Blueprint $table): void {
                $table->dropColumn('last_auto_watered_at');
            });
        }
    }
};