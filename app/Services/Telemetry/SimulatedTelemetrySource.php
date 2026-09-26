<?php

namespace App\Services\Telemetry;

use App\Models\Taman;
use DateTimeInterface;

class SimulatedTelemetrySource
{
    public function read(Taman $taman, ?DateTimeInterface $at = null): array
    {
        $at ??= now();
        $slot = intdiv($at->getTimestamp(), 300);
        $seed = crc32($taman->id . ':' . $slot);
        $phase = ($seed % 1000) / 1000;
        $baseline = match ($taman->type) {
            'greenhouse' => ['ph' => 6.2, 'moisture' => 31.0, 'temperature' => 24.0, 'ec' => 1.4],
            'rice' => ['ph' => 6.0, 'moisture' => 42.0, 'temperature' => 28.0, 'ec' => 0.9],
            default => ['ph' => 6.4, 'moisture' => 28.0, 'temperature' => 25.0, 'ec' => 1.1],
        };
        $values = [
            'ph' => round($baseline['ph'] + (($phase - 0.5) * 0.2), 2),
            'moisture' => round(max(0, min(100, $baseline['moisture'] + (($phase - 0.5) * 4))), 2),
            'temperature' => round($baseline['temperature'] + (($phase - 0.5) * 2), 2),
            'ec' => round(max(0, $baseline['ec'] + (($phase - 0.5) * 0.2)), 3),
        ];

        $active = $this->activeMetrics($taman);
        return array_intersect_key($values, array_flip($active));
    }

    private function activeMetrics(Taman $taman): array
    {
        $config = $taman->sensor_config;
        if (is_array($config) && isset($config['sensors'])) {
            return array_keys(array_filter($config['sensors'], fn ($sensor) => ($sensor['enabled'] ?? false) === true));
        }

        if (is_array($taman->sensor_types) && $taman->sensor_types !== []) {
            return $taman->sensor_types;
        }

        return ['moisture', 'ph', 'temperature', 'ec'];
    }
}
