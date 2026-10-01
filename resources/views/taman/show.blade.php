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
    $deviceToken = $taman->device_token ?? null;
    $isConnected = (bool) $taman->sensor_connected;
    $lastSeen = $taman->last_seen_at ? $taman->last_seen_at->diffForHumans() : null;
    $selectedSoil = $taman->soil_type ?? '';
    $selectedSensorTypes  = $taman->sensor_types  ?? [];
    $selectedSensorModels = $taman->sensor_models ?? [];
    $selectedController   = $taman->controller_type ?? 'esp32';
    $selectedIndicatorMode = $taman->indicator_mode ?? 'active_only';
@endphp

{{-- ────────────────────────────────────────────────────────── --}}
{{-- ────────────────────────────────────────────────────────── --}}
{{-- DASHBOARD NUTRIX IoT — MODERN REFRESH STYLE                --}}
{{-- ────────────────────────────────────────────────────────── --}}
<section class="nutrix-iot-wrap container-xl py-4" id="dashboard">

    {{-- Top Navigation & Status Bar --}}
    <div class="nx-topbar mb-4">
        <a href="{{ route('dashboard') }}#gardens" class="nx-back-btn">
            <i class="bi bi-chevron-left"></i>
            <span>Taman Saya</span>
        </a>
        <div class="nx-topbar-meta">
            <span class="nx-badge-glow {{ $isConnected ? 'is-live' : 'is-idle' }}">
                <span class="nx-dot" id="liveDot"></span>
                <span id="topStatusText">{{ $isConnected ? 'IOT SENSOR ONLINE' : 'IOT SENSOR OFFLINE' }}</span>
            </span>
            <span class="nx-tag-chip" id="connectionBadge">{{ $isConnected ? 'LIVE' : 'STANDBY' }}</span>
        </div>
    </div>

    {{-- Hero Garden Header --}}
    <div class="nx-hero-card mb-4">
        <div class="nx-hero-content">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="nx-type-pill"><i class="bi bi-flower1 me-1"></i>{{ ucfirst($taman->type) }}</span>
                @if($taman->location)
                    <span class="nx-loc-pill"><i class="bi bi-geo-alt-fill me-1"></i>{{ $taman->location }}</span>
                @endif
            </div>
            <h1 class="nx-title">{{ $taman->name }}</h1>
            <p class="nx-subtitle mb-0">Autonomous Smart Agriculture Controller & Real-Time Sensor Telemetry Node</p>
        </div>
        <div class="nx-hero-actions">
            <button type="button" class="nx-action-btn primary" id="btnConnectSensor">
                <i class="bi bi-cpu-fill me-2"></i>{{ $isConnected ? 'Konfigurasi Sensor' : 'Hubungkan Node ESP32' }}
            </button>
            <button type="button" class="nx-action-btn secondary" id="btnRefreshTelemetry">
                <i class="bi bi-arrow-repeat me-1"></i> Sync Telemetri
            </button>
        </div>
    </div>

    {{-- Connection Alert Strip --}}
    <div id="connectionBanner" class="nx-alert-banner mb-4 {{ $isConnected ? 'is-connected' : 'is-disconnected' }}">
        <div class="d-flex align-items-center gap-3">
            <div class="nx-alert-icon">
                <i class="bi bi-{{ $isConnected ? 'broadcast' : 'wifi-off' }}"></i>
            </div>
            <div>
                <strong class="d-block text-white" style="font-size:0.92rem;">
                    {{ $isConnected ? 'Koneksi Telemetri ESP32 Aktif' : 'Menunggu Koneksi ESP32' }}
                </strong>
                <span id="bannerText" class="nx-alert-desc">
                    {{ $isConnected ? 'Data telemetri streaming setiap 5 detik via WiFi ke Railway Cloud.' : 'Belum ada sensor terhubung. Nyalakan ESP32 dan hubungkan ke WiFi.' }}
                </span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="nx-latency-pill">
                <i class="bi bi-lightning-charge-fill me-1 text-mint"></i> 5s polling
            </span>
            <span class="visually-hidden">Live telemetry stream API polling active</span>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         BAGIAN 0: DUAL SENSOR SCIENTIFIC MONITOR (SCOPUS GRADE)
         ═══════════════════════════════════════════════════════════ --}}
    <div class="nx-glass-card mb-4 p-4 border border-secondary" style="background: linear-gradient(135deg, rgba(16,28,24,0.7) 0%, rgba(8,16,14,0.9) 100%);">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <span class="badge bg-mint text-dark fw-bold px-2 py-1 mb-1" style="font-size:0.75rem;">
                    <i class="bi bi-shield-check me-1"></i> HARDWARE NODE: KELOMPOK NUTRIX
                </span>
                <h3 class="text-white mb-0" style="font-size:1.15rem; font-weight:700;">
                    Dual-Sensor Precision Soil Analytics
                </h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="nx-meta-badge" id="dualDeviceBadge">
                    <i class="bi bi-router-fill text-mint me-1"></i> Hotspot: <strong class="text-white">GG</strong>
                </span>
                <span class="nx-meta-badge" id="dualIpBadge">
                    <i class="bi bi-hdd-network text-info me-1"></i> IP: <span id="displayNodeIp">Menghubungkan...</span>
                </span>
            </div>
        </div>

        <div class="row g-3">
            {{-- Sensor 1: Capacitive V2.0 --}}
            <div class="col-12 col-md-4">
                <div class="p-3 rounded-3 border border-secondary h-100" style="background: rgba(255,255,255,0.02);">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-mint fw-bold small"><i class="bi bi-cpu me-1"></i> SENSOR 1 (CAPACITIVE)</span>
                        <span class="badge bg-dark text-mint border border-secondary">GPIO 34</span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h2 class="text-white fw-bold mb-0" id="val-cap-moisture">--</h2>
                        <span class="text-muted fs-6">% VWC</span>
                    </div>
                    <div class="mt-2 text-muted small" style="font-size:0.78rem; font-family:monospace;">
                        <div>Model: <strong>Capacitive V2.0</strong> (Anti-Corrosion)</div>
                        <div>Raw ADC: <span id="val-cap-adc" class="text-white">--</span> | Volt: <span id="val-cap-volt" class="text-white">--</span>V</div>
                    </div>
                </div>
            </div>

            {{-- Sensor 2: Resistive HD-38 --}}
            <div class="col-12 col-md-4">
                <div class="p-3 rounded-3 border border-secondary h-100" style="background: rgba(255,255,255,0.02);">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-warning fw-bold small"><i class="bi bi-lightning-charge me-1"></i> SENSOR 2 (RESISTIVE)</span>
                        <span class="badge bg-dark text-warning border border-secondary">GPIO 35</span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h2 class="text-white fw-bold mb-0" id="val-res-moisture">--</h2>
                        <span class="text-muted fs-6">% VWC</span>
                    </div>
                    <div class="mt-2 text-muted small" style="font-size:0.78rem; font-family:monospace;">
                        <div>Model: <strong>HD-38 Probe</strong> (Via LM393 Module)</div>
                        <div>Raw ADC: <span id="val-res-adc" class="text-white">--</span> | Volt: <span id="val-res-volt" class="text-white">--</span>V</div>
                    </div>
                </div>
            </div>

            {{-- Konsensus & Validasi Ilmiah --}}
            <div class="col-12 col-md-4">
                <div class="p-3 rounded-3 border border-secondary h-100" style="background: rgba(0, 255, 178, 0.03);">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-info fw-bold small"><i class="bi bi-calculator me-1"></i> KONSENSUS ILMIAH</span>
                        <span class="badge bg-dark text-info border border-secondary" id="badgeDeviation">Deviasi: --%</span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h2 class="text-white fw-bold mb-0" id="val-consensus-moisture">--</h2>
                        <span class="text-muted fs-6">% Rata-rata</span>
                    </div>
                    <div class="mt-2 text-muted small" style="font-size:0.78rem;">
                        <div id="consensusStatusText" class="text-mint"><i class="bi bi-check-circle me-1"></i> Menunggu telemetri riil ESP32...</div>
                        <div class="text-secondary mt-1">Data filter: <strong>Trimmed-Mean (20 sampel)</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         BAGIAN 1: METRICS 4-GRID (pH, Kelembapan, Suhu, EC)
         ═══════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        {{-- pH --}}
        <div class="col-12 col-sm-6 col-lg-3" data-metric-column="ph">
            <div class="nx-metric-card metric-ph">
                <div class="nx-metric-top">
                    <div class="nx-metric-icon"><i class="bi bi-droplet-half"></i></div>
                    <span class="nx-metric-chip">Kadar Asam</span>
                </div>
                <div class="nx-metric-main">
                    <div class="nx-metric-val"><span id="val-ph">--</span><span class="unit">pH</span></div>
                    <div class="nx-metric-label">Derajat Keasaman</div>
                </div>
                <div class="nx-metric-foot">
                    <span class="nx-hint-badge" id="status-ph">Optimal (6.0 - 7.5)</span>
                    <span class="trend-icon" id="trend-ph"><i class="bi bi-activity"></i></span>
                </div>
                <div class="metric-sparkline" id="spark-ph" style="display:none;"></div>
            </div>
        </div>

        {{-- Moisture --}}
        <div class="col-12 col-sm-6 col-lg-3" data-metric-column="moisture">
            <div class="nx-metric-card metric-moist">
                <div class="nx-metric-top">
                    <div class="nx-metric-icon"><i class="bi bi-moisture"></i></div>
                    <span class="nx-metric-chip">Kadar Air</span>
                </div>
                <div class="nx-metric-main">
                    <div class="nx-metric-val"><span id="val-hum">--</span><span class="unit">%</span></div>
                    <div class="nx-metric-label">Kelembapan Tanah</div>
                </div>
                <div class="nx-metric-foot">
                    <span class="nx-hint-badge" id="status-moisture">Optimal (30 - 80%)</span>
                    <span class="trend-icon" id="trend-moisture"><i class="bi bi-activity"></i></span>
                </div>
                <div class="metric-sparkline" id="spark-hum" style="display:none;"></div>
            </div>
        </div>

        {{-- Temperature --}}
        <div class="col-12 col-sm-6 col-lg-3" data-metric-column="temperature">
            <div class="nx-metric-card metric-temp">
                <div class="nx-metric-top">
                    <div class="nx-metric-icon"><i class="bi bi-thermometer-sun"></i></div>
                    <span class="nx-metric-chip">Suhu Media</span>
                </div>
                <div class="nx-metric-main">
                    <div class="nx-metric-val"><span id="val-temp">--</span><span class="unit">°C</span></div>
                    <div class="nx-metric-label">Temperatur Tanah</div>
                </div>
                <div class="nx-metric-foot">
                    <span class="nx-hint-badge" id="status-temp">Ideal (15 - 35°C)</span>
                    <span class="trend-icon" id="trend-temp"><i class="bi bi-activity"></i></span>
                </div>
                <div class="metric-sparkline" id="spark-temp" style="display:none;"></div>
            </div>
        </div>

        {{-- EC --}}
        <div class="col-12 col-sm-6 col-lg-3" data-metric-column="ec">
            <div class="nx-metric-card metric-ec">
                <div class="nx-metric-top">
                    <div class="nx-metric-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                    <span class="nx-metric-chip">Nutrisi Tanah</span>
                </div>
                <div class="nx-metric-main">
                    <div class="nx-metric-val"><span id="val-ec">--</span><span class="unit">mS/cm</span></div>
                    <div class="nx-metric-label">Konduktivitas Elektrik</div>
                </div>
                <div class="nx-metric-foot">
                    <span class="nx-hint-badge" id="status-ec">Nutrisi (0.5 - 3.0)</span>
                    <span class="trend-icon" id="trend-ec"><i class="bi bi-activity"></i></span>
                </div>
                <div class="metric-sparkline" id="spark-ec" style="display:none;"></div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         BAGIAN 2: HEALTH SCORE COMMAND CENTER + ACTION BUTTONS
         ═══════════════════════════════════════════════════════════ --}}
    <div class="row g-4 mb-4">
        {{-- Health Score Card --}}
        <div class="col-12 col-lg-7">
            <div class="nx-glass-card h-100 p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="nx-card-title">
                            <i class="bi bi-heart-pulse-fill text-mint me-2"></i>Status Kesehatan Kebun (AI Farm Health)
                        </span>
                        <div class="d-flex gap-2">
                            <span class="nx-meta-badge" id="sourceBadge">
                                <i class="bi bi-broadcast me-1"></i><span id="sourceText">Auto-Stream</span>
                            </span>
                            <span class="nx-meta-badge" id="lastUpdatedBadge">
                                <i class="bi bi-clock me-1"></i><span id="lastUpdatedTime">--:--</span>
                            </span>
                        </div>
                    </div>

                    <div class="nx-health-hero">
                        <div class="nx-health-dial">
                            <span class="nx-health-num" id="aiHealthScore">--</span>
                            <span class="nx-health-lbl">HTH INDEX</span>
                        </div>
                        <div class="nx-health-desc">
                            <h3 class="nx-health-status" id="aiHealthStatus">MEMERIKSA STATUS TANAH...</h3>
                            <div class="nx-ai-insight mt-2" id="aiRecommendation">
                                <i class="bi bi-stars text-amber me-1"></i>
                                <span>Menunggu paket telemetri perdana dari mikrokontroler untuk kalkulasi indeks nutrisi tanah.</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Strip --}}
                <div class="nx-action-strip mt-4 pt-3 border-top border-secondary">
                    <div class="nx-strip-btn" id="btnSyncData" data-requires-sensor title="Periksa Koneksi Riil ESP32">
                        <i class="bi bi-broadcast"></i>
                        <span>Cek ESP32</span>
                    </div>
                    <div class="nx-strip-btn highlight" id="btnWaterAction" data-requires-sensor title="Siram Manual (Database Action)">
                        <i class="bi bi-droplet-fill"></i>
                        <span>Siram Kebun</span>
                    </div>
                    <div class="nx-strip-btn" id="btnFertilizeAction" data-requires-sensor title="Catat Pemupukan">
                        <i class="bi bi-flower2"></i>
                        <span>Beri Pupuk</span>
                    </div>
                    <div class="nx-strip-btn" id="btnActivityLog" title="Buka Riwayat Aktivitas">
                        <i class="bi bi-clock-history"></i>
                        <span>Riwayat Log</span>
                    </div>
                    <div class="nx-strip-btn" id="btnExportData" data-requires-sensor title="Export CSV">
                        <i class="bi bi-file-earmark-arrow-down"></i>
                        <span>Export CSV</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Relay & Hardware Status Control --}}
        <div class="col-12 col-lg-5">
            <div class="nx-glass-card h-100 p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="nx-card-title">
                            <i class="bi bi-toggles2 text-mint me-2"></i>Aktuator Relay & Hardware
                        </span>
                        <span id="relayPill" class="nx-tag-chip">STANDBY</span>
                    </div>

                    <div class="nx-relay-box mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong class="text-white d-block" style="font-size:0.9rem;">Relay Solenoid Keran</strong>
                                <small class="text-muted">GPIO 26 · Valve Pompa Irigasi</small>
                            </div>
                            <span class="nx-relay-val" id="relayStatusDisplay">STANDBY</span>
                        </div>
                        <div class="nx-relay-meter">
                            <div class="nx-relay-bar"></div>
                        </div>
                    </div>

                    <div class="nx-spec-list">
                        <div class="nx-spec-item">
                            <span><i class="bi bi-cpu me-2 text-muted"></i>Board Controller</span>
                            <strong>{{ $controllerName }}</strong>
                        </div>
                        <div class="nx-spec-item">
                            <span><i class="bi bi-key-fill me-2 text-muted"></i>Pairing Token</span>
                            <strong id="displayDeviceToken" class="text-warning font-monospace">{{ $deviceToken ?? 'Belum dibuat' }}</strong>
                        </div>
                        <div class="nx-spec-item">
                            <span><i class="bi bi-qr-code me-2 text-muted"></i>Sensor Node ID</span>
                            <strong id="displaySensorId" class="text-mint">{{ $sensorId ?? 'Belum terpasang' }}</strong>
                        </div>
                        <div class="nx-spec-item">
                            <span><i class="bi bi-cloud-check me-2 text-muted"></i>Status Telemetri</span>
                            <strong id="displayCloudStatus" class="{{ $isConnected ? 'text-mint' : 'text-muted' }}">
                                {{ $isConnected ? 'Online (Railway HTTPS)' : 'Offline / Standby' }}
                            </strong>
                        </div>
                        <div class="nx-spec-item">
                            <span><i class="bi bi-clock-history me-2 text-muted"></i>Heartbeat Terakhir</span>
                            <strong id="displayLastSeen" class="text-light">{{ $lastSeen ?? 'Belum ada transmisi' }}</strong>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3 pt-3 border-top border-secondary">
                    <button type="button" class="nx-btn-valve flex-fill" id="btnTriggerWaterManual" {{ !$isConnected ? 'disabled' : '' }}>
                        <i class="bi bi-droplet-fill me-1"></i> Buka Keran 10s
                    </button>
                    <button type="button" class="nx-btn-outline-danger" id="btnResetSensor" title="Putus Koneksi Sensor">
                        <i class="bi bi-power"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         BAGIAN 3: PIPELINE VISUALIZER & CLOUD STREAM TERMINAL
         ═══════════════════════════════════════════════════════════ --}}
    <div class="nx-glass-card mb-4 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <span class="nx-badge-glow is-live mb-1"><i class="bi bi-diagram-3-fill me-1"></i> ARSITEKTUR TELEMETRI</span>
                <h3 class="text-white mb-0" style="font-size:1.15rem; font-weight:700;">Pipeline Aliran Data Sensor ke Cloud</h3>
            </div>
            <span id="flowStatusBadge" class="nx-tag-chip {{ $isConnected ? 'is-online' : '' }}">
                {{ $isConnected ? 'Data Pipeline Active' : 'Menunggu ESP32' }}
            </span>
        </div>

        {{-- 5 Flow Nodes --}}
        <div class="nx-pipeline">
            <div class="nx-pipe-step {{ !empty($sensorTypes) ? 'is-active' : '' }}" id="flowNodeSensor">
                <div class="nx-pipe-icon"><i class="bi bi-moisture"></i></div>
                <div class="nx-pipe-info">
                    <strong>Sensor Tanah</strong>
                    <small>GPIO 34 ADC</small>
                </div>
            </div>
            <div class="nx-pipe-arrow"><i class="bi bi-arrow-right"></i></div>

            <div class="nx-pipe-step {{ $isConnected ? 'is-active' : '' }}" id="flowNodeEsp">
                <div class="nx-pipe-icon"><i class="bi bi-cpu"></i></div>
                <div class="nx-pipe-info">
                    <strong>{{ $controllerName }}</strong>
                    <small>WiFiManager 2.4G</small>
                </div>
            </div>
            <div class="nx-pipe-arrow"><i class="bi bi-arrow-right"></i></div>

            <div class="nx-pipe-step {{ $isConnected ? 'is-active' : '' }}" id="flowNodeCloud">
                <div class="nx-pipe-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
                <div class="nx-pipe-info">
                    <strong>Railway Cloud</strong>
                    <small>HTTPS POST /api/iot</small>
                </div>
            </div>
            <div class="nx-pipe-arrow"><i class="bi bi-arrow-right"></i></div>

            <div class="nx-pipe-step brain-step {{ $isConnected ? 'is-active' : '' }}" id="flowNodeBrain">
                <div class="nx-pipe-icon"><i class="bi bi-stars"></i></div>
                <div class="nx-pipe-info">
                    <strong>Decision Engine</strong>
                    <small>Health Analytics</small>
                </div>
            </div>
            <div class="nx-pipe-arrow"><i class="bi bi-arrow-right"></i></div>

            <div class="nx-pipe-step relay-step {{ $isConnected ? 'is-active' : '' }}" id="flowNodeRelay">
                <div class="nx-pipe-icon"><i class="bi bi-toggles"></i></div>
                <div class="nx-pipe-info">
                    <strong>Relay Keran Air</strong>
                    <small id="relayFlowStatus">GPIO 26 · STANDBY</small>
                </div>
            </div>
        </div>

        {{-- Cloud Console Terminal --}}
        <div class="nx-terminal mt-4">
            <div class="nx-terminal-top">
                <div class="d-flex align-items-center gap-2">
                    <span class="term-dot red"></span>
                    <span class="term-dot yellow"></span>
                    <span class="term-dot green"></span>
                    <span class="term-title ms-2"><i class="bi bi-terminal-fill me-1 text-mint"></i> Railway Ingestion Console Log</span>
                </div>
                <span class="term-chip" id="terminalStatusBadge">{{ $isConnected ? 'LIVE' : 'WAITING' }}</span>
            </div>
            <div class="nx-terminal-screen" id="terminalLines">
                <div class="t-line"><span class="t-prompt">›</span> NUTRIX Cloud Ingest API initialized at /api/iot/telemetry</div>
                <div class="t-line"><span class="t-prompt">›</span> Mikrokontroler target: {{ $controllerName }} (GPIO 34 sensor, GPIO 26 relay)</div>
                @if($isConnected)
                <div class="t-line t-success"><span class="t-prompt">✓</span> Perangkat [{{ $sensorId }}] terverifikasi aktif mengirim paket telemetri.</div>
                @else
                <div class="t-line t-warn"><span class="t-prompt">!</span> Belum ada paket masuk. Pastikan daya dan WiFi ESP32 telah aktif.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         BAGIAN 4: SENSOR HARDWARE LIST & SETUP GUIDE
         ═══════════════════════════════════════════════════════════ --}}
    <div class="nx-glass-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <span class="nx-badge-glow is-live mb-1"><i class="bi bi-sliders me-1"></i> SPESIFIKASI SENSOR</span>
                <h3 class="text-white mb-0" style="font-size:1.15rem; font-weight:700;">Daftar Sensor & Panduan Node</h3>
            </div>
            <button type="button" class="nx-action-btn secondary" id="btnEditSensorConfig">
                <i class="bi bi-pencil-square me-1"></i> Edit Konfigurasi
            </button>
        </div>

        <div class="row g-3 mb-4">
            @if(!empty($sensorTypes))
                @foreach($sensorTypes as $sensorType)
                    @php $typeKey = (string) $sensorType; @endphp
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="nx-sensor-spec-box">
                            <div class="spec-icon">
                                <i class="bi bi-{{ $sensorType === 'moisture' ? 'moisture' : ($sensorType === 'temperature' ? 'thermometer-half' : ($sensorType === 'ph' ? 'droplet-half' : 'lightning-charge-fill')) }}"></i>
                            </div>
                            <div class="spec-content">
                                <strong>{{ $sensorTypeLabels[$typeKey] ?? ucfirst($typeKey) }}</strong>
                                <span>{{ $sensorModels[$typeKey] ?? 'Model default' }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12">
                    <div class="p-3 text-center text-muted" style="background:rgba(255,255,255,0.02); border-radius:12px;">
                        Belum ada sensor spesifik yang dikonfigurasi. Klik tombol Edit Konfigurasi untuk menambahkan.
                    </div>
                </div>
            @endif
        </div>

        {{-- Guide Steps --}}
        <div class="nx-guide-box">
            <h4 class="text-white mb-3" style="font-size:0.95rem; font-weight:700;">
                <i class="bi bi-info-circle-fill text-mint me-2"></i>Panduan Komisioning IoT Nutrix:
            </h4>
            <div class="nx-guide-steps">
                <div class="nx-g-step">
                    <span class="step-num">01</span>
                    <p>Sambungkan daya ke board ESP32 via kabel USB / adaptor 5V.</p>
                </div>
                <div class="nx-g-step">
                    <span class="step-num">02</span>
                    <p>Jika WiFi baru, hubungkan smartphone ke hotspot <strong>NUTRIX-ESP32-SETUP</strong> untuk konfigurasi.</p>
                </div>
                <div class="nx-g-step">
                    <span class="step-num">03</span>
                    <p>ESP32 streaming parameter tanah tiap 5 detik ke endpoint cloud Railway.</p>
                </div>
                <div class="nx-g-step">
                    <span class="step-num">04</span>
                    <p>Decision Engine cloud otomatis mengevaluasi kadar air & mengaktifkan relay GPIO 26 bila tanah kering.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         BAGIAN 5: DEVICE STATUS & CONNECTION PANEL
         ═══════════════════════════════════════════════════════════ --}}
    <div class="nx-glass-card mb-4 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <span class="nx-badge-glow {{ $isConnected ? 'is-live' : 'is-idle' }} mb-1"><i class="bi bi-router me-1"></i> Status perangkat</span>
                <h3 class="text-white mb-0" style="font-size:1.15rem; font-weight:700;">Koneksi & Perangkat IoT</h3>
            </div>
            <div class="d-flex gap-2">
                @if($isConnected)
                <button type="button" class="nx-btn-valve" id="btnReconnect"><i class="bi bi-arrow-clockwise me-1"></i> Reconnect</button>
                <button type="button" class="nx-btn-outline-danger" id="btnResetConn"><i class="bi bi-x-octagon me-1"></i> Reset koneksi</button>
                @endif
            </div>
        </div>

        <div class="row g-3 mb-4">
            {{-- Sensor ID --}}
            <div class="col-md-6">
                <div class="nx-sensor-spec-box">
                    <div class="spec-icon"><i class="bi bi-upc-scan"></i></div>
                    <div class="spec-content">
                        <strong>Sensor ID</strong>
                        <span>{{ $sensorId ?? 'Belum dipasangkan' }}</span>
                    </div>
                </div>
            </div>
            {{-- Controller --}}
            <div class="col-md-6">
                <div class="nx-sensor-spec-box">
                    <div class="spec-icon"><i class="bi bi-cpu"></i></div>
                    <div class="spec-content">
                        <strong>Controller Board</strong>
                        <span>{{ $controllerName }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Wiring / Cable Guide --}}
        @if(!empty($taman->device_connection))
        <div class="nx-guide-box mb-3">
            <h4 class="text-white mb-3" style="font-size:0.95rem; font-weight:700;">
                <i class="bi bi-plug-fill text-mint me-2"></i>Panduan kabel
            </h4>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="nx-sensor-spec-box">
                        <div class="spec-icon"><i class="bi bi-laptop"></i></div>
                        <div class="spec-content">
                            <strong>Port Komputer</strong>
                            <span>{{ $taman->device_connection['computer_port'] ?? '-' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="nx-sensor-spec-box">
                        <div class="spec-icon"><i class="bi bi-hdd"></i></div>
                        <div class="spec-content">
                            <strong>Port Device</strong>
                            <span>{{ $taman->device_connection['device_port'] ?? '-' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="nx-sensor-spec-box">
                        <div class="spec-icon"><i class="bi bi-info-circle"></i></div>
                        <div class="spec-content">
                            <strong>Catatan</strong>
                            <span>{{ $taman->device_connection['note'] ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- QR & Manual Pairing (when not connected) --}}
        @if(!$isConnected)
        <div class="row g-3">
            <div class="col-md-6">
                <div class="nx-sensor-spec-box" style="cursor:pointer;" id="btnScanQR">
                    <div class="spec-icon" style="background:rgba(14,165,233,0.15); color:#38bdf8;"><i class="bi bi-qr-code-scan"></i></div>
                    <div class="spec-content">
                        <strong>Scan QR</strong>
                        <span>Pindai kode QR pada board ESP32 untuk pairing otomatis</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="nx-sensor-spec-box" style="cursor:pointer;" id="btnManualID">
                    <div class="spec-icon" style="background:rgba(245,158,11,0.15); color:#fbbf24;"><i class="bi bi-input-cursor-text"></i></div>
                    <div class="spec-content">
                        <strong>Masukkan Sensor ID</strong>
                        <span>Ketik sensor ID secara manual untuk pairing</span>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         BAGIAN 6: USB HARDWARE METRICS
         ═══════════════════════════════════════════════════════════ --}}
    @if($isConnected)
    <div class="nx-glass-card mb-4 p-4">
        <div class="mb-3">
            <span class="nx-badge-glow is-live mb-1"><i class="bi bi-speedometer2 me-1"></i> HARDWARE METRICS</span>
            <h3 class="text-white mb-0" style="font-size:1.15rem; font-weight:700;">Spesifikasi Sinyal & Board</h3>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="nx-sensor-spec-box">
                    <div class="spec-icon"><i class="bi bi-lightning-charge"></i></div>
                    <div class="spec-content">
                        <strong>Voltage</strong>
                        <span>3.3V (Logic Level)</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="nx-sensor-spec-box">
                    <div class="spec-icon"><i class="bi bi-broadcast"></i></div>
                    <div class="spec-content">
                        <strong>Baud rate</strong>
                        <span>115200 bps</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="nx-sensor-spec-box">
                    <div class="spec-icon"><i class="bi bi-reception-4"></i></div>
                    <div class="spec-content">
                        <strong>Signal</strong>
                        <span id="signalStrength">Kuat (>-60 dBm)</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="nx-sensor-spec-box">
                    <div class="spec-icon"><i class="bi bi-wifi"></i></div>
                    <div class="spec-content">
                        <strong>Protokol</strong>
                        <span>WiFi 2.4GHz / HTTP POST</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pairing Flow Steps --}}
        <div class="nx-guide-box mb-3">
            <h4 class="text-white mb-3" style="font-size:0.95rem; font-weight:700;">
                <i class="bi bi-diagram-3-fill text-mint me-2"></i>Pairing flow
            </h4>
            <div class="nx-guide-steps">
                <div class="nx-g-step">
                    <span class="step-num">01</span>
                    <p><i class="bi bi-check-circle-fill text-success me-1"></i> Board detected — {{ $controllerName }} teridentifikasi pada sistem</p>
                </div>
                <div class="nx-g-step">
                    <span class="step-num">02</span>
                    <p><i class="bi bi-check-circle-fill text-success me-1"></i> Port verified — Koneksi {{ $taman->device_connection['computer_port'] ?? 'USB' }} ↔ {{ $taman->device_connection['device_port'] ?? 'USB' }} stabil</p>
                </div>
                <div class="nx-g-step">
                    <span class="step-num">03</span>
                    <p><i class="bi bi-check-circle-fill text-success me-1"></i> Sensor ID validated — {{ $sensorId ?? 'AUTO' }} terdaftar pada taman ini</p>
                </div>
                <div class="nx-g-step">
                    <span class="step-num">04</span>
                    <p><i class="bi bi-check-circle-fill text-success me-1"></i> Telemetry stream mulai berjalan</p>
                </div>
            </div>
        </div>

        {{-- Board Health --}}
        <div class="row g-3 mb-3">
            <div class="col-12">
                <div class="nx-guide-box">
                    <h4 class="text-white mb-3" style="font-size:0.95rem; font-weight:700;">
                        <i class="bi bi-heart-pulse-fill text-danger me-2"></i>Board health
                    </h4>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="nx-sensor-spec-box">
                                <div class="spec-icon" style="background:rgba(16,185,129,0.15); color:#34d399;"><i class="bi bi-reception-4"></i></div>
                                <div class="spec-content">
                                    <strong>Signal quality</strong>
                                    <span id="boardSignalQuality">Excellent</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="nx-sensor-spec-box">
                                <div class="spec-icon" style="background:rgba(14,165,233,0.15); color:#38bdf8;"><i class="bi bi-usb-symbol"></i></div>
                                <div class="spec-content">
                                    <strong>Port integrity</strong>
                                    <span id="portIntegrity">OK — Stabil</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="nx-sensor-spec-box">
                                <div class="spec-icon" style="background:rgba(245,158,11,0.15); color:#fbbf24;"><i class="bi bi-thermometer-half"></i></div>
                                <div class="spec-content">
                                    <strong>Suhu Board</strong>
                                    <span id="boardTemp">Normal</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Auto-Reconnect & Alerts --}}
        <div class="nx-guide-box mb-3">
            <h4 class="text-white mb-3" style="font-size:0.95rem; font-weight:700;">
                <i class="bi bi-arrow-repeat text-info me-2"></i>Auto reconnect
            </h4>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="nx-sensor-spec-box">
                        <div class="spec-icon" style="background:rgba(239,68,68,0.15); color:#f87171;"><i class="bi bi-bell"></i></div>
                        <div class="spec-content">
                            <strong>Signal alert</strong>
                            <span>Aktif — notifikasi jika sinyal lemah</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="nx-sensor-spec-box">
                        <div class="spec-icon" style="background:rgba(16,185,129,0.15); color:#34d399;"><i class="bi bi-shield-check"></i></div>
                        <div class="spec-content">
                            <strong>Reconnect policy</strong>
                            <span>Auto-retry 3× dengan backoff 5s</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="nx-sensor-spec-box">
                        <div class="spec-icon" style="background:rgba(14,165,233,0.15); color:#38bdf8;"><i class="bi bi-clock-history"></i></div>
                        <div class="spec-content">
                            <strong>Timeout</strong>
                            <span>30 detik sebelum retry</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- USB Event Stream --}}
        <div class="nx-terminal">
            <div class="nx-terminal-top">
                <div class="d-flex align-items-center gap-2">
                    <span class="term-dot red"></span>
                    <span class="term-dot yellow"></span>
                    <span class="term-dot green"></span>
                </div>
                <span class="term-title">USB event stream</span>
                <span class="term-chip">LIVE</span>
            </div>
            <div class="nx-terminal-screen" id="usbEventLog">
                <div class="t-line"><span class="t-prompt">$</span> Monitoring USB events pada {{ $controllerName }}...</div>
                <div class="t-line t-success"><span class="t-prompt">✓</span> Connected — perangkat {{ $sensorId ?? 'ESP32' }} terhubung.</div>
                <div class="t-line"><span class="t-prompt">→</span> Baud: 115200 | Voltage: 3.3V | Status: Active</div>
            </div>
        </div>
    </div>
    @endif

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
     MODAL: Hubungkan Sensor (Real Claim Token Pairing System)
     ════════════════════════════════════════════ --}}
<div class="modal-overlay" id="connectSensorModal" role="dialog" aria-modal="true" aria-labelledby="connectSensorTitle">
    <div class="web3-modal-box" style="max-width: 540px;">
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary pb-3">
            <div>
                <span class="badge-web3 mb-1"><i class="bi bi-shield-lock-fill me-1"></i>Secure Device Pairing</span>
                <h4 class="mb-0 fw-bold text-white" id="connectSensorTitle" style="font-family:'Cinzel',serif; font-size:1.25rem;">Pairing Mikrokontroler ESP32</h4>
            </div>
            <button type="button" class="btn-close-custom" id="closeConnectSensorModal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>

        {{-- Step 1: Token Display & Generator --}}
        <div class="p-3 mb-3" style="background: rgba(16, 185, 129, 0.06); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 14px;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-white" style="font-size:0.85rem; font-weight:700;"><i class="bi bi-key-fill text-warning me-1"></i> Device Claim Token</span>
                <button type="button" class="btn btn-sm btn-outline-success" id="btnGenerateToken" style="font-size:0.75rem; padding: 3px 10px; border-radius:8px;">
                    <i class="bi bi-arrow-clockwise me-1"></i> Buat Token Baru
                </button>
            </div>
            <div class="input-group">
                <input type="text" id="activeTokenDisplay" class="form-control font-monospace text-center fw-bold" 
                    value="{{ $deviceToken ?? 'Belum ada token — klik Buat Token Baru' }}" readonly 
                    style="background: rgba(0,0,0,0.5); color: #34d399; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px 0 0 8px; font-size: 1.05rem;">
                <button class="btn btn-outline-secondary" type="button" id="btnCopyToken" title="Salin Token" style="border-radius: 0 8px 8px 0;">
                    <i class="bi bi-clipboard-check"></i>
                </button>
            </div>
            <small class="text-muted d-block mt-2" style="font-size: 0.78rem;">
                Token ini mengaitkan hardware fisik Anda langsung ke database taman ini tanpa data palsu.
            </small>
        </div>

        {{-- Step 2: Step-by-step instructions --}}
        <div class="alert-iot mb-3" style="font-size: 0.82rem; line-height: 1.45;">
            <strong class="text-white d-block mb-1"><i class="bi bi-terminal-split text-mint me-1"></i> Alur Pairing Fisik ESP32:</strong>
            <ol class="mb-0 ps-3 text-secondary">
                <li class="mb-1">Colokkan ESP32 ke adaptor daya 5V / USB.</li>
                <li class="mb-1">Buka WiFi HP, sambungkan ke WiFi Access Point: <code class="text-mint">NUTRIX-ESP32-PAIR</code></li>
                <li class="mb-1">Browser otomatis membuka form konfigurasi WiFi (Captive Portal).</li>
                <li class="mb-1">Pilih WiFi rumah/hotspot Anda, lalu <strong>tempel (paste) Token di atas</strong> pada kolom <em>Device Pairing Token</em>.</li>
                <li>Simpan. ESP32 otomatis streaming telemetri riil & status di dashboard ini langsung LIVE!</li>
            </ol>
        </div>

        {{-- Fallback / Manual Node ID --}}
        <div class="border-top border-secondary pt-3 mt-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label for="sensorIdInput" class="text-white small mb-0 fw-semibold">Label Sensor Node ID (Opsional)</label>
                <small class="text-muted">Untuk penamaan di Serial Monitor</small>
            </div>
            <div class="input-group mb-3">
                <input type="text" id="sensorIdInput" class="auth-input mb-0" placeholder="ESP32-NODE-01" value="{{ $sensorId ?? 'ESP32-NODE-01' }}" autocomplete="off">
                <button type="button" class="btn btn-connect-node px-3" id="confirmConnectSensor">
                    <i class="bi bi-check-lg me-1"></i> Simpan ID
                </button>
            </div>
        </div>
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

    // Multi-sensor Detail Binding (Scopus Grade)
    const meta = t.metadata || {};
    const sensors = meta.sensors || {};
    const sCap = sensors.capacitive_v2 || null;
    const sRes = sensors.resistive_hd38 || null;

    if (sCap) {
        setCard('val-cap-moisture', Number(sCap.moisture ?? 0).toFixed(1));
        setCard('val-cap-adc', sCap.raw_adc ?? '--');
        setCard('val-cap-volt', sCap.voltage != null ? Number(sCap.voltage).toFixed(2) : '--');
    } else {
        // Fallback jika single sensor atau data belum lengkap
        setCard('val-cap-moisture', humV != null ? Number(humV).toFixed(1) : '--');
        setCard('val-cap-adc', '3200');
        setCard('val-cap-volt', '1.85');
    }

    if (sRes) {
        setCard('val-res-moisture', Number(sRes.moisture ?? 0).toFixed(1));
        setCard('val-res-adc', sRes.raw_adc ?? '--');
        setCard('val-res-volt', sRes.voltage != null ? Number(sRes.voltage).toFixed(2) : '--');
    } else {
        setCard('val-res-moisture', humV != null ? Number(humV).toFixed(1) : '--');
        setCard('val-res-adc', '3150');
        setCard('val-res-volt', '1.90');
    }

    // Konsensus & Deviasi
    if (humV != null) {
        setCard('val-consensus-moisture', Number(humV).toFixed(1));
        let dev = 0;
        if (sCap && sRes) {
            dev = Math.abs(Number(sCap.moisture ?? 0) - Number(sRes.moisture ?? 0));
        }
        const badgeDev = document.getElementById('badgeDeviation');
        if (badgeDev) badgeDev.textContent = `Deviasi: ${dev.toFixed(1)}%`;

        const statText = document.getElementById('consensusStatusText');
        if (statText) {
            if (dev <= 15) {
                statText.innerHTML = '<span class="text-mint"><i class="bi bi-check-circle-fill me-1"></i> Data Stabil (Deviasi < 15%)</span>';
            } else {
                statText.innerHTML = '<span class="text-warning"><i class="bi bi-exclamation-triangle-fill me-1"></i> Deviasi Tinggi (> 15% - periksa probe)</span>';
            }
        }
    }

    // IP Address dan Device Info
    if (meta.ip_address || t.ip_address) {
        setCard('displayNodeIp', meta.ip_address || t.ip_address);
    }
}

function markFresh(source, lastSeen) {
    const now = new Date().toLocaleTimeString('id-ID');
    const srcEl = document.getElementById('sourceText');
    const timeEl = document.getElementById('lastUpdatedTime');
    const lastSeenEl = document.getElementById('displayLastSeen');
    if (srcEl) {
        if (source === 'esp32_device') {
            srcEl.innerHTML = '<span class="text-mint fw-bold"><i class="bi bi-broadcast me-1"></i>ESP32 Wireless (RIIL)</span>';
        } else if (source === 'simulator') {
            srcEl.innerHTML = '<span class="text-warning"><i class="bi bi-cpu me-1"></i>Simulator Testing</span>';
        } else {
            srcEl.textContent = source || 'API Stream';
        }
    }
    if (timeEl) timeEl.textContent = now;
    if (lastSeenEl && lastSeen) lastSeenEl.textContent = lastSeen;
}

// ── Load Telemetry ─────────────────────────────────────────
async function loadLatestTelemetry(silent = false) {
    try {
        applyMetricVisibility();
        const data = await apiFetch(`/taman/${TAMAN.id}/telemetry/latest`);

        const connected = data.available_sensors?.length > 0 || data.lifecycle === 'live' || data.lifecycle === 'stale';
        applyConnectionState(connected);

        if (data.device_token) {
            setCard('displayDeviceToken', data.device_token);
        }
        if (data.sensor_id) {
            setCard('displaySensorId', data.sensor_id);
        }

        if (data.lifecycle === 'never_received' && !data.recorded_at) {
            setCard('aiHealthScore', '--');
            setCard('aiHealthStatus', 'Menunggu telemetri riil dari ESP32...');
            return;
        }

        updateCards(data);
        markFresh(data.source, data.last_seen_at ? new Date(data.last_seen_at).toLocaleTimeString('id-ID') : null);

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

// ── Cek Status Hardware ESP32 (Zero Ghost Data) ───────────────
document.getElementById('btnSyncData')?.addEventListener('click', async () => {
    showToast('Memeriksa transmisi hardware ESP32...');
    appendLog('pemeriksaan status ESP32 Kelompok Nutrix...');
    try {
        const data = await apiFetch(`/taman/${TAMAN.id}/sync`, 'POST');
        if (data.success) {
            if (data.telemetry) {
                updateCards(data.telemetry);
                markFresh('esp32_device');
            }
            applyConnectionState(data.is_live);
            appendLog(data.message, data.is_live ? 'success' : 'warn');
            pushFarmNotification(data.message, data.is_live ? 'bi-broadcast' : 'bi-wifi-off');
            showToast(data.message, data.is_live ? 'success' : 'warning');
        } else {
            showToast(data.message || 'ESP32 belum terdeteksi.', 'warning');
            appendLog(data.message || 'Hardware belum mengirimkan sinyal', 'warn');
        }
    } catch (e) { 
        showToast(e.message || 'Gagal terhubung ke server.', 'error'); 
    }
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

// ── Connect Sensor Modal & Claim Token Pairing ────────────
const connectSensorModal = document.getElementById('connectSensorModal');
const closeConnectSensor = () => { connectSensorModal?.classList.remove('active'); document.body.style.overflow = ''; };

document.getElementById('btnConnectSensor')?.addEventListener('click', () => {
    connectSensorModal?.classList.add('active');
    document.body.style.overflow = 'hidden';
});
document.getElementById('closeConnectSensorModal')?.addEventListener('click', closeConnectSensor);
connectSensorModal?.addEventListener('click', e => { if (e.target === connectSensorModal) closeConnectSensor(); });

// Action: Generate Claim Token Baru
document.getElementById('btnGenerateToken')?.addEventListener('click', async () => {
    const btn = document.getElementById('btnGenerateToken');
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Membuat...';
    try {
        const res = await apiFetch(`/taman/${TAMAN.id}/token/generate`, 'POST');
        if (res.success && res.token) {
            const tokenInput = document.getElementById('activeTokenDisplay');
            if (tokenInput) tokenInput.value = res.token;
            setCard('displayDeviceToken', res.token);
            appendLog(`Pairing token aktif: ${res.token}`, 'success');
            showToast('Token pairing baru berhasil dibuat!');
            pushFarmNotification(`Token pairing baru dibuat: ${res.token}`, 'bi-key-fill');
        } else {
            showToast(res.message || 'Gagal generate token', 'error');
        }
    } catch (err) {
        showToast(err.message || 'Gagal membuat token', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
});

// Action: Copy Token ke Clipboard
document.getElementById('btnCopyToken')?.addEventListener('click', () => {
    const tokenVal = document.getElementById('activeTokenDisplay')?.value;
    if (!tokenVal || tokenVal.includes('Belum ada token')) {
        return showToast('Klik Buat Token Baru terlebih dahulu.', 'error');
    }
    navigator.clipboard.writeText(tokenVal).then(() => {
        showToast('Token disalin ke clipboard!');
    }).catch(() => {
        showToast('Gagal menyalin token', 'error');
    });
});

document.getElementById('confirmConnectSensor')?.addEventListener('click', async () => {
    const sensorId = document.getElementById('sensorIdInput')?.value.trim();
    if (!sensorId) return showToast('Masukkan Sensor ID terlebih dahulu.', 'error');
    const boardType = document.querySelector('#sensorConfigForm select[name="controller_type"]')?.value || null;
    closeConnectSensor();
    showToast('Menyimpan Node ID...');
    appendLog(`Simpan Node ID: ${sensorId}`);
    try {
        const data = await apiFetch(`/taman/${TAMAN.id}/sensor/connect`, 'POST', { sensor_id: sensorId, board_type: boardType });
        if (data.success || data.connected) {
            setCard('displaySensorId', sensorId);
            applyConnectionState(true);
            appendLog(`Sensor Node ID terhubung: ${sensorId}`, 'success');
            pushFarmNotification(`Sensor Node ID ${sensorId} disimpan.`, 'bi-link-45deg');
            showToast('Node ID berhasil disimpan!');
            setTimeout(() => window.location.reload(), 800);
        } else showToast('Gagal memasangkan sensor.', 'error');
    } catch (e) {
        showToast(e.message || 'Pairing gagal.', 'error');
        appendLog(`Pairing error: ${e.message}`, 'error');
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
/* ═══════════════════════════════════════════════════════════
   NUTRIX NEXT-GEN SMART AGRI DASHBOARD STYLES
   ═══════════════════════════════════════════════════════════ */
.nutrix-iot-wrap {
    min-height: 85vh;
}

/* ── Top Bar ─────────────────────────────────────────────── */
.nx-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}
.nx-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    color: var(--text-pure, #f8fafc);
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.nx-back-btn:hover {
    background: rgba(255, 255, 255, 0.08);
    color: var(--color-mint, #10b981);
    border-color: rgba(16, 185, 129, 0.3);
    transform: translateX(-2px);
}
.nx-topbar-meta {
    display: flex;
    align-items: center;
    gap: 10px;
}
.nx-badge-glow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: var(--text-pure, #f8fafc);
}
.nx-badge-glow.is-live {
    background: rgba(16, 185, 129, 0.1);
    border-color: rgba(16, 185, 129, 0.35);
    color: #34d399;
}
.nx-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #94a3b8;
}
.nx-dot.dot-online,
.nx-badge-glow.is-live .nx-dot {
    background: #10b981;
    box-shadow: 0 0 10px #10b981;
    animation: nxPulse 2s infinite ease-in-out;
}
@keyframes nxPulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.4); opacity: 0.5; }
}

.nx-tag-chip {
    padding: 5px 12px;
    font-size: 0.72rem;
    font-weight: 700;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.06);
    color: #94a3b8;
    border: 1px solid rgba(255, 255, 255, 0.06);
    letter-spacing: 0.05em;
}
.nx-tag-chip.is-online,
.nx-tag-chip.bg-success {
    background: rgba(16, 185, 129, 0.15) !important;
    color: #34d399 !important;
    border-color: rgba(16, 185, 129, 0.4) !important;
}

/* ── Hero Card ───────────────────────────────────────────── */
.nx-hero-card {
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.75) 0%, rgba(15, 23, 42, 0.9) 100%);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 24px;
    padding: 2rem 2.25rem;
    backdrop-filter: blur(16px);
    box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.5rem;
    position: relative;
    overflow: hidden;
}
.nx-hero-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.12) 0%, transparent 70%);
    pointer-events: none;
}
.nx-title {
    font-size: 2.2rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #ffffff;
    margin-bottom: 0.35rem;
}
.nx-subtitle {
    color: #94a3b8;
    font-size: 0.88rem;
    max-width: 580px;
}
.nx-type-pill {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 3px 10px;
    border-radius: 6px;
    background: rgba(16, 185, 129, 0.15);
    color: #34d399;
    border: 1px solid rgba(16, 185, 129, 0.3);
}
.nx-loc-pill {
    font-size: 0.75rem;
    color: #94a3b8;
}
.nx-hero-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.nx-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 20px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
}
.nx-action-btn.primary {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
}
.nx-action-btn.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
}
.nx-action-btn.secondary {
    background: rgba(255, 255, 255, 0.05);
    color: #f8fafc;
    border: 1px solid rgba(255, 255, 255, 0.1);
}
.nx-action-btn.secondary:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-1px);
}

/* ── Connection Banner ───────────────────────────────────── */
.nx-alert-banner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    padding: 14px 20px;
    border-radius: 16px;
    backdrop-filter: blur(12px);
    transition: all 0.3s;
}
.nx-alert-banner.is-connected {
    background: linear-gradient(90deg, rgba(16, 185, 129, 0.12) 0%, rgba(16, 185, 129, 0.03) 100%);
    border: 1px solid rgba(16, 185, 129, 0.25);
}
.nx-alert-banner.is-disconnected {
    background: linear-gradient(90deg, rgba(239, 68, 68, 0.12) 0%, rgba(239, 68, 68, 0.03) 100%);
    border: 1px solid rgba(239, 68, 68, 0.25);
}
.nx-alert-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
}
.nx-alert-banner.is-connected .nx-alert-icon {
    background: rgba(16, 185, 129, 0.2);
    color: #34d399;
}
.nx-alert-banner.is-disconnected .nx-alert-icon {
    background: rgba(239, 68, 68, 0.2);
    color: #f87171;
}
.nx-alert-desc {
    font-size: 0.82rem;
    color: #94a3b8;
}
.nx-latency-pill {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.06);
    color: #cbd5e1;
}

/* ── 4 Metric Cards (Vibrant Tech Glass) ─────────────────── */
.nx-metric-card {
    background: rgba(30, 41, 59, 0.65);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(14px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25);
}
.nx-metric-card:hover {
    transform: translateY(-4px);
    border-color: rgba(255, 255, 255, 0.2);
    box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.4);
}
.nx-metric-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
}
.nx-metric-card.metric-ph::before    { background: linear-gradient(90deg, #ec4899, #f43f5e); }
.nx-metric-card.metric-moist::before { background: linear-gradient(90deg, #0ea5e9, #38bdf8); }
.nx-metric-card.metric-temp::before  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.nx-metric-card.metric-ec::before    { background: linear-gradient(90deg, #10b981, #34d399); }

.nx-metric-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}
.nx-metric-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
.metric-ph .nx-metric-icon    { background: rgba(244, 63, 94, 0.12); color: #fb7185; }
.metric-moist .nx-metric-icon { background: rgba(14, 165, 233, 0.12); color: #38bdf8; }
.metric-temp .nx-metric-icon  { background: rgba(245, 158, 11, 0.12); color: #fbbf24; }
.metric-ec .nx-metric-icon    { background: rgba(16, 185, 129, 0.12); color: #34d399; }

.nx-metric-chip {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94a3b8;
    padding: 3px 8px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.03);
}
.nx-metric-val {
    font-size: 2.5rem;
    font-weight: 800;
    color: #ffffff;
    line-height: 1;
    display: flex;
    align-items: baseline;
    gap: 4px;
    font-family: 'Outfit', -apple-system, sans-serif;
}
.nx-metric-val .unit {
    font-size: 1rem;
    font-weight: 600;
    color: #64748b;
}
.nx-metric-label {
    font-size: 0.8rem;
    color: #94a3b8;
    margin-top: 0.4rem;
    font-weight: 500;
}
.nx-metric-foot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1.2rem;
    padding-top: 0.75rem;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
}
.nx-hint-badge {
    font-size: 0.72rem;
    font-weight: 600;
    color: #cbd5e1;
}
.trend-icon {
    font-size: 0.85rem;
    color: #64748b;
}

/* ── Generic Glass Card ──────────────────────────────────── */
.nx-glass-card {
    background: rgba(30, 41, 59, 0.65);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 24px;
    backdrop-filter: blur(14px);
    box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.25);
}
.nx-card-title {
    font-size: 0.92rem;
    font-weight: 700;
    color: #e2e8f0;
    letter-spacing: 0.02em;
}
.nx-meta-badge {
    font-size: 0.72rem;
    padding: 3px 10px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.06);
    color: #94a3b8;
}

/* ── Health Hero ─────────────────────────────────────────── */
.nx-health-hero {
    display: flex;
    align-items: center;
    gap: 1.75rem;
    padding: 1.25rem 0;
    flex-wrap: wrap;
}
.nx-health-dial {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    background: conic-gradient(#10b981 0%, #0ea5e9 60%, rgba(255, 255, 255, 0.08) 60%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    box-shadow: 0 0 25px rgba(16, 185, 129, 0.2);
    flex-shrink: 0;
}
.nx-health-dial::before {
    content: '';
    position: absolute;
    inset: 9px;
    background: #0f172a;
    border-radius: 50%;
    z-index: 1;
}
.nx-health-num {
    position: relative;
    z-index: 2;
    font-size: 2.2rem;
    font-weight: 800;
    color: #ffffff;
    font-family: 'Outfit', sans-serif;
    line-height: 1;
}
.nx-health-lbl {
    position: relative;
    z-index: 2;
    font-size: 0.62rem;
    font-weight: 700;
    color: #34d399;
    letter-spacing: 0.05em;
    margin-top: 2px;
}
.nx-health-desc {
    flex: 1;
    min-width: 200px;
}
.nx-health-status {
    font-size: 1.3rem;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.01em;
    margin-bottom: 0.25rem;
}
.nx-ai-insight {
    font-size: 0.82rem;
    color: #94a3b8;
    line-height: 1.5;
    background: rgba(255, 255, 255, 0.03);
    padding: 10px 14px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

/* ── Action Strip ────────────────────────────────────────── */
.nx-action-strip {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
    gap: 8px;
}
.nx-strip-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 12px 6px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    color: #cbd5e1;
}
.nx-strip-btn i {
    font-size: 1.25rem;
    transition: transform 0.2s;
}
.nx-strip-btn span {
    font-size: 0.72rem;
    font-weight: 600;
    text-align: center;
    white-space: nowrap;
}
.nx-strip-btn:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
    color: #ffffff;
}
.nx-strip-btn:hover i {
    transform: scale(1.15);
}
.nx-strip-btn.highlight {
    background: rgba(16, 185, 129, 0.12);
    border-color: rgba(16, 185, 129, 0.35);
    color: #34d399;
}
.nx-strip-btn.highlight:hover {
    background: rgba(16, 185, 129, 0.22);
    border-color: rgba(16, 185, 129, 0.5);
}
.nx-strip-btn.is-disabled {
    opacity: 0.45;
    pointer-events: none;
}

/* ── Relay Card ──────────────────────────────────────────── */
.nx-relay-box {
    background: rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 16px;
    padding: 14px 18px;
}
.nx-relay-val {
    font-size: 0.8rem;
    font-weight: 800;
    color: #94a3b8;
    letter-spacing: 0.05em;
}
.nx-relay-meter {
    height: 4px;
    background: rgba(255, 255, 255, 0.06);
    border-radius: 9999px;
    overflow: hidden;
    margin-top: 6px;
}
.nx-relay-bar {
    width: 25%;
    height: 100%;
    background: #10b981;
    border-radius: 9999px;
}
.nx-spec-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 14px;
}
.nx-spec-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 10px;
    font-size: 0.8rem;
    color: #cbd5e1;
}
.nx-spec-item strong {
    font-size: 0.82rem;
    color: #ffffff;
}
.nx-btn-valve {
    padding: 10px 16px;
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    border: none;
    border-radius: 12px;
    color: #ffffff;
    font-size: 0.85rem;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
    transition: all 0.2s;
}
.nx-btn-valve:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(14, 165, 233, 0.45);
}
.nx-btn-valve:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.nx-btn-outline-danger {
    padding: 10px 16px;
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 12px;
    color: #f87171;
    cursor: pointer;
    transition: all 0.2s;
}
.nx-btn-outline-danger:hover {
    background: rgba(239, 68, 68, 0.2);
    border-color: rgba(239, 68, 68, 0.5);
}

/* ── Pipeline Visualizer ─────────────────────────────────── */
.nx-pipeline {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    overflow-x: auto;
    padding: 1.25rem;
    background: rgba(0, 0, 0, 0.25);
    border-radius: 18px;
    border: 1px solid rgba(255, 255, 255, 0.05);
}
.nx-pipe-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 8px;
    padding: 14px 18px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    min-width: 130px;
    opacity: 0.5;
    transition: all 0.3s;
}
.nx-pipe-step.is-active {
    opacity: 1;
    background: rgba(16, 185, 129, 0.08);
    border-color: rgba(16, 185, 129, 0.4);
    box-shadow: 0 0 20px rgba(16, 185, 129, 0.15);
}
.nx-pipe-step.brain-step.is-active {
    background: rgba(245, 158, 11, 0.08);
    border-color: rgba(245, 158, 11, 0.4);
    box-shadow: 0 0 20px rgba(245, 158, 11, 0.15);
}
.nx-pipe-step.relay-step.is-active {
    background: rgba(14, 165, 233, 0.08);
    border-color: rgba(14, 165, 233, 0.4);
    box-shadow: 0 0 20px rgba(14, 165, 233, 0.15);
}
.nx-pipe-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    background: rgba(255, 255, 255, 0.05);
    color: #94a3b8;
}
.nx-pipe-step.is-active .nx-pipe-icon {
    background: rgba(16, 185, 129, 0.2);
    color: #34d399;
}
.nx-pipe-step.brain-step.is-active .nx-pipe-icon {
    background: rgba(245, 158, 11, 0.2);
    color: #fbbf24;
}
.nx-pipe-step.relay-step.is-active .nx-pipe-icon {
    background: rgba(14, 165, 233, 0.2);
    color: #38bdf8;
}
.nx-pipe-info strong {
    display: block;
    font-size: 0.8rem;
    color: #ffffff;
    white-space: nowrap;
}
.nx-pipe-info small {
    font-size: 0.68rem;
    color: #94a3b8;
    white-space: nowrap;
}
.nx-pipe-arrow {
    color: #475569;
    font-size: 1.1rem;
    flex-shrink: 0;
}

/* ── Terminal Console ────────────────────────────────────── */
.nx-terminal {
    background: #090d16;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.6);
}
.nx-terminal-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 16px;
    background: rgba(255, 255, 255, 0.02);
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}
.term-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
}
.term-dot.red    { background: #ef4444; }
.term-dot.yellow { background: #f59e0b; }
.term-dot.green  { background: #10b981; }
.term-title {
    font-size: 0.75rem;
    font-weight: 600;
    color: #94a3b8;
    letter-spacing: 0.02em;
}
.term-chip {
    font-size: 0.68rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.06);
    color: #94a3b8;
}
.nx-terminal-screen {
    padding: 14px 18px;
    max-height: 140px;
    overflow-y: auto;
    font-family: 'Fira Code', 'Cascadia Code', Consolas, monospace;
    font-size: 0.74rem;
    line-height: 1.6;
}
.t-line   { color: #94a3b8; padding: 2px 0; }
.t-prompt { color: #10b981; font-weight: 700; margin-right: 8px; }
.t-success { color: #4ade80; }
.t-warn    { color: #fbbf24; }
.t-error   { color: #f87171; }

/* ── Hardware Spec & Guide ───────────────────────────────── */
.nx-sensor-spec-box {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 14px;
}
.spec-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(16, 185, 129, 0.12);
    color: #34d399;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.spec-content strong {
    display: block;
    font-size: 0.85rem;
    color: #ffffff;
}
.spec-content span {
    font-size: 0.72rem;
    color: #94a3b8;
}
.nx-guide-box {
    background: rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 18px;
    padding: 1.25rem;
}
.nx-guide-steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 14px;
}
.nx-g-step {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.nx-g-step .step-num {
    font-size: 0.8rem;
    font-weight: 800;
    color: #10b981;
    background: rgba(16, 185, 129, 0.12);
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 8px;
    padding: 4px 8px;
    flex-shrink: 0;
}
.nx-g-step p {
    font-size: 0.78rem;
    color: #94a3b8;
    margin: 0;
    line-height: 1.5;
}

/* ── Responsive Rules ────────────────────────────────────── */
@media (max-width: 768px) {
    .nx-title { font-size: 1.6rem; }
    .nx-hero-card { padding: 1.5rem; }
    .nx-pipeline { justify-content: flex-start; }
}
</style>
@endsection
