<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tamans', function (Blueprint $table): void {
            if (!Schema::hasColumn('tamans', 'sensor_config')) {
                $table->json('sensor_config')->nullable()->after('sensor_models');
            }
            if (!Schema::hasColumn('tamans', 'device_name')) {
                $table->string('device_name')->nullable()->default('Kelompok Nutrix')->after('device_token');
            }
            if (!Schema::hasColumn('tamans', 'wifi_ssid')) {
                $table->string('wifi_ssid')->nullable()->default('GG')->after('device_name');
            }
            if (!Schema::hasColumn('tamans', 'ip_address')) {
                $table->string('ip_address')->nullable()->after('wifi_ssid');
            }
        });

        Schema::table('sensor_telemetries', function (Blueprint $table): void {
            if (!Schema::hasColumn('sensor_telemetries', 'metadata')) {
                $table->json('metadata')->nullable()->after('health_status');
            }
            if (!Schema::hasColumn('sensor_telemetries', 'sensor_source')) {
                $table->string('sensor_source', 64)->default('esp32_device')->after('metadata');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tamans', function (Blueprint $table): void {
            $table->dropColumn(['sensor_config', 'device_name', 'wifi_ssid', 'ip_address']);
        });

        Schema::table('sensor_telemetries', function (Blueprint $table): void {
            $table->dropColumn(['metadata', 'sensor_source']);
        });
    }
};
