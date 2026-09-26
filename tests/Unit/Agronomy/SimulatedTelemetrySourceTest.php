<?php

namespace Tests\Unit\Agronomy;

use App\Models\Taman;
use App\Services\Telemetry\SimulatedTelemetrySource;
use Tests\TestCase;

class SimulatedTelemetrySourceTest extends TestCase
{
    public function test_same_taman_and_time_produce_same_values(): void
    {
        $taman = new Taman(['type' => 'corn', 'sensor_types' => ['moisture', 'ph']]);
        $taman->id = 12;
        $time = new \DateTimeImmutable('2026-09-19T03:12:00Z');
        $source = new SimulatedTelemetrySource();

        $this->assertSame($source->read($taman, $time), $source->read($taman, $time));
    }

    public function test_only_configured_metrics_are_generated(): void
    {
        $taman = new Taman([
            'type' => 'corn',
            'sensor_config' => [
                'schema' => 1,
                'sensors' => [
                    'moisture' => ['enabled' => true],
                    'ph' => ['enabled' => false],
                ],
            ],
        ]);
        $taman->id = 12;

        $values = (new SimulatedTelemetrySource())->read($taman, new \DateTimeImmutable('2026-09-19T03:12:00Z'));

        $this->assertSame(['moisture'], array_keys($values));
    }
}
