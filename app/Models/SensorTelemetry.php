<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorTelemetry extends Model
{
    public $timestamps = false; // pakai recorded_at manual

    protected $fillable = [
        'taman_id',
        'ph',
        'moisture',
        'moisture_unit',
        'temperature',
        'ec',
        'health_score',
        'health_status',
        'recorded_at',
        'source',
        'quality',
    ];

    protected $casts = [
        'ph'           => 'float',
        'moisture'     => 'float',
        'temperature'  => 'float',
        'ec'           => 'float',
        'health_score' => 'float',
        'recorded_at'  => 'datetime',
        'quality' => 'array',
    ];

    public function taman(): BelongsTo
    {
        return $this->belongsTo(Taman::class);
    }
}
