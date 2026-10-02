<?php

namespace App\Http\Controllers;

use App\Models\Taman;
use App\Models\FarmActivity;
use App\Models\SensorTelemetry;
use App\Services\Agronomy\TelemetryDecisionEngine;
use App\Services\Telemetry\SimulatedTelemetrySource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TamanController extends Controller
{
    public function __construct(
        private readonly SimulatedTelemetrySource $telemetrySource,
        private readonly TelemetryDecisionEngine $decisionEngine,
    ) {
    }

    // GET /dashboard -> guest lihat halaman publik + modal Sign In,
    // user login tapi 0 taman lihat state "tambah taman pertama",
    // user login dengan taman lihat daftar tamannya
    public function index()
    {
        $tamans = Auth::user()->tamans()
            ->with('latestTelemetry')
            ->latest()
            ->get();

        $healthCounts = [
            'optimal' => $tamans->filter(fn ($taman) => $taman->latestTelemetry?->health_status === 'optimal')->count(),
            'warning' => $tamans->filter(fn ($taman) => $taman->latestTelemetry?->health_status === 'warning')->count(),
            'critical' => $tamans->filter(fn ($taman) => $taman->latestTelemetry?->health_status === 'critical')->count(),
        ];

        $recentActivities = FarmActivity::where('user_id', Auth::id())
            ->with('taman:id,name')
            ->latest()
            ->limit(8)
            ->get();

        return view('taman.index', [
            'tamans' => $tamans,
            'dashboardState' => $tamans->isEmpty() ? 'setup' : 'ready',
            'healthCounts' => $healthCounts,
            'recentActivities' => $recentActivities,
        ]);
    }

    // POST /taman -> bikin taman baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:corn,greenhouse,rice,custom'],
            'location' => ['nullable', 'string', 'max:255'],
            'soil_type' => ['nullable', Rule::in(array_keys(config('nutrix_catalog.soils')))],
            'indicator_mode' => ['nullable', 'in:active_only,all_with_unavailable'],
            'sensor_types' => ['nullable', 'array'],
            'sensor_types.*' => ['in:moisture,temperature,ph,ec'],
            'sensor_models' => ['nullable', 'array'],
            'sensor_models.*' => ['nullable', Rule::in(array_merge(...array_values(config('nutrix_catalog.sensor_models'))))],
            'controller_type' => ['nullable', Rule::in(config('nutrix_catalog.boards'))],
            'device_connection' => ['nullable', 'array'],
            'device_connection.computer_port' => ['nullable', 'string', 'max:50'],
            'device_connection.device_port' => ['nullable', 'string', 'max:50'],
            'device_connection.note' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['sensor_types'] = array_values($validated['sensor_types'] ?? []);
        $validated['sensor_models'] = $validated['sensor_models'] ?? [];
        $validated['controller_type'] = $validated['controller_type'] ?? null;
        $validated['device_connection'] = $validated['device_connection'] ?? [];
        $validated['sensor_connected'] = false;
        $validated['soil_type'] = $validated['soil_type'] ?? 'unspecified';
        $validated['indicator_mode'] = $validated['indicator_mode'] ?? 'active_only';
        $validated['sensor_config'] = $this->sensorConfigFromLegacy($validated['sensor_types']);

        $token = 'NTX-' . strtoupper(\Illuminate\Support\Str::random(12));
        $expiresAt = now()->addDays(30);

        $taman = Auth::user()->tamans()->create([
            ...$validated,
            'device_token' => $token,
            'device_token_expires_at' => $expiresAt,
            'device_name' => 'Kelompok Nutrix',
        ]);

        $this->createInitialTelemetry($taman);

        FarmActivity::create([
            'taman_id' => $taman->id,
            'user_id'  => Auth::id(),
            'type'     => 'connection',
            'title'    => 'Taman Baru Dibuat & Token Siap',
            'detail'   => "Token Pairing IoT: {$token}. Siap dihubungkan ke ESP32 secara wireless.",
            'status'   => 'info',
        ]);

        return redirect()->route('taman.show', $taman)->with('newly_created_token', $token);
    }

    // GET /taman/{taman} -> halaman detail (4 kartu sensor + health score, dsb)
    public function show(Taman $taman)
    {
        abort_unless($taman->user_id === Auth::id(), 403);

        $latest = $taman->latestTelemetry;
        $telemetries = $taman->telemetries()->latest('recorded_at')->limit(20)->get();

        return view('taman.show', compact('taman', 'latest', 'telemetries'));
    }

    // PATCH /taman/{taman} -> edit taman
    public function update(Request $request, Taman $taman)
    {
        abort_unless($taman->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:corn,greenhouse,rice,custom'],
            'location' => ['nullable', 'string', 'max:255'],
            'soil_type' => ['nullable', Rule::in(array_keys(config('nutrix_catalog.soils')))],
            'indicator_mode' => ['nullable', 'in:active_only,all_with_unavailable'],
            'sensor_types' => ['nullable', 'array'],
            'sensor_types.*' => ['in:moisture,temperature,ph,ec'],
            'sensor_models' => ['nullable', 'array'],
            'sensor_models.*' => ['nullable', Rule::in(array_merge(...array_values(config('nutrix_catalog.sensor_models'))))],
            'controller_type' => ['nullable', Rule::in(config('nutrix_catalog.boards'))],
            'device_connection' => ['nullable', 'array'],
            'device_connection.computer_port' => ['nullable', 'string', 'max:50'],
            'device_connection.device_port' => ['nullable', 'string', 'max:50'],
            'device_connection.note' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['sensor_types'] = array_values($validated['sensor_types'] ?? $taman->sensor_types ?? []);
        $validated['sensor_models'] = $validated['sensor_models'] ?? $taman->sensor_models ?? [];
        $validated['controller_type'] = $validated['controller_type'] ?? $taman->controller_type;
        $validated['device_connection'] = $validated['device_connection'] ?? $taman->device_connection ?? [];
        $validated['soil_type'] = $validated['soil_type'] ?? $taman->soil_type ?? 'unspecified';
        $validated['indicator_mode'] = $validated['indicator_mode'] ?? $taman->indicator_mode ?? 'active_only';
        $validated['sensor_config'] = $this->sensorConfigFromLegacy($validated['sensor_types']);

        $taman->update($validated);

        return redirect()->route('taman.show', $taman)->with('success', 'Taman berhasil diperbarui.');
    }

    private function sensorConfigFromLegacy(array $sensorTypes): array
    {
        $sensors = [];
        foreach (['moisture', 'ph', 'temperature', 'ec'] as $metric) {
            $sensors[$metric] = ['enabled' => in_array($metric, $sensorTypes, true)];
        }

        return ['schema' => 1, 'sensors' => $sensors];
    }

    private function createInitialTelemetry(Taman $taman): SensorTelemetry
    {
        $data = $this->telemetrySource->read($taman);
        $analysis = $this->decisionEngine->evaluate($data, $taman->type, $taman->soil_type);

        return SensorTelemetry::create([
            'taman_id' => $taman->id,
            'ph' => $data['ph'] ?? null,
            'moisture' => $data['moisture'] ?? null,
            'temperature' => $data['temperature'] ?? null,
            'ec' => $data['ec'] ?? null,
            'moisture_unit' => array_key_exists('moisture', $data) ? 'vwc_pct' : null,
            'health_score' => $analysis['health']['score'],
            'health_status' => $analysis['health']['score'] === null ? 'unknown' : ($analysis['health']['score'] >= 80 ? 'optimal' : ($analysis['health']['score'] >= 55 ? 'warning' : 'critical')),
            'source' => 'simulator',
            'recorded_at' => now(),
        ]);
    }

    // DELETE /taman/{taman}
    public function destroy(Taman $taman)
    {
        abort_unless($taman->user_id === Auth::id(), 403);
        $taman->delete();

        return redirect()->route('dashboard');
    }

}