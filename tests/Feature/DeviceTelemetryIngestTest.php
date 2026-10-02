<?php

namespace Tests\Feature;

use App\Models\SensorTelemetry;
use App\Models\Taman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceTelemetryIngestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_pairing_token_selects_its_farm_instead_of_the_submitted_farm_id(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $pairedFarm = Taman::create([
            'user_id' => $user->id,
            'name' => 'Paired Farm',
            'type' => 'corn',
            'device_token' => 'NTX-PAIRED-DEVICE',
            'device_token_expires_at' => now()->addDay(),
        ]);
        $otherFarm = Taman::create([
            'user_id' => $user->id,
            'name' => 'Other Farm',
            'type' => 'corn',
            'device_token' => 'NTX-OTHER-DEVICE',
            'device_token_expires_at' => now()->addDay(),
        ]);

        $response = $this->postJson('/api/iot/telemetry', [
            'device_token' => 'NTX-PAIRED-DEVICE',
            'taman_id' => $otherFarm->id,
            'sensor_id' => 'ESP32-PAIRED-01',
            'moisture' => 42.5,
        ]);

        $response->assertOk()->assertJsonPath('taman_id', $pairedFarm->id);
        $this->assertSame(1, $pairedFarm->telemetries()->count());
        $this->assertSame(0, $otherFarm->telemetries()->count());
    }

    public function test_telemetry_without_a_pairing_token_is_rejected_even_with_a_farm_id(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $farm = Taman::create([
            'user_id' => $user->id,
            'name' => 'Token Required Farm',
            'type' => 'corn',
        ]);

        $this->postJson('/api/iot/telemetry', [
            'taman_id' => $farm->id,
            'moisture' => 42.5,
        ])->assertUnauthorized();

        $this->assertSame(0, SensorTelemetry::count());
        $this->assertFalse($farm->fresh()->sensor_connected);
    }

    public function test_telemetry_with_an_expired_pairing_token_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $farm = Taman::create([
            'user_id' => $user->id,
            'name' => 'Expired Token Farm',
            'type' => 'corn',
            'device_token' => 'NTX-EXPIRED-DEVICE',
            'device_token_expires_at' => now()->subMinute(),
        ]);

        $this->postJson('/api/iot/telemetry', [
            'device_token' => 'NTX-EXPIRED-DEVICE',
            'taman_id' => $farm->id,
            'moisture' => 42.5,
        ])->assertUnauthorized();

        $this->assertSame(0, SensorTelemetry::count());
        $this->assertFalse($farm->fresh()->sensor_connected);
    }

    public function test_creating_a_farm_does_not_mark_its_device_online(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->post(route('taman.store'), [
            'name' => 'Waiting Farm',
            'type' => 'corn',
            'sensor_types' => ['moisture'],
        ])->assertRedirect();

        $farm = Taman::firstOrFail();
        $this->assertFalse($farm->sensor_connected);
        $this->assertNull($farm->last_seen_at);
    }

    public function test_saving_a_sensor_id_does_not_mark_its_device_online(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $farm = Taman::create([
            'user_id' => $user->id,
            'name' => 'Pairing Farm',
            'type' => 'corn',
            'controller_type' => 'esp32',
            'device_token' => 'NTX-WAITING-DEVICE',
            'device_token_expires_at' => now()->addDay(),
        ]);

        $this->actingAs($user)
            ->postJson("/api/taman/{$farm->id}/sensor/connect", [
                'sensor_id' => 'ESP32-WAITING-01',
                'board_type' => 'esp32',
            ])
            ->assertOk()
            ->assertJsonPath('configured', true)
            ->assertJsonPath('connected', false);

        $this->assertFalse($farm->fresh()->sensor_connected);
        $this->assertNull($farm->fresh()->last_seen_at);
    }

    public function test_ingest_persists_both_moisture_probe_readings_and_adc_diagnostics(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $farm = Taman::create([
            'user_id' => $user->id,
            'name' => 'Dual Probe Farm',
            'type' => 'corn',
            'device_token' => 'NTX-DUAL-PROBE',
            'device_token_expires_at' => now()->addDay(),
        ]);

        $this->postJson('/api/iot/telemetry', [
            'device_token' => 'NTX-DUAL-PROBE',
            'sensor_id' => 'ESP32-DUAL-01',
            'moisture' => 51.2,
            'wifi_rssi' => -55,
            'sensors' => [
                'capacitive_v2' => ['gpio' => 34, 'raw_adc' => 2100, 'voltage' => 1.69, 'moisture' => 48.4],
                'resistive_hd38' => ['gpio' => 35, 'raw_adc' => 1850, 'voltage' => 1.49, 'moisture' => 54.0],
            ],
        ])->assertOk();

        $metadata = $farm->latestTelemetry->metadata;
        $this->assertSame(34, $metadata['sensors']['capacitive_v2']['gpio']);
        $this->assertEquals(48.4, $metadata['sensors']['capacitive_v2']['moisture']);
        $this->assertSame(2100, $metadata['sensors']['capacitive_v2']['raw_adc']);
        $this->assertEquals(1.69, $metadata['sensors']['capacitive_v2']['voltage']);
        $this->assertSame(35, $metadata['sensors']['resistive_hd38']['gpio']);
        $this->assertEquals(54.0, $metadata['sensors']['resistive_hd38']['moisture']);
        $this->assertSame(1850, $metadata['sensors']['resistive_hd38']['raw_adc']);
        $this->assertSame(-55, $metadata['wifi_rssi']);
    }

    public function test_old_telemetry_without_a_hardware_heartbeat_is_not_reported_as_live(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $farm = Taman::create([
            'user_id' => $user->id,
            'name' => 'No Heartbeat Farm',
            'type' => 'corn',
            'sensor_connected' => true,
        ]);
        SensorTelemetry::create([
            'taman_id' => $farm->id,
            'moisture' => 42.5,
            'source' => 'simulator',
            'recorded_at' => now(),
        ]);

        $this->actingAs($user)
            ->getJson("/api/taman/{$farm->id}/telemetry/latest")
            ->assertOk()
            ->assertJsonPath('lifecycle', 'never_received')
            ->assertJsonPath('connected', false)
            ->assertJsonPath('is_live', false)
            ->assertJsonPath('recorded_at', null)
            ->assertJsonPath('source', null)
            ->assertJsonPath('metrics.moisture.value', null);
    }

    public function test_dashboard_does_not_invent_hardware_diagnostic_values(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $farm = Taman::create([
            'user_id' => $user->id,
            'name' => 'Empty Hardware Farm',
            'type' => 'corn',
        ]);

        $this->actingAs($user)
            ->get(route('taman.show', $farm))
            ->assertOk()
            ->assertSee('id="val-cap-adc" class="text-white">--</span>', false)
            ->assertSee('id="val-res-adc" class="text-white">--</span>', false)
            ->assertSee('id="miniRssi">-- dBm</strong>', false)
            ->assertSee('id="displayNodeIp">--</span>', false);
    }

    public function test_automatic_watering_is_not_reissued_on_every_low_moisture_post(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $farm = Taman::create([
            'user_id' => $user->id,
            'name' => 'Automatic Water Farm',
            'type' => 'corn',
            'device_token' => 'NTX-AUTO-WATER',
            'device_token_expires_at' => now()->addDay(),
        ]);
        $payload = [
            'device_token' => 'NTX-AUTO-WATER',
            'moisture' => 25,
        ];

        $firstResponse = $this->postJson('/api/iot/telemetry', $payload)
            ->assertOk()
            ->assertJsonPath('commands.water_valve', 'ON');
        $commandId = $firstResponse->json('commands.command_id');
        $this->assertNotEmpty($commandId);

        $this->postJson('/api/iot/telemetry', [
            ...$payload,
            'last_command_id' => $commandId,
            'last_command_status' => 'executed',
            'relay_state' => 'off',
        ])
            ->assertOk()
            ->assertJsonPath('commands.water_valve', 'OFF');

        $activity = $farm->activities()->where('type', 'water')->firstOrFail();
        $this->assertSame(1, $farm->activities()->where('type', 'water')->count());
        $this->assertSame('success', $activity->status);
        $this->assertSame($commandId, $activity->metadata['command_id']);
    }
}