<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FarmActivity;
use App\Models\SensorTelemetry;
use App\Models\Taman;
use App\Services\Agronomy\TelemetryDecisionEngine;
use App\Services\Telemetry\SimulatedTelemetrySource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TelemetryController extends Controller
{
    public function __construct(
        private readonly SimulatedTelemetrySource $telemetrySource,
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
                'latest' => null,
                'history' => [],
            ]);
        }

        $latest = $taman->latestTelemetry;
        $history = $taman->telemetries()->limit(20)->get(['ph','moisture','temperature','ec','health_score','recorded_at']);

        return response()->json([
            'taman_id' => $taman->id,
            'connected' => true,
            'sensor_id' => $taman->sensor_id,
            'latest'   => $latest,
            'history'  => $history,
        ]);
    }

    public function latest(Taman $taman)
    {
        $this->authorizeOwner($taman);
        $latest = $taman->latestTelemetry;
        $data = $latest?->only(['ph', 'moisture', 'temperature', 'ec']) ?? [];
        $analysis = $this->decisionEngine->evaluate($data, $taman->type, $taman->soil_type);
        $available = array_keys(array_filter($data, fn ($value) => $value !== null));
        $lifecycle = $this->lifecycle($latest?->recorded_at);

        return response()->json([
            'schema' => 1,
            'taman_id' => $taman->id,
            'recorded_at' => $latest?->recorded_at?->toISOString(),
            'source' => $latest?->source ?? 'simulator',
            'lifecycle' => $lifecycle,
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
     * Generate simulasi rekam sensor baru dan simpan ke DB.
     * Fase production: ganti dengan pembacaan serial/MQTT dari perangkat keras.
     */
    public function sync(Taman $taman)
    {
        $this->authorizeOwner($taman);

        if (! $taman->sensor_connected) {
            return response()->json([
                'success' => false,
                'connected' => false,
                'message' => 'Sensor belum terhubung. Hubungkan sensor terlebih dahulu.',
            ], 409);
        }

        $data = $this->telemetrySource->read($taman);
        $analysis = $this->decisionEngine->evaluate($data, $taman->type, $taman->soil_type);
        $record = SensorTelemetry::create([
            'taman_id' => $taman->id,
            'ph' => $data['ph'] ?? null,
            'moisture' => $data['moisture'] ?? null,
            'temperature' => $data['temperature'] ?? null,
            'ec' => $data['ec'] ?? null,
            'moisture_unit' => array_key_exists('moisture', $data) ? 'vwc_pct' : null,
            'health_score' => $analysis['health']['score'],
            'health_status' => $this->healthStatus($analysis['health']['score']),
            'source' => 'simulator',
            'recorded_at' => now(),
        ]);

        FarmActivity::create([
            'taman_id' => $taman->id,
            'user_id'  => Auth::id(),
            'type'     => 'sync',
            'title'    => 'Sinkronisasi sensor selesai',
            'detail'   => sprintf('Telemetry simulator: %s sensor aktif.', count($data)),
            'status'   => 'success',
        ]);

        return response()->json(['success' => true, 'telemetry' => $record, 'analysis' => $analysis]);
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
            'duration_sec' => ['nullable', 'integer', 'min:5', 'max:3600'],
        ]);

        $duration = $validated['duration_sec'] ?? 30;
        $telemetry = $this->applyManualAdjustment($taman, 'water', [
            'duration_sec' => $duration,
        ]);

        $activity = FarmActivity::create([
            'taman_id' => $taman->id,
            'user_id'  => Auth::id(),
            'type'     => 'water',
            'title'    => "Pompa dinyalakan — {$duration} detik",
            'detail'   => "Penyiraman manual oleh {$request->user()->name}.",
            'status'   => 'success',
            'metadata' => ['duration_sec' => $duration],
        ]);

        return response()->json(['success' => true, 'activity' => $activity, 'telemetry' => $telemetry]);
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

        $taman->update([
            'sensor_id' => $validated['sensor_id'],
            'sensor_connected' => true,
            'sensor_connected_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'connected' => true,
            'sensor_id' => $taman->sensor_id,
            'board_type' => $taman->controller_type,
        ]);
    }

    public function disconnectSensor(Taman $taman)
    {
        $this->authorizeOwner($taman);

        $taman->update([
            'sensor_id' => null,
            'sensor_connected' => false,
            'sensor_connected_at' => null,
        ]);

        FarmActivity::create([
            'taman_id' => $taman->id,
            'user_id'  => Auth::id(),
            'type'     => 'connection',
            'title'    => 'Koneksi sensor diputus',
            'detail'   => 'Sensor telah direset dan menunggu koneksi ulang.',
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

        $rows = $taman->telemetries()->limit(1000)->get();

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
        abort_unless($taman->user_id === Auth::id(), 403, 'Akses ditolak.');
    }

    /**
     * Simulasi nilai sensor berbasis tipe taman + drift kecil dari data sebelumnya.
     * Fase production: ganti dengan pembacaan nyata dari perangkat keras RS-485.
     */
    private function simulateSensor(string $type, ?SensorTelemetry $prev): array
    {
        $baseline = match ($type) {
            'corn'        => ['ph' => 6.5, 'moisture' => 62, 'temperature' => 27.5, 'ec' => 1.4],
            'greenhouse'  => ['ph' => 6.0, 'moisture' => 75, 'temperature' => 25.0, 'ec' => 1.8],
            'rice'        => ['ph' => 5.8, 'moisture' => 88, 'temperature' => 29.0, 'ec' => 0.9],
            default       => ['ph' => 6.5, 'moisture' => 65, 'temperature' => 27.0, 'ec' => 1.2],
        };

        // Kalau ada rekam sebelumnya, drift kecil dari nilai itu (± 5%)
        if ($prev) {
            $drift = fn($val) => round($val + (lcg_value() - 0.5) * $val * 0.05, 2);
            $ph          = max(4.0, min(9.0,   $drift($prev->ph ?? $baseline['ph'])));
            $moisture    = max(0,   min(100,   $drift($prev->moisture ?? $baseline['moisture'])));
            $temperature = max(10,  min(50,    $drift($prev->temperature ?? $baseline['temperature'])));
            $ec          = max(0,   min(4.0,   $drift($prev->ec ?? $baseline['ec'])));
        } else {
            // Pertama kali, pakai baseline + noise kecil
            $noise       = fn($val) => round($val + (lcg_value() - 0.5) * $val * 0.03, 2);
            $ph          = $noise($baseline['ph']);
            $moisture    = $noise($baseline['moisture']);
            $temperature = $noise($baseline['temperature']);
            $ec          = $noise($baseline['ec']);
        }

        return $this->hydrateTelemetryState($baseline, [
            'ph' => $ph,
            'moisture' => $moisture,
            'temperature' => $temperature,
            'ec' => $ec,
        ]);
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
     */
    public function ingestDeviceTelemetry(Request $request)
    {
        $validated = $request->validate([
            'taman_id'  => ['required', 'integer', 'exists:tamans,id'],
            'sensor_id' => ['nullable', 'string', 'max:64'],
            'moisture'  => ['required', 'numeric', 'min:0', 'max:100'],
            'temperature' => ['nullable', 'numeric', 'min:-10', 'max:60'],
            'ph'        => ['nullable', 'numeric', 'min:0', 'max:14'],
            'ec'        => ['nullable', 'numeric', 'min:0', 'max:10'],
        ]);

        $taman = Taman::find($validated['taman_id']);

        // Update status sensor taman menjadi aktif/online
        $taman->update([
            'sensor_connected' => true,
            'sensor_connected_at' => now(),
            'sensor_id' => $validated['sensor_id'] ?? $taman->sensor_id ?? ('ESP32-' . $taman->id),
        ]);

        $telemetryData = [
            'moisture' => (float) $validated['moisture'],
            'temperature' => isset($validated['temperature']) ? (float) $validated['temperature'] : null,
            'ph' => isset($validated['ph']) ? (float) $validated['ph'] : null,
            'ec' => isset($validated['ec']) ? (float) $validated['ec'] : null,
        ];

        // Otak Sistem: Hitung analisa kesehatan via TelemetryDecisionEngine
        $analysis = $this->decisionEngine->evaluate($telemetryData, $taman->type, $taman->soil_type);

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
            'recorded_at' => now(),
        ]);

        // Keputusan otomatis: jika kelembaban tanah di bawah 30% -> Buka keran (Relay ON)
        $shouldWater = $telemetryData['moisture'] < 30.0;
        $waterDuration = $shouldWater ? 10 : 0; // 10 detik

        if ($shouldWater) {
            FarmActivity::create([
                'taman_id' => $taman->id,
                'user_id'  => $taman->user_id,
                'type'     => 'water',
                'title'    => 'Penyiraman Otomatis (Relay Aktif)',
                'detail'   => "Kelembaban {$telemetryData['moisture']}% (kritis). Keran dibuka selama {$waterDuration} detik.",
                'status'   => 'success',
                'metadata' => ['duration_sec' => $waterDuration, 'trigger' => 'auto_decision_engine'],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Telemetry recorded successfully',
            'health_score' => $analysis['health']['score'],
            'health_status' => $this->healthStatus($analysis['health']['score']),
            'commands' => [
                'water_valve' => $shouldWater ? 'ON' : 'OFF',
                'duration_sec' => $waterDuration,
                'buzzer_alert' => $shouldWater,
            ],
        ]);
    }
}

