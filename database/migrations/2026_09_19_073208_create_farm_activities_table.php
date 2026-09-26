<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('taman_id');
            $table->foreign('taman_id')->references('id')->on('tamans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32);        // sync|water|fertilize|alert|export
            $table->string('title', 255);
            $table->text('detail')->nullable();
            $table->string('status', 32)->default('success'); // success|pending|failed
            $table->json('metadata')->nullable();  // payload tambahan (durasi, volume, dll)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_activities');
    }
};
