<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_telemetries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('taman_id');
            $table->foreign('taman_id')->references('id')->on('tamans')->cascadeOnDelete();
            $table->decimal('ph', 4, 2)->nullable();           // 0.00 – 14.00
            $table->decimal('moisture', 5, 2)->nullable();     // 0.00 – 100.00 %
            $table->decimal('temperature', 5, 2)->nullable();  // °C
            $table->decimal('ec', 6, 3)->nullable();           // mS/cm
            $table->decimal('health_score', 5, 2)->nullable(); // 0 – 100
            $table->string('health_status', 32)->default('unknown'); // optimal|warning|critical
            $table->timestamp('recorded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_telemetries');
    }
};
