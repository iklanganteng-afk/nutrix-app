<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FarmActivity;
use App\Models\SensorTelemetry;
use App\Models\Taman;
use App\Services\Agronomy\TelemetryDecisionEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TelemetryController extends Controller
{
    public function __construct(
        private readonly TelemetryDecisionEngine $decisionEngine,
    ) {
    }

    /**
     * GET /api/taman/{taman}/telemetry
     * Kembalikan data sensor terbaru dan 20 riwayat terakhir.
     */
    public function index(Taman $taman)
    {
        $this->authorizeOwner($taman);

        if (! $taman->sensor_connected) {
            return response()->json([
                'taman_id' => $taman->id,
                'connected' => false,
                'sensor_id' => null,
                'device_token' => $taman->device_token,
                'last_seen_at' => $taman->last_seen_at?->toISOString(),
                'latest' => null,
                'history' => [],
            ]);
        }

        $latest = $taman->latestHardwareTelemetry;
        $history = $taman->hardwareTelemetries()->limit(20)->get(['ph','moisture','temperature','ec','health_score','recorded_at','source']);

        return response()->json([
            'taman_id' => $taman->id,
            'connected' => true,
            'sensor_id' => $taman->sensor_id,
            'device_token' => $taman->device_token,
            'last_seen_at' => $taman->last_seen_at?->toISOString(),
            'latest'   => $latest,
            'history'  => $history,
        ]);
    }

    public function latest(Taman $taman)
    {
        $this->authorizeOwner($taman);
        $latest = $taman->latestHardwareTelemetry;
        $data = $latest?->only(['ph', 'moisture', 'temperature', 'ec']) ?? [];
        $analysis = $this->decisionEngine->evaluate($data, $taman->type, $taman->soil_type);
        $available = array_keys(array_filter($data, fn ($value) => $value !== null));
        $ageSeconds = $taman->last_seen_at?->diffInSeconds(now());
        $isLive = $ageSeconds !== null && $ageSeconds <= 60;
        $lifecycle = $ageSeconds === null
            ? 'never_received'
            : ($ageSeconds <= 60 ? 'live' : ($ageSeconds <= 600 ? 'stale' : 'offline'));

        return response()->json([
            'schema' => 1,
            'taman_id' => $taman->id,
            'recorded_at' => $latest?->recorded_at?->toISOString(),
            'source' => $latest?->sensor_source ?? $latest?->source,
            'lifecycle' => $lifecycle,
            'connected' => $isLive,
            'is_live' => $isLive,
            'sensor_id' => $taman->sensor_id,
            'device_token' => $taman->device_token,
            'device_name' => $taman->device_name ?? 'Kelompok Nutrix',
            'wifi_ssid' => $taman->wifi_ssid ?? 'GG',
            'ip_address' => $taman->ip_address,
            'last_seen_at' => $taman->last_seen_at?->toISOString(),
            'sensor_config' => $taman->sensor_config,
            'metadata' => $latest?->metadata,
            'soil' => ['key' => $taman->soil_type ?: 'unspecified', 'profile' => ($taman->soil_type ?: 'unspecified') . '@v1'],
            'available_sensors' => $available,
            'missing_sensors' => array_values(array_diff(['moisture', 'ph', 'temperature', 'ec'], $available)),
            'metrics' => $analysis['metrics'],
            'health' => $analysis['health'],
            'decision' => $analysis['decision'],
            'engine' => $analysis['engine'],
        ]);
    }

    /**
     * POST /api/taman/{taman}/sync
     * Cek status transmisi riil dari hardware ESP32 (Zero Ghost Data).
     */
    public function sync(Taman $taman)
    {
        $this->authorizeOwner($taman);

        $isLive = $taman->last_seen_at && now()->diffInSeconds($taman->last_seen_at) <= 60;
        $latest = $taman->latestHardwareTelemetry;

        if (! $taman->sensor_connected) {
            return response()->json([
                'success' => false,
                'connected' => false,
                'message' => 'Sensor belum terhubung. Konfigurasikan token pada ESP32 terlebih dahulu.',
            ], 409);
        }

        if (! $isLive && ! $latest) {
            return response()->json([
                'success' => false,
                'connected' => true,
                'is_live' => false,
                'message' => 'ESP32 belum mengirimkan data. Pastikan perangkat menyala dan terhubung ke hotspot GG.',
            ], 404);
        }

        $analysis = $latest ? $this->decisionEngine->evaluate(
            $latest->only(['ph', 'moisture', 'temperature', 'ec']),
            $taman->type,
            $taman->soil_type
        ) : null;

        FarmActivity::create([
            'taman_id' => $taman->id,
            'user_id'  => Auth::id() ?? $taman->user_id,
            'type'     => 'sync',
            'title'    => 'Pemeriksaan status telemetry ESP32',
            'detail'   => $isLive ? 'Hardware ESP32 aktif mentransmisikan data riil.' : 'Hardware ESP32 sedang offline.',
            'status'   => $isLive ? 'success' : 'info',
        ]);

        return response()->json([
            'success' => true,
            'is_live' => $isLive,
            'telemetry' => $latest,
            'analysis' => $analysis,
            'message' => $isLive ? 'Data sensor riil tersinkronisasi.' : 'Menampilkan rekam data terakhir ESP32.',
        ]);
    }

    private function healthStatus(?float $score): string
    {
        return $score === null ? 'unknown' : ($score >= 80 ? 'optimal' : ($score >= 55 ? 'warning' : 'critical'));
    }

    private function lifecycle(?\DateTimeInterface $recordedAt): string
    {
        if ($recordedAt === null) {
            return 'never_received';
        }

        $age = now()->diffInSeconds($recordedAt);
        return $age > 3000 ? 'offline' : ($age > 600 ? 'stale' : 'live');
    }

    /**
     * POST /api/taman/{taman}/actions/water
     * Catat aksi penyiraman (siram).
     */
    public function water(Request $request, Taman $taman)
    {
        $this->authorizeOwner($taman);
        abort_unless($taman->sensor_connected, 409, 'Sensor belum terhubung.');
        $validated = $request->validate([
            'duration_sec' => ['nullable', 'integer', 'min:1', 'max:60'],
        ]);

        $duration = min(10, max(1, (int) ($validated['duration_sec'] ?? 5)));
        $commandId = (string) Str::uuid();

        // Simpan antrean perintah manual untuk ESP32 (berlaku 60 detik)
        \Illuminate\Support\Facades\Cache::put("esp32_cmd_water_{$taman->id}", [
            'command_id'   => $commandId,
            'duration_sec' => $duration,
        ], 60);

        $telemetry = $this->applyManualAdjustment($taman, 'water', [
            'duration_sec' => $duration,
        ]);

        $activity = FarmActivity::create([
            'taman_id' => $taman->id,
            'user_id'  => Auth::id(),
            'type'     => 'water',
            'title'    => "Solenoid Valve dinyalakan — {$duration} detik",
            'detail'   => "Perintah penyiraman manual dikirim oleh {$request->user()->name}.",
            'status'   => 'success',
            'metadata' => ['command_id' => $commandId, 'duration_sec' => $duration, 'trigger' => 'manual_dashboard'],
        ]);

        return response()->json([
            'success'   => true,
            'message'   => "Perintah penyiraman {$duration} detik berhasil dikirim ke ESP32!",
            'activity'  => $activity,
            'telemetry' => $telemetry
        ]);
    }

    /**
     * POST /api/taman/{taman}/actions/fertilize
     * Catat aksi pemupukan.
     */
    public function fertilize(Request $request, Taman $taman)
    {
        $this->authorizeOwner($taman);
        abort_unless($taman->sensor_connected, 409, 'Sensor belum terhubung.');
        $validated = $request->validate([
            'fertilizer_type' => ['nullable', 'string', 'max:100'],
            'volume_ml'       => ['nullable', 'integer', 'min:10', 'max:5000'],
        ]);

        $type   = $validated['fertilizer_type'] ?? 'NPK';
        $volume = $validated['volume_ml'] ?? 200;
        $telemetry = $this->applyManualAdjustment($taman, 'fertilize', [
            'fertilizer_type' => $type,
            'volume_ml' => $volume,
        ]);

        $activity = FarmActivity::create([
            'taman_id' => $taman->id,
            'user_id'  => Auth::id(),
            'type'     => 'fertilize',
            'title'    => "Pemupukan {$type} — {$volume}ml",
            'detail'   => "Pemupukan manual oleh {$request->user()->name}.",
            'status'   => 'success',
            'metadata' => ['fertilizer_type' => $type, 'volume_ml' => $volume],
        ]);

        return response()->json(['success' => true, 'activity' => $activity, 'telemetry' => $telemetry]);
    }

    /**
     * POST /api/taman/{taman}/token/generate
     * Generate token unik 32-karakter untuk pairing mikrokontroler ESP32 secara aman.
     */
     public function generateDeviceToken(Taman $taman)
     {
         $this->authorizeOwner($taman);

         $token = 'NTX-' . strtoupper(Str::random(12));
         $expiresAt = now()->addDays(30);

         $taman->update([
             'device_token' => $token,
             'device_token_expires_at' => $expiresAt,
         ]);

         FarmActivity::create([
             'taman_id' => $taman->id,
             'user_id'  => Auth::id(),
             'type'     => 'connection',
             'title'    => 'Token Pairing Baru Dibuat',
             'detail'   => "Token perangkat: {$token} (Aktif 30 hari)",
             'status'   => 'info',
         ]);

         return response()->json([
             'success' => true,
             'token' => $token,
             'expires_at' => $expiresAt->toISOString(),
             'message' => 'Token pairing berhasil dibuat. Masukkan token ini pada form WiFi ESP32.',
         ]);
     }

    public function connectSensor(Request $request, Taman $taman)
    {
        $this->authorizeOwner($taman);
        $validated = $request->validate([
            'sensor_id' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/', 'unique:tamans,sensor_id,' . $taman->id],
            'board_type' => ['nullable', 'in:esp32,esp8266,arduino'],
        ]);

        if (! empty($validated['board_type']) && $validated['board_type'] !== $taman->controller_type) {
            return response()->json([
                'success' => false,
                'code' => 'board_mismatch',
                'message' => 'Board yang dipasangkan berbeda dari board yang dipilih pada konfigurasi taman.',
            ], 422);
        }

        $taman->update(['sensor_id' => $validated['sensor_id']]);
        $ageSeconds = $taman->last_seen_at?->diffInSeconds(now());
        $isLive = $ageSeconds !== null && $ageSeconds <= 60;

        return response()->json([
            'success' => true,
            'configured' => true,
            'connected' => $isLive,
            'is_live' => $isLive,
            'sensor_id' => $taman->sensor_id,
            'board_type' => $taman->controller_type,
            'device_token' => $taman->device_token,
        ]);
    }

    public function disconnectSensor(Taman $taman)
    {
        $this->authorizeOwner($taman);

        $taman->update([
            'sensor_id' => null,
            'sensor_connected' => false,
            'sensor_connected_at' => null,
            'device_token' => null,
            'device_token_expires_at' => null,
        ]);

        FarmActivity::create([
            'taman_id' => $taman->id,
            'user_id'  => Auth::id(),
            'type'     => 'connection',
            'title'    => 'Koneksi sensor diputus',
            'detail'   => 'Sensor dan token pairing telah direset.',
            'status'   => 'warning',
        ]);

        return response()->json(['success' => true, 'connected' => false]);
    }

    /**
     * GET /api/taman/{taman}/activities
     * Riwayat aktivitas taman, 50 terakhir.
     */
    public function activities(Taman $taman)
    {
        $this->authorizeOwner($taman);

        $activities = $taman->activities()
            ->with('user:id,name')
            ->limit(50)
            ->get();

        return response()->json(['activities' => $activities]);
    }

    /**
     * GET /api/taman/{taman}/export.csv
     * Export telemetry sebagai CSV.
     */
    public function exportCsv(Taman $taman)
    {
        $this->authorizeOwner($taman);

        $rows = $taman->hardwareTelemetries()->limit(1000)->get();

        $csv = "recorded_at,ph,moisture,temperature,ec,health_score,health_status\n";
        foreach ($rows as $row) {
            $csv .= implode(',', [
                $row->recorded_at->toISOString(),
                $row->ph ?? '',
                $row->moisture ?? '',
                $row->temperature ?? '',
                $row->ec ?? '',
                $row->health_score ?? '',
                $row->health_status,
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . str($taman->name)->slug() . '-telemetry.csv"',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  HELPERS
    // ──────────────────────────────────────────────────────────────

    private function authorizeOwner(Taman $taman): void
    {
        $token = request()->query('token') ?? request()->header('X-Device-Token') ?? request()->input('device_token');
        if ($token && $taman->device_token && hash_equals($taman->device_token, $token)) {
            return;
        }

        abort_unless($taman->user_id === Auth::id(), 403, 'Akses ditolak.');
    }

    private function applyManualAdjustment(Taman $taman, string $action, array $meta = []): SensorTelemetry
    {
        $baseline = $taman->latestTelemetry ?? new SensorTelemetry([
            'ph' => 6.3,
            'moisture' => 60,
            'temperature' => 27.0,
            'ec' => 1.2,
        ]);

        $data = [
            'ph' => $baseline->ph ?? 6.3,
            'moisture' => $baseline->moisture ?? 60,
            'temperature' => $baseline->temperature ?? 27.0,
            'ec' => $baseline->ec ?? 1.2,
        ];

        if ($action === 'water') {
            $duration = (int) ($meta['duration_sec'] ?? 30);
            $data['moisture'] = min(100, round($data['moisture'] + ($duration * 0.6), 2));
            $data['temperature'] = max(18, round($data['temperature'] - min(3, $duration * 0.0333), 2));
            $data['ph'] = max(4.5, min(8.5, round($data['ph'] + 0.2, 2)));
        }

        if ($action === 'fertilize') {
            $volume = (int) ($meta['volume_ml'] ?? 200);
            $data['ec'] = min(4.0, round($data['ec'] + 0.3, 2));
            $data['ph'] = max(4.5, min(8.5, round($data['ph'] + 0.2, 2)));
            $data['moisture'] = min(100, round($data['moisture'] + ($volume / 1000) * 4, 2));
        }

        $telemetry = SensorTelemetry::create([
            'taman_id' => $taman->id,
            'ph' => $data['ph'],
            'moisture' => $data['moisture'],
            'temperature' => $data['temperature'],
            'ec' => $data['ec'],
            ...$this->hydrateTelemetryState($this->baselineForType($taman->type), $data),
            'recorded_at' => now(),
        ]);

        return $telemetry;
    }

    private function baselineForType(string $type): array
    {
        return match ($type) {
            'corn' => ['ph' => 6.5, 'moisture' => 62, 'temperature' => 27.5, 'ec' => 1.4],
            'greenhouse' => ['ph' => 6.0, 'moisture' => 75, 'temperature' => 25.0, 'ec' => 1.8],
            'rice' => ['ph' => 5.8, 'moisture' => 88, 'temperature' => 29.0, 'ec' => 0.9],
            default => ['ph' => 6.5, 'moisture' => 65, 'temperature' => 27.0, 'ec' => 1.2],
        };
    }

    private function hydrateTelemetryState(array $baseline, array $current): array
    {
        $ph = max(4.0, min(9.0, (float) ($current['ph'] ?? $baseline['ph'])));
        $moisture = max(0, min(100, (float) ($current['moisture'] ?? $baseline['moisture'])));
        $temperature = max(10, min(50, (float) ($current['temperature'] ?? $baseline['temperature'])));
        $ec = max(0, min(4.0, (float) ($current['ec'] ?? $baseline['ec'])));

        $phScore = max(0, 100 - abs($ph - $baseline['ph']) * 20);
        $humScore = max(0, 100 - abs($moisture - $baseline['moisture']) * 1.5);
        $tempScore = max(0, 100 - abs($temperature - $baseline['temperature']) * 5);
        $ecScore = max(0, 100 - abs($ec - $baseline['ec']) * 25);
        $health = round(($phScore + $humScore + $tempScore + $ecScore) / 4, 1);
        $status = $health >= 80 ? 'optimal' : ($health >= 55 ? 'warning' : 'critical');

        return [
            'ph' => round($ph, 2),
            'moisture' => round($moisture, 2),
            'temperature' => round($temperature, 2),
            'ec' => round($ec, 3),
            'health_score' => $health,
            'health_status' => $status,
        ];
    }

    /**
     * POST /api/iot/telemetry
     * Endpoint publik yang dipanggil oleh ESP32 (Wireless WiFi).
     * Mendukung autentikasi via device_token (Claim Token) atau taman_id.
     */
    public function ingestDeviceTelemetry(Request $request)
    {
        $validated = $request->validate([
            'device_token' => ['nullable', 'string', 'max:64'],
            'taman_id'     => ['nullable', 'integer'],
            'sensor_id'    => ['nullable', 'string', 'max:64'],
            'device_name'  => ['nullable', 'string', 'max:64'],
            'wifi_rssi'    => ['nullable', 'integer'],
            'wifi_ssid'    => ['nullable', 'string', 'max:64'],
            'hostname'     => ['nullable', 'string', 'max:64'],
            'ip_address'   => ['nullable', 'string', 'max:45'],
            'moisture'     => ['required', 'numeric', 'min:0', 'max:100'],
            'moisture_cap' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'moisture_res' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'raw_cap'      => ['nullable', 'integer', 'min:0', 'max:4095'],
            'raw_res'      => ['nullable', 'integer', 'min:0', 'max:4095'],
            'last_command_id' => ['nullable', 'uuid'],
            'last_command_status' => ['nullable', 'in:executed,failed'],
            'relay_state' => ['nullable', 'in:on,off'],
            'temperature'  => ['nullable', 'numeric', 'min:-10', 'max:60'],
            'ph'           => ['nullable', 'numeric', 'min:0', 'max:14'],
            'ec'           => ['nullable', 'numeric', 'min:0', 'max:10'],
            'sensors'      => ['nullable', 'array'],
            'sensors.capacitive_v2' => ['nullable', 'array'],
            'sensors.capacitive_v2.gpio' => ['nullable', 'integer', 'min:0', 'max:39'],
            'sensors.capacitive_v2.moisture' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sensors.capacitive_v2.raw_adc' => ['nullable', 'integer', 'min:0', 'max:4095'],
            'sensors.capacitive_v2.voltage' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'sensors.resistive_hd38' => ['nullable', 'array'],
            'sensors.resistive_hd38.gpio' => ['nullable', 'integer', 'min:0', 'max:39'],
            'sensors.resistive_hd38.moisture' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sensors.resistive_hd38.raw_adc' => ['nullable', 'integer', 'min:0', 'max:4095'],
            'sensors.resistive_hd38.voltage' => ['nullable', 'numeric', 'min:0', 'max:5'],
        ]);

        $deviceToken = $validated['device_token'] ?? $request->header('X-Device-Token');
        if (! is_string($deviceToken) || $deviceToken === '' || strlen($deviceToken) > 64) {
            return response()->json([
                'status' => 'error',
                'code' => 'DEVICE_TOKEN_REQUIRED',
                'message' => 'Token pairing perangkat wajib disertakan.',
            ], 401);
        }

        // taman_id may be sent by older firmware, but only the pairing token selects a farm.
        $taman = Taman::where('device_token', $deviceToken)->first();
        if (! $taman || ($taman->device_token_expires_at && $taman->device_token_expires_at->isPast())) {
            return response()->json([
                'status' => 'error',
                'code' => 'INVALID_DEVICE_TOKEN',
                'message' => 'Token pairing tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        if (! empty($validated['last_command_id']) && ! empty($validated['last_command_status'])) {
            $pendingActivity = $taman->activities()
                ->where('type', 'water')
                ->latest()
                ->limit(20)
                ->get()
                ->first(fn (FarmActivity $activity) => ($activity->metadata['command_id'] ?? null) === $validated['last_command_id']);

            if ($pendingActivity && $pendingActivity->status === 'info') {
                $activityMetadata = $pendingActivity->metadata ?? [];
                $activityMetadata['acknowledged_at'] = now()->toISOString();
                $activityMetadata['relay_state'] = $validated['relay_state'] ?? null;
                $pendingActivity->update([
                    'status' => $validated['last_command_status'] === 'executed' ? 'success' : 'failed',
                    'detail' => $validated['last_command_status'] === 'executed'
                        ? 'ESP32 mengonfirmasi perintah relay telah dijalankan.'
                        : 'ESP32 melaporkan perintah relay gagal dijalankan.',
                    'metadata' => $activityMetadata,
                ]);
            }
        }

        // Update status sensor taman menjadi aktif/online & catat heartbeat
        $taman->update([
            'sensor_connected' => true,
            'sensor_connected_at' => $taman->sensor_connected_at ?? now(),
            'last_seen_at' => now(),
            'sensor_id' => $validated['sensor_id'] ?? $taman->sensor_id ?? ('ESP32-' . $taman->id),
            'device_name' => $validated['device_name'] ?? $taman->device_name ?? 'Kelompok Nutrix',
            'ip_address' => $validated['ip_address'] ?? $taman->ip_address,
        ]);

        $telemetryData = [
            'moisture' => (float) $validated['moisture'],
            'temperature' => isset($validated['temperature']) ? (float) $validated['temperature'] : null,
            'ph' => isset($validated['ph']) ? (float) $validated['ph'] : null,
            'ec' => isset($validated['ec']) ? (float) $validated['ec'] : null,
        ];

        // Otak Sistem: Hitung analisa kesehatan via TelemetryDecisionEngine
        $analysis = $this->decisionEngine->evaluate($telemetryData, $taman->type, $taman->soil_type);

        $sensorDiagnostics = $validated['sensors'] ?? [];
        $capacitive = array_filter([
            'moisture' => $validated['moisture_cap'] ?? data_get($sensorDiagnostics, 'capacitive_v2.moisture'),
            'raw_adc' => $validated['raw_cap'] ?? data_get($sensorDiagnostics, 'capacitive_v2.raw_adc'),
        ], fn ($value) => $value !== null);
        $resistive = array_filter([
            'moisture' => $validated['moisture_res'] ?? data_get($sensorDiagnostics, 'resistive_hd38.moisture'),
            'raw_adc' => $validated['raw_res'] ?? data_get($sensorDiagnostics, 'resistive_hd38.raw_adc'),
        ], fn ($value) => $value !== null);
        if ($capacitive !== []) {
            $sensorDiagnostics['capacitive_v2'] = array_replace(
                data_get($sensorDiagnostics, 'capacitive_v2', []),
                $capacitive
            );
        }
        if ($resistive !== []) {
            $sensorDiagnostics['resistive_hd38'] = array_replace(
                data_get($sensorDiagnostics, 'resistive_hd38', []),
                $resistive
            );
        }

        $metadata = [
            'wifi_rssi' => $validated['wifi_rssi'] ?? null,
            'ip_address' => $validated['ip_address'] ?? null,
            'device_name' => $validated['device_name'] ?? 'Kelompok Nutrix',
            'wifi_ssid' => $validated['wifi_ssid'] ?? null,
            'hostname' => $validated['hostname'] ?? null,
            'sensors' => $sensorDiagnostics ?: null,
            'actuator' => array_filter([
                'relay_state' => $validated['relay_state'] ?? null,
                'last_command_id' => $validated['last_command_id'] ?? null,
                'last_command_status' => $validated['last_command_status'] ?? null,
            ], fn ($value) => $value !== null) ?: null,
        ];

        $record = SensorTelemetry::create([
            'taman_id' => $taman->id,
            'ph' => $telemetryData['ph'],
            'moisture' => $telemetryData['moisture'],
            'temperature' => $telemetryData['temperature'],
            'ec' => $telemetryData['ec'],
            'moisture_unit' => 'vwc_pct',
            'health_score' => $analysis['health']['score'],
            'health_status' => $this->healthStatus($analysis['health']['score']),
            'source' => 'esp32_device',
            'sensor_source' => 'esp32_device',
            'metadata' => $metadata,
            'recorded_at' => now(),
        ]);

        $threshold = (float) config('nutrix_iot.auto_water_below', 30);
        $cooldownMinutes = max(1, (int) config('nutrix_iot.auto_water_cooldown_minutes', 30));
        $waterDuration = max(1, min(10, (int) config('nutrix_iot.auto_water_duration_sec', 10)));
        $cooldownActive = $taman->last_auto_watered_at
            && $taman->last_auto_watered_at->greaterThan(now()->subMinutes($cooldownMinutes));
        $shouldWater = $telemetryData['moisture'] < $threshold && ! $cooldownActive;
        $commandId = $shouldWater ? (string) Str::uuid() : null;

        // 1. Cek apakah ada antrean perintah manual dari dashboard web
        $manualWaterCmd = \Illuminate\Support\Facades\Cache::pull("esp32_cmd_water_{$taman->id}");

        if ($manualWaterCmd) {
            $valveAction = 'ON';
            $activeCommandId = $manualWaterCmd['command_id'];
            $activeDuration = $manualWaterCmd['duration_sec'];
            $taman->update(['last_auto_watered_at' => now()]);
        } elseif ($shouldWater) {
            $valveAction = 'ON';
            $activeCommandId = $commandId;
            $activeDuration = $waterDuration;
            $taman->update(['last_auto_watered_at' => now()]);
            FarmActivity::create([
                'taman_id' => $taman->id,
                'user_id'  => $taman->user_id,
                'type'     => 'water',
                'title'    => 'Perintah penyiraman otomatis dikirim',
                'detail'   => "Kelembapan {$telemetryData['moisture']}% di bawah ambang {$threshold}%. Menunggu konfirmasi ESP32.",
                'status'   => 'info',
                'metadata' => ['command_id' => $commandId, 'duration_sec' => $waterDuration, 'trigger' => 'auto_decision_engine'],
            ]);
        } else {
            $valveAction = 'OFF';
            $activeCommandId = null;
            $activeDuration = 0;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Telemetry recorded successfully',
            'taman_id' => $taman->id,
            'taman_name' => $taman->name,
            'health_score' => $analysis['health']['score'],
            'health_status' => $this->healthStatus($analysis['health']['score']),
            'commands' => [
                'water_valve' => $valveAction,
                'command_id' => $activeCommandId,
                'duration_sec' => $activeDuration,
                'buzzer_alert' => $valveAction === 'ON',
            ],
        ]);
    }
}

