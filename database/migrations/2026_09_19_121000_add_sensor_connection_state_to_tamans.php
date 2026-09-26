<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tamans', function (Blueprint $table): void {
            $table->string('sensor_id')->nullable()->unique()->after('location');
            $table->boolean('sensor_connected')->default(false)->after('sensor_id');
            $table->timestamp('sensor_connected_at')->nullable()->after('sensor_connected');
        });
    }

    public function down(): void
    {
        Schema::table('tamans', function (Blueprint $table): void {
            $table->dropUnique('tamans_sensor_id_unique');
            $table->dropColumn(['sensor_id', 'sensor_connected', 'sensor_connected_at']);
        });
    }
};
