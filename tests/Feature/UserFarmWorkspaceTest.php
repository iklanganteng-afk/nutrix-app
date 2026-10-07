<?php

namespace Tests\Feature;

use App\Models\SensorTelemetry;
use App\Models\Taman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFarmWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_dashboard_starts_empty_and_uses_the_user_scope(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        Taman::create([
            'user_id' => $otherUser->id,
            'name' => 'Other Farm',
            'type' => 'corn',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertViewHas('dashboardState', 'setup')
            ->assertViewHas('tamans', fn ($tamans) => $tamans->isEmpty());
    }

    public function test_user_can_create_a_farm_and_is_redirected_to_its_detail(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->post(route('taman.store'), [
            'name' => 'Kebun Utara',
            'type' => 'corn',
            'location' => 'Blok A',
            'sensor_types' => ['moisture', 'temperature', 'ph', 'ec'],
            'sensor_models' => [
                'moisture' => 'SEN0193',
                'temperature' => 'DHT22',
                'ph' => 'PH-4502C',
                'ec' => 'DFRobot EC',
            ],
            'controller_type' => 'esp32',
            'device_connection' => [
                'computer_port' => 'Wireless (WiFi)',
                'device_port' => 'WiFi 2.4GHz HTTP Cloud',
                'wifi_ssid' => 'GG',
                'hostname' => 'Kelompok-Nutrix',
                'note' => 'ESP32 terpasang di panel kebun dan mengirim telemetri secara wireless ke cloud.',
            ],
        ]);

        $taman = Taman::firstOrFail();
        $response->assertRedirect(route('taman.show', $taman));
        $this->assertSame($user->id, $taman->user_id);
        $this->assertSame(['moisture', 'temperature', 'ph', 'ec'], $taman->sensor_types);
        $this->assertSame('esp32', $taman->controller_type);
        $this->assertSame('Wireless (WiFi)', $taman->device_connection['computer_port']);
    }

    public function test_add_farm_wizard_describes_wireless_esp32_setup_only(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Wi-Fi captive portal')
            ->assertSee('USB hanya untuk upload firmware dan Serial Monitor')
            ->assertDontSee('Kabel Serial (USB)')
            ->assertDontSee('name="sensor_types[]" value="ph"', false)
            ->assertDontSee('name="sensor_types[]" value="temperature"', false)
            ->assertDontSee('name="sensor_types[]" value="ec"', false);
    }

    public function test_creating_a_sensor_aware_farm_waits_for_real_device_telemetry(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->post(route('taman.store'), [
            'name' => 'Partial Sensor Farm',
            'type' => 'corn',
            'sensor_types' => ['moisture', 'ph'],
        ]);

        $taman = Taman::firstOrFail();
        $this->assertNull($taman->latestHardwareTelemetry);
        $this->assertSame(0, $taman->telemetries()->count());
        $this->assertSame(1, $taman->sensor_config['schema']);
    }

    public function test_user_cannot_read_or_mutate_another_users_farm(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Owner Farm',
            'type' => 'corn',
        ]);

        $this->actingAs($otherUser)->get(route('taman.show', $taman))->assertForbidden();
        $this->actingAs($otherUser)->patch(route('taman.update', $taman), [
            'name' => 'Hijacked',
            'type' => 'rice',
        ])->assertForbidden();
        $this->actingAs($otherUser)->delete(route('taman.destroy', $taman))->assertForbidden();
        $this->assertDatabaseHas('tamans', ['id' => $taman->id, 'user_id' => $owner->id, 'name' => $taman->name]);
    }

    public function test_owner_detail_page_shows_fixed_hardware_specs_without_sensor_configuration_modal(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Configured Farm',
            'type' => 'corn',
            'sensor_connected' => true,
            'sensor_types' => ['moisture', 'temperature', 'ph'],
            'sensor_models' => [
                'moisture' => 'SEN0193',
                'temperature' => 'DHT22',
                'ph' => 'PH-4502C',
            ],
            'controller_type' => 'esp32',
            'device_connection' => [
                'computer_port' => 'Wireless (WiFi)',
                'device_port' => 'WiFi 2.4GHz HTTP Cloud',
                'wifi_ssid' => 'GG',
                'hostname' => 'Kelompok-Nutrix',
            ],
        ]);

        $this->actingAs($owner)
            ->get(route('taman.show', $taman))
            ->assertOk()
            ->assertSee('Capacitive V2.0')
            ->assertSee('GPIO 34 / D34')
            ->assertSee('HD-38')
            ->assertSee('GPIO 35 / D35')
            ->assertSee('Normally Closed')
            ->assertSee('GPIO 2 / D2')
            ->assertDontSee('sensorConfigModal', false)
            ->assertDontSee('btnEditSensorConfig', false)
            ->assertSee('ESP32')
            ->assertSee('Wireless (WiFi)');
    }

    public function test_owner_can_view_hardware_wiring_guide_on_detail_page(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Wiring Guide Farm',
            'type' => 'greenhouse',
            'sensor_connected' => true,
            'sensor_types' => ['moisture', 'temperature'],
            'controller_type' => 'esp32',
            'device_connection' => [
                'wifi_ssid' => 'GG',
                'hostname' => 'Kelompok-Nutrix',
                'note' => 'ESP32 menempel di panel kebun dan terhubung ke hotspot GG.',
            ],
        ]);

        $this->actingAs($owner)
            ->get(route('taman.show', $taman))
            ->assertOk()
            ->assertSee('Panduan pairing nirkabel')
            ->assertSee('GG')
            ->assertSee('Kelompok-Nutrix')
            ->assertSee('ESP32');
    }

    public function test_owner_detail_page_keeps_supported_pairing_and_omits_dead_qr_controls(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Pairing Options Farm',
            'type' => 'greenhouse',
            'sensor_connected' => false,
        ]);

        $this->actingAs($owner)
            ->get(route('taman.show', $taman))
            ->assertOk()
            ->assertSee('id="connectSensorModal"', false)
            ->assertDontSee('btnScanQR', false)
            ->assertDontSee('btnManualID', false);
    }

    public function test_owner_can_view_device_status_and_connection_detail_on_detail_page(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Device Status Farm',
            'type' => 'greenhouse',
            'sensor_id' => 'SENSOR-STATUS-001',
            'sensor_connected' => true,
            'controller_type' => 'esp32',
            'device_connection' => [
                'wifi_ssid' => 'GG',
                'hostname' => 'Kelompok-Nutrix',
                'note' => 'Koneksi WiFi stabil, sinyal kuat.',
            ],
        ]);

        $this->actingAs($owner)
            ->get(route('taman.show', $taman))
            ->assertOk()
            ->assertSee('Status perangkat')
            ->assertSee('SENSOR-STATUS-001')
            ->assertSee('GG')
            ->assertSee('Kelompok-Nutrix');
    }

    public function test_owner_detail_page_omits_unimplemented_reconnect_controls(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Reconnect Farm',
            'type' => 'greenhouse',
            'sensor_connected' => true,
            'sensor_id' => 'SENSOR-RECONNECT-001',
        ]);

        $this->actingAs($owner)
            ->get(route('taman.show', $taman))
            ->assertOk()
            ->assertDontSee('btnReconnect', false)
            ->assertDontSee('btnResetConn', false);
    }

    public function test_dashboard_detail_does_not_render_non_hardware_telemetry(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Manual Estimate Farm',
            'type' => 'corn',
            'sensor_types' => ['moisture', 'ph', 'temperature', 'ec'],
        ]);
        SensorTelemetry::create([
            'taman_id' => $taman->id,
            'moisture' => 73.0,
            'ph' => 6.8,
            'temperature' => 25.0,
            'ec' => 1.4,
            'health_score' => 94,
            'source' => 'manual_action',
            'sensor_source' => 'manual_action',
            'recorded_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get(route('taman.show', $taman))
            ->assertOk()
            ->assertSee('id="val-hum">--</span>', false)
            ->assertSee('id="val-consensus-moisture">--</h2>', false)
            ->assertSee('id="aiHealthScore">--</span>', false)
            ->assertDontSee('id="val-hum">73.0</span>', false)
            ->assertDontSee('id="val-consensus-moisture">73.0</h2>', false);
    }

    public function test_user_can_record_a_water_action_only_for_owned_farm(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Water Farm',
            'type' => 'greenhouse',
            'sensor_id' => 'SENSOR-WATER-001',
            'sensor_connected' => true,
        ]);

        $response = $this->actingAs($owner)->postJson("/api/taman/{$taman->id}/actions/water", [
            'duration_sec' => 30,
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('farm_activities', [
            'taman_id' => $taman->id,
            'user_id' => $owner->id,
            'type' => 'water',
            'status' => 'success',
        ]);
    }

    public function test_user_cannot_record_activity_for_another_users_farm(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Private Farm',
            'type' => 'rice',
            'sensor_id' => 'SENSOR-PRIVATE-001',
            'sensor_connected' => true,
        ]);

        $this->actingAs($otherUser)
            ->postJson("/api/taman/{$taman->id}/actions/water", ['duration_sec' => 30])
            ->assertForbidden();

        $this->assertDatabaseCount('farm_activities', 0);
    }

    public function test_water_action_updates_latest_sensor_telemetry(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Water Response Farm',
            'type' => 'greenhouse',
            'sensor_id' => 'SENSOR-WATER-RESPONSE',
            'sensor_connected' => true,
        ]);

        SensorTelemetry::create([
            'taman_id' => $taman->id,
            'ph' => 6.3,
            'moisture' => 58,
            'temperature' => 27.4,
            'ec' => 1.2,
            'health_score' => 82,
            'health_status' => 'optimal',
            'recorded_at' => now(),
        ]);

        $this->actingAs($owner)
            ->postJson("/api/taman/{$taman->id}/actions/water", ['duration_sec' => 30])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('telemetry.moisture', 76)
            ->assertJsonPath('telemetry.temperature', 26.4);

        $this->assertDatabaseHas('farm_activities', [
            'taman_id' => $taman->id,
            'user_id' => $owner->id,
            'type' => 'water',
            'status' => 'success',
        ]);
    }

    public function test_fertilize_action_updates_sensor_ec_and_health_status(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Fertilizer Response Farm',
            'type' => 'corn',
            'sensor_id' => 'SENSOR-FERT-RESPONSE',
            'sensor_connected' => true,
        ]);

        SensorTelemetry::create([
            'taman_id' => $taman->id,
            'ph' => 6.5,
            'moisture' => 63,
            'temperature' => 27.0,
            'ec' => 1.1,
            'health_score' => 85,
            'health_status' => 'optimal',
            'recorded_at' => now(),
        ]);

        $this->actingAs($owner)
            ->postJson("/api/taman/{$taman->id}/actions/fertilize", [
                'fertilizer_type' => 'NPK',
                'volume_ml' => 200,
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('telemetry.ec', 1.4)
            ->assertJsonPath('telemetry.ph', 6.7);

        $this->assertDatabaseHas('farm_activities', [
            'taman_id' => $taman->id,
            'user_id' => $owner->id,
            'type' => 'fertilize',
            'status' => 'success',
        ]);
    }

    public function test_user_can_record_a_fertilize_action_for_owned_farm(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Fertilizer Farm',
            'type' => 'custom',
            'sensor_id' => 'SENSOR-FERT-001',
            'sensor_connected' => true,
        ]);

        $this->actingAs($owner)
            ->postJson("/api/taman/{$taman->id}/actions/fertilize", [
                'fertilizer_type' => 'NPK',
                'volume_ml' => 200,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('farm_activities', [
            'taman_id' => $taman->id,
            'user_id' => $owner->id,
            'type' => 'fertilize',
            'status' => 'success',
        ]);
    }

    public function test_disconnected_farm_returns_zero_state_without_sensor_data(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Unconnected Farm',
            'type' => 'corn',
        ]);

        $this->actingAs($owner)
            ->getJson("/api/taman/{$taman->id}/telemetry")
            ->assertOk()
            ->assertJson(['connected' => false, 'latest' => null, 'history' => []]);

        $this->actingAs($owner)
            ->postJson("/api/taman/{$taman->id}/sync")
            ->assertStatus(409)
            ->assertJsonPath('connected', false);
    }

    public function test_sensor_aware_latest_payload_exposes_availability_and_decision(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Sensor Aware Farm',
            'type' => 'corn',
            'soil_type' => 'latosol',
            'sensor_connected' => true,
            'last_seen_at' => now(),
            'sensor_config' => [
                'schema' => 1,
                'sensors' => [
                    'moisture' => ['enabled' => true],
                    'ph' => ['enabled' => true],
                ],
            ],
        ]);
        SensorTelemetry::create([
            'taman_id' => $taman->id,
            'moisture' => 27.0,
            'ph' => 6.4,
            'source' => 'esp32_device',
            'sensor_source' => 'esp32_device',
            'recorded_at' => now(),
        ]);

        $this->actingAs($owner)
            ->getJson("/api/taman/{$taman->id}/telemetry/latest")
            ->assertOk()
            ->assertJsonPath('lifecycle', 'live')
            ->assertJsonPath('available_sensors.0', 'ph')
            ->assertJsonPath('health.coverage', 0.6)
            ->assertJsonPath('metrics.temperature.value', null)
            ->assertJsonPath('decision.scope', 'partial');
    }

    public function test_owner_can_pair_a_sensor_for_future_telemetry(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Pairing Farm',
            'type' => 'corn',
        ]);

        $this->actingAs($owner)
            ->postJson("/api/taman/{$taman->id}/sensor/connect", ['sensor_id' => 'SENSOR-FIELD-001'])
            ->assertOk()
            ->assertJson(['configured' => true, 'connected' => false, 'sensor_id' => 'SENSOR-FIELD-001']);

        $this->assertDatabaseHas('tamans', [
            'id' => $taman->id,
            'sensor_id' => 'SENSOR-FIELD-001',
            'sensor_connected' => 0,
        ]);
    }

    public function test_owner_can_reset_sensor_connection_and_log_it_as_activity(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Reset Farm',
            'type' => 'corn',
            'sensor_id' => 'SENSOR-RESET-001',
            'sensor_connected' => true,
        ]);

        $this->actingAs($owner)
            ->deleteJson("/api/taman/{$taman->id}/sensor")
            ->assertOk()
            ->assertJson(['connected' => false]);

        $this->assertDatabaseHas('tamans', [
            'id' => $taman->id,
            'sensor_id' => null,
            'sensor_connected' => 0,
        ]);

        $this->assertDatabaseHas('farm_activities', [
            'taman_id' => $taman->id,
            'user_id' => $owner->id,
            'type' => 'connection',
            'status' => 'warning',
        ]);
    }

    public function test_owner_can_view_wireless_hardware_metrics_on_detail_page(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Wireless Metrics Farm',
            'type' => 'greenhouse',
            'sensor_connected' => true,
            'controller_type' => 'esp32',
            'device_connection' => [
                'wifi_ssid' => 'GG',
                'hostname' => 'Kelompok-Nutrix',
            ],
        ]);

        $this->actingAs($owner)
            ->get(route('taman.show', $taman))
            ->assertOk()
            ->assertSee('Voltage')
            ->assertSee('RSSI')
            ->assertSee('Signal')
            ->assertSee('3.3V')
            ->assertSee('WiFi 2.4GHz / HTTP POST');
    }

    public function test_owner_can_view_pairing_sequence_on_detail_page(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Pairing Farm',
            'type' => 'greenhouse',
            'sensor_connected' => true,
            'controller_type' => 'esp32',
            'device_connection' => [
                'wifi_ssid' => 'GG',
                'hostname' => 'Kelompok-Nutrix',
            ],
        ]);

        $this->actingAs($owner)
            ->get(route('taman.show', $taman))
            ->assertOk()
            ->assertSee('Pairing flow')
            ->assertSee('Board detected')
            ->assertSee('Network verified')
            ->assertSee('Sensor ID validated');
    }

    public function test_owner_can_view_live_telemetry_stream_status_on_detail_page(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Stream Farm',
            'type' => 'greenhouse',
            'sensor_connected' => true,
            'controller_type' => 'esp32',
            'device_connection' => [
                'wifi_ssid' => 'GG',
                'hostname' => 'Kelompok-Nutrix',
            ],
        ]);

        $this->actingAs($owner)
            ->get(route('taman.show', $taman))
            ->assertOk()
            ->assertSee('Live telemetry stream')
            ->assertSee('API polling active');
    }

    public function test_owner_can_view_board_health_indicators_on_detail_page(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Health Farm',
            'type' => 'greenhouse',
            'sensor_connected' => true,
            'controller_type' => 'esp32',
            'device_connection' => [
                'wifi_ssid' => 'GG',
                'hostname' => 'Kelompok-Nutrix',
            ],
        ]);

        $this->actingAs($owner)
            ->get(route('taman.show', $taman))
            ->assertOk()
            ->assertSee('Board health')
            ->assertSee('Signal quality')
            ->assertSee('Link integrity');
    }

    public function test_owner_can_view_auto_reconnect_alert_on_detail_page(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Reconnect Farm',
            'type' => 'greenhouse',
            'sensor_connected' => true,
            'controller_type' => 'esp32',
            'device_connection' => [
                'wifi_ssid' => 'GG',
                'hostname' => 'Kelompok-Nutrix',
            ],
        ]);

        $this->actingAs($owner)
            ->get(route('taman.show', $taman))
            ->assertOk()
            ->assertSee('Auto reconnect')
            ->assertSee('Signal alert')
            ->assertSee('Reconnect policy');
    }

    public function test_owner_can_view_wireless_event_stream_on_detail_page(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Event Stream Farm',
            'type' => 'greenhouse',
            'sensor_connected' => true,
            'controller_type' => 'esp32',
            'device_connection' => [
                'wifi_ssid' => 'GG',
                'hostname' => 'Kelompok-Nutrix',
            ],
        ]);

        $this->actingAs($owner)
            ->get(route('taman.show', $taman))
            ->assertOk()
            ->assertSee('Wireless event stream')
            ->assertSee('Connected');
    }

    public function test_pairing_rejects_a_board_type_that_does_not_match_configuration(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $taman = Taman::create([
            'user_id' => $owner->id,
            'name' => 'Board Match Farm',
            'type' => 'corn',
            'controller_type' => 'arduino',
        ]);

        $this->actingAs($owner)
            ->postJson("/api/taman/{$taman->id}/sensor/connect", [
                'sensor_id' => 'ESP-DEVICE-001',
                'board_type' => 'esp32',
            ])
            ->assertStatus(422)
            ->assertJsonPath('code', 'board_mismatch');

        $this->assertDatabaseHas('tamans', [
            'id' => $taman->id,
            'sensor_connected' => false,
        ]);
    }
}
