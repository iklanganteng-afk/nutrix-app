<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Taman extends Model
{
    use HasFactory;

    protected $table = 'tamans';

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'location',
        'soil_type',
        'indicator_mode',
        'sensor_id',
        'sensor_connected',
        'sensor_connected_at',
        'sensor_types',
        'sensor_models',
        'controller_type',
        'device_connection',
        'sensor_config',
    ];

    protected $casts = [
        'sensor_connected' => 'boolean',
        'sensor_connected_at' => 'datetime',
        'sensor_types' => 'array',
        'sensor_models' => 'array',
        'device_connection' => 'array',
        'sensor_config' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function telemetries(): HasMany
    {
        return $this->hasMany(SensorTelemetry::class)->latest('recorded_at');
    }

    public function latestTelemetry()
    {
        return $this->hasOne(SensorTelemetry::class)->latestOfMany('recorded_at');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(FarmActivity::class)->latest();
    }
}