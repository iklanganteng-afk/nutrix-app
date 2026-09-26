@extends('layouts.app')

@section('content')
@php
    $sensorTypeLabels = [
        'moisture'    => 'Kelembapan',
        'temperature' => 'Suhu',
        'ph'          => 'pH',
        'ec'          => 'EC',
    ];
    $sensorTypes  = $taman->sensor_types  ?? [];
    $sensorModels = $taman->sensor_models ?? [];
    $controller   = $taman->controller_type ? strtoupper($taman->controller_type) : 'Belum dipilih';
    $controllerName = match($taman->controller_type ?? '') {
        'esp32'   => 'ESP32',
        'arduino' => 'Arduino Uno / Nano',
        'esp8266' => 'ESP8266',
        default   => 'Board belum dipilih',
    };
    $sensorId = $taman->sensor_id ?? null;
    $isConnected = (bool) $taman->sensor_connected;
    $selectedSoil = $taman->soil_type ?? '';
    $selectedSensorTypes  = $taman->sensor_types  ?? [];
    $selectedSensorModels = $taman->sensor_models ?? [];
    $selectedController   = $taman->controller_type ?? 'esp32';
    $selectedIndicatorMode = $taman->indicator_mode ?? 'active_only';
@endphp

{{-- ────────────────────────────────────────────────────────── --}}
{{-- DASHBOARD NUTRIX IoT                                       --}}
{{-- ────────────────────────────────────────────────────────── --}}
<section class="dashboard-section standalone-dashboard container" id="dashboard">

    {{-- ── Header ── --}}
    <div class="farm-detail-toolbar">
        <a href="{{ route('dashboard') }}#gardens" class="farm-back-link">
            <i class="bi bi-arrow-left"></i>
            <span>Kembali ke Taman Saya</span>
        </a>
        <span class="farm-detail-context">
            <i class="bi bi-broadcast-pin"></i>
            <span id="topStatusText">{{ $isConnected ? 'SENSOR CONNECTED' : 'SENSOR OFFLINE' }}</span>
        </span>
    </div>

    <div class="d-flex justify-content-between align-items-end mb-4 position-relative z-2 border-bottom border-secondary pb-3">
        <div>
            <span class="badge-web3 mb-2">{{ ucfirst($taman->type) }}</span>
            <h2 class="section-title mb-0" style="font-family:'Cinzel',serif;">{{ $taman->name }}</h2>
            @if($taman->location)
                <small class="text-muted"><i class="bi bi-geo-alt"></i> {{ $taman->location }}</small>
            @endif
        </div>
        <div class="live-indicator">
            <div class="live-dot" id="liveDot"></div>
            <span id="connectionBadge" class="badge {{ $isConnected ? 'bg-success' : 'bg-secondary' }} text-white">
                {{ $isConnected ? 'ONLINE' : 'OFFLINE' }}
            </span>
        </div>
    </div>

    {{-- ── Connection Banner ── --}}
    <div id="connectionBanner" class="sensor-connection-banner {{ $isConnected ? 'is-connected' : 'is-disconnected' }}">
        <div>
            <i class="bi bi-{{ $isConnected ? 'check-circle' : 'plug' }}"></i>
            <span id="bannerText">{{ $isConnected ? 'ESP32 aktif mengirim telemetri via WiFi ke Railway Cloud.' : 'Belum ada sensor terhubung. Nyalakan ESP32 dan pastikan WiFi aktif.' }}</span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnConnectSensor">
            {{ $isConnected ? 'Ganti Sensor' : 'Hubungkan Sensor' }}
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         BAGIAN 1: FLOW VISUALIZER (alur IoT dari kiri ke kanan)
         ═══════════════════════════════════════════════════════════ --}}
    <div class="iot-flow-section mt-4 section-panel">
        <div class="sensor-config-header">
            <div>
                <span class="badge-web3">Alur Telemetri</span>
                <h3 class="mb-0 mt-2">Cara Kerja Sistem IoT NUTRIX</h3>
                <small class="text-muted d-block mt-1">Data mengalir dari sensor fisik → ESP32 → Cloud → Dashboard setiap 5 detik</small>
            </div>
            <span id="flowStatusBadge" class="badge {{ $isConnected ? 'bg-success' : 'bg-secondary' }} text-white">
                {{ $isConnected ? 'Stream Aktif' : 'Menunggu ESP32' }}
            </span>
        </div>

        <div class="iot-flow-diagram">
            {{-- Node 1: Sensor --}}
            <div class="flow-node {{ !empty($sensorTypes) ? 'node-active' : '' }}" id="flowNodeSensor">
                <div class="flow-node-icon"><i class="bi bi-moisture"></i></div>
                <div class="flow-node-label">Sensor Tanah</div>
                <div class="flow-node-sub">GPIO 34 (ADC)</div>
            </div>
            <div class="flow-arrow" id="arrow1"><i class="bi bi-arrow-right"></i></div>

            {{-- Node 2: ESP32 --}}
            <div class="flow-node {{ $isConnected ? 'node-active' : '' }}" id="flowNodeEsp">
                <div class="flow-node-icon"><i class="bi bi-cpu"></i></div>
                <div class="flow-node-label">{{ $controllerName }}</div>
                <div class="flow-node-sub">WiFiManager</div>
            </div>
            <div class="flow-arrow" id="arrow2"><i class="bi bi-arrow-right"></i></div>

            {{-- Node 3: WiFi/Cloud --}}
            <div class="flow-node {{ $isConnected ? 'node-active' : '' }}" id="flowNodeCloud">
                <div class="flow-node-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                <div class="flow-node-label">Railway Cloud</div>
                <div class="flow-node-sub">HTTPS POST</div>
            </div>
            <div class="flow-arrow" id="arrow3"><i class="bi bi-arrow-right"></i></div>

            {{-- Node 4: Decision Engine --}}
            <div class="flow-node node-brain {{ $isConnected ? 'node-active' : '' }}" id="flowNodeBrain">
                <div class="flow-node-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                <div class="flow-node-label">Decision Engine</div>
                <div class="flow-node-sub">Health Score</div>
            </div>
            <div class="flow-arrow flow-arrow-down" id="arrow4"><i class="bi bi-arrow-down"></i></div>

            {{-- Node 5: Relay (di bawah) --}}
            <div class="flow-node flow-node-relay {{ $isConnected ? 'node-active' : '' }}" id="flowNodeRelay">
                <div class="flow-node-icon"><i class="bi bi-toggles"></i></div>
                <div class="flow-node-label">Relay Keran</div>
                <div class="flow-node-sub" id="relayFlowStatus">GPIO 26 · STANDBY</div>
            </div>
        </div>

        {{-- Live Cloud Log --}}
        <div class="cloud-log-terminal mt-3" id="cloudLogTerminal">
            <div class="terminal-header">
                <span><i class="bi bi-terminal-fill text-mint me-1"></i> Cloud Telemetry Log</span>
                <span class="badge {{ $isConnected ? 'bg-success' : 'bg-secondary' }} text-white" id="terminalStatusBadge">
                    {{ $isConnected ? 'Live' : 'Waiting' }}
                </span>
            </div>
            <div class="terminal-body" id="terminalLines">
                <div class="t-line"><span class="t-prompt">›</span> endpoint /api/iot/telemetry siap menerima data</div>
                <div class="t-line"><span class="t-prompt">›</span> node {{ $controllerName }} dikonfigurasi ke hotspot WiFi</div>
                @if($isConnected)
                <div class="t-line t-success"><span class="t-prompt">✓</span> sensor {{ $sensorId }} terhubung — telemetri aktif</div>
                @else
                <div class="t-line t-warn"><span class="t-prompt">!</span> menunggu paket pertama dari ESP32...</div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         BAGIAN 2: 4 KARTU SENSOR METRIC
         ═══════════════════════════════════════════════════════════ --}}
    <div class="row g-4 mt-1 position-relative z-2">
        {{-- pH --}}
        <div class="col-12 col-md-6 col-lg-3" data-metric-column="ph">
            <div class="metric-card interactive-metric" data-metric="ph">
                <div class="metric-header d-flex justify-content-between">
                    <span><i class="bi bi-droplet-half"></i> pH Level</span>
                    <span class="trend-icon text-muted" id="trend-ph"><i class="bi bi-activity"></i></span>
                </div>
                <div class="metric-value"><span id="val-ph">--</span> <small>pH</small></div>
                <div class="metric-status small mt-1" id="status-ph">—</div>
                <div class="metric-sparkline" id="spark-ph">
                    <div class="bar"></div><div class="bar"></div><div class="bar"></div>
                    <div class="bar"></div><div class="bar"></div><div class="bar"></div>
                </div>
            </div>
        </div>
        {{-- Moisture --}}
        <div class="col-12 col-md-6 col-lg-3" data-metric-column="moisture">
            <div class="metric-card interactive-metric" data-metric="moisture">
                <div class="metric-header d-flex justify-content-between">
                    <span><i class="bi bi-moisture"></i> Kelembapan</span>
                    <span class="trend-icon text-muted" id="trend-moisture"><i class="bi bi-activity"></i></span>
                </div>
                <div class="metric-value"><span id="val-hum">--</span> <small>%</small></div>
                <div class="metric-status small mt-1" id="status-moisture">—</div>
                <div class="metric-sparkline" id="spark-hum">
                    <div class="bar"></div><div class="bar"></div><div class="bar"></div>
                    <div class="bar"></div><div class="bar"></div><div class="bar"></div>
                </div>
            </div>
        </div>
        {{-- Temperature --}}
        <div class="col-12 col-md-6 col-lg-3" data-metric-column="temperature">
            <div class="metric-card interactive-metric" data-metric="temp">
                <div class="metric-header d-flex justify-content-between">
                    <span><i class="bi bi-thermometer-half"></i> Suhu</span>
                    <span class="trend-icon text-muted" id="trend-temp"><i class="bi bi-activity"></i></span>
                </div>
                <div class="metric-value"><span id="val-temp">--</span> <small>°C</small></div>
                <div class="metric-status small mt-1" id="status-temp">—</div>
                <div class="metric-sparkline" id="spark-temp">
                    <div class="bar"></div><div class="bar"></div><div class="bar"></div>
                    <div class="bar"></div><div class="bar"></div><div class="bar"></div>
                </div>
            </div>
        </div>
        {{-- EC --}}
        <div class="col-12 col-md-6 col-lg-3" data-metric-column="ec">
            <div class="metric-card interactive-metric" data-metric="ec">
                <div class="metric-header d-flex justify-content-between">
                    <span class="text-mint"><i class="bi bi-lightning-charge-fill"></i> Konduktivitas</span>
                    <span class="trend-icon text-mint" id="trend-ec"><i class="bi bi-graph-up"></i></span>
                </div>
                <div class="metric-value text-mint"><span id="val-ec">--</span> <small class="text-mint">mS/cm</small></div>
                <div class="metric-status small mt-1" id="status-ec">—</div>
                <div class="metric-sparkline" id="spark-ec">
                    <div class="bar highlight"></div><div class="bar highlight"></div><div class="bar highlight"></div>
                    <div class="bar highlight"></div><div class="bar highlight"></div><div class="bar highlight"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         BAGIAN 3: HEALTH SCORE & QUICK ACTIONS
         ═══════════════════════════════════════════════════════════ --}}
    <div class="row mt-5 mb-4 position-relative z-2 justify-content-center">
        <div class="col-12 col-md-8 col-lg-6 text-center">
            <div class="portfolio-dashboard">
                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                    <span class="text-secondary fw-bold" style="letter-spacing:1px;font-size:.85rem;">OVERALL FARM HEALTH</span>
                </div>
                <div class="display-1 fw-bold text-white mb-0 mt-3 d-flex align-items-center justify-content-center gap-2" style="font-family:'Outfit',sans-serif;">
                    <span id="aiHealthScore">--</span> <span class="fs-4 text-mint">HTH</span>
                </div>
                <div class="text-muted mt-2 d-flex align-items-center justify-content-center gap-2">
                    <span id="aiHealthStatus">Status: {{ $isConnected ? 'MEMUAT...' : 'NOT CONNECTED' }}</span>
                </div>
                <div class="d-flex justify-content-center gap-2 mt-2">
                    <span class="badge bg-dark border border-secondary small" id="lastUpdatedBadge">
                        <i class="bi bi-clock me-1"></i> <span id="lastUpdatedTime">—</span>
                    </span>
                    <span class="badge bg-dark border border-secondary small" id="sourceBadge">
                        <i class="bi bi-broadcast me-1"></i> <span id="sourceText">—</span>
                    </span>
                </div>

                {{-- Quick Actions --}}
                <div class="d-flex justify-content-center gap-4 mt-4 pt-3">
                    <div class="quick-action-btn" id="btnSyncData" data-requires-sensor data-bs-toggle="tooltip" title="Sync Telemetry">
                        <div class="action-icon-circle"><i class="bi bi-arrow-down-up"></i></div>
                        <span>Sync</span>
                    </div>
                    <div class="quick-action-btn" id="btnWaterAction" data-requires-sensor data-bs-toggle="tooltip" title="Siram Sekarang">
                        <div class="action-icon-circle text-mint border-mint"><i class="bi bi-droplet-fill"></i></div>
                        <span class="text-mint">Siram</span>
                    </div>
                    <div class="quick-action-btn" id="btnFertilizeAction" data-requires-sensor data-bs-toggle="tooltip" title="Pupuk">
                        <div class="action-icon-circle"><i class="bi bi-flower2"></i></div>
                        <span>Pupuk</span>
                    </div>
                    <div class="quick-action-btn" id="btnActivityLog" data-bs-toggle="tooltip" title="Riwayat Aktivitas">
                        <div class="action-icon-circle"><i class="bi bi-clock-history"></i></div>
                        <span>Riwayat</span>
                    </div>
                    <div class="quick-action-btn" id="btnExportData" data-requires-sensor data-bs-toggle="tooltip" title="Export CSV">
                        <div class="action-icon-circle"><i class="bi bi-download"></i></div>
                        <span>Export</span>
                    </div>
                </div>

                {{-- AI Recommendation --}}
                <div class="mt-4 pt-3 border-top border-secondary text-start">
                    <p class="text-secondary mb-0" id="aiRecommendation" style="font-size:.85rem;line-height:1.5;">
                        <i class="bi bi-lightbulb text-mint me-1"></i>
                        <strong>Insight:</strong> Menunggu data sensor pertama...
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         BAGIAN 4: STATUS PERANGKAT & KONTROL RELAY
         ═══════════════════════════════════════════════════════════ --}}
    <div class="section-panel mt-4">
        <div class="sensor-config-header">
            <div>
                <span class="badge-web3">Status Perangkat</span>
                <h3 class="mb-0 mt-2">Kontrol & Status IoT Node</h3>
            </div>
        </div>

        <div class="device-status-grid">
            <div class="device-status-card">
                <span class="device-status-label">Sensor ID</span>
                <strong class="text-mint" id="displaySensorId">{{ $sensorId ?? 'Belum dipasangkan' }}</strong>
            </div>
            <div class="device-status-card">
                <span class="device-status-label">Status Cloud</span>
                <strong id="displayCloudStatus" class="{{ $isConnected ? 'text-mint' : 'text-muted' }}">
                    {{ $isConnected ? 'Online' : 'Offline' }}
                </strong>
            </div>
            <div class="device-status-card">
                <span class="device-status-label">Board Controller</span>
                <strong>{{ $controllerName }}</strong>
            </div>
            <div class="device-status-card">
                <span class="device-status-label">Relay Keran (GPIO 26)</span>
                <strong id="relayStatusDisplay" class="text-white">STANDBY</strong>
            </div>
            <div class="device-status-card">
                <span class="device-status-label">Interval Pengiriman</span>
                <strong>5 detik</strong>
            </div>
        </div>

        {{-- Relay Control Panel --}}
        <div class="mt-3 p-3 rounded border border-secondary" style="background:rgba(11,20,17,.72);">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-toggles text-mint"></i>
                    <strong class="text-white">Kontrol Relay Manual</strong>
                </div>
                <span id="relayPill" class="badge bg-secondary text-white">STANDBY</span>
            </div>
            <p class="small text-muted mb-3">
                Perintah ini dikirim via API ke server Railway. Server kemudian meneruskan respons ke ESP32 pada pengiriman telemetri berikutnya (maks. 5 detik).
            </p>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-connect-node" id="btnTriggerWaterManual" {{ !$isConnected ? 'disabled' : '' }}>
                    <i class="bi bi-droplet-fill"></i> Buka Keran (10 detik)
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnRefreshTelemetry">
                    <i class="bi bi-arrow-repeat"></i> Refresh Telemetri
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" id="btnResetSensor">
                    <i class="bi bi-plug"></i> Putus Sensor
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         BAGIAN 5: KONFIGURASI SENSOR (collapsed panel)
         ═══════════════════════════════════════════════════════════ --}}
    <div class="sensor-config-summary mt-4 section-panel">
        <div class="sensor-config-header">
            <div>
                <span class="badge-web3">Konfigurasi</span>
                <h3 class="mb-0 mt-2">Daftar Sensor & Board</h3>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary section-action-btn" id="btnEditSensorConfig">
                Edit konfigurasi
            </button>
        </div>

        @if(!empty($sensorTypes))
            <div class="sensor-config-list">
                @foreach($sensorTypes as $sensorType)
                    @php $typeKey = (string) $sensorType; @endphp
                    <div class="sensor-config-item">
                        <div class="sensor-config-icon">
                            <i class="bi bi-{{ $sensorType === 'moisture' ? 'moisture' : ($sensorType === 'temperature' ? 'thermometer-half' : ($sensorType === 'ph' ? 'droplet-half' : 'lightning-charge-fill')) }}"></i>
                        </div>
                        <div class="sensor-config-copy">
                            <strong>{{ $sensorTypeLabels[$typeKey] ?? ucfirst($typeKey) }}</strong>
                            <small>{{ $sensorModels[$typeKey] ?? 'Model tidak dipilih' }}</small>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="sensor-config-empty">Belum ada sensor yang dipilih untuk taman ini.</div>
        @endif

        <div class="sensor-connection-panel">
            <div class="sensor-connection-row">
                <span>Board Controller</span>
                <strong>{{ $controller }}</strong>
            </div>
            <div class="sensor-connection-row">
                <span>Mode Jaringan</span>
                <strong>WiFi 2.4 GHz (via WiFiManager)</strong>
            </div>
            <div class="sensor-connection-row">
                <span>Endpoint Telemetri</span>
                <strong>/api/iot/telemetry (HTTPS POST)</strong>
            </div>
            <div class="sensor-connection-row">
                <span>Cloud Server</span>
                <strong>Railway (nutrix-app-production.up.railway.app)</strong>
            </div>
            <div class="sensor-connection-row">
                <span>Database</span>
                <strong>Aiven Cloud MySQL</strong>
            </div>
            <div class="sensor-connection-row">
                <span>Keputusan Otomatis</span>
                <strong>Kelembapan &lt; 30% → Relay ON (10 detik)</strong>
            </div>
        </div>

        {{-- Setup Guide --}}
        <div class="guide-step-list mt-3">
            <div class="guide-step-item"><span>1</span> Nyalakan daya ESP32 (adaptor 5V atau powerbank).</div>
            <div class="guide-step-item"><span>2</span> Jika WiFi belum tersimpan → ESP32 jadi hotspot <strong>NUTRIX-ESP32-SETUP</strong>. Buka browser HP, isi WiFi + ID Taman.</div>
            <div class="guide-step-item"><span>3</span> ESP32 otomatis kirim data kelembapan tiap 5 detik ke Railway via HTTP POST.</div>
            <div class="guide-step-item"><span>4</span> Server menganalisa data, kirim balasan perintah relay (<code>water_valve: ON/OFF</code>) ke ESP32.</div>
            <div class="guide-step-item"><span>5</span> Dashboard ini otomatis update tiap 5 detik — tidak perlu refresh manual.</div>
        </div>
    </div>

</section>

{{-- ════════════════════════════════════════════
     MODAL: Farm Action (Siram / Pupuk)
     ════════════════════════════════════════════ --}}
<div class="modal-overlay" id="farmActionModal" role="dialog" aria-modal="true" aria-labelledby="farmActionTitle">
    <div class="web3-modal-box">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
            <div>
                <span class="badge-web3 mb-2">Konfirmasi Aksi</span>
                <h4 class="mb-0 fw-bold" id="farmActionTitle" style="font-family:'Cinzel',serif;">Farm Action</h4>
            </div>
            <button type="button" class="btn-close-custom" id="closeFarmActionModal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>
        <p class="text-muted" id="farmActionDescription">Periksa instruksi sebelum disimpan ke riwayat taman.</p>
        <div class="workspace-stat mb-4">
            <span class="workspace-stat-label">Target</span>
            <strong class="fs-4" id="farmActionTarget">{{ $taman->name }}</strong>
            <i class="bi bi-broadcast-pin"></i>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary flex-fill" id="cancelFarmAction">Batal</button>
            <button type="button" class="btn btn-connect-node flex-fill" id="confirmFarmAction">Konfirmasi</button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     MODAL: Edit Konfigurasi Sensor (Wizard)
     ════════════════════════════════════════════ --}}
<div class="modal-overlay" id="sensorConfigModal" role="dialog" aria-modal="true" aria-labelledby="sensorConfigTitle">
    <div class="web3-modal-box add-taman-wizard-box">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
            <h4 class="mb-0 fw-bold" id="sensorConfigTitle" style="font-family:'Cinzel',serif;">Edit konfigurasi sensor</h4>
            <button type="button" class="btn-close-custom" id="closeSensorConfigModal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>
        <form action="{{ route('taman.update', $taman) }}" method="POST" id="sensorConfigForm">
            @csrf
            @method('PATCH')

            <div class="wizard-step active" data-sensor-step="1">
                <div class="auth-input-group">
                    <label>Jenis tanah (opsional)</label>
                    <select class="auth-input" name="soil_type">
                        <option value="" {{ $selectedSoil === '' || $selectedSoil === 'unspecified' ? 'selected' : '' }}>Tidak dipilih</option>
                        <option value="pasir" {{ $selectedSoil === 'pasir' ? 'selected' : '' }}>Pasir</option>
                        <option value="liat_berpasir" {{ $selectedSoil === 'liat_berpasir' ? 'selected' : '' }}>Liat berpasir</option>
                        <option value="latosol" {{ $selectedSoil === 'latosol' ? 'selected' : '' }}>Latosol</option>
                        <option value="liat" {{ $selectedSoil === 'liat' ? 'selected' : '' }}>Liat</option>
                        <option value="organosol" {{ $selectedSoil === 'organosol' ? 'selected' : '' }}>Organosol / gambut</option>
                    </select>
                </div>
                <div class="auth-input-group">
                    <label>Indikator aktif</label>
                    <div class="sensor-check-grid">
                        <label class="sensor-check-card">
                            <input type="checkbox" name="sensor_types[]" value="moisture" {{ in_array('moisture', $selectedSensorTypes, true) ? 'checked' : '' }}>
                            <span><i class="bi bi-moisture"></i> Kelembapan</span>
                        </label>
                        <label class="sensor-check-card">
                            <input type="checkbox" name="sensor_types[]" value="temperature" {{ in_array('temperature', $selectedSensorTypes, true) ? 'checked' : '' }}>
                            <span><i class="bi bi-thermometer-half"></i> Suhu</span>
                        </label>
                        <label class="sensor-check-card">
                            <input type="checkbox" name="sensor_types[]" value="ph" {{ in_array('ph', $selectedSensorTypes, true) ? 'checked' : '' }}>
                            <span><i class="bi bi-droplet-half"></i> pH</span>
                        </label>
                        <label class="sensor-check-card">
                            <input type="checkbox" name="sensor_types[]" value="ec" {{ in_array('ec', $selectedSensorTypes, true) ? 'checked' : '' }}>
                            <span><i class="bi bi-lightning-charge-fill"></i> EC</span>
                        </label>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="button" class="btn btn-connect-node btn-sensor-step-next">Selanjutnya</button>
                </div>
            </div>

            <div class="wizard-step" data-sensor-step="2">
                <div class="auth-input-group">
                    <label>Pilih model sensor</label>
                    <div id="sensorConfigModelRows">
                        <div class="sensor-model-row" data-sensor-row="moisture">
                            <div class="sensor-model-label"><i class="bi bi-moisture"></i> Kelembapan</div>
                            <select class="auth-input" name="sensor_models[moisture]">
                                <option value="SEN0193" {{ ($selectedSensorModels['moisture'] ?? '') === 'SEN0193' ? 'selected' : '' }}>SEN0193 - Soil Moisture Sensor</option>
                                <option value="YL-69" {{ ($selectedSensorModels['moisture'] ?? '') === 'YL-69' ? 'selected' : '' }}>YL-69 - Resistive Soil Sensor</option>
                                <option value="Capacitive-1" {{ ($selectedSensorModels['moisture'] ?? '') === 'Capacitive-1' ? 'selected' : '' }}>Capacitive Soil Sensor</option>
                            </select>
                        </div>
                        <div class="sensor-model-row" data-sensor-row="temperature">
                            <div class="sensor-model-label"><i class="bi bi-thermometer-half"></i> Suhu</div>
                            <select class="auth-input" name="sensor_models[temperature]">
                                <option value="DHT22" {{ ($selectedSensorModels['temperature'] ?? '') === 'DHT22' ? 'selected' : '' }}>DHT22 - Temperature & Humidity</option>
                                <option value="DS18B20" {{ ($selectedSensorModels['temperature'] ?? '') === 'DS18B20' ? 'selected' : '' }}>DS18B20 - Waterproof Temperature</option>
                                <option value="LM35" {{ ($selectedSensorModels['temperature'] ?? '') === 'LM35' ? 'selected' : '' }}>LM35 - Analog Temperature</option>
                            </select>
                        </div>
                        <div class="sensor-model-row" data-sensor-row="ph">
                            <div class="sensor-model-label"><i class="bi bi-droplet-half"></i> pH</div>
                            <select class="auth-input" name="sensor_models[ph]">
                                <option value="PH-4502C" {{ ($selectedSensorModels['ph'] ?? '') === 'PH-4502C' ? 'selected' : '' }}>PH-4502C - pH Sensor</option>
                                <option value="Atlas-pH" {{ ($selectedSensorModels['ph'] ?? '') === 'Atlas-pH' ? 'selected' : '' }}>Atlas Scientific pH</option>
                                <option value="PH-1" {{ ($selectedSensorModels['ph'] ?? '') === 'PH-1' ? 'selected' : '' }}>PH-1 - Analog pH Module</option>
                            </select>
                        </div>
                        <div class="sensor-model-row" data-sensor-row="ec">
                            <div class="sensor-model-label"><i class="bi bi-lightning-charge-fill"></i> EC</div>
                            <select class="auth-input" name="sensor_models[ec]">
                                <option value="DFRobot-EC" {{ ($selectedSensorModels['ec'] ?? '') === 'DFRobot-EC' ? 'selected' : '' }}>DFRobot EC</option>
                                <option value="Atlas-EC" {{ ($selectedSensorModels['ec'] ?? '') === 'Atlas-EC' ? 'selected' : '' }}>Atlas Scientific EC</option>
                                <option value="TDS-V1" {{ ($selectedSensorModels['ec'] ?? '') === 'TDS-V1' ? 'selected' : '' }}>TDS Sensor V1</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-outline-secondary btn-sensor-step-prev">Kembali</button>
                    <button type="button" class="btn btn-connect-node btn-sensor-step-next">Selanjutnya</button>
                </div>
            </div>

            <div class="wizard-step" data-sensor-step="3">
                <div class="auth-input-group">
                    <label>Jenis board / otak perangkat</label>
                    <select class="auth-input" name="controller_type">
                        <option value="esp32" {{ $selectedController === 'esp32' ? 'selected' : '' }}>ESP32 (Direkomendasikan)</option>
                        <option value="esp8266" {{ $selectedController === 'esp8266' ? 'selected' : '' }}>ESP8266</option>
                        <option value="arduino" {{ $selectedController === 'arduino' ? 'selected' : '' }}>Arduino Uno / Nano</option>
                    </select>
                </div>
                <div class="auth-input-group">
                    <label>Tampilan indikator</label>
                    <select class="auth-input" name="indicator_mode">
                        <option value="active_only" {{ $selectedIndicatorMode === 'active_only' ? 'selected' : '' }}>Hanya sensor aktif</option>
                        <option value="all_with_unavailable" {{ $selectedIndicatorMode === 'all_with_unavailable' ? 'selected' : '' }}>Tampilkan semua, tandai yang tidak tersedia</option>
                    </select>
                </div>
                <div class="auth-input-group">
                    <label>Ringkasan konfigurasi</label>
                    <div class="wizard-summary" id="sensorConfigSummary"></div>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-outline-secondary btn-sensor-step-prev">Kembali</button>
                    <button type="submit" class="btn btn-connect-node">Simpan Konfigurasi</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ════════════════════════════════════════════
     MODAL: Hubungkan Sensor (Pairing via Sensor ID)
     ════════════════════════════════════════════ --}}
<div class="modal-overlay" id="connectSensorModal" role="dialog" aria-modal="true" aria-labelledby="connectSensorTitle">
    <div class="web3-modal-box">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
            <div>
                <span class="badge-web3 mb-2">Pairing Sensor</span>
                <h4 class="mb-0 fw-bold" id="connectSensorTitle" style="font-family:'Cinzel',serif;">Hubungkan ESP32</h4>
            </div>
            <button type="button" class="btn-close-custom" id="closeConnectSensorModal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>

        <div class="alert-iot mb-3">
            <i class="bi bi-info-circle text-mint me-2"></i>
            <div>
                <strong class="text-white d-block mb-1">Cara mendapat Sensor ID</strong>
                <span class="small text-muted">Nyalakan ESP32 → sambungkan ke WiFi → buka Serial Monitor Arduino IDE. Sensor ID tampil di baris pertama log boot (contoh: <code>ESP32-NUTRIX-01</code>).</span>
            </div>
        </div>

        <div class="auth-input-group mb-3">
            <label for="sensorIdInput">Sensor ID (dari firmware ESP32)</label>
            <input type="text" id="sensorIdInput" class="auth-input" placeholder="ESP32-NUTRIX-01" autocomplete="off">
            <small class="text-muted">Sesuaikan dengan nilai <code>custom_sensor_id</code> di firmware ESP32 kamu.</small>
        </div>

        <button type="button" class="btn btn-connect-node w-100" id="confirmConnectSensor">
            <i class="bi bi-link-45deg me-1"></i> Pasangkan Sensor
        </button>
    </div>
</div>
@endsection

@section('scripts')
<script>
// ═══════════════════════════════════════════════════════
//  NUTRIX — Taman Detail: Real IoT Dashboard
// ═══════════════════════════════════════════════════════
const TAMAN = {
    id:            {{ $taman->id }},
    type:          @json($taman->type),
    name:          @json($taman->name),
    indicatorMode: @json($taman->indicator_mode ?: 'active_only'),
    activeSensors: @json(array_values($taman->sensor_types ?? [])),
};
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
let isConnected = {{ $isConnected ? 'true' : 'false' }};
let telemetryInterval = null;
window.NUTRIX_TAMAN = TAMAN;

// ── Utility ──────────────────────────────────────────────
function apiFetch(path, method = 'GET', body = null) {
    const opts = {
        method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    };
    if (body) opts.body = JSON.stringify(body);
    return fetch(`/api${path}`, opts).then(async r => {
        const d = await r.json().catch(() => ({}));
        if (!r.ok) throw new Error(d.message || 'Request failed');
        return d;
    });
}

function setCard(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value ?? '--';
}

function showToast(msg, type = 'success') {
    const wrap = document.getElementById('nutrixToastWrap') ?? (() => {
        const d = document.createElement('div');
        d.id = 'nutrixToastWrap';
        Object.assign(d.style, { position:'fixed', bottom:'24px', right:'24px', zIndex:'9999', display:'flex', flexDirection:'column', gap:'8px' });
        document.body.appendChild(d);
        return d;
    })();
    const el = document.createElement('div');
    el.style.cssText = `background:${type==='error'?'#ef4444':'var(--color-accent-highlight)'};color:#fff;padding:12px 20px;border-radius:12px;font-weight:600;font-size:.9rem;box-shadow:0 4px 20px rgba(0,0,0,.35);animation:fadeIn .2s ease;`;
    el.textContent = msg;
    wrap.appendChild(el);
    setTimeout(() => el.remove(), 3500);
}

function pushFarmNotification(message, icon = 'bi-bell') {
    if (typeof pushNotification === 'function') { pushNotification(message, icon); return; }
    const panel = document.getElementById('notificationList');
    const badge = document.getElementById('notificationBadge');
    if (!panel) return;
    const item = document.createElement('div');
    item.className = 'notification-item';
    item.innerHTML = `<div class="notification-item-icon"><i class="bi ${icon}"></i></div><div><p>${message}</p><small>Baru saja</small></div>`;
    panel.prepend(item);
    if (badge) badge.textContent = panel.querySelectorAll('.notification-item').length || '';
}

// ── Terminal Log ──────────────────────────────────────────
function appendLog(msg, type = 'info') {
    const el = document.getElementById('terminalLines');
    if (!el) return;
    const line = document.createElement('div');
    const cls = type === 'success' ? 't-success' : type === 'warn' ? 't-warn' : type === 'error' ? 't-error' : '';
    const prompt = type === 'success' ? '✓' : type === 'warn' ? '!' : type === 'error' ? '✗' : '›';
    line.className = `t-line ${cls}`;
    line.innerHTML = `<span class="t-prompt">${prompt}</span> ${msg}`;
    el.appendChild(line);
    while (el.children.length > 10) el.removeChild(el.firstChild);
    el.scrollTop = el.scrollHeight;
}

// ── Connection State UI ────────────────────────────────────
function applyConnectionState(connected) {
    isConnected = connected;
    const badge    = document.getElementById('connectionBadge');
    const dot      = document.getElementById('liveDot');
    const banner   = document.getElementById('connectionBanner');
    const bannerTx = document.getElementById('bannerText');
    const flowBadge = document.getElementById('flowStatusBadge');
    const termBadge = document.getElementById('terminalStatusBadge');
    const topTx    = document.getElementById('topStatusText');
    const cloudSt  = document.getElementById('displayCloudStatus');
    const relayBtn = document.getElementById('btnTriggerWaterManual');

    if (badge) { badge.textContent = connected ? 'ONLINE' : 'OFFLINE'; badge.className = `badge ${connected ? 'bg-success' : 'bg-secondary'} text-white`; }
    if (dot)   { dot.classList.toggle('dot-online', connected); }
    if (banner){ banner.className = `sensor-connection-banner ${connected ? 'is-connected' : 'is-disconnected'}`; }
    if (bannerTx) bannerTx.textContent = connected ? 'ESP32 aktif mengirim telemetri via WiFi ke Railway Cloud.' : 'Belum ada sensor terhubung. Nyalakan ESP32 dan pastikan WiFi aktif.';
    if (flowBadge) { flowBadge.textContent = connected ? 'Stream Aktif' : 'Menunggu ESP32'; flowBadge.className = `badge ${connected ? 'bg-success' : 'bg-secondary'} text-white`; }
    if (termBadge) { termBadge.textContent = connected ? 'Live' : 'Waiting'; termBadge.className = `badge ${connected ? 'bg-success' : 'bg-secondary'} text-white`; }
    if (topTx) topTx.textContent = connected ? 'SENSOR CONNECTED' : 'SENSOR OFFLINE';
    if (cloudSt) { cloudSt.textContent = connected ? 'Online' : 'Offline'; cloudSt.className = connected ? 'text-mint' : 'text-muted'; }
    if (relayBtn) relayBtn.disabled = !connected;

    // Flow nodes
    ['flowNodeSensor','flowNodeEsp','flowNodeCloud','flowNodeBrain','flowNodeRelay'].forEach(id => {
        const n = document.getElementById(id);
        if (n) n.classList.toggle('node-active', connected);
    });
    document.querySelectorAll('[data-requires-sensor]').forEach(el => {
        el.classList.toggle('is-disabled', !connected);
        el.setAttribute('aria-disabled', connected ? 'false' : 'true');
    });
}

function applyMetricVisibility() {
    const active = new Set(TAMAN.activeSensors.length ? TAMAN.activeSensors : ['moisture', 'ph', 'temperature', 'ec']);
    document.querySelectorAll('[data-metric-column]').forEach(col => {
        col.hidden = TAMAN.indicatorMode === 'active_only' && !active.has(col.dataset.metricColumn);
    });
}

// ── Relay Status ──────────────────────────────────────────
function setRelayStatus(on) {
    const display = document.getElementById('relayStatusDisplay');
    const flow    = document.getElementById('relayFlowStatus');
    const pill    = document.getElementById('relayPill');
    if (display) { display.textContent = on ? 'AKTIF (KERAN TERBUKA)' : 'STANDBY'; display.className = on ? 'text-mint animate-pulse' : 'text-white'; }
    if (flow)    flow.textContent = on ? 'GPIO 26 · AKTIF 🟢' : 'GPIO 26 · STANDBY';
    if (pill)    { pill.textContent = on ? 'AKTIF' : 'STANDBY'; pill.className = `badge ${on ? 'bg-success' : 'bg-secondary'} text-white`; }
}

// ── Update Cards ───────────────────────────────────────────
const METRIC_HINTS = {
    ph:          { ok: [6.0, 7.5], label: (v) => v < 6.0 ? 'Asam — perlu kapur' : v > 7.5 ? 'Basa — perlu asam' : 'Optimal' },
    moisture:    { ok: [30, 80],   label: (v) => v < 30 ? '⚠ Kering — siram segera!' : v > 80 ? 'Terlalu basah' : 'Optimal' },
    temperature: { ok: [15, 35],  label: (v) => v < 15 ? 'Terlalu dingin' : v > 35 ? 'Terlalu panas' : 'Optimal' },
    ec:          { ok: [0.5, 3.0], label: (v) => v < 0.5 ? 'Nutrisi rendah' : v > 3.0 ? 'Terlalu tinggi' : 'Optimal' },
};

function updateCards(t) {
    if (!t) return;
    const metrics = t.metrics || {};
    const val = (k) => metrics[k]?.value ?? t[k] ?? null;

    const phV   = val('ph');
    const humV  = val('moisture');
    const tmpV  = val('temperature');
    const ecV   = val('ec');

    setCard('val-ph',   phV  != null ? Number(phV).toFixed(1)  : '--');
    setCard('val-hum',  humV != null ? Number(humV).toFixed(0) : '--');
    setCard('val-temp', tmpV != null ? Number(tmpV).toFixed(1) : '--');
    setCard('val-ec',   ecV  != null ? Number(ecV).toFixed(2)  : '--');

    // Metric status hints
    [['status-ph', phV, 'ph'], ['status-moisture', humV, 'moisture'],
     ['status-temp', tmpV, 'temperature'], ['status-ec', ecV, 'ec']].forEach(([id, v, key]) => {
        const el = document.getElementById(id);
        if (!el || v == null) return;
        const hint = METRIC_HINTS[key];
        const label = hint.label(Number(v));
        const inRange = Number(v) >= hint.ok[0] && Number(v) <= hint.ok[1];
        el.textContent = label;
        el.style.color = inRange ? 'var(--color-accent-highlight)' : (label.startsWith('⚠') ? '#ef4444' : '#fbbf24');
    });

    // Health score
    if (t.health?.score != null || t.health_score != null) {
        const score = Math.round(t.health?.score ?? t.health_score);
        setCard('aiHealthScore', score);
        const tier = t.decision?.tier ? `PRIORITAS ${t.decision.tier}` : (t.health_status || 'UNKNOWN').toUpperCase();
        setCard('aiHealthStatus', 'Status: ' + tier);

        const tip = score >= 80
            ? 'Semua parameter dalam batas optimal. Taman dalam kondisi prima.'
            : score >= 55
                ? 'Beberapa parameter mulai bergeser. Pantau dan pertimbangkan penyiraman.'
                : 'Parameter kritis terdeteksi! Segera lakukan penyiraman atau pemupukan.';
        const ai = document.getElementById('aiRecommendation');
        if (ai) ai.innerHTML = `<i class="bi bi-lightbulb text-mint me-1"></i> <strong>Insight:</strong> ${tip} (Skor: ${score})`;
    }
}

function markFresh(source) {
    const now = new Date().toLocaleTimeString('id-ID');
    const srcEl = document.getElementById('sourceText');
    const timeEl = document.getElementById('lastUpdatedTime');
    if (srcEl) srcEl.textContent = source === 'esp32_device' ? 'ESP32 Wireless' : source === 'simulator' ? 'Simulator' : source || 'API';
    if (timeEl) timeEl.textContent = now;
}

// ── Load Telemetry ─────────────────────────────────────────
async function loadLatestTelemetry(silent = false) {
    try {
        applyMetricVisibility();
        const data = await apiFetch(`/taman/${TAMAN.id}/telemetry/latest`);

        const connected = data.available_sensors?.length > 0 || data.lifecycle === 'live' || data.lifecycle === 'stale';
        applyConnectionState(connected);

        if (data.lifecycle === 'never_received' && !data.recorded_at) {
            setCard('aiHealthScore', '--');
            setCard('aiHealthStatus', 'Menunggu data pertama dari ESP32...');
            return;
        }

        updateCards(data);
        markFresh(data.source);

        if (!silent) {
            appendLog(`telemetry ok — lifecycle: ${data.lifecycle} — source: ${data.source}`, 'success');
        }

        // Auto detect relay trigger dari decision engine
        const shouldWater = (data.metrics?.moisture?.value ?? data.moisture ?? 100) < 30;
        if (shouldWater) {
            setRelayStatus(true);
            appendLog('⚡ kelembapan < 30% — relay ON otomatis dari Decision Engine', 'warn');
        }

    } catch (e) {
        if (!silent) appendLog(`error: ${e.message}`, 'error');
    }
}

// ── Start / Stop polling ───────────────────────────────────
function startPolling() {
    stopPolling();
    telemetryInterval = setInterval(() => {
        if (!document.hidden) loadLatestTelemetry(true);
    }, 5000);
}

function stopPolling() {
    if (telemetryInterval) { clearInterval(telemetryInterval); telemetryInterval = null; }
}

// ── Init ───────────────────────────────────────────────────
applyMetricVisibility();
applyConnectionState(isConnected);
loadLatestTelemetry();
startPolling();

document.addEventListener('visibilitychange', () => {
    if (document.hidden) stopPolling();
    else { loadLatestTelemetry(); startPolling(); }
});

// ── Refresh Button ─────────────────────────────────────────
document.getElementById('btnRefreshTelemetry')?.addEventListener('click', () => {
    appendLog('manual refresh...');
    loadLatestTelemetry();
    showToast('Telemetri diperbarui.');
});

// ── Water Relay Button ─────────────────────────────────────
document.getElementById('btnTriggerWaterManual')?.addEventListener('click', async () => {
    if (!isConnected) return showToast('Sensor belum terhubung.', 'error');
    try {
        showToast('Mengirim perintah relay ke server...');
        appendLog('manual water command — duration: 10s');
        const res = await apiFetch(`/taman/${TAMAN.id}/actions/water`, 'POST', { duration_sec: 10 });
        if (res.success) {
            setRelayStatus(true);
            appendLog('relay ON — keran terbuka (10 detik)', 'success');
            pushFarmNotification('Keran air dibuka selama 10 detik.', 'bi-droplet-fill');
            showToast('Keran berhasil dibuka!');
            if (res.telemetry) updateCards(res.telemetry);
            setTimeout(() => { setRelayStatus(false); appendLog('relay OFF — keran ditutup', 'info'); }, 10000);
        }
    } catch (err) {
        showToast(err.message || 'Gagal mengirim perintah relay.', 'error');
        appendLog(`relay error: ${err.message}`, 'error');
    }
});

// ── Sync (Simulator) ───────────────────────────────────────
document.getElementById('btnSyncData')?.addEventListener('click', async () => {
    showToast('Menyinkronkan sensor (simulator)...');
    appendLog('sync triggered — simulator mode');
    try {
        const data = await apiFetch(`/taman/${TAMAN.id}/sync`, 'POST');
        if (data.success) {
            updateCards(data.telemetry);
            markFresh('simulator');
            applyConnectionState(true);
            appendLog('sync ok — data updated via simulator', 'success');
            pushFarmNotification('Sinkronisasi sensor selesai.', 'bi-arrow-repeat');
            showToast('Sinkronisasi berhasil!');
        }
    } catch { showToast('Gagal terhubung ke server.', 'error'); }
});

// ── Reset Sensor ───────────────────────────────────────────
document.getElementById('btnResetSensor')?.addEventListener('click', async () => {
    if (!confirm('Putus koneksi sensor ini? ESP32 perlu dipasangkan ulang.')) return;
    try {
        const data = await apiFetch(`/taman/${TAMAN.id}/sensor`, 'DELETE');
        if (data.success || data.connected === false) {
            applyConnectionState(false);
            setCard('displaySensorId', 'Belum dipasangkan');
            appendLog('sensor disconnected — awaiting re-pair', 'warn');
            pushFarmNotification('Sensor diputus. Silakan pasangkan ulang.', 'bi-plug');
            showToast('Sensor berhasil diputus.', 'error');
        }
    } catch (e) { showToast(e.message || 'Reset gagal.', 'error'); }
});

// ── Farm Action Modal (Pupuk / Siram) ─────────────────────
const farmActionModal = document.getElementById('farmActionModal');
let pendingAction = null;

const closeFarmAction = () => {
    farmActionModal?.classList.remove('active');
    document.body.style.overflow = '';
    pendingAction = null;
};

function openFarmAction(action) {
    pendingAction = action;
    document.getElementById('farmActionTitle').textContent = action.title;
    document.getElementById('farmActionDescription').textContent = action.description;
    farmActionModal?.classList.add('active');
    document.body.style.overflow = 'hidden';
}

document.getElementById('btnWaterAction')?.addEventListener('click', () => openFarmAction({
    endpoint: `/taman/${TAMAN.id}/actions/water`,
    body: { duration_sec: 30 },
    title: 'Konfirmasi penyiraman',
    description: 'Mencatat penyiraman manual 30 detik ke riwayat taman dan menghitung efek pada telemetri.',
    success: 'Penyiraman berhasil dicatat!',
    icon: 'bi-droplet-fill',
}));

document.getElementById('btnFertilizeAction')?.addEventListener('click', () => openFarmAction({
    endpoint: `/taman/${TAMAN.id}/actions/fertilize`,
    body: { fertilizer_type: 'NPK', volume_ml: 200 },
    title: 'Konfirmasi pemupukan',
    description: 'Mencatat pemupukan NPK 200ml ke riwayat taman dan menyesuaikan nilai EC & pH.',
    success: 'Pemupukan berhasil dicatat!',
    icon: 'bi-flower2',
}));

document.getElementById('confirmFarmAction')?.addEventListener('click', async () => {
    if (!pendingAction) return;
    const action = pendingAction;
    closeFarmAction();
    showToast('Menyimpan aksi...');
    appendLog(`${action.icon?.replace('bi-', '') || 'action'} — ${action.title}`);
    try {
        const data = await apiFetch(action.endpoint, 'POST', action.body);
        if (data.success) {
            if (data.telemetry) { updateCards(data.telemetry); markFresh('manual action'); }
            pushFarmNotification(action.success, action.icon || 'bi-check-circle');
            appendLog(`${action.title} berhasil`, 'success');
            showToast(action.success);
        }
    } catch (e) { showToast(e.message || 'Aksi gagal.', 'error'); }
});

document.getElementById('closeFarmActionModal')?.addEventListener('click', closeFarmAction);
document.getElementById('cancelFarmAction')?.addEventListener('click', closeFarmAction);
farmActionModal?.addEventListener('click', e => { if (e.target === farmActionModal) closeFarmAction(); });

// ── Activity Log Drawer ────────────────────────────────────
document.getElementById('btnActivityLog')?.addEventListener('click', async () => {
    try {
        const data = await apiFetch(`/taman/${TAMAN.id}/activities`);
        const items = data.activities || [];
        const iconMap = { sync:'arrow-repeat', water:'droplet-fill', fertilize:'flower2', alert:'exclamation-triangle', connection:'plug', export:'download' };
        const html = items.length === 0
            ? '<p style="color:var(--text-muted);font-size:.9rem;">Belum ada aktivitas.</p>'
            : items.map(a => `<div style="display:flex;gap:10px;padding:10px 0;border-bottom:1px solid var(--border-subtle);">
                <i class="bi bi-${iconMap[a.type]||'journal'}" style="color:var(--color-accent-highlight);margin-top:3px;"></i>
                <div>
                    <div style="font-weight:600;font-size:.88rem;">${a.title}</div>
                    <div style="font-size:.75rem;color:var(--text-muted);">${new Date(a.created_at).toLocaleString('id-ID')}</div>
                    ${a.detail ? `<div style="font-size:.75rem;color:var(--text-secondary);">${a.detail}</div>` : ''}
                </div>
            </div>`).join('');

        let modal = document.getElementById('activityDrawer');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'activityDrawer';
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.7);backdrop-filter:blur(6px);z-index:9998;display:flex;align-items:flex-end;justify-content:center;';
            modal.innerHTML = `<div style="background:var(--bg-secondary);border-radius:24px 24px 0 0;width:100%;max-width:580px;max-height:70vh;padding:1.5rem;overflow-y:auto;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                    <h5 style="font-family:'Cinzel',serif;font-weight:700;margin:0;">Riwayat Aktivitas</h5>
                    <button onclick="document.getElementById('activityDrawer').remove()" style="background:var(--bg-primary);border:1px solid var(--border-subtle);border-radius:50%;width:32px;height:32px;cursor:pointer;color:var(--text-muted);">✕</button>
                </div>
                <div id="activityDrawerBody"></div></div>`;
            document.body.appendChild(modal);
            modal.addEventListener('click', e => { if (e.target === modal) modal.remove(); });
        }
        document.getElementById('activityDrawerBody').innerHTML = html;
    } catch { showToast('Gagal memuat riwayat.', 'error'); }
});

// ── Export CSV ─────────────────────────────────────────────
document.getElementById('btnExportData')?.addEventListener('click', () => {
    window.location.href = `/api/taman/${TAMAN.id}/export.csv`;
});

// ── Sensor Config Wizard ───────────────────────────────────
const sensorConfigModal = document.getElementById('sensorConfigModal');
const configWizardSteps = Array.from(document.querySelectorAll('[data-sensor-step]'));
let currentConfigStep = 1;

const updateSensorWizardStep = (n) => {
    currentConfigStep = n;
    configWizardSteps.forEach(s => s.classList.toggle('active', Number(s.dataset.sensorStep) === n));
};

const refreshSensorConfigSummary = () => {
    const selected = Array.from(document.querySelectorAll('#sensorConfigForm input[name="sensor_types[]"]:checked')).map(i => i.value);
    const summary = document.getElementById('sensorConfigSummary');
    if (!summary) return;
    if (!selected.length) { summary.innerHTML = '<div class="wizard-summary-empty">Belum ada sensor dipilih.</div>'; return; }
    const labels = { moisture:'Kelembapan', temperature:'Suhu', ph:'pH', ec:'EC' };
    const rows = selected.map(t => {
        const model = document.querySelector(`#sensorConfigForm select[name="sensor_models[${t}]"]`)?.value || 'Belum dipilih';
        return `<div class="wizard-summary-item"><span>${labels[t]||t}</span><strong>${model}</strong></div>`;
    }).join('');
    const ctrl = document.querySelector('#sensorConfigForm select[name="controller_type"]')?.value || 'esp32';
    summary.innerHTML = `<div class="wizard-summary-item"><span>Sensor</span><strong>${selected.length} tipe aktif</strong></div>${rows}<div class="wizard-summary-item"><span>Board</span><strong>${ctrl.toUpperCase()}</strong></div>`;
};

document.querySelectorAll('.btn-sensor-step-next').forEach(b => b.addEventListener('click', () => { if (currentConfigStep < 3) updateSensorWizardStep(currentConfigStep + 1); refreshSensorConfigSummary(); }));
document.querySelectorAll('.btn-sensor-step-prev').forEach(b => b.addEventListener('click', () => { if (currentConfigStep > 1) updateSensorWizardStep(currentConfigStep - 1); }));
document.querySelectorAll('#sensorConfigForm input[name="sensor_types[]"]').forEach(cb => cb.addEventListener('change', () => {
    const sel = Array.from(document.querySelectorAll('#sensorConfigForm input[name="sensor_types[]"]:checked')).map(i => i.value);
    document.querySelectorAll('#sensorConfigForm [data-sensor-row]').forEach(r => { r.style.display = sel.includes(r.dataset.sensorRow) ? 'block' : 'none'; });
    refreshSensorConfigSummary();
}));

document.getElementById('btnEditSensorConfig')?.addEventListener('click', () => {
    updateSensorWizardStep(1); refreshSensorConfigSummary();
    sensorConfigModal?.classList.add('active'); document.body.style.overflow = 'hidden';
});
document.getElementById('closeSensorConfigModal')?.addEventListener('click', () => { sensorConfigModal?.classList.remove('active'); document.body.style.overflow = ''; });
sensorConfigModal?.addEventListener('click', e => { if (e.target === sensorConfigModal) { sensorConfigModal.classList.remove('active'); document.body.style.overflow = ''; } });

// ── Connect Sensor Modal ───────────────────────────────────
const connectSensorModal = document.getElementById('connectSensorModal');
const closeConnectSensor = () => { connectSensorModal?.classList.remove('active'); document.body.style.overflow = ''; };

document.getElementById('btnConnectSensor')?.addEventListener('click', () => {
    connectSensorModal?.classList.add('active');
    document.body.style.overflow = 'hidden';
    setTimeout(() => document.getElementById('sensorIdInput')?.focus(), 50);
});
document.getElementById('closeConnectSensorModal')?.addEventListener('click', closeConnectSensor);
connectSensorModal?.addEventListener('click', e => { if (e.target === connectSensorModal) closeConnectSensor(); });

document.getElementById('confirmConnectSensor')?.addEventListener('click', async () => {
    const sensorId = document.getElementById('sensorIdInput')?.value.trim();
    if (!sensorId) return showToast('Masukkan Sensor ID terlebih dahulu.', 'error');
    const boardType = document.querySelector('#sensorConfigForm select[name="controller_type"]')?.value || null;
    closeConnectSensor();
    showToast('Memasangkan sensor...');
    appendLog(`pairing: sensor_id=${sensorId}`);
    try {
        const data = await apiFetch(`/taman/${TAMAN.id}/sensor/connect`, 'POST', { sensor_id: sensorId, board_type: boardType });
        if (data.success || data.connected) {
            setCard('displaySensorId', sensorId);
            applyConnectionState(true);
            appendLog(`sensor paired: ${sensorId}`, 'success');
            pushFarmNotification(`Sensor ${sensorId} berhasil dipasangkan.`, 'bi-link-45deg');
            showToast('Sensor berhasil dipasangkan!');
            setTimeout(() => window.location.reload(), 800);
        } else showToast('Gagal memasangkan sensor.', 'error');
    } catch (e) {
        showToast(e.message || 'Pairing gagal.', 'error');
        appendLog(`pairing error: ${e.message}`, 'error');
    }
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        sensorConfigModal?.classList.remove('active');
        connectSensorModal?.classList.remove('active');
        closeFarmAction();
        document.body.style.overflow = '';
    }
});
</script>

<style>
/* ── IoT Flow Diagram ─────────────────────────────── */
.iot-flow-section { padding: 1.5rem; }

.iot-flow-diagram {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 1.5rem;
    padding: 1rem;
    background: rgba(0,0,0,.25);
    border-radius: 16px;
    border: 1px solid var(--border-subtle);
    position: relative;
}

.flow-node {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 14px 18px;
    border-radius: 14px;
    background: rgba(255,255,255,.04);
    border: 1.5px solid rgba(148,163,184,.25);
    transition: all .4s ease;
    min-width: 90px;
    opacity: 0.55;
}
.flow-node.node-active {
    opacity: 1;
    border-color: rgba(27,196,146,.6);
    background: rgba(27,196,146,.08);
    box-shadow: 0 0 16px rgba(27,196,146,.15);
    animation: node-pulse 2.5s ease infinite;
}
.flow-node.node-brain.node-active {
    border-color: rgba(251,191,36,.6);
    background: rgba(251,191,36,.08);
    box-shadow: 0 0 16px rgba(251,191,36,.15);
}
.flow-node-icon { font-size: 1.5rem; color: var(--color-accent-highlight); }
.flow-node.node-brain .flow-node-icon { color: #fbbf24; }
.flow-node-label { font-size: .78rem; font-weight: 700; color: var(--text-primary); white-space: nowrap; }
.flow-node-sub   { font-size: .66rem; color: var(--text-muted); white-space: nowrap; }

.flow-arrow { color: var(--text-muted); font-size: 1.1rem; flex-shrink: 0; }
.flow-arrow-down {
    position: absolute;
    right: calc(90px + 1.5rem);
    bottom: -32px;
    font-size: 1.1rem;
    color: var(--text-muted);
}
.flow-node-relay {
    position: absolute;
    right: 0;
    bottom: -80px;
    border-color: rgba(239,68,68,.3);
}
.flow-node-relay.node-active {
    border-color: rgba(239,68,68,.7);
    background: rgba(239,68,68,.08);
    box-shadow: 0 0 16px rgba(239,68,68,.15);
}
.flow-node-relay .flow-node-icon { color: #ef4444; }

@keyframes node-pulse {
    0%,100% { box-shadow: 0 0 14px rgba(27,196,146,.15); }
    50%      { box-shadow: 0 0 28px rgba(27,196,146,.35); }
}

/* ── Cloud Log Terminal ──────────────────────────── */
.cloud-log-terminal {
    border-radius: 12px;
    border: 1px solid var(--border-subtle);
    overflow: hidden;
    background: rgba(0,0,0,.45);
}
.terminal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: .5rem 1rem;
    background: rgba(255,255,255,.04);
    border-bottom: 1px solid var(--border-subtle);
    font-size: .78rem;
    color: var(--text-secondary);
    font-weight: 600;
}
.terminal-body {
    padding: .75rem 1rem;
    max-height: 140px;
    overflow-y: auto;
    font-family: 'Courier New', monospace;
    font-size: .73rem;
}
.t-line   { padding: 2px 0; color: var(--text-muted); }
.t-prompt { margin-right: 8px; color: var(--color-accent-highlight); }
.t-success .t-prompt { color: #4ade80; }
.t-warn   .t-prompt  { color: #fbbf24; }
.t-error  .t-prompt  { color: #ef4444; }
.t-success { color: rgba(74,222,128,.85); }
.t-warn    { color: rgba(251,191,36,.85); }
.t-error   { color: rgba(239,68,68,.85); }

/* ── Live dot ──────────────────────────────────────── */
.live-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--text-muted);
    transition: background .3s;
}
.live-dot.dot-online {
    background: var(--color-accent-highlight);
    box-shadow: 0 0 0 0 rgba(27,196,146,.5);
    animation: blink 1.4s ease infinite;
}
@keyframes blink {
    0%,100% { box-shadow: 0 0 0 0 rgba(27,196,146,.5); }
    70%     { box-shadow: 0 0 0 8px rgba(27,196,146,0); }
}

/* ── Alert IoT info box ────────────────────────────── */
.alert-iot {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: .85rem 1rem;
    background: rgba(27,196,146,.08);
    border: 1px solid rgba(27,196,146,.3);
    border-radius: 12px;
}

/* ── Border Mint ───────────────────────────────────── */
.border-mint  { border-color: var(--color-accent-highlight) !important; }
</style>
@endsection
