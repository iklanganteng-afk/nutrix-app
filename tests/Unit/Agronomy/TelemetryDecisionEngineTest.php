<?php

namespace Tests\Unit\Agronomy;

use App\Services\Agronomy\TelemetryDecisionEngine;
use Tests\TestCase;

class TelemetryDecisionEngineTest extends TestCase
{
    public function test_partial_sensor_subset_reports_unavailable_metrics_and_weighted_coverage(): void
    {
        $result = (new TelemetryDecisionEngine())->evaluate([
            'moisture' => 27.0,
            'ph' => 6.4,
        ], 'corn', 'latosol');

        $this->assertSame('adequate', $result['metrics']['moisture']['band']);
        $this->assertSame('optimal', $result['metrics']['ph']['band']);
        $this->assertSame('unknown', $result['metrics']['temperature']['severity']);
        $this->assertNull($result['metrics']['temperature']['value']);
        $this->assertSame(0.6, $result['health']['coverage']);
        $this->assertSame(['temperature', 'ec'], $result['decision']['missing_for_full']);
    }

    public function test_out_of_range_sensor_is_fault_and_is_excluded_from_score(): void
    {
        $result = (new TelemetryDecisionEngine())->evaluate([
            'moisture' => 140,
            'ph' => 6.4,
        ], 'corn', 'latosol');

        $this->assertSame('sensor_fault', $result['metrics']['moisture']['band']);
        $this->assertSame('fault', $result['metrics']['moisture']['severity']);
        $this->assertNull($result['metrics']['moisture']['score']);
        $this->assertSame(0.25, $result['health']['coverage']);
    }

    public function test_critical_domain_produces_immediate_tier_a(): void
    {
        $result = (new TelemetryDecisionEngine())->evaluate([
            'moisture' => 2,
            'ph' => 6.4,
            'temperature' => 26,
            'ec' => 1.0,
        ], 'corn', 'pasir');

        $this->assertSame('critical', $result['metrics']['moisture']['severity']);
        $this->assertSame('A', $result['decision']['tier']);
        $this->assertLessThanOrEqual(49, $result['health']['score']);
    }
}
