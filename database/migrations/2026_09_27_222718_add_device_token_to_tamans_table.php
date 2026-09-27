<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tamans', function (Blueprint $table): void {
            // Token rahasia untuk pairing ESP32 (hashed di DB, plain ditampilkan sekali ke user)
            $table->string('device_token', 64)->nullable()->unique()->after('sensor_id');
            // Token kadaluarsa setelah 24 jam (bisa regenerate)
            $table->timestamp('device_token_expires_at')->nullable()->after('device_token');
            // Waktu terakhir ESP32 mengirim data (heartbeat)
            $table->timestamp('last_seen_at')->nullable()->after('sensor_connected_at');
        });
    }

    public function down(): void
    {
        Schema::table('tamans', function (Blueprint $table): void {
            $table->dropColumn(['device_token', 'device_token_expires_at', 'last_seen_at']);
        });
    }
};
