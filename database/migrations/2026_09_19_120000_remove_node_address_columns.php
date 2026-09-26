<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'node_address')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropUnique('users_node_address_unique');
                $table->dropColumn('node_address');
            });
        }

        if (Schema::hasColumn('tamans', 'node_address')) {
            Schema::table('tamans', function (Blueprint $table): void {
                $table->dropUnique('tamans_node_address_unique');
                $table->dropColumn('node_address');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'node_address')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('node_address')->nullable()->unique()->after('email');
            });
        }

        if (! Schema::hasColumn('tamans', 'node_address')) {
            Schema::table('tamans', function (Blueprint $table): void {
                $table->string('node_address')->nullable()->unique()->after('location');
            });
        }
    }
};
