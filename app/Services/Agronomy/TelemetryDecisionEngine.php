<?php

namespace App\Services\Agronomy;

class TelemetryDecisionEngine
{
    private const METRICS = ['moisture', 'ph', 'temperature', 'ec'];

    public function evaluate(array $telemetry, string $regime = 'custom', ?string $soilKey = null): array
    {
        $rules = config('nutrix_agronomy');
        $soil = $this->soilProfile($soilKey);
        $targets = $rules['targets'][$regime] ?? $rules['targets']['custom'];
        $metrics = [];
        $scores = [];
        $available = [];

        foreach (self::METRICS as $metric) {
            $value = $telemetry[$metric] ?? null;
            $result = match ($metric) {
                'moisture' => $this->moisture($value, $soil),
                'ph' => $this->ph($value, $targets['ph']),
                'temperature' => $this->temperature($value, $targets['temperature']),
                'ec' => $this->ec($value, $targets['ec']),
            };
            $metrics[$metric] = $result;

            if ($result['severity'] !== 'unknown' && $result['severity'] !== 'fault') {
                $available[] = $metric;
                $scores[$metric] = $result['score'];
            }
        }

        $weightTotal = array_sum(array_intersect_key($rules['weights'], $scores));
        $score = $weightTotal > 0
            ? round(array_sum(array_map(fn ($metric) => $scores[$metric] * $rules['weights'][$metric], array_keys($scores))) / $weightTotal * 100)
            : null;
        $coverage = round(array_sum(array_intersect_key($rules['weights'], array_flip($available))) / array_sum($rules['weights']), 2);
        $worst = $scores === [] ? null : min($scores);
        if ($score !== null && $worst !== null) {
            $score = min($score, $worst < 0.25 ? 49 : ($worst < 0.5 ? 74 : 100));
        }

        $warnCount = count(array_filter($metrics, fn ($metric) => in_array($metric['severity'], ['warn', 'critical'], true)));
        $critical = count(array_filter($metrics, fn ($metric) => $metric['severity'] === 'critical')) > 0;
        $watch = count(array_filter($metrics, fn ($metric) => $metric['severity'] === 'watch')) > 0;
        $tier = $critical || $warnCount >= 2 ? 'A' : ($warnCount > 0 || $watch ? 'B' : 'C');

        return [
            'metrics' => $metrics,
            'health' => ['score' => $score, 'coverage' => $coverage, 'capped_by' => $worst !== null && $worst < 0.5 ? 'limiting_factor' : null],
            'decision' => [
                'tier' => $tier,
                'scope' => $coverage >= 1 ? 'full' : ($coverage >= 0.5 ? 'partial' : 'limited'),
                'confidence' => round(min(1, $coverage * $soil['confidence']), 2),
                'missing_for_full' => array_values(array_diff(self::METRICS, $available)),
            ],
            'engine' => ['version' => $rules['version'], 'ruleset' => $rules['ruleset']],
        ];
    }

    private function soilProfile(?string $soilKey): array
    {
        $soils = config('nutrix_catalog.soils');
        $key = $soilKey ?: 'unspecified';
        foreach ($soils as $candidate => $profile) {
            if ($candidate === $key || in_array($key, $profile['aliases'], true)) {
                return $profile;
            }
        }
        return $soils['unspecified'];
    }

    private function unavailable(): array
    {
        return ['value' => null, 'band' => 'unavailable', 'severity' => 'unknown', 'score' => null];
    }

    private function moisture(mixed $value, array $soil): array
    {
        if ($value === null) return $this->unavailable();
        $value = (float) $value;
        if ($value < 0 || $value > 100) return ['value' => $value, 'band' => 'sensor_fault', 'severity' => 'fault', 'score' => null];
        $fraction = ($value - $soil['theta_pwp']) / max(1, $soil['theta_fc'] - $soil['theta_pwp']);
        $band = $fraction < 0.2 ? 'very_dry' : ($fraction < 0.5 ? 'dry' : ($value <= $soil['theta_fc'] ? 'adequate' : ($value <= $soil['theta_fc'] + 0.6 * ($soil['theta_sat'] - $soil['theta_fc']) ? 'wet' : 'saturated')));
        $severity = in_array($band, ['very_dry', 'saturated'], true) ? 'critical' : ($band === 'dry' || $band === 'wet' ? 'watch' : 'ok');
        return ['value' => $value, 'band' => $band, 'severity' => $severity, 'score' => max(0, min(1, 1 - abs($fraction - 0.7) / 0.7))];
    }

    private function ph(mixed $value, array $target): array
    {
        if ($value === null) return $this->unavailable();
        $value = (float) $value;
        if ($value < 0 || $value > 14) return ['value' => $value, 'band' => 'sensor_fault', 'severity' => 'fault', 'score' => null];
        $distance = $value < $target[0] ? $target[0] - $value : max(0, $value - $target[1]);
        $band = $distance === 0 ? 'optimal' : ($value < $target[0] ? ($distance <= 0.5 ? 'slightly_acidic' : 'acidic') : ($distance <= 0.5 ? 'slightly_alkaline' : 'alkaline'));
        $severity = $distance === 0 ? 'ok' : ($distance <= 0.5 ? 'watch' : ($distance <= 1 ? 'warn' : 'critical'));
        return ['value' => $value, 'band' => $band, 'severity' => $severity, 'score' => max(0, 1 - $distance / 2)];
    }

    private function temperature(mixed $value, array $target): array
    {
        if ($value === null) return $this->unavailable();
        $value = (float) $value;
        if ($value < -20 || $value > 80) return ['value' => $value, 'band' => 'sensor_fault', 'severity' => 'fault', 'score' => null];
        $distance = $value < $target[0] ? $target[0] - $value : max(0, $value - $target[1]);
        $band = $distance === 0 ? 'optimal' : ($value < $target[0] ? ($distance <= 3 ? 'cool' : 'cold') : ($distance <= 3 ? 'warm' : 'hot'));
        $severity = $distance === 0 ? 'ok' : ($distance <= 3 ? 'watch' : ($distance <= 6 ? 'warn' : 'critical'));
        return ['value' => $value, 'band' => $band, 'severity' => $severity, 'score' => max(0, 1 - $distance / 6)];
    }

    private function ec(mixed $value, float $upper): array
    {
        if ($value === null) return $this->unavailable();
        $value = (float) $value;
        if ($value < 0 || $value > 20) return ['value' => $value, 'band' => 'sensor_fault', 'severity' => 'fault', 'score' => null];
        $band = $value >= $upper ? 'high' : ($value < 0.3 ? 'low' : 'optimal');
        $severity = $band === 'high' ? ($value >= $upper + 4.2 ? 'critical' : 'warn') : ($band === 'low' ? 'watch' : 'ok');
        return ['value' => $value, 'band' => $band, 'severity' => $severity, 'score' => $band === 'high' ? max(0, 1 - ($value - $upper) / 4.2) : ($band === 'low' ? 0.7 : 1.0)];
    }
}
