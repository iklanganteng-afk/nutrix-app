<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tamans', function (Blueprint $table): void {
            $table->json('sensor_types')->nullable()->after('sensor_connected_at');
            $table->json('sensor_models')->nullable()->after('sensor_types');
            $table->string('controller_type')->nullable()->after('sensor_models');
            $table->json('device_connection')->nullable()->after('controller_type');
        });
    }

    public function down(): void
    {
        Schema::table('tamans', function (Blueprint $table): void {
            $table->dropColumn(['sensor_types', 'sensor_models', 'controller_type', 'device_connection']);
        });
    }
};
