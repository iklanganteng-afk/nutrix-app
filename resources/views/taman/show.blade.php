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
    $lastSeenAge = $taman->last_seen_at?->diffInSeconds(now());
    $isConnected = $lastSeenAge !== null && $lastSeenAge <= 60;
    $lastSeen = $taman->last_seen_at ? $taman->last_seen_at->diffForHumans() : null;
    $selectedSoil = $taman->soil_type ?? '';
    $selectedSensorTypes  = $taman->sensor_types  ?? [];
    $selectedSensorModels = $taman->sensor_models ?? [];
    $selectedController   = $taman->controller_type ?? 'esp32';
    $selectedIndicatorMode = $taman->indicator_mode ?? 'active_only';

    // Ekstraksi nilai telemetri awal (Zero Wait - Langsung Muncul saat Page Load)
    $latest = $latest ?? $taman->latestHardwareTelemetry;
    $meta = $latest?->metadata ?? [];
    $sensors = $meta['sensors'] ?? [];
    $sCap = $sensors['capacitive_v2'] ?? null;
    $sRes = $sensors['resistive_hd38'] ?? null;

    $initCapMoist = isset($sCap['moisture']) ? number_format($sCap['moisture'], 1) : '--';
    $initCapAdc = $sCap['raw_adc'] ?? '--';
    $initCapVolt = isset($sCap['voltage']) ? number_format($sCap['voltage'], 2) : '--';

    $initResMoist = isset($sRes['moisture']) ? number_format($sRes['moisture'], 1) : '--';
    $initResAdc = $sRes['raw_adc'] ?? '--';
    $initResVolt = isset($sRes['voltage']) ? number_format($sRes['voltage'], 2) : '--';

    $initMoist = $latest?->moisture !== null ? number_format($latest->moisture, 1) : '--';
    $initDev = ($sCap && $sRes && isset($sCap['moisture'], $sRes['moisture'])) ? number_format(abs($sCap['moisture'] - $sRes['moisture']), 1) : '--';
    $initRssi = $meta['wifi_rssi'] ?? '--';
    $initIp = $meta['ip_address'] ?? $taman->ip_address ?? '--';
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
            <span data-i18n="detail-back">Taman Saya</span>
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
            <p class="nx-subtitle mb-0" data-i18n="dashboard-tagline">Autonomous Smart Agriculture Controller & Real-Time Sensor Telemetry Node</p>
        </div>
        <div class="nx-hero-actions">
            <button type="button" class="nx-action-btn primary" id="btnConnectSensor">
                <i class="bi bi-cpu-fill me-2"></i><span id="connectSensorButtonLabel" data-i18n="{{ $isConnected ? 'detail-config' : 'detail-connect-sensor' }}">{{ $isConnected ? 'Konfigurasi Sensor' : 'Hubungkan Node ESP32' }}</span>
            </button>
            <button type="button" class="nx-action-btn secondary" id="btnRefreshTelemetry">
                <i class="bi bi-arrow-repeat me-1"></i> <span data-i18n="dashboard-refresh-telemetry">Sync Telemetri</span>
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
                    <span id="bannerHeading" data-i18n="{{ $isConnected ? 'dashboard-banner-live' : 'dashboard-banner-waiting' }}">{{ $isConnected ? 'Koneksi Telemetri ESP32 Aktif' : 'Menunggu Koneksi ESP32' }}</span>
                </strong>
                <span id="bannerText" class="nx-alert-desc">
                    {{ $isConnected ? 'Data telemetri streaming setiap 5 detik via WiFi ke Railway Cloud.' : 'Belum ada sensor terhubung. Nyalakan ESP32 dan hubungkan ke WiFi.' }}
                </span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="nx-latency-pill" data-i18n="dashboard-api-refresh">
                <i class="bi bi-lightning-charge-fill me-1 text-mint"></i> API refresh · 3s
            </span>
            <span class="visually-hidden" data-i18n="detail-api-polling">Live telemetry stream API polling active</span>
        </div>
    </div>

    @php
        $activeToken = session('newly_created_token') ?? $deviceToken;
    @endphp

    @if($activeToken)
        <div class="nx-token-pairing-card mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="nx-token-info">
                    <span class="nx-badge-glow is-live mb-2 d-inline-flex align-items-center">
                        <i class="bi bi-key-fill me-1"></i> <span data-i18n="iot-token-badge">TOKEN PAIRING ESP32 WIRELESS</span>
                    </span>
                    <h5 class="nx-token-title mb-1 fw-bold">
                        <span data-i18n="{{ $isConnected ? 'iot-token-connected-title' : 'iot-token-ready-title' }}">
                            {{ $isConnected ? 'Node ESP32 Terhubung dengan Token Ini' : 'Token Siap Digunakan untuk ESP32' }}
                        </span>
                    </h5>
                    <p class="nx-token-desc mb-0">
                        <span data-i18n="{{ $isConnected ? 'iot-token-connected-desc' : 'iot-token-ready-desc' }}">
                            {{ $isConnected ? 'ESP32 sedang mengirim telemetri streaming menggunakan token ini.' : 'Salin token ini untuk portal WiFi ESP32, atau langsung unduh/salin kode firmware di bawah.' }}
                        </span>
                    </p>
                </div>
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <div class="nx-token-box" title="Device Claim Token">
                        <span class="nx-token-text font-monospace">{{ $activeToken }}</span>
                    </div>
                    <button type="button" class="nx-action-btn secondary" onclick="navigator.clipboard.writeText('{{ $activeToken }}'); alert('Token berhasil disalin: {{ $activeToken }}');">
                        <i class="bi bi-clipboard me-1"></i> <span data-i18n="iot-btn-copy-token">Salin Token</span>
                    </button>
                    <button type="button" class="nx-action-btn primary" onclick="toggleFirmwarePreview()">
                        <i class="bi bi-code-slash me-1"></i> <span id="btnFirmwareToggleText" data-i18n="iot-btn-view-firmware">Lihat Kode Firmware</span>
                    </button>
                </div>
            </div>

            {{-- Collapsible Firmware Code & Download Panel --}}
            <div id="firmwareCodePanel" class="nx-firmware-panel mt-3 pt-3" style="display: none;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <strong class="nx-firmware-heading d-block">
                            <i class="bi bi-file-earmark-code text-mint me-1"></i> <span data-i18n="iot-firmware-title">Source Code Firmware ESP32 (Ready-to-Flash)</span>
                        </strong>
                        <small class="nx-token-desc" data-i18n="iot-firmware-subtitle">Kode Arduino (.ino) sudah diselaraskan dengan Solenoid Valve NC (GPIO 2) & Dual Sensor (D34/D35).</small>
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <button type="button" class="nx-action-btn secondary py-1 px-2" style="font-size:0.82rem;" onclick="copyFirmwareCode()" title="Salin seluruh isi kode">
                            <i class="bi bi-clipboard-check me-1"></i> <span data-i18n="iot-btn-copy-all-code">Salin Kode</span>
                        </button>
                        <button type="button" class="nx-action-btn secondary py-1 px-2" style="font-size:0.82rem;" onclick="downloadFirmwareFile('ino')" title="Unduh sketch Arduino .ino">
                            <i class="bi bi-download me-1"></i> <span data-i18n="iot-btn-download-ino">.INO</span>
                        </button>
                        <button type="button" class="nx-action-btn secondary py-1 px-2" style="font-size:0.82rem;" onclick="downloadFirmwareFile('md')" title="Unduh dokumentasi Markdown .md">
                            <i class="bi bi-markdown me-1"></i> <span data-i18n="iot-btn-download-md">.MD</span>
                        </button>
                        <button type="button" class="nx-action-btn secondary py-1 px-2" style="font-size:0.82rem;" onclick="downloadFirmwareFile('json')" title="Unduh paket konfigurasi & kode JSON">
                            <i class="bi bi-filetype-json me-1"></i> <span>.JSON</span>
                        </button>
                        <button type="button" class="nx-action-btn secondary py-1 px-2" id="btnToggleMaximizeFirmware" style="font-size:0.82rem;" onclick="toggleMaximizeFirmware()" title="Perbesar / Perkecil Tampilan">
                            <i class="bi bi-arrows-fullscreen me-1" id="iconMaximizeFirmware"></i> <span id="textMaximizeFirmware">Maximize</span>
                        </button>
                    </div>
                </div>

                <div class="position-relative">
                    <pre id="firmwareCodeBlock" class="nx-code-viewer p-3 rounded font-monospace small mb-0">{{ $firmwareCode ?? '' }}</pre>
                </div>
                <div class="mt-2 d-flex justify-content-between align-items-center flex-wrap gap-2 nx-token-desc" style="font-size: 0.78rem;">
                    <span><i class="bi bi-check-circle-fill text-mint me-1"></i> <span data-i18n="iot-firmware-ready-note">Auto-Pairing Ready: Anda bisa langsung flash via Arduino IDE tanpa perlu ubah kode.</span></span>
                    <span class="font-monospace">Path: <code>iot_firmware/nutrix_esp32_firmware/nutrix_esp32_firmware.ino</code></span>
                </div>
            </div>
        </div>
    @endif

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
                    <span data-i18n="dashboard-dual-sensor-title">Dual-Sensor Precision Soil Analytics</span>
                </h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="nx-meta-badge" id="dualDeviceBadge">
                    <i class="bi bi-router-fill text-mint me-1"></i> Hotspot: <strong class="text-white">GG</strong>
                </span>
                <span class="nx-meta-badge" id="dualIpBadge">
                    <i class="bi bi-hdd-network text-info me-1"></i> IP: <span id="displayNodeIp">{{ $initIp }}</span>
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
                        <h2 class="text-white fw-bold mb-0" id="val-cap-moisture">{{ $initCapMoist }}</h2>
                        <span class="text-muted fs-6">% VWC</span>
                    </div>
                    <div class="mt-2 text-muted small" style="font-size:0.78rem; font-family:monospace;">
                        <div>Model: <strong>Capacitive V2.0</strong> (Anti-Corrosion)</div>
                        <div>Raw ADC: <span id="val-cap-adc" class="text-white">{{ $initCapAdc }}</span> | Volt: <span id="val-cap-volt" class="text-white">{{ $initCapVolt }}</span>V</div>
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
                        <h2 class="text-white fw-bold mb-0" id="val-res-moisture">{{ $initResMoist }}</h2>
                        <span class="text-muted fs-6">% VWC</span>
                    </div>
                    <div class="mt-2 text-muted small" style="font-size:0.78rem; font-family:monospace;">
                        <div>Model: <strong>HD-38 Probe</strong> (Via LM393 Module)</div>
                        <div>Raw ADC: <span id="val-res-adc" class="text-white">{{ $initResAdc }}</span> | Volt: <span id="val-res-volt" class="text-white">{{ $initResVolt }}</span>V</div>
                    </div>
                </div>
            </div>

            {{-- Konsensus & Validasi Ilmiah --}}
            <div class="col-12 col-md-4">
                <div class="p-3 rounded-3 border border-secondary h-100" style="background: rgba(0, 255, 178, 0.03);">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-info fw-bold small"><i class="bi bi-calculator me-1"></i><span data-i18n="dashboard-consensus-heading">RATA-RATA DARI ESP32</span></span>
                        <span class="badge bg-dark text-info border border-secondary" id="badgeDeviation" data-i18n="dashboard-deviation-unavailable">Deviasi: {{ $initDev === '--' ? '—' : $initDev . ' pp' }}</span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h2 class="text-white fw-bold mb-0" id="val-consensus-moisture">{{ $initMoist }}</h2>
                        <span class="text-muted fs-6" data-i18n="dashboard-aggregate-unit">% agregat</span>
                    </div>
                    <div class="mt-2 text-muted small" style="font-size:0.78rem;">
                                <div id="consensusStatusText" class="text-muted" data-i18n="dashboard-consensus-waiting">Menunggu telemetri perangkat.</div>
                                <div class="text-secondary mt-1" data-i18n="dashboard-consensus-method">Nilai agregat ESP32; deviasi membandingkan dua probe bila tersedia.</div>
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
                    <span class="nx-metric-chip" data-i18n="sensor-ph">Kadar Asam</span>
                </div>
                <div class="nx-metric-main">
                    <div class="nx-metric-val"><span id="val-ph">--</span><span class="unit">pH</span></div>
                    <div class="nx-metric-label" data-i18n="sensor-ph-label">Derajat Keasaman</div>
                </div>
                <div class="nx-metric-foot">
                    <span class="nx-hint-badge" id="status-ph" data-i18n="dashboard-not-available">Belum tersedia</span>
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
                    <span class="nx-metric-chip" data-i18n="sensor-moisture">Kadar Air</span>
                </div>
                <div class="nx-metric-main">
                    <div class="nx-metric-val"><span id="val-hum">{{ $initMoist }}</span><span class="unit">%</span></div>
                    <div class="nx-metric-label" data-i18n="dashboard-soil-moisture">Kelembapan Tanah</div>
                </div>
                <div class="nx-metric-foot">
                    <span class="nx-hint-badge" id="status-moisture" data-i18n="dashboard-not-available">Belum tersedia</span>
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
                    <span class="nx-metric-chip" data-i18n="sensor-temperature">Suhu Media</span>
                </div>
                <div class="nx-metric-main">
                    <div class="nx-metric-val"><span id="val-temp">--</span><span class="unit">°C</span></div>
                    <div class="nx-metric-label" data-i18n="dashboard-soil-temperature">Temperatur Tanah</div>
                </div>
                <div class="nx-metric-foot">
                    <span class="nx-hint-badge" id="status-temp" data-i18n="dashboard-not-available">Belum tersedia</span>
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
                    <span class="nx-metric-chip" data-i18n="sensor-conductivity">Nutrisi Tanah</span>
                </div>
                <div class="nx-metric-main">
                    <div class="nx-metric-val"><span id="val-ec">--</span><span class="unit">mS/cm</span></div>
                    <div class="nx-metric-label" data-i18n="dashboard-soil-conductivity">Konduktivitas Elektrik</div>
                </div>
                <div class="nx-metric-foot">
                    <span class="nx-hint-badge" id="status-ec" data-i18n="dashboard-not-available">Belum tersedia</span>
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
                            <i class="bi bi-heart-pulse-fill text-mint me-2"></i><span data-i18n="detail-health">Status Kesehatan Kebun (AI Farm Health)</span>
                        </span>
                        <div class="d-flex gap-2">
                            <span class="nx-meta-badge" id="sourceBadge">
                                <i class="bi bi-broadcast me-1"></i><span id="sourceText" data-i18n="dashboard-source-waiting">Menunggu telemetri</span>
                            </span>
                            <span class="nx-meta-badge" id="lastUpdatedBadge">
                                <i class="bi bi-clock me-1"></i><span id="lastUpdatedTime">--:--</span>
                            </span>
                        </div>
                    </div>

                    <div class="nx-health-hero">
                        <div class="nx-health-dial" id="aiHealthDial">
                            <span class="nx-health-num" id="aiHealthScore">--</span>
                            <span class="nx-health-lbl" data-i18n="dashboard-health-score">SKOR KESEHATAN</span>
                        </div>
                        <div class="nx-health-desc">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="nx-tag-chip" id="aiHealthStatus" data-i18n="dashboard-health-insufficient">{{ $isConnected ? 'DATA TERHUBUNG' : 'MEMERIKSA STATUS...' }}</span>
                            </div>
                            <div class="nx-ai-insight" id="aiRecommendation">
                                <i class="bi bi-stars text-mint me-1"></i>
                                <span data-i18n="dashboard-health-waiting">{{ $isConnected ? 'Telemetri dual-sensor ESP32 tersinkronisasi secara real-time.' : 'Menghubungkan ke node telemetri ESP32 untuk kalkulasi indeks agronomi cerdas.' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Mini Metrics Summary to balance card height --}}
                    <div class="nx-health-metrics-row mt-3 p-3 rounded-3" style="background: rgba(255,255,255,0.025); border: 1px solid rgba(255,255,255,0.07);">
                        <div class="row g-2 text-center align-items-center">
                            <div class="col-4">
                                <small class="text-muted d-block" style="font-size:0.75rem;" data-i18n="dashboard-average-moisture">Kelembapan Rerata</small>
                                <strong class="text-mint fs-5" id="miniConsensusMoisture">{{ $initMoist }}%</strong>
                            </div>
                            <div class="col-4 border-start border-end border-secondary border-opacity-25">
                                <small class="text-muted d-block" style="font-size:0.75rem;" data-i18n="dashboard-probe-deviation">Deviasi Dua Probe</small>
                                <strong class="text-white fs-5" id="miniDeviation">{{ $initDev === '--' ? '—' : $initDev . ' pp' }}</strong>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block" style="font-size:0.75rem;" data-i18n="dashboard-wifi-rssi">Sinyal WiFi RSSI</small>
                                <strong class="text-warning fs-5" id="miniRssi">{{ $initRssi === '--' ? '—' : $initRssi . ' dBm' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Strip --}}
                <div class="nx-action-strip mt-3 pt-3 border-top border-secondary border-opacity-25">
                    <div class="nx-strip-btn" id="btnSyncData" data-requires-sensor title="Periksa Koneksi Riil ESP32">
                        <i class="bi bi-broadcast"></i>
                        <span data-i18n="dashboard-check-device">Cek ESP32</span>
                    </div>
                    <div class="nx-strip-btn" id="btnActivityLog" title="Buka Riwayat Aktivitas">
                        <i class="bi bi-clock-history"></i>
                        <span data-i18n="detail-activity">Riwayat Log</span>
                    </div>
                    <div class="nx-strip-btn" id="btnExportData" data-requires-sensor title="Export CSV">
                        <i class="bi bi-file-earmark-arrow-down"></i>
                        <span data-i18n="detail-export">Export CSV</span>
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
                            <i class="bi bi-toggles2 text-mint me-2"></i><span data-i18n="dashboard-relay-control">Aktuator Relay & Hardware</span>
                        </span>
                        <span id="relayPill" class="nx-tag-chip">Belum dilaporkan</span>
                    </div>

                    <div class="nx-relay-box mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong class="text-white d-block" style="font-size:0.9rem;">Solenoid Valve Plastik (NC 220V AC)</strong>
                                <small class="text-muted">Relay GPIO 2 · Tipe Normally Closed bertekanan</small>
                            </div>
                            <span class="nx-relay-val" id="relayStatusDisplay">Standby (Tertutup)</span>
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
                            <span><i class="bi bi-water me-2 text-info"></i>Tipe Aktuator Irigasi</span>
                            <strong class="text-info">Solenoid NC AC 220V (Bertekanan)</strong>
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
                            <strong id="displayLastSeen" class="text-light" data-i18n="dashboard-not-available">—</strong>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3 pt-3 border-top border-secondary">
                    <span class="text-muted small flex-fill align-self-center">Solenoid membuka otomatis saat kelembapan di bawah ambang batas kritis.</span>
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
                <span class="nx-badge-glow is-live mb-1"><i class="bi bi-diagram-3-fill me-1"></i> <span data-i18n="dashboard-pipeline">ARSITEKTUR TELEMETRI</span></span>
                <h3 class="text-white mb-0" style="font-size:1.15rem; font-weight:700;" data-i18n="dashboard-pipeline-title">Pipeline Aliran Data Sensor ke Cloud</h3>
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
                <div class="nx-pipe-icon"><i class="bi bi-water"></i></div>
                <div class="nx-pipe-info">
                    <strong>Solenoid Valve NC</strong>
                    <small id="relayFlowStatus">GPIO 2 · 220V AC Bertekanan</small>
                </div>
            </div>
        </div>

        {{-- Cloud Console Terminal --}}
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         BAGIAN 4: SENSOR HARDWARE LIST & SETUP GUIDE
         ═══════════════════════════════════════════════════════════ --}}
    <div class="nx-glass-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <span class="nx-badge-glow is-live mb-1"><i class="bi bi-sliders me-1"></i> SPESIFIKASI SENSOR</span>
                <h3 class="text-white mb-0" style="font-size:1.15rem; font-weight:700;" data-i18n="detail-hardware-metrics">Daftar Sensor & Panduan Node</h3>
            </div>
            <button type="button" class="nx-action-btn secondary" id="btnEditSensorConfig">
                <i class="bi bi-pencil-square me-1"></i> <span data-i18n="detail-edit-config">Edit Konfigurasi</span>
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
                        <span data-i18n="detail-no-sensors">Belum ada sensor spesifik yang dikonfigurasi. Klik tombol Edit Konfigurasi untuk menambahkan.</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Guide Steps --}}
        <div class="nx-guide-box">
            <h4 class="text-white mb-3" style="font-size:0.95rem; font-weight:700;">
                <i class="bi bi-info-circle-fill text-mint me-2"></i><span data-i18n="dashboard-commissioning-guide">Panduan Komisioning IoT Nutrix:</span>
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
                    <p>Decision Engine cloud mengevaluasi kadar air & mentrigger relay GPIO 2 untuk membuka <strong>Solenoid Valve Plastik NC AC 220V</strong> saat kelembapan kritis.</p>
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
                <h3 class="text-white mb-0" style="font-size:1.15rem; font-weight:700;" data-i18n="detail-device-status">Koneksi & Perangkat IoT</h3>
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

        {{-- Wireless pairing guide --}}
        @if(!empty($taman->device_connection))
        <div class="nx-guide-box mb-3">
            <h4 class="text-white mb-3" style="font-size:0.95rem; font-weight:700;">
                <i class="bi bi-wifi text-mint me-2"></i>Panduan pairing nirkabel
            </h4>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="nx-sensor-spec-box">
                        <div class="spec-icon"><i class="bi bi-rss-fill"></i></div>
                        <div class="spec-content">
                            <strong>Target Hotspot</strong>
                            <span>Wireless (WiFi) · {{ $taman->device_connection['wifi_ssid'] ?? ($taman->device_connection['computer_port'] ?? 'WiFi / Hotspot') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="nx-sensor-spec-box">
                        <div class="spec-icon"><i class="bi bi-router"></i></div>
                        <div class="spec-content">
                            <strong>Hostname</strong>
                            <span>{{ $taman->device_connection['hostname'] ?? ($taman->device_connection['device_port'] ?? 'Kelompok-Nutrix') }}</span>
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

    </div>

</section>

{{-- ════════════════════════════════════════════
     MODAL: Edit Konfigurasi Sensor (Wizard)
     ════════════════════════════════════════════ --}}
<div class="modal-overlay" id="sensorConfigModal" role="dialog" aria-modal="true" aria-labelledby="sensorConfigTitle">
    <div class="web3-modal-box add-taman-wizard-box">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
            <h4 class="mb-0 fw-bold" id="sensorConfigTitle" style="font-family:'Cinzel',serif;" data-i18n="detail-edit-config">Edit konfigurasi sensor</h4>
            <button type="button" class="btn-close-custom" id="closeSensorConfigModal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>
        <form action="{{ route('taman.update', $taman) }}" method="POST" id="sensorConfigForm">
            @csrf
            @method('PATCH')

            <div class="wizard-step active" data-sensor-step="1">
                <div class="auth-input-group">
                    <label data-i18n="detail-soil-optional">Jenis tanah (opsional)</label>
                    <select class="auth-input" name="soil_type">
                        <option value="" data-i18n="dashboard-soil-none" {{ $selectedSoil === '' || $selectedSoil === 'unspecified' ? 'selected' : '' }}>Tidak dipilih</option>
                        <option value="pasir" data-i18n="dashboard-soil-sand" {{ $selectedSoil === 'pasir' ? 'selected' : '' }}>Pasir</option>
                        <option value="liat_berpasir" data-i18n="dashboard-soil-sandy-loam" {{ $selectedSoil === 'liat_berpasir' ? 'selected' : '' }}>Liat berpasir</option>
                        <option value="latosol" data-i18n="dashboard-soil-latosol" {{ $selectedSoil === 'latosol' ? 'selected' : '' }}>Latosol</option>
                        <option value="liat" data-i18n="dashboard-soil-clay" {{ $selectedSoil === 'liat' ? 'selected' : '' }}>Liat</option>
                        <option value="organosol" data-i18n="dashboard-soil-organic" {{ $selectedSoil === 'organosol' ? 'selected' : '' }}>Organosol / gambut</option>
                    </select>
                </div>
                <div class="auth-input-group">
                    <label data-i18n="detail-active-indicators">Indikator aktif</label>
                    <div class="sensor-check-grid">
                        <label class="sensor-check-card">
                            <input type="checkbox" name="sensor_types[]" value="moisture" {{ in_array('moisture', $selectedSensorTypes, true) ? 'checked' : '' }}>
                            <span><i class="bi bi-moisture"></i> <span data-i18n="sensor-moisture">Kelembapan</span></span>
                        </label>
                        <label class="sensor-check-card">
                            <input type="checkbox" name="sensor_types[]" value="temperature" {{ in_array('temperature', $selectedSensorTypes, true) ? 'checked' : '' }}>
                            <span><i class="bi bi-thermometer-half"></i> <span data-i18n="sensor-temperature">Suhu</span></span>
                        </label>
                        <label class="sensor-check-card">
                            <input type="checkbox" name="sensor_types[]" value="ph" {{ in_array('ph', $selectedSensorTypes, true) ? 'checked' : '' }}>
                            <span><i class="bi bi-droplet-half"></i> <span data-i18n="sensor-ph">pH</span></span>
                        </label>
                        <label class="sensor-check-card">
                            <input type="checkbox" name="sensor_types[]" value="ec" {{ in_array('ec', $selectedSensorTypes, true) ? 'checked' : '' }}>
                            <span><i class="bi bi-lightning-charge-fill"></i> <span data-i18n="sensor-conductivity">EC</span></span>
                        </label>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="button" class="btn btn-connect-node btn-sensor-step-next" data-i18n="dashboard-next">Selanjutnya</button>
                </div>
            </div>

            <div class="wizard-step" data-sensor-step="2">
                <div class="auth-input-group">
                    <label data-i18n="dashboard-sensor-model">Pilih model sensor</label>
                    <div id="sensorConfigModelRows">
                        <div class="sensor-model-row" data-sensor-row="moisture">
                            <div class="sensor-model-label"><i class="bi bi-moisture"></i> <span data-i18n="sensor-moisture">Kelembapan</span></div>
                            <select class="auth-input" name="sensor_models[moisture]">
                                <option value="SEN0193" {{ ($selectedSensorModels['moisture'] ?? '') === 'SEN0193' ? 'selected' : '' }}>SEN0193 - Soil Moisture Sensor</option>
                                <option value="YL-69" {{ ($selectedSensorModels['moisture'] ?? '') === 'YL-69' ? 'selected' : '' }}>YL-69 - Resistive Soil Sensor</option>
                                <option value="Capacitive-1" {{ ($selectedSensorModels['moisture'] ?? '') === 'Capacitive-1' ? 'selected' : '' }}>Capacitive Soil Sensor</option>
                            </select>
                        </div>
                        <div class="sensor-model-row" data-sensor-row="temperature">
                            <div class="sensor-model-label"><i class="bi bi-thermometer-half"></i> <span data-i18n="sensor-temperature">Suhu</span></div>
                            <select class="auth-input" name="sensor_models[temperature]">
                                <option value="DHT22" {{ ($selectedSensorModels['temperature'] ?? '') === 'DHT22' ? 'selected' : '' }}>DHT22 - Temperature & Humidity</option>
                                <option value="DS18B20" {{ ($selectedSensorModels['temperature'] ?? '') === 'DS18B20' ? 'selected' : '' }}>DS18B20 - Waterproof Temperature</option>
                                <option value="LM35" {{ ($selectedSensorModels['temperature'] ?? '') === 'LM35' ? 'selected' : '' }}>LM35 - Analog Temperature</option>
                            </select>
                        </div>
                        <div class="sensor-model-row" data-sensor-row="ph">
                            <div class="sensor-model-label"><i class="bi bi-droplet-half"></i> <span data-i18n="sensor-ph">pH</span></div>
                            <select class="auth-input" name="sensor_models[ph]">
                                <option value="PH-4502C" {{ ($selectedSensorModels['ph'] ?? '') === 'PH-4502C' ? 'selected' : '' }}>PH-4502C - pH Sensor</option>
                                <option value="Atlas-pH" {{ ($selectedSensorModels['ph'] ?? '') === 'Atlas-pH' ? 'selected' : '' }}>Atlas Scientific pH</option>
                                <option value="PH-1" {{ ($selectedSensorModels['ph'] ?? '') === 'PH-1' ? 'selected' : '' }}>PH-1 - Analog pH Module</option>
                            </select>
                        </div>
                        <div class="sensor-model-row" data-sensor-row="ec">
                            <div class="sensor-model-label"><i class="bi bi-lightning-charge-fill"></i> <span data-i18n="sensor-conductivity">EC</span></div>
                            <select class="auth-input" name="sensor_models[ec]">
                                <option value="DFRobot-EC" {{ ($selectedSensorModels['ec'] ?? '') === 'DFRobot-EC' ? 'selected' : '' }}>DFRobot EC</option>
                                <option value="Atlas-EC" {{ ($selectedSensorModels['ec'] ?? '') === 'Atlas-EC' ? 'selected' : '' }}>Atlas Scientific EC</option>
                                <option value="TDS-V1" {{ ($selectedSensorModels['ec'] ?? '') === 'TDS-V1' ? 'selected' : '' }}>TDS Sensor V1</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-outline-secondary btn-sensor-step-prev" data-i18n="dashboard-previous">Kembali</button>
                    <button type="button" class="btn btn-connect-node btn-sensor-step-next" data-i18n="dashboard-next">Selanjutnya</button>
                </div>
            </div>

            <div class="wizard-step" data-sensor-step="3">
                <div class="auth-input-group">
                    <label data-i18n="detail-board-type">Jenis board / otak perangkat</label>
                    <select class="auth-input" name="controller_type">
                        <option value="esp32" {{ $selectedController === 'esp32' ? 'selected' : '' }}>ESP32 (Direkomendasikan)</option>
                        <option value="esp8266" {{ $selectedController === 'esp8266' ? 'selected' : '' }}>ESP8266</option>
                        <option value="arduino" {{ $selectedController === 'arduino' ? 'selected' : '' }}>Arduino Uno / Nano</option>
                    </select>
                </div>
                <div class="auth-input-group">
                    <label data-i18n="dashboard-indicator-display">Tampilan indikator</label>
                    <select class="auth-input" name="indicator_mode">
                        <option value="active_only" data-i18n="detail-only-active" {{ $selectedIndicatorMode === 'active_only' ? 'selected' : '' }}>Hanya sensor aktif</option>
                        <option value="all_with_unavailable" data-i18n="detail-all-unavailable" {{ $selectedIndicatorMode === 'all_with_unavailable' ? 'selected' : '' }}>Tampilkan semua, tandai yang tidak tersedia</option>
                    </select>
                </div>
                <div class="auth-input-group">
                    <label data-i18n="dashboard-config-summary">Ringkasan konfigurasi</label>
                    <div class="wizard-summary" id="sensorConfigSummary"></div>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-outline-secondary btn-sensor-step-prev" data-i18n="dashboard-previous">Kembali</button>
                    <button type="submit" class="btn btn-connect-node" data-i18n="dashboard-save-config">Simpan Konfigurasi</button>
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
            <ol class="mb-2 ps-3 text-secondary">
                <li class="mb-1">Colokkan ESP32 ke adaptor daya 5V / USB.</li>
                <li class="mb-1">Buka WiFi HP, sambungkan ke WiFi Access Point: <code class="text-mint">NUTRIX-ESP32-PAIR</code></li>
                <li class="mb-1">Browser otomatis membuka form konfigurasi WiFi (Captive Portal).</li>
                <li class="mb-1">Pilih WiFi rumah/hotspot Anda, lalu <strong>tempel (paste) Token di atas</strong> pada kolom <em>Device Pairing Token</em>.</li>
                <li>Simpan. ESP32 otomatis streaming telemetri riil & status di dashboard ini langsung LIVE!</li>
            </ol>
            <div class="pt-2 border-top border-secondary border-opacity-25 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span class="text-white small fw-semibold"><i class="bi bi-cpu me-1 text-mint"></i> Butuh kode sketch ESP32?</span>
                <div class="d-flex gap-1 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-mint py-1 px-2" style="font-size:0.75rem;" onclick="copyFirmwareCode()">
                        <i class="bi bi-clipboard me-1"></i> Salin Kode
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light py-1 px-2" style="font-size:0.75rem;" onclick="downloadFirmwareFile('ino')">
                        <i class="bi bi-download me-1"></i> .INO
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info py-1 px-2" style="font-size:0.75rem;" onclick="downloadFirmwareFile('md')">
                        <i class="bi bi-markdown me-1"></i> .MD
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning py-1 px-2" style="font-size:0.75rem;" onclick="downloadFirmwareFile('json')">
                        <i class="bi bi-filetype-json me-1"></i> .JSON
                    </button>
                </div>
            </div>
        </div>

        {{-- Fallback / Manual Node ID --}}
        <div class="border-top border-secondary border-opacity-25 pt-3 mt-3">
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                <label for="sensorIdInput" class="text-white small mb-0 fw-semibold">
                    <i class="bi bi-tag-fill text-mint me-1"></i> Label Sensor Node ID (Opsional)
                </label>
                <small class="text-muted" style="font-size:0.75rem;">Sesuaikan dengan kolom ID Sensor di portal 192.168.4.1</small>
            </div>
            <div class="d-flex gap-2 mb-2">
                <input type="text" id="sensorIdInput" class="auth-input font-monospace mb-0 flex-grow-1" 
                       placeholder="NUTRIX-DUAL-01" value="{{ $sensorId ?? 'NUTRIX-DUAL-01' }}" autocomplete="off"
                       style="background: var(--bg-obsidian); color: var(--color-mint); border: 1px solid var(--border-subtle); border-radius: 8px;">
                <button type="button" class="btn btn-connect-node px-3 text-nowrap" id="confirmConnectSensor" style="border-radius: 8px;">
                    <i class="bi bi-check-lg me-1"></i> Simpan ID
                </button>
            </div>
            <small class="text-muted d-block" style="font-size:0.75rem;">
                Nilai ini default-nya <code>NUTRIX-DUAL-01</code> sesuai firmware. Jika diubah, samakan juga di portal ESP32.
            </small>
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
    deviceToken:   @json($deviceToken),
};
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
let isConnected = {{ $isConnected ? 'true' : 'false' }};
let telemetryInterval = null;
window.NUTRIX_TAMAN = TAMAN;

// ── Firmware Code Helpers ─────────────────────────────────
const RAW_FIRMWARE_CODE = @json($firmwareCode ?? '');

function toggleFirmwarePreview() {
    const panel = document.getElementById('firmwareCodePanel');
    const toggleBtn = document.getElementById('btnFirmwareToggleText');
    if (!panel) return;
    const isHidden = panel.style.display === 'none' || panel.style.display === '';
    panel.style.display = isHidden ? 'block' : 'none';
    if (toggleBtn) {
        toggleBtn.textContent = isHidden ? 'Tutup Kode Firmware' : 'Lihat Kode Firmware';
    }
}

function toggleMaximizeFirmware() {
    const codeBlock = document.getElementById('firmwareCodeBlock');
    const panel = document.getElementById('firmwareCodePanel');
    const icon = document.getElementById('iconMaximizeFirmware');
    const text = document.getElementById('textMaximizeFirmware');
    if (!codeBlock) return;

    const isMax = codeBlock.classList.toggle('is-maximized');
    if (isMax) {
        codeBlock.style.maxHeight = '75vh';
        if (icon) icon.className = 'bi bi-fullscreen-exit me-1';
        if (text) text.textContent = 'Minimize';
        codeBlock.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
        codeBlock.style.maxHeight = '360px';
        if (icon) icon.className = 'bi bi-arrows-fullscreen me-1';
        if (text) text.textContent = 'Maximize';
    }
}

function copyFirmwareCode() {
    const code = RAW_FIRMWARE_CODE || document.getElementById('firmwareCodeBlock')?.textContent || '';
    if (!code) {
        alert('Kode firmware belum tersedia.');
        return;
    }
    navigator.clipboard.writeText(code).then(() => {
        alert('✅ Kode firmware ESP32 berhasil disalin ke clipboard! Siap di-paste ke Arduino IDE.');
    }).catch(() => {
        const textarea = document.createElement('textarea');
        textarea.value = code;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('✅ Kode firmware ESP32 berhasil disalin ke clipboard!');
    });
}

function downloadFirmwareFile(format = 'ino') {
    const code = RAW_FIRMWARE_CODE || document.getElementById('firmwareCodeBlock')?.textContent || '';
    if (!code) {
        alert('Kode firmware belum tersedia.');
        return;
    }

    let filename = 'nutrix_esp32_firmware.ino';
    let mimeType = 'text/plain';
    let fileContent = code;

    if (format === 'md') {
        filename = 'NUTRIX_ESP32_FIRMWARE.md';
        fileContent = `# Sistem IoT Nutrix — Firmware ESP32\n\nTanggal Unduh: ${new Date().toISOString()}\nTarget Board: DOIT ESP32 DEVKIT V1\nTarget Pinout: \n- Capacitive Soil Sensor V2.0: GPIO 34 (Shield D34)\n- Resistive Soil Sensor HD-38: GPIO 35 (Shield D35)\n- Solenoid Valve NC AC 220V (Relay Songle Active-LOW): GPIO 2 (Shield D2)\n\n\`\`\`cpp\n${code}\n\`\`\`\n`;
    } else if (format === 'json') {
        filename = 'nutrix_firmware_package.json';
        mimeType = 'application/json';
        const jsonPkg = {
            project: "NUTRIX SMART AGRICULTURE",
            device_token: TAMAN.deviceToken || "",
            taman_id: TAMAN.id,
            taman_name: TAMAN.name,
            exported_at: new Date().toISOString(),
            hardware: {
                board: "ESP32 Dev Module",
                pin_capacitive: 34,
                pin_resistive: 35,
                pin_relay_solenoid: 2,
                solenoid_valve: "Normally Closed (NC) AC 220V Bertekanan"
            },
            firmware_source_ino: code
        };
        fileContent = JSON.stringify(jsonPkg, null, 2);
    }

    const blob = new Blob([fileContent], { type: `${mimeType};charset=utf-8` });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

// ── Utility ──────────────────────────────────────────────
function apiFetch(path, method = 'GET', body = null) {
    let url = path.startsWith('/api') || path.startsWith('/taman') ? path : `/taman/${TAMAN.id}${path}`;
    const sep = url.includes('?') ? '&' : '?';
    if (TAMAN.deviceToken) {
        url += `${sep}token=${encodeURIComponent(TAMAN.deviceToken)}`;
    }

    const opts = {
        method,
        credentials: 'same-origin',
        headers: { 
            'Content-Type': 'application/json', 
            'X-CSRF-TOKEN': CSRF, 
            'X-Device-Token': TAMAN.deviceToken || '',
            'Accept': 'application/json' 
        }
    };
    if (body) opts.body = JSON.stringify(body);
    return fetch(url, opts).then(async r => {
        const d = await r.json().catch(() => ({}));
        if (!r.ok) throw new Error(d.message || 'Request failed');
        return d;
    });
}

function setCard(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value ?? '--';
}

const dashboardTextFallbacks = {
    'dashboard-state-live': 'ONLINE',
    'dashboard-state-stale': 'Data lama',
    'dashboard-state-waiting': 'Menunggu data ESP32',
    'dashboard-state-offline': 'OFFLINE',
};

function dashboardText(key, params = {}) {
    const translation = window.nutrixText?.(key);
    const template = translation && translation !== key ? translation : (dashboardTextFallbacks[key] || key);
    return Object.entries(params).reduce((text, [name, value]) => text.replaceAll(`{${name}}`, value), template);
}

let toastTimer = null;
function showToast(msg, type = 'success') {
    const wrap = document.getElementById('nutrixToastWrap') ?? (() => {
        const d = document.createElement('div');
        d.id = 'nutrixToastWrap';
        Object.assign(d.style, { 
            position:'fixed', 
            bottom:'28px', 
            right:'28px', 
            zIndex:'99999', 
            display:'flex', 
            flexDirection:'column', 
            alignItems:'flex-end',
            gap:'10px',
            pointerEvents:'none'
        });
        document.body.appendChild(d);
        return d;
    })();

    // Bersihkan toast sebelumnya agar tidak menumpuk / dobel!
    wrap.innerHTML = '';
    if (toastTimer) clearTimeout(toastTimer);

    const el = document.createElement('div');
    el.style.pointerEvents = 'auto';

    let bg = 'linear-gradient(135deg, rgba(16,185,129,0.95) 0%, rgba(5,150,105,0.95) 100%)';
    let border = 'rgba(16,185,129,0.4)';
    let icon = 'bi-check-circle-fill';

    if (type === 'error') {
        bg = 'linear-gradient(135deg, rgba(239,68,68,0.95) 0%, rgba(185,28,28,0.95) 100%)';
        border = 'rgba(239,68,68,0.4)';
        icon = 'bi-exclamation-octagon-fill';
    } else if (type === 'warning') {
        bg = 'linear-gradient(135deg, rgba(245,158,11,0.95) 0%, rgba(217,119,6,0.95) 100%)';
        border = 'rgba(245,158,11,0.4)';
        icon = 'bi-exclamation-triangle-fill';
    } else if (type === 'info') {
        bg = 'linear-gradient(135deg, rgba(14,165,233,0.95) 0%, rgba(2,132,199,0.95) 100%)';
        border = 'rgba(14,165,233,0.4)';
        icon = 'bi-arrow-repeat';
    }

    const spinClass = type === 'info' ? 'nx-spin' : '';
    el.style.cssText = `background:${bg};color:#fff;padding:12px 20px;border-radius:14px;font-weight:600;font-size:.9rem;box-shadow:0 8px 32px rgba(0,0,0,.45);display:flex;align-items:center;gap:10px;border:1px solid ${border};backdrop-filter:blur(10px);transition:all 0.25s cubic-bezier(0.16,1,0.3,1);transform:translateY(12px);opacity:0;`;
    el.innerHTML = `<i class="bi ${icon} ${spinClass} fs-5"></i><span>${msg}</span>`;
    wrap.appendChild(el);

    requestAnimationFrame(() => {
        el.style.transform = 'translateY(0)';
        el.style.opacity = '1';
    });

    toastTimer = setTimeout(() => {
        el.style.transform = 'translateY(12px)';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 250);
    }, 3600);
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

// ── Connection State UI ────────────────────────────────────
function applyConnectionState(connected, lifecycle = connected ? 'live' : 'offline') {
    isConnected = connected;
    const badge    = document.getElementById('connectionBadge');
    const dot      = document.getElementById('liveDot');
    const banner   = document.getElementById('connectionBanner');
    const bannerTx = document.getElementById('bannerText');
    const flowBadge = document.getElementById('flowStatusBadge');
    const topTx    = document.getElementById('topStatusText');
    const cloudSt  = document.getElementById('displayCloudStatus');
    const bannerHeading = document.getElementById('bannerHeading');
    const connectButtonLabel = document.getElementById('connectSensorButtonLabel');

    const stateKey = lifecycle === 'live' ? 'dashboard-state-live' : lifecycle === 'stale' ? 'dashboard-state-stale' : lifecycle === 'never_received' ? 'dashboard-state-waiting' : 'dashboard-state-offline';
    const stateText = dashboardText(stateKey);
    if (badge) { badge.textContent = stateText; badge.className = `badge ${connected ? 'bg-success' : 'bg-secondary'} text-white`; }
    if (dot)   { dot.classList.toggle('dot-online', connected); }
    if (banner){ banner.className = `sensor-connection-banner ${connected ? 'is-connected' : 'is-disconnected'}`; }
    if (bannerTx) bannerTx.textContent = dashboardText(connected ? 'dashboard-connection-live' : stateKey);
    if (bannerHeading) {
        bannerHeading.dataset.i18n = stateKey;
        bannerHeading.textContent = stateText;
    }
    if (flowBadge) { flowBadge.textContent = stateText; flowBadge.className = `badge ${connected ? 'bg-success' : 'bg-secondary'} text-white`; }
    if (topTx) topTx.textContent = stateText;
    if (cloudSt) { cloudSt.textContent = stateText; cloudSt.className = connected ? 'text-mint' : 'text-muted'; }
    if (connectButtonLabel) {
        const key = connected ? 'detail-config' : 'detail-connect-sensor';
        connectButtonLabel.dataset.i18n = key;
        connectButtonLabel.textContent = dashboardText(key);
    }

    // Flow nodes
    ['flowNodeSensor','flowNodeEsp','flowNodeCloud','flowNodeBrain','flowNodeRelay'].forEach(id => {
        const n = document.getElementById(id);
        if (n) n.classList.toggle('is-active', connected);
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
    const stateKey = on === true ? 'dashboard-relay-on' : on === false ? 'dashboard-relay-off' : 'dashboard-not-reported';
    const stateText = dashboardText(stateKey);
    if (display) { display.textContent = stateText; display.className = on === true ? 'text-mint animate-pulse' : 'text-white'; }
    if (flow)    flow.textContent = `GPIO 2 · ${stateText}`;
    if (pill)    { pill.textContent = stateText; pill.className = `badge ${on === true ? 'bg-success' : 'bg-secondary'} text-white`; }
}

// ── Update Cards ───────────────────────────────────────────
const METRIC_STATUS_IDS = { ph: 'status-ph', moisture: 'status-moisture', temperature: 'status-temp', ec: 'status-ec' };
let latestTelemetryPayload = null;

function updateMetricStatus(metric, data) {
    const element = document.getElementById(METRIC_STATUS_IDS[metric]);
    if (!element) return;

    const band = data?.band;
    const key = band === 'sensor_fault'
        ? 'dashboard-sensor-fault'
        : (!band || band === 'unavailable' ? 'dashboard-not-available' : `dashboard-band-${band.replaceAll('_', '-')}`);
    element.dataset.i18n = key;
    element.dataset.severity = data?.severity || 'unknown';
    element.textContent = dashboardText(key);
}

function updateCards(t) {
    if (!t) return;
    latestTelemetryPayload = t;
    const metrics = t.metrics || {};
    const val = (k) => metrics[k]?.value ?? t[k] ?? null;

    const phV   = val('ph');
    const humV  = val('moisture');
    const tmpV  = val('temperature');
    const ecV   = val('ec');

    setCard('val-ph',   phV  != null ? Number(phV).toFixed(1)  : '--');
    setCard('val-hum',  humV != null ? Number(humV).toFixed(1) : '--');
    setCard('val-temp', tmpV != null ? Number(tmpV).toFixed(1) : '--');
    setCard('val-ec',   ecV  != null ? Number(ecV).toFixed(2)  : '--');
    Object.entries(METRIC_STATUS_IDS).forEach(([metric]) => updateMetricStatus(metric, metrics[metric]));

    // Multi-sensor Detail Binding (Scopus Grade)
    const meta = t.metadata || {};
    const sensors = meta.sensors || {};
    const sCap = sensors.capacitive_v2 || null;
    const sRes = sensors.resistive_hd38 || null;
    const relayState = meta.actuator?.relay_state;
    setRelayStatus(relayState === 'on' ? true : relayState === 'off' ? false : null);

    if (sCap) {
        setCard('val-cap-moisture', sCap.moisture != null ? Number(sCap.moisture).toFixed(1) : '--');
        setCard('val-cap-adc', sCap.raw_adc ?? '--');
        setCard('val-cap-volt', sCap.voltage != null ? Number(sCap.voltage).toFixed(2) : '--');
    } else {
        setCard('val-cap-moisture', '--');
        setCard('val-cap-adc', '--');
        setCard('val-cap-volt', '--');
    }

    if (sRes) {
        setCard('val-res-moisture', sRes.moisture != null ? Number(sRes.moisture).toFixed(1) : '--');
        setCard('val-res-adc', sRes.raw_adc ?? '--');
        setCard('val-res-volt', sRes.voltage != null ? Number(sRes.voltage).toFixed(2) : '--');
    } else {
        setCard('val-res-moisture', '--');
        setCard('val-res-adc', '--');
        setCard('val-res-volt', '--');
    }

    // The canonical moisture value is device-reported; compare probes only when both exist.
    setCard('val-consensus-moisture', humV != null ? Number(humV).toFixed(1) : '--');
    const capMoistureValue = sCap?.moisture;
    const resMoistureValue = sRes?.moisture;
    const capMoisture = Number(capMoistureValue);
    const resMoisture = Number(resMoistureValue);
    const hasBothProbes = capMoistureValue != null && resMoistureValue != null
        && Number.isFinite(capMoisture) && Number.isFinite(resMoisture);
    const deviation = hasBothProbes ? Math.abs(capMoisture - resMoisture) : null;
    const deviationText = deviation === null
        ? dashboardText('dashboard-deviation-unavailable')
        : dashboardText('dashboard-deviation-value', { value: deviation.toFixed(1) });
    setCard('badgeDeviation', deviationText);
    const consensusKey = humV == null
        ? 'dashboard-consensus-waiting'
        : hasBothProbes ? 'dashboard-consensus-probes-available' : 'dashboard-consensus-probes-missing';
    const consensusText = dashboardText(consensusKey, { value: deviation === null ? '' : deviation.toFixed(1) });
    setCard('consensusStatusText', consensusText);

    // Update Health Score, Dial & Recommendation
    const health = t.health || {};
    const score = t.health ? health.score : (t.health_score ?? null);
    const scoreEl = document.getElementById('aiHealthScore');
    const dialEl = document.getElementById('aiHealthDial');
    const statusEl = document.getElementById('aiHealthStatus');
    const recEl = document.getElementById('aiRecommendation');

    if (score != null) {
        if (scoreEl) scoreEl.textContent = score;
        const color = score >= 75 ? '#10b981' : (score >= 45 ? '#f59e0b' : '#ef4444');
        if (dialEl) {
            dialEl.style.background = `conic-gradient(${color} 0%, ${color} ${score}%, rgba(255, 255, 255, 0.08) ${score}%)`;
            dialEl.style.boxShadow = `0 0 25px ${color}33`;
        }
        const scope = t.decision?.scope || 'limited';
        const tier = t.decision?.tier;
        const scopeKey = `dashboard-scope-${scope}`;
        if (statusEl) {
            statusEl.textContent = dashboardText(tier ? `dashboard-tier-${tier.toLowerCase()}` : scopeKey);
            statusEl.className = `nx-tag-chip ${tier === 'A' ? 'is-critical' : (tier === 'B' ? 'is-warning' : 'is-online')}`;
        }
        const coverage = Math.round(Number(health.coverage || 0) * 100);
        const confidence = Math.round(Number(t.decision?.confidence || 0) * 100);
        if (recEl) recEl.textContent = dashboardText('dashboard-health-summary', { coverage, confidence, scope: dashboardText(scopeKey) });
    } else {
        if (scoreEl) scoreEl.textContent = '--';
        if (dialEl) {
            dialEl.style.background = 'conic-gradient(rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.08) 100%)';
            dialEl.style.boxShadow = 'none';
        }
        if (statusEl) {
            statusEl.textContent = dashboardText('dashboard-health-insufficient');
            statusEl.className = 'nx-tag-chip';
        }
        if (recEl) recEl.textContent = dashboardText('dashboard-health-waiting');
    }

    // Mini preview row
    const miniMoist = document.getElementById('miniConsensusMoisture');
    if (miniMoist) miniMoist.textContent = humV != null ? `${Number(humV).toFixed(1)}%` : '--%';
    
    const miniDev = document.getElementById('miniDeviation');
    if (miniDev) {
        if (deviation !== null) {
            miniDev.textContent = `${deviation.toFixed(1)} pp`;
            miniDev.className = 'text-mint fs-5';
        } else {
            miniDev.textContent = '—';
            miniDev.className = 'text-muted fs-5';
        }
    }

    const miniRssi = document.getElementById('miniRssi');
    if (miniRssi) {
        const rssi = meta.wifi_rssi ?? t.wifi_rssi;
        miniRssi.textContent = rssi !== null && rssi !== undefined ? `${rssi} dBm` : '—';
    }

    // IP Address dan Device Info
    setCard('displayNodeIp', meta.ip_address || t.ip_address || '--');
}

function markFresh(source, recordedAt, lastSeen) {
    const srcEl = document.getElementById('sourceText');
    const timeEl = document.getElementById('lastUpdatedTime');
    const lastSeenEl = document.getElementById('displayLastSeen');
    if (srcEl) {
        if (source === 'esp32_device') {
            srcEl.textContent = dashboardText('dashboard-source-device');
        } else {
            srcEl.textContent = source || dashboardText('dashboard-source-api');
        }
    }
    if (timeEl) timeEl.textContent = recordedAt ? new Date(recordedAt).toLocaleString(window.currentLanguage || 'id') : '--';
    if (lastSeenEl) lastSeenEl.textContent = lastSeen ? new Date(lastSeen).toLocaleString(window.currentLanguage || 'id') : dashboardText('dashboard-not-available');
}

// ── Load Telemetry ─────────────────────────────────────────
async function loadLatestTelemetry(silent = false) {
    try {
        applyMetricVisibility();
        const data = await apiFetch(`/taman/${TAMAN.id}/telemetry/latest`);

        const connected = data.lifecycle === 'live';
        applyConnectionState(connected, data.lifecycle);

        if (data.device_token) {
            setCard('displayDeviceToken', data.device_token);
        }
        if (data.sensor_id) {
            setCard('displaySensorId', data.sensor_id);
        }

        if (data.lifecycle === 'never_received' && !data.recorded_at) {
            updateCards(data);
            setCard('sourceText', dashboardText('dashboard-source-waiting'));
            setCard('lastUpdatedTime', '--:--');
            setCard('displayLastSeen', dashboardText('dashboard-not-available'));
            return;
        }

        updateCards(data);
        markFresh(data.source, data.recorded_at, data.last_seen_at);

    } catch (e) {
        if (latestTelemetryPayload) latestTelemetryPayload = { ...latestTelemetryPayload, lifecycle: 'offline' };
        applyConnectionState(false, 'offline');
    }
}

// ── Start / Stop polling ───────────────────────────────────
function startPolling() {
    stopPolling();
    telemetryInterval = setInterval(() => {
        if (!document.hidden) loadLatestTelemetry(true);
    }, 3000);
}

function stopPolling() {
    if (telemetryInterval) { clearInterval(telemetryInterval); telemetryInterval = null; }
}

// ── Init ───────────────────────────────────────────────────
applyMetricVisibility();
applyConnectionState(isConnected, isConnected ? 'live' : 'offline');
setRelayStatus(null);
loadLatestTelemetry();
startPolling();

document.addEventListener('nutrix:languagechange', () => {
    if (latestTelemetryPayload) updateCards(latestTelemetryPayload);
    const lifecycle = latestTelemetryPayload?.lifecycle || (isConnected ? 'live' : 'offline');
    applyConnectionState(lifecycle === 'live', lifecycle);
    if (!latestTelemetryPayload) setRelayStatus(null);
});

document.addEventListener('visibilitychange', () => {
    if (document.hidden) stopPolling();
    else { loadLatestTelemetry(); startPolling(); }
});

// ── Refresh Button ─────────────────────────────────────────
document.getElementById('btnRefreshTelemetry')?.addEventListener('click', () => {
    loadLatestTelemetry();
    showToast('Telemetri diperbarui.');
});

// ── Cek Status Hardware ESP32 (Zero Ghost Data) ───────────────
document.getElementById('btnSyncData')?.addEventListener('click', async () => {
    const btn = document.getElementById('btnSyncData');
    const icon = btn?.querySelector('i');
    if (icon) icon.className = 'bi bi-arrow-repeat nx-spin';

    showToast('Memeriksa transmisi hardware ESP32...', 'info');
    try {
        const data = await apiFetch(`/taman/${TAMAN.id}/sync`, 'POST');
        if (icon) icon.className = 'bi bi-broadcast';

        if (data.success) {
            await loadLatestTelemetry(true);
            pushFarmNotification(data.message, data.is_live ? 'bi-broadcast' : 'bi-wifi-off');
            showToast(data.message, data.is_live ? 'success' : 'warning');
        } else {
            showToast(data.message || 'ESP32 belum terdeteksi.', 'warning');
        }
    } catch (e) { 
        if (icon) icon.className = 'bi bi-broadcast';
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
            pushFarmNotification('Sensor diputus. Silakan pasangkan ulang.', 'bi-plug');
            showToast('Sensor berhasil diputus.', 'error');
        }
    } catch (e) { showToast(e.message || 'Reset gagal.', 'error'); }
});

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
    try {
        const data = await apiFetch(`/taman/${TAMAN.id}/sensor/connect`, 'POST', { sensor_id: sensorId, board_type: boardType });
        if (data.success || data.configured) {
            setCard('displaySensorId', sensorId);
            applyConnectionState(data.is_live === true);
            pushFarmNotification(data.is_live ? `ESP32 ${sensorId} sedang online.` : `ID ${sensorId} disimpan; menunggu telemetry pertama.`, 'bi-link-45deg');
            showToast(data.is_live ? 'ESP32 sedang online.' : 'ID tersimpan. Menunggu ESP32 mengirim data.', data.is_live ? 'success' : 'info');
            setTimeout(() => window.location.reload(), 800);
        } else showToast('Gagal memasangkan sensor.', 'error');
    } catch (e) {
        showToast(e.message || 'Pairing gagal.', 'error');
    }
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        sensorConfigModal?.classList.remove('active');
        connectSensorModal?.classList.remove('active');
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
    text-decoration: none;
}
.nx-action-btn.primary {
    background: linear-gradient(135deg, var(--color-mint) 0%, #059669 100%);
    color: #ffffff !important;
    box-shadow: 0 4px 14px var(--glow-ambient);
}
.nx-action-btn.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px var(--border-glow);
}
.nx-action-btn.secondary {
    background: var(--bg-emerald);
    color: var(--text-pure) !important;
    border: 1px solid var(--border-subtle);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}
.nx-action-btn.secondary:hover {
    background: color-mix(in srgb, var(--color-mint) 12%, var(--bg-emerald));
    border-color: var(--color-mint);
    color: var(--color-mint) !important;
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
    padding: 0.75rem 0;
    flex-wrap: wrap;
}
.nx-health-dial {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: conic-gradient(#10b981 0%, #10b981 85%, rgba(255, 255, 255, 0.08) 85%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    box-shadow: 0 0 25px rgba(16, 185, 129, 0.25);
    flex-shrink: 0;
    transition: all 0.5s ease;
}
.nx-health-dial::before {
    content: '';
    position: absolute;
    inset: 10px;
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
    font-family: 'Outfit', -apple-system, sans-serif;
    line-height: 1;
}
.nx-health-lbl {
    position: relative;
    z-index: 2;
    font-size: 0.58rem;
    font-weight: 700;
    color: #34d399;
    letter-spacing: 0.08em;
    margin-top: 4px;
    text-transform: uppercase;
}
.nx-health-desc {
    flex: 1;
    min-width: 220px;
}
.nx-health-status {
    font-size: 1.2rem;
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
    border: 1px solid rgba(255, 255, 255, 0.06);
}

@keyframes nxSpin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.nx-spin {
    animation: nxSpin 0.8s linear infinite;
    display: inline-block;
}

.nx-tag-chip.is-warning {
    background: rgba(245, 158, 11, 0.15);
    color: #fbbf24;
    border: 1px solid rgba(245, 158, 11, 0.35);
}
.nx-tag-chip.is-critical {
    background: rgba(239, 68, 68, 0.15);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.35);
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
    background: var(--bg-emerald);
    border: 1px solid var(--border-subtle);
    border-radius: 14px;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    color: var(--text-body);
}
.nx-strip-btn i {
    font-size: 1.25rem;
    transition: transform 0.2s;
    color: var(--color-mint);
}
.nx-strip-btn span {
    font-size: 0.72rem;
    font-weight: 600;
    text-align: center;
    white-space: nowrap;
    color: var(--text-body);
}
.nx-strip-btn:hover {
    background: color-mix(in srgb, var(--color-mint) 12%, var(--bg-emerald));
    border-color: var(--color-mint);
    transform: translateY(-2px);
    color: var(--color-mint);
}
.nx-strip-btn:hover span {
    color: var(--color-mint);
}
.nx-strip-btn:hover i {
    transform: scale(1.15);
}
.nx-strip-btn.highlight {
    background: color-mix(in srgb, var(--color-mint) 15%, var(--bg-emerald));
    border-color: var(--color-mint);
    color: var(--color-mint);
}
.nx-strip-btn.highlight span {
    color: var(--color-mint);
}
.nx-strip-btn.highlight:hover {
    background: color-mix(in srgb, var(--color-mint) 25%, var(--bg-emerald));
    border-color: var(--color-mint);
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

.nutrix-iot-wrap .nx-glass-card,
.nutrix-iot-wrap .nx-hero-card,
.nutrix-iot-wrap .nx-metric-card,
.nutrix-iot-wrap .nx-guide-box,
.nutrix-iot-wrap .nx-sensor-spec-box,
.nutrix-iot-wrap .nx-relay-box,
.nutrix-iot-wrap .nx-health-metrics-row,
.nutrix-iot-wrap .nx-pipeline,
.nutrix-iot-wrap .nx-pipe-step {
    background-color: color-mix(in srgb, var(--bg-secondary) 88%, var(--bg-primary));
    border-color: var(--border-glass);
}

.nutrix-iot-wrap .nx-hero-card,
.nutrix-iot-wrap .nx-glass-card[style*="linear-gradient"],
.nutrix-iot-wrap .nx-health-metrics-row {
    background: color-mix(in srgb, var(--bg-secondary) 88%, var(--bg-primary)) !important;
    border-color: var(--border-glass) !important;
}

.nutrix-iot-wrap .text-white,
.nutrix-iot-wrap .nx-metric-val,
.nutrix-iot-wrap .nx-card-title,
.nutrix-iot-wrap .nx-title,
.nutrix-iot-wrap .spec-content strong {
    color: var(--text-pure) !important;
}

.nutrix-iot-wrap .nx-metric-label,
.nutrix-iot-wrap .nx-g-step p,
.nutrix-iot-wrap .spec-content span {
    color: var(--text-muted);
}

.nutrix-iot-wrap .nx-hint-badge[data-severity="critical"],
.nutrix-iot-wrap .nx-hint-badge[data-severity="fault"] {
    color: #ef4444;
}

.nutrix-iot-wrap .nx-hint-badge[data-severity="watch"],
.nutrix-iot-wrap .nx-hint-badge[data-severity="warn"] {
    color: #d97706;
}

/* ── High-Contrast Guarantee across Light & Dark Themes (DKV Rules) ── */
.nutrix-iot-wrap .nx-action-btn.secondary {
    background: var(--bg-emerald) !important;
    color: var(--text-pure) !important;
    border: 1px solid var(--border-glass) !important;
}

.nutrix-iot-wrap .nx-action-btn.secondary:hover {
    background: color-mix(in srgb, var(--color-mint) 15%, var(--bg-emerald)) !important;
    border-color: var(--color-mint) !important;
    color: var(--color-mint) !important;
}

.nutrix-iot-wrap .nx-meta-badge {
    background: var(--bg-emerald);
    border-color: var(--border-glass);
    color: var(--text-pure);
}

.nutrix-iot-wrap .nx-token-box {
    background: var(--bg-emerald);
    border-color: var(--border-glass);
}

.nutrix-iot-wrap .nx-sensor-spec-box {
    background: var(--bg-emerald);
    border-color: var(--border-glass);
}

#sensorConfigModal .web3-modal-box,
#connectSensorModal .web3-modal-box {
    background: var(--bg-secondary);
    border-color: var(--border-glass);
    color: var(--text-pure);
}

#sensorConfigModal .text-white,
#connectSensorModal .text-white {
    color: var(--text-pure) !important;
}
</style>
@endsection
