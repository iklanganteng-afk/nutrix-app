<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tamans', function (Blueprint $table): void {
            $table->string('soil_type', 40)->nullable()->after('location');
            $table->string('indicator_mode', 24)->default('active_only')->after('soil_type');
            $table->json('sensor_config')->nullable()->after('device_connection');
        });

        Schema::table('sensor_telemetries', function (Blueprint $table): void {
            $table->string('moisture_unit', 24)->nullable()->after('moisture');
            $table->string('source', 24)->default('simulator')->after('recorded_at');
            $table->json('quality')->nullable()->after('source');
            $table->index(['taman_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::table('sensor_telemetries', function (Blueprint $table): void {
            $table->dropIndex(['taman_id', 'recorded_at']);
            $table->dropColumn(['moisture_unit', 'source', 'quality']);
        });

        Schema::table('tamans', function (Blueprint $table): void {
            $table->dropColumn(['soil_type', 'indicator_mode', 'sensor_config']);
        });
    }
};
