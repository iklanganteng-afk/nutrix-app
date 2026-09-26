@extends('layouts.app')

@section('content')
<!-- DASHBOARD IoT SIMULATION PREVIEW (scoped ke taman ini) -->
<section class="dashboard-section standalone-dashboard container" id="dashboard">
    <div class="farm-detail-toolbar">
        <a href="{{ route('dashboard') }}#gardens" class="farm-back-link">
            <i class="bi bi-arrow-left"></i>
            <span data-i18n="detail-back">Kembali ke Taman Saya</span>
        </a>
        <span class="farm-detail-context"><i class="bi bi-broadcast-pin"></i> <span data-i18n="{{ $taman->sensor_connected ? 'detail-connected' : 'detail-disconnected' }}">{{ $taman->sensor_connected ? 'SENSOR CONNECTED' : 'SENSOR NOT CONNECTED' }}</span></span>
    </div>
    <div class="d-flex justify-content-between align-items-end mb-4 position-relative z-2 border-bottom border-secondary pb-3">
        <div>
            <span class="badge-web3 mb-2">{{ ucfirst($taman->type) }}</span>
            <h2 class="section-title mb-0" style="font-family: 'Cinzel', serif;">{{ $taman->name }}</h2>
            @if($taman->location)
                <small class="text-muted"><i class="bi bi-geo-alt"></i> {{ $taman->location }}</small>
            @endif
        </div>
        <div class="live-indicator">
            <div class="live-dot"></div>
            <span class="badge {{ $taman->sensor_connected ? 'bg-success' : 'bg-secondary' }} text-white" data-i18n="{{ $taman->sensor_connected ? 'detail-connected' : 'detail-disconnected' }}">{{ $taman->sensor_connected ? 'SENSOR CONNECTED' : 'SENSOR NOT CONNECTED' }}</span>
        </div>
    </div>

    <div class="sensor-connection-banner {{ $taman->sensor_connected ? 'is-connected' : 'is-disconnected' }}">
        <div><i class="bi bi-{{ $taman->sensor_connected ? 'check-circle' : 'plug' }}"></i><span data-i18n="{{ $taman->sensor_connected ? 'detail-sensor-ready' : 'detail-sensor-missing' }}">{{ $taman->sensor_connected ? 'Sensor siap mengirim telemetry.' : 'Belum ada sensor terhubung. Nilai ditampilkan 0.' }}</span></div>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnConnectSensor" data-i18n="detail-connect-sensor">Hubungkan sensor</button>
    </div>

    @php
        $sensorTypeLabels = [
            'moisture' => 'Kelembapan',
            'temperature' => 'Suhu',
            'ph' => 'pH',
            'ec' => 'EC',
        ];
        $sensorTypes = $taman->sensor_types ?? [];
        $sensorModels = $taman->sensor_models ?? [];
        $controller = $taman->controller_type ? strtoupper($taman->controller_type) : 'Belum dipilih';
        $deviceConnection = $taman->device_connection ?? [];
        $computerPort = $deviceConnection['computer_port'] ?? 'USB-A';
        $devicePort = $deviceConnection['device_port'] ?? 'USB-C';
        $connectionNote = $deviceConnection['note'] ?? 'Belum ada catatan koneksi';
        $controllerName = match($taman->controller_type ?? '') {
            'esp32' => 'ESP32',
            'arduino' => 'Arduino Uno / Nano',
            'esp8266' => 'ESP8266',
            default => 'Board belum dipilih',
        };
        $connectionStatusText = $taman->sensor_connected
            ? 'Board terdeteksi dan siap menerima telemetri dari sensor.'
            : 'Belum ada perangkat terhubung. Sambungkan kabel dan lakukan pairing sensor.';
        $sensorId = $taman->sensor_id ?? 'Belum dipasangkan';
    @endphp

    <div class="sensor-config-summary mt-4 section-panel">
        <div class="sensor-config-header">
            <div>
                <span class="badge-web3" data-i18n="detail-config">Konfigurasi sensor</span>
                <h3 class="mb-0 mt-2" data-i18n="detail-device-list">Daftar perangkat yang terhubung</h3>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary section-action-btn" id="btnEditSensorConfig" data-i18n="detail-edit-config">Edit konfigurasi</button>
        </div>

        @if(!empty($sensorTypes))
            <div class="sensor-config-list">
                @foreach($sensorTypes as $sensorType)
                    @php $typeKey = (string) $sensorType; @endphp
                    <div class="sensor-config-item">
                        <div class="sensor-config-icon"><i class="bi bi-{{ $sensorType === 'moisture' ? 'moisture' : ($sensorType === 'temperature' ? 'thermometer-half' : ($sensorType === 'ph' ? 'droplet-half' : 'lightning-charge-fill')) }}"></i></div>
                        <div class="sensor-config-copy">
                            <strong>{{ $sensorTypeLabels[$typeKey] ?? ucfirst($typeKey) }}</strong>
                            <small>{{ $sensorModels[$typeKey] ?? 'Model tidak dipilih' }}</small>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="sensor-config-empty" data-i18n="detail-no-sensors">Belum ada sensor yang dipilih untuk taman ini.</div>
        @endif

        <div class="sensor-connection-panel">
            <div class="sensor-connection-row">
                <span data-i18n="detail-board-controller">Board controller</span>
                <strong>{{ $controller }}</strong>
            </div>
            <div class="sensor-connection-row">
                <span data-i18n="detail-laptop-port">Port laptop</span>
                <strong>{{ $computerPort }}</strong>
            </div>
            <div class="sensor-connection-row">
                <span data-i18n="detail-device-port">Port alat</span>
                <strong>{{ $devicePort }}</strong>
            </div>
            <div class="sensor-connection-row">
                <span data-i18n="detail-note">Catatan</span>
                <strong>{{ $connectionNote }}</strong>
            </div>
        </div>
    </div>

    <div class="hardware-wiring-guide mt-4 section-panel">
        <div class="sensor-config-header">
            <div>
                <span class="badge-web3" data-i18n="detail-wireless-node">Wireless IoT Architecture</span>
                <h3 class="mb-0 mt-2" data-i18n="detail-wireless-status">Status Koneksi Node & Topologi Nirkabel</h3>
            </div>
        </div>

        <div class="hardware-guide-grid">
            <div class="hardware-guide-card">
                <div class="guide-card-header">
                    <span class="guide-chip"><i class="bi bi-wifi me-1"></i> Jaringan</span>
                    <strong>WiFi 2.4 GHz</strong>
                </div>
                <p>ESP32 terhubung via hotspot <strong>GG</strong> atau konfigurasi mandiri melalui Captive Portal <strong>NUTRIX-ESP32-SETUP</strong>.</p>
            </div>

            <div class="hardware-guide-card">
                <div class="guide-card-header">
                    <span class="guide-chip"><i class="bi bi-cpu me-1"></i> Controller</span>
                    <strong>{{ $controllerName }}</strong>
                </div>
                <p>Membaca sensor kelembapan pada <strong>GPIO 34</strong> dan mengendalikan relay keran air pada <strong>GPIO 26</strong>.</p>
            </div>

            <div class="hardware-guide-card guide-status-card">
                <div class="guide-card-header">
                    <span class="guide-chip status-chip">Cloud Sync</span>
                    <strong class="text-white">{{ $taman->sensor_connected ? 'ONLINE / SYNCD' : 'MENUNGGU TELEMETRI' }}</strong>
                </div>
                <p>{{ $taman->sensor_connected ? 'Data telemetri realtime diterima secara nirkabel dari ESP32.' : 'Alat belum mengirimkan paket data. Nyalakan daya ESP32.' }}</p>
            </div>
        </div>

        <div class="guide-step-list">
            <div class="guide-step-item"><span>1</span> Nyalakan daya ESP32 (colok adaptor charger HP atau powerbank).</div>
            <div class="guide-step-item"><span>2</span> Pastikan hotspot HP <strong>GG</strong> aktif atau koneksikan WiFi melalui portal <strong>NUTRIX-ESP32-SETUP</strong>.</div>
            <div class="guide-step-item"><span>3</span> Data telemetri tanah akan langsung terkirim otomatis setiap 5 detik ke cloud Railway.</div>
        </div>
    </div>

    <div class="device-status-panel mt-4 section-panel">
        <div class="sensor-config-header">
            <div>
                <span class="badge-web3" data-i18n="detail-device-status">Status perangkat</span>
                <h3 class="mb-0 mt-2" data-i18n="detail-device-status">Status Perangkat Nirkabel</h3>
            </div>
        </div>

        <div class="device-status-grid">
            <div class="device-status-card">
                <span class="device-status-label">Sensor ID</span>
                <strong class="text-mint">{{ $sensorId }}</strong>
            </div>
            <div class="device-status-card">
                <span class="device-status-label">Status Cloud</span>
                <strong class="{{ $taman->sensor_connected ? 'text-mint' : 'text-muted' }}">{{ $taman->sensor_connected ? 'Cloud Online' : 'Offline' }}</strong>
            </div>
            <div class="device-status-card">
                <span class="device-status-label">Board Controller</span>
                <strong>{{ $controllerName }}</strong>
            </div>
            <div class="device-status-card">
                <span class="device-status-label">Aktuator Keran (Relay)</span>
                <strong id="relayStatusDisplay" class="text-white">SIAP (STANDBY)</strong>
            </div>
            <div class="device-status-card">
                <span class="device-status-label">Metode Koneksi</span>
                <strong>WiFi / Cloud API</strong>
            </div>
        </div>

        <div class="mt-3 p-3 rounded border border-secondary" style="background: rgba(11, 20, 17, 0.72);">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-broadcast text-mint"></i>
                    <strong class="text-white" data-i18n="detail-wireless-detection">Wireless Telemetry Stream</strong>
                </div>
                <span id="usbBoardPill" class="badge {{ $taman->sensor_connected ? 'bg-success' : 'bg-secondary' }} text-white">
                    {{ $taman->sensor_connected ? 'Sync Live' : 'Awaiting Telemetry' }}
                </span>
            </div>
            <div class="small text-muted mb-3" id="usbDetectionText">
                {{ $taman->sensor_connected ? 'ESP32 aktif mengirim data ke endpoint /api/iot/telemetry.' : 'Nyalakan ESP32 untuk mulai mengirimkan telemetri ke server Railway.' }}
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-connect-node" id="btnSyncDataDirect">
                    <i class="bi bi-arrow-repeat"></i> <span>Refresh Telemetri</span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnTriggerWaterManual">
                    <i class="bi bi-droplet-fill text-mint"></i> <span>Uji Keran / Pompa</span>
                </button>
            </div>
        </div>

        <div class="mt-3 p-3 rounded border border-secondary" style="background: rgba(7, 14, 11, 0.8);">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-terminal text-mint"></i>
                    <strong class="text-white" data-i18n="detail-serial-monitor">USB serial monitor</strong>
                </div>
                <span class="small text-muted">{{ $controllerName }} / {{ $computerPort }}</span>
            </div>
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnAutoDetectUsbPort">
                    <i class="bi bi-search"></i> <span data-i18n="detail-detect-port">Detect port automatically</span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnSimulateUsbRefresh">
                    <i class="bi bi-arrow-clockwise"></i> <span data-i18n="detail-refresh-log">Refresh log</span>
                </button>
            </div>
            <div class="d-grid gap-2" id="usbConsoleList" style="font-size: .78rem; color: var(--text-muted);">
                <div class="py-1"><span class="text-mint">&gt;</span> board ready on {{ $computerPort }}</div>
                <div class="py-1"><span class="text-mint">&gt;</span> waiting for sensor id...</div>
            </div>
        </div>

        <div class="mt-3 p-3 rounded border border-secondary" style="background: rgba(9, 16, 15, 0.72);">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-check2-circle text-mint"></i>
                <strong class="text-white" data-i18n="detail-checklist">USB setup checklist</strong>
            </div>
            <div class="small text-muted d-grid gap-2">
                <div class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-mint"></i> Cable USB terhubung ke laptop dan board.</div>
                <div class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-mint"></i> Board {{ $controllerName }} terlihat pada port {{ $computerPort }}.</div>
                <div class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-mint"></i> Sensor ID valid sebelum klik Hubungkan sensor.</div>
                <div class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-mint"></i> Data akan mulai tervalidasi setelah koneksi berhasil.</div>
            </div>
        </div>

        <div class="mt-3 p-3 rounded border border-secondary" style="background: rgba(12, 21, 19, 0.85);">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-diagram-3 text-mint"></i>
                    <strong class="text-white" data-i18n="detail-pairing">Pairing flow</strong>
                </div>
                <span class="badge bg-success text-white" id="pairingFlowStatus" data-i18n="pair-ready">Ready</span>
            </div>
            <div class="row g-2">
                <div class="col-12 col-md-4">
                    <div class="rounded border border-secondary p-3 h-100 bg-dark" data-pair-step="board">
                        <div class="small text-muted mb-1"><span data-i18n="pair-step">Step</span> 1</div>
                        <div class="fw-semibold text-white" data-i18n="pair-board-detected">Board detected</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="rounded border border-secondary p-3 h-100 bg-dark" data-pair-step="port">
                        <div class="small text-muted mb-1"><span data-i18n="pair-step">Step</span> 2</div>
                        <div class="fw-semibold text-white" data-i18n="pair-port-verified">Port verified</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="rounded border border-secondary p-3 h-100 bg-dark" data-pair-step="sensor">
                        <div class="small text-muted mb-1"><span data-i18n="pair-step">Step</span> 3</div>
                        <div class="fw-semibold text-white" data-i18n="pair-sensor-validated">Sensor ID validated</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 p-3 rounded border border-secondary" style="background: rgba(8, 18, 15, 0.82);">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-activity text-mint"></i>
                    <strong class="text-white" data-i18n="detail-live-stream">Live telemetry stream</strong>
                </div>
                <span class="badge bg-success text-white" id="liveTelemetryStatus" data-i18n="detail-stream-active">Stream active</span>
            </div>
            <div class="small text-muted">
                <span data-i18n="detail-stream-description">Telemetry dari board {{ $controllerName }} diteruskan melalui port {{ $computerPort }} secara real-time setelah pairing berhasil.</span>
            </div>
            <div class="row g-2 mt-2 small">
                <div class="col-12 col-md-6 text-muted"><span data-i18n="detail-source">Sumber data</span>: <strong class="text-white" id="telemetryDataSource">USB serial stream</strong></div>
                <div class="col-12 col-md-6 text-muted"><span data-i18n="detail-last-update">Pembaruan terakhir</span>: <strong class="text-white" id="telemetryLastUpdated">Menunggu data</strong></div>
            </div>
        </div>

        <div class="mt-3 p-3 rounded border border-secondary" style="background: rgba(8, 18, 15, 0.82);">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-receipt text-mint"></i>
                    <strong class="text-white" data-i18n="detail-event-stream">USB event stream</strong>
                </div>
                <span class="badge bg-success text-white" id="usbEventStreamStatus" data-i18n="detail-event-connected">Connected</span>
            </div>
            <div class="d-grid gap-2" id="usbEventStreamList" style="font-size: .78rem; color: var(--text-muted);">
                <div class="py-1"><span class="text-mint">&gt;</span> board detected on {{ $computerPort }}</div>
                <div class="py-1"><span class="text-mint">&gt;</span> serial channel open</div>
                <div class="py-1"><span class="text-mint">&gt;</span> sensor stream ready</div>
            </div>
        </div>

        <div class="mt-3 p-3 rounded border border-secondary" style="background: rgba(10, 19, 17, 0.8);">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-heart-pulse text-mint"></i>
                    <strong class="text-white" data-i18n="detail-board-health">Board health</strong>
                </div>
                <span class="badge bg-success text-white" id="boardHealthPill" data-i18n="detail-board-stable">Stable</span>
            </div>
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="bg-dark rounded p-3 border border-secondary h-100">
                        <div class="text-muted small mb-1" data-i18n="detail-signal-quality">Signal quality</div>
                        <div class="fw-bold text-white" id="boardSignalValue">96%</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="bg-dark rounded p-3 border border-secondary h-100">
                        <div class="text-muted small mb-1" data-i18n="detail-port-integrity">Port integrity</div>
                        <div class="fw-bold text-white" id="boardPortValue">Nominal</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="bg-dark rounded p-3 border border-secondary h-100">
                        <div class="text-muted small mb-1" data-i18n="detail-retry-count">Retry count</div>
                        <div class="fw-bold text-white" id="boardRetryValue">1</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 p-3 rounded border border-secondary" style="background: rgba(10, 19, 17, 0.8);">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-repeat text-mint"></i>
                    <strong class="text-white" data-i18n="detail-auto-reconnect">Auto reconnect</strong>
                </div>
                <span class="badge bg-success text-white" id="reconnectPolicyStatus">Ready</span>
            </div>
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="bg-dark rounded p-3 border border-secondary h-100">
                        <div class="text-muted small mb-1" data-i18n="detail-signal-alert">Signal alert</div>
                        <div class="fw-bold text-white" id="signalAlertStatus">Stable</div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="bg-dark rounded p-3 border border-secondary h-100">
                        <div class="text-muted small mb-1" data-i18n="detail-reconnect-policy">Reconnect policy</div>
                        <div class="fw-bold text-white" id="reconnectPolicyText">Queued</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 p-3 rounded border border-secondary" style="background: rgba(10, 19, 17, 0.8);">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-speedometer2 text-mint"></i>
                    <strong class="text-white" data-i18n="detail-hardware-metrics">Board hardware metrics</strong>
            </div>
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="bg-dark rounded p-3 border border-secondary h-100">
                        <div class="text-muted small mb-1" data-i18n="detail-voltage">Voltage</div>
                        <div class="fw-bold text-white" id="usbVoltageValue">3.3V</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="bg-dark rounded p-3 border border-secondary h-100">
                        <div class="text-muted small mb-1" data-i18n="detail-baud-rate">Baud rate</div>
                        <div class="fw-bold text-white" id="usbBaudValue">115200</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="bg-dark rounded p-3 border border-secondary h-100">
                        <div class="text-muted small mb-1" data-i18n="detail-signal">Signal</div>
                        <div class="fw-bold text-white" id="usbSignalValue">96%</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="device-status-actions">
            <button type="button" class="btn btn-connect-node btn-reconnect-device">
                <i class="bi bi-arrow-repeat"></i> <span data-i18n="detail-reconnect">Reconnect</span>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-reset-device">
                <i class="bi bi-plug"></i> <span data-i18n="detail-reset">Reset koneksi</span>
            </button>
        </div>
    </div>

    <!-- ==========================================
         4 KARTU SENSOR DENGAN SPARKLINE
         ========================================== -->
    <div class="row g-4 position-relative z-2">
        <!-- pH Card -->
        <div class="col-12 col-md-6 col-lg-3" data-metric-column="ph">
            <div class="metric-card interactive-metric" data-metric="ph">
                <div class="metric-header d-flex justify-content-between">
                    <span><i class="bi bi-droplet-half"></i> <span data-i18n="sensor-ph">pH Level</span></span>
                    <span class="trend-icon text-muted"><i class="bi bi-activity"></i></span>
                </div>
                <div class="metric-value"><span id="val-ph">--</span> <small>pH</small></div>
                <div class="metric-sparkline" id="spark-ph">
                    <div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div>
                </div>
            </div>
        </div>
        <!-- Moisture Card -->
        <div class="col-12 col-md-6 col-lg-3" data-metric-column="moisture">
            <div class="metric-card interactive-metric" data-metric="moisture">
                <div class="metric-header d-flex justify-content-between">
                    <span><i class="bi bi-moisture"></i> <span data-i18n="sensor-moisture">Moisture</span></span>
                    <span class="trend-icon text-muted"><i class="bi bi-activity"></i></span>
                </div>
                <div class="metric-value"><span id="val-hum">--</span> <small>%</small></div>
                <div class="metric-sparkline" id="spark-hum">
                    <div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div>
                </div>
            </div>
        </div>
        <!-- Temp Card -->
        <div class="col-12 col-md-6 col-lg-3" data-metric-column="temperature">
            <div class="metric-card interactive-metric" data-metric="temp">
                <div class="metric-header d-flex justify-content-between">
                    <span><i class="bi bi-thermometer-half"></i> <span data-i18n="sensor-temperature">Temperature</span></span>
                    <span class="trend-icon text-muted"><i class="bi bi-activity"></i></span>
                </div>
                <div class="metric-value"><span id="val-temp">--</span> <small>Â°C</small></div>
                <div class="metric-sparkline" id="spark-temp">
                    <div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div>
                </div>
            </div>
        </div>
        <!-- EC Card -->
        <div class="col-12 col-md-6 col-lg-3" data-metric-column="ec">
            <div class="metric-card interactive-metric" data-metric="ec">
                <div class="metric-header d-flex justify-content-between">
                    <span class="text-mint"><i class="bi bi-lightning-charge-fill"></i> <span data-i18n="sensor-conductivity">Conductivity</span></span>
                    <span class="trend-icon text-mint"><i class="bi bi-graph-up"></i></span>
                </div>
                <div class="metric-value text-mint"><span id="val-ec">--</span> <small class="text-mint">mS/cm</small></div>
                <div class="metric-sparkline" id="spark-ec">
                    <div class="bar highlight"></div><div class="bar highlight"></div><div class="bar highlight"></div><div class="bar highlight"></div><div class="bar highlight"></div><div class="bar highlight"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==========================================
         PORTFOLIO / HEALTH SCORE DASHBOARD
         ========================================== -->
    <div class="row mt-5 mb-4 position-relative z-2 justify-content-center">
        <div class="col-12 col-md-8 col-lg-6 text-center">
            <div class="portfolio-dashboard">
                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                    <span class="text-secondary fw-bold" style="letter-spacing: 1px; font-size: 0.85rem;" data-i18n="detail-health">OVERALL FARM HEALTH</span>
                </div>

                <div class="display-1 fw-bold text-white mb-0 mt-3 d-flex align-items-center justify-content-center gap-2" style="font-family: 'Outfit', sans-serif;">
                    <span id="aiHealthScore">0</span> <span class="fs-4 text-mint">HTH</span>
                </div>

                <div class="text-muted mt-2 d-flex align-items-center justify-content-center gap-2">
                    <span id="aiHealthStatus">Status: NOT CONNECTED</span>
                </div>

                <!-- Quick Actions (MetaMask style) - sudah fully wired di script.js -->
                <div class="d-flex justify-content-center gap-4 mt-4 pt-3">
                    <div class="quick-action-btn" id="btnSyncData" data-requires-sensor data-bs-toggle="tooltip" title="Sync Telemetry">
                        <div class="action-icon-circle"><i class="bi bi-arrow-down-up"></i></div>
                        <span data-i18n="detail-sync">Sync</span>
                    </div>
                    <div class="quick-action-btn" id="btnSwapAction" data-requires-sensor data-bs-toggle="tooltip" title="Remote Actions">
                        <div class="action-icon-circle text-amber border-amber"><i class="bi bi-shuffle"></i></div>
                        <span class="text-amber" data-i18n="detail-actions">Actions</span>
                    </div>
                    <div class="quick-action-btn" id="btnFertilizeAction" data-requires-sensor data-bs-toggle="tooltip" title="Fertilize">
                        <div class="action-icon-circle"><i class="bi bi-flower2"></i></div>
                        <span data-i18n="detail-fertilize">Fertilize</span>
                    </div>
                    <div class="quick-action-btn" id="btnActivityLog" data-bs-toggle="tooltip" title="View History">
                        <div class="action-icon-circle"><i class="bi bi-clock-history"></i></div>
                        <span data-i18n="detail-activity">Activity</span>
                    </div>
                    <div class="quick-action-btn" id="btnExportData" data-requires-sensor data-bs-toggle="tooltip" title="Export CSV">
                        <div class="action-icon-circle"><i class="bi bi-download"></i></div>
                        <span data-i18n="detail-export">Export</span>
                    </div>
                </div>

                <!-- AI Dynamic Recommendation -->
                <div class="mt-4 pt-3 border-top border-secondary text-start">
                    <p class="text-secondary mb-0" id="aiRecommendation" role="button" tabindex="0" title="Connect Smart AI Analytics" style="font-size: 0.85rem; line-height: 1.5;">
                        <i class="bi bi-lightbulb text-mint me-1"></i> <strong data-i18n="detail-insight">Simulated insight:</strong> Menunggu data sensor pertama...
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal-overlay" id="farmActionModal" role="dialog" aria-modal="true" aria-labelledby="farmActionTitle">
    <div class="web3-modal-box">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
            <div>
                <span class="badge-web3 mb-2" data-i18n="detail-review">Review action</span>
                <h4 class="mb-0 fw-bold" id="farmActionTitle" style="font-family: 'Cinzel', serif;">Farm action</h4>
            </div>
            <button type="button" class="btn-close-custom" id="closeFarmActionModal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>
        <p class="text-muted" id="farmActionDescription">Periksa instruksi sebelum disimpan ke riwayat taman.</p>
        <div class="workspace-stat mb-4"><span class="workspace-stat-label" data-i18n="detail-target">Target</span><strong class="fs-4" id="farmActionTarget">{{ $taman->name }}</strong><i class="bi bi-broadcast-pin"></i></div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary flex-fill" id="cancelFarmAction" data-i18n="detail-cancel">Cancel</button>
            <button type="button" class="btn btn-connect-node flex-fill" id="confirmFarmAction" data-i18n="detail-confirm">Confirm</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="sensorConfigModal" role="dialog" aria-modal="true" aria-labelledby="sensorConfigTitle">
    <div class="web3-modal-box add-taman-wizard-box">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
            <h4 class="mb-0 fw-bold" id="sensorConfigTitle" style="font-family: 'Cinzel', serif;">Edit konfigurasi sensor</h4>
            <button type="button" class="btn-close-custom" id="closeSensorConfigModal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>

        <form action="{{ route('taman.update', $taman) }}" method="POST" id="sensorConfigForm">
            @csrf
            @method('PATCH')

            @php
                $selectedSensorTypes = $taman->sensor_types ?? [];
                $selectedSensorModels = $taman->sensor_models ?? [];
                $selectedController = $taman->controller_type ?? 'esp32';
                $selectedConnection = $taman->device_connection ?? [];
                $selectedSoil = $taman->soil_type ?? '';
                $selectedIndicatorMode = $taman->indicator_mode ?? 'active_only';
            @endphp

            <div class="wizard-step active" data-sensor-step="1">
                <div class="auth-input-group">
                    <label data-i18n="detail-soil-optional">Jenis tanah (opsional)</label>
                    <select class="auth-input" name="soil_type">
                        <option value="" {{ $selectedSoil === '' || $selectedSoil === 'unspecified' ? 'selected' : '' }}>Tidak dipilih</option>
                        <option value="pasir" {{ $selectedSoil === 'pasir' ? 'selected' : '' }}>Pasir</option>
                        <option value="liat_berpasir" {{ $selectedSoil === 'liat_berpasir' ? 'selected' : '' }}>Liat berpasir</option>
                        <option value="latosol" {{ $selectedSoil === 'latosol' ? 'selected' : '' }}>Latosol</option>
                        <option value="liat" {{ $selectedSoil === 'liat' ? 'selected' : '' }}>Liat</option>
                        <option value="organosol" {{ $selectedSoil === 'organosol' ? 'selected' : '' }}>Organosol / gambut</option>
                    </select>
                    <small class="text-muted">Boleh dikosongkan. Mesin akan memakai profil umum dengan confidence lebih rendah.</small>
                </div>
                <div class="auth-input-group">
                    <label data-i18n="detail-active-indicators">Indikator aktif</label>
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
                    <label data-i18n="detail-board-type">Jenis board / otak perangkat</label>
                    <select class="auth-input" name="controller_type">
                        <option value="esp32" {{ $selectedController === 'esp32' ? 'selected' : '' }}>ESP32 (ESP)</option>
                        <option value="esp8266" {{ $selectedController === 'esp8266' ? 'selected' : '' }}>ESP8266 (ESP)</option>
                        <option value="arduino" {{ $selectedController === 'arduino' ? 'selected' : '' }}>Arduino Uno / Nano</option>
                    </select>
                    <small class="text-muted" data-i18n="detail-board-required">Pilih board yang dikonfigurasi untuk taman ini.</small>
                </div>
                <div class="row g-2">
                    <div class="col-6 auth-input-group">
                        <label>Port laptop</label>
                        <select class="auth-input" name="device_connection[computer_port]">
                            <option value="USB-A" {{ ($selectedConnection['computer_port'] ?? 'USB-A') === 'USB-A' ? 'selected' : '' }}>USB-A</option>
                            <option value="USB-C" {{ ($selectedConnection['computer_port'] ?? 'USB-A') === 'USB-C' ? 'selected' : '' }}>USB-C</option>
                            <option value="USB-B" {{ ($selectedConnection['computer_port'] ?? 'USB-A') === 'USB-B' ? 'selected' : '' }}>USB-B</option>
                        </select>
                    </div>
                    <div class="col-6 auth-input-group">
                        <label>Port alat</label>
                        <select class="auth-input" name="device_connection[device_port]">
                            <option value="USB-C" {{ ($selectedConnection['device_port'] ?? 'USB-C') === 'USB-C' ? 'selected' : '' }}>USB-C</option>
                            <option value="USB-B" {{ ($selectedConnection['device_port'] ?? 'USB-C') === 'USB-B' ? 'selected' : '' }}>USB-B</option>
                            <option value="UART" {{ ($selectedConnection['device_port'] ?? 'USB-C') === 'UART' ? 'selected' : '' }}>UART</option>
                        </select>
                    </div>
                </div>
                <div class="auth-input-group">
                    <label>Catatan koneksi</label>
                    <textarea class="auth-input" name="device_connection[note]" rows="3">{{ $selectedConnection['note'] ?? '' }}</textarea>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-outline-secondary btn-sensor-step-prev">Kembali</button>
                    <button type="button" class="btn btn-connect-node btn-sensor-step-next">Review</button>
                </div>
            </div>

            <div class="wizard-step" data-sensor-step="4">
                <div class="auth-input-group">
                    <label>Tampilan indikator</label>
                    <select class="auth-input" name="indicator_mode">
                        <option value="active_only" data-i18n="detail-only-active" {{ $selectedIndicatorMode === 'active_only' ? 'selected' : '' }}>Hanya sensor aktif</option>
                        <option value="all_with_unavailable" data-i18n="detail-all-unavailable" {{ $selectedIndicatorMode === 'all_with_unavailable' ? 'selected' : '' }}>Tampilkan semua, tandai yang tidak tersedia</option>
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

<div class="modal-overlay" id="connectSensorModal" role="dialog" aria-modal="true" aria-labelledby="connectSensorTitle">
    <div class="web3-modal-box">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
            <h4 class="mb-0 fw-bold" id="connectSensorTitle" style="font-family: 'Cinzel', serif;" data-i18n="detail-connect-title">Hubungkan sensor</h4>
            <button type="button" class="btn-close-custom" id="closeConnectSensorModal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>

        <div class="sensor-connect-methods mb-3">
            <button type="button" class="sensor-connect-method active" data-connect-mode="usb">
                <i class="bi bi-usb-port"></i>
                <span>USB langsung</span>
            </button>
            <button type="button" class="sensor-connect-method" data-connect-mode="manual">
                <i class="bi bi-keyboard"></i>
                <span>Masukkan Sensor ID</span>
            </button>
        </div>

        <div class="sensor-connect-panel active" data-connect-panel="usb">
            <div class="alert alert-secondary border-0 mb-3" style="background: rgba(255,255,255,.04); color: var(--text-secondary);">
                <strong class="text-white d-block mb-1">Mode USB-first</strong>
                Sambungkan kabel USB dari laptop ke board {{ $controllerName }}. Setelah perangkat terdeteksi, isi Sensor ID manual bila diperlukan untuk pairing.
            </div>
            <div class="auth-input-group mb-3">
                <label for="sensorIdInput" data-i18n="detail-sensor-id">Sensor ID</label>
                <input type="text" id="sensorIdInput" class="auth-input" placeholder="SENSOR-001" autocomplete="off">
            </div>
            <p class="text-muted mb-0">Scan QR tetap bisa dipakai sebagai fallback, tetapi untuk proyek ini prioritas utama adalah koneksi USB.</p>
        </div>

        <div class="sensor-connect-panel" data-connect-panel="manual">
            <div class="auth-input-group mb-3">
                <label for="sensorIdInputManual" data-i18n="detail-sensor-id">Sensor ID</label>
                <input type="text" id="sensorIdInputManual" class="auth-input" placeholder="SENSOR-001" autocomplete="off">
            </div>
            <p class="text-muted mb-0">Setelah sensor terpasang via kabel USB, masukkan ID perangkat untuk mengaktifkan koneksi.</p>
        </div>

        <button type="button" class="btn btn-connect-node w-100" id="confirmConnectSensor" data-i18n="detail-connect-sensor">Hubungkan sensor</button>
    </div>
</div>
@endsection

@section('scripts')
<script>
// ================================================================
//  NUTRIX - Taman Detail: API-wired Telemetry & Actions
// ================================================================
const TAMAN = {
    id:   {{ $taman->id }},
    type: @json($taman->type),
    name: @json($taman->name),
    indicatorMode: @json($taman->indicator_mode ?: 'active_only'),
    activeSensors: @json(array_values($taman->sensor_types ?? [])),
};
const USB_BOARD_NAME = @json($controllerName);
const USB_PORT_NAME = @json($computerPort);
let usbDeviceConnected = {{ $taman->sensor_connected ? 'true' : 'false' }};
let usbCandidateDetected = false;
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
window.NUTRIX_TAMAN = TAMAN;
window.NUTRIX_USE_API = true;

const detailDynamicCopy = {
    id: {
        optimal: 'Semua parameter berada dalam batas optimal.',
        warning: 'Satu atau lebih parameter mulai berubah. Pertimbangkan sinkronisasi ulang.',
        critical: 'Parameter kritis terdeteksi. Pertimbangkan penyiraman atau pemupukan.',
        empty: 'Belum ada data sensor. Klik Sync untuk membaca data pertama.',
        disconnected: 'Sensor belum terhubung. Hubungkan perangkat untuk mulai menerima telemetry.',
        score: 'Skor'
    },
    en: {
        optimal: 'All parameters are within the optimal range.',
        warning: 'One or more parameters are drifting. Consider syncing again.',
        critical: 'Critical parameters detected. Consider watering or fertilizing.',
        empty: 'No sensor data yet. Click Sync to read the first measurement.',
        disconnected: 'The sensor is not connected. Pair a device to start receiving telemetry.',
        score: 'Score'
    },
    ja: {
        optimal: 'すべてのパラメータは最適範囲内です。',
        warning: '一部のパラメータが変化しています。再同期を検討してください。',
        critical: '重要なパラメータを検出しました。散水または施肥を検討してください。',
        empty: 'センサーデータがありません。同期を押して最初の測定値を取得してください。',
        disconnected: 'センサーが接続されていません。デバイスを接続してデータを受信してください。',
        score: 'スコア'
    },
    ar: {
        optimal: 'جميع المعايير ضمن النطاق المثالي.',
        warning: 'بدأ معيار أو أكثر في الانحراف. فكّر في إعادة المزامنة.',
        critical: 'تم اكتشاف معايير حرجة. فكّر في الري أو التسميد.',
        empty: 'لا توجد بيانات مستشعر بعد. اضغط مزامنة لقراءة القياس الأول.',
        disconnected: 'المستشعر غير متصل. صِل جهازاً لبدء استقبال البيانات.',
        score: 'النتيجة'
    }
};

function detailCopy(key) {
    const language = window.currentLanguage || document.documentElement.lang || 'id';
    const family = language === 'id' || language === 'ja' || language === 'ar' ? language : 'en';
    return detailDynamicCopy[family][key];
}

function apiFetch(path, method = 'GET', body = null) {
    const opts = { method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } };
    if (body) opts.body = JSON.stringify(body);
    return fetch(`/api${path}`, opts).then(async response => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(data.message || 'Request failed');
        return data;
    });
}

function setCard(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value ?? '-';
}

function applyMetricVisibility() {
    const active = new Set(TAMAN.activeSensors.length ? TAMAN.activeSensors : ['moisture', 'ph', 'temperature', 'ec']);
    document.querySelectorAll('[data-metric-column]').forEach(column => {
        const metric = column.dataset.metricColumn;
        column.hidden = TAMAN.indicatorMode === 'active_only' && !active.has(metric);
    });
}

function updateUsbDetectionState() {
    const statusEl = document.getElementById('usbBoardStatus');
    const textEl = document.getElementById('usbDetectionText');
    const pillEl = document.getElementById('usbBoardPill');
    if (!statusEl || !textEl) return;

    const connected = usbDeviceConnected || Boolean(document.querySelector('[data-sensor-connected="true"]'));
    statusEl.textContent = connected ? 'Terdeteksi' : 'Menunggu board';
    statusEl.style.color = connected ? 'var(--color-accent-highlight)' : 'var(--text-muted)';
    textEl.innerHTML = connected
        ? `<i class="bi bi-usb-port me-1"></i> Board ${USB_BOARD_NAME} terdeteksi pada port ${USB_PORT_NAME}.`
        : `<i class="bi bi-usb-port me-1"></i> Hubungkan board ${USB_BOARD_NAME} ke port ${USB_PORT_NAME} sebelum pairing dimulai.`;

    if (pillEl) {
        pillEl.textContent = connected ? 'Board Ready' : 'Awaiting Board';
        pillEl.className = `badge ${connected ? 'bg-success' : 'bg-secondary'} text-white`;
    }
}

function appendUsbConsole(message, type = 'info') {
    const list = document.getElementById('usbConsoleList');
    if (!list) return;

    const line = document.createElement('div');
    line.className = 'py-1';
    const tone = type === 'warn' ? 'text-warning' : type === 'error' ? 'text-danger' : 'text-mint';
    line.innerHTML = `<span class="${tone}">&gt;</span> ${message}`;
    list.appendChild(line);

    while (list.children.length > 8) {
        list.removeChild(list.firstChild);
    }
}

function appendUsbEvent(message, type = 'info') {
    const list = document.getElementById('usbEventStreamList');
    if (!list) return;

    const line = document.createElement('div');
    const tone = type === 'warn' ? 'text-warning' : type === 'error' ? 'text-danger' : 'text-mint';
    line.className = 'py-1';
    line.innerHTML = `<span class="${tone}">&gt;</span> ${message}`;
    list.appendChild(line);

    while (list.children.length > 6) {
        list.removeChild(list.firstChild);
    }
}

function markTelemetryFresh(source = 'USB serial stream') {
    const sourceEl = document.getElementById('telemetryDataSource');
    const updatedEl = document.getElementById('telemetryLastUpdated');
    if (sourceEl) sourceEl.textContent = source;
    if (updatedEl) updatedEl.textContent = new Date().toLocaleTimeString('id-ID');
}

const pairingSequenceState = {
    board: false,
    port: false,
    sensor: false,
};
let telemetryStreamInterval = null;
let boardHealthMonitor = null;
let autoReconnectLock = false;

function stopTelemetryStream() {
    if (telemetryStreamInterval) {
        clearInterval(telemetryStreamInterval);
        telemetryStreamInterval = null;
    }
    const liveStatus = document.getElementById('liveTelemetryStatus');
    if (liveStatus) {
        liveStatus.textContent = 'Stream inactive';
        liveStatus.className = 'badge bg-secondary text-white';
    }
}

function startTelemetryStream() {
    stopTelemetryStream();
    const liveStatus = document.getElementById('liveTelemetryStatus');
    if (liveStatus) {
        liveStatus.textContent = 'API polling active';
        liveStatus.className = 'badge bg-success text-white';
    }

}

function updatePairingProgress() {
    const statusEl = document.getElementById('pairingFlowStatus');
    const steps = document.querySelectorAll('[data-pair-step]');
    const connected = usbDeviceConnected || Boolean(document.querySelector('[data-sensor-connected="true"]'));

    if (!steps.length) return;

    const boardDone = pairingSequenceState.board || connected;
    const portDone = pairingSequenceState.port || connected;
    const sensorDone = pairingSequenceState.sensor || connected;

    steps.forEach(step => {
        const stepName = step.dataset.pairStep;
        const done = stepName === 'board' ? boardDone : stepName === 'port' ? portDone : sensorDone;
        step.classList.toggle('border-success', done);
        step.classList.toggle('bg-success-subtle', done);
        step.style.opacity = done ? '1' : '0.65';
        step.style.borderColor = done ? 'rgba(27, 196, 146, 0.8)' : 'rgba(148,163,184,0.4)';
    });

    if (statusEl) {
        statusEl.textContent = connected ? 'Ready' : (pairingSequenceState.board || pairingSequenceState.port || pairingSequenceState.sensor) ? 'Verifying' : 'Waiting';
        statusEl.className = `badge ${connected ? 'bg-success' : 'bg-secondary'} text-white`;
    }
}

function resetPairingSequence() {
    pairingSequenceState.board = false;
    pairingSequenceState.port = false;
    pairingSequenceState.sensor = false;
    updatePairingProgress();
}

function beginPairingSequence(sensorId) {
    const boardName = USB_BOARD_NAME || 'ESP32';
    const portName = USB_PORT_NAME || 'USB-A';

    resetPairingSequence();
    usbDeviceConnected = false;
    updateUsbDetectionState();
    updateHardwareMetrics();

    appendUsbConsole(`board detection: scanning ${boardName}...`);
    setTimeout(() => {
        pairingSequenceState.board = true;
        usbDeviceConnected = true;
        updateUsbDetectionState();
        updateHardwareMetrics();
        appendUsbConsole(`board detected: ${boardName} on ${portName}`);
    }, 350);

    setTimeout(() => {
        pairingSequenceState.port = true;
        updateHardwareMetrics();
        appendUsbConsole(`port verified: ${portName} ready for serial handshake`);
    }, 900);

    setTimeout(() => {
        pairingSequenceState.sensor = true;
        updateUsbDetectionState();
        updateHardwareMetrics();
        appendUsbConsole(`sensor id validated: ${sensorId}`);
        appendUsbConsole('channel ready: telemetry stream enabled');
        appendUsbEvent(`sensor paired: ${sensorId}`);
        appendUsbEvent('telemetry channel ready');
        pushFarmNotification(`Sensor ${sensorId} berhasil dipasangkan via USB.`, 'bi-usb-port');
        showToast('Pairing sensor berhasil dilakukan.');
        startTelemetryStream();
        setTimeout(() => window.location.reload(), 700);
    }, 1500);
}

function updateHardwareMetrics() {
    const voltage = document.getElementById('usbVoltageValue');
    const baud = document.getElementById('usbBaudValue');
    const signal = document.getElementById('usbSignalValue');
    const boardSignal = document.getElementById('boardSignalValue');
    const boardPort = document.getElementById('boardPortValue');
    const boardRetry = document.getElementById('boardRetryValue');
    const healthPill = document.getElementById('boardHealthPill');
    if (!voltage || !baud || !signal) return;

    const connected = usbDeviceConnected || Boolean(document.querySelector('[data-sensor-connected="true"]'));
    if (connected) {
        voltage.textContent = '3.3V';
        baud.textContent = '115200';
        signal.textContent = '96%';
        if (boardSignal) boardSignal.textContent = '96%';
        if (boardPort) boardPort.textContent = 'Nominal';
        if (boardRetry) boardRetry.textContent = '1';
        if (healthPill) {
            healthPill.textContent = 'Stable';
            healthPill.className = 'badge bg-success text-white';
        }
    } else {
        voltage.textContent = '0.0V';
        baud.textContent = '0';
        signal.textContent = '0%';
        if (boardSignal) boardSignal.textContent = '0%';
        if (boardPort) boardPort.textContent = 'Offline';
        if (boardRetry) boardRetry.textContent = '0';
        if (healthPill) {
            healthPill.textContent = 'Offline';
            healthPill.className = 'badge bg-secondary text-white';
        }
    }
    updatePairingProgress();
}

function refreshUsbEventStream(state = 'Connected') {
    const statusEl = document.getElementById('usbEventStreamStatus');
    if (statusEl) {
        statusEl.textContent = state;
        statusEl.className = `badge ${state === 'Connected' ? 'bg-success' : state === 'Reconnecting' ? 'bg-warning text-dark' : 'bg-secondary'} text-white`;
    }
}

function handleReconnectionAlert(status, signal) {
    const signalAlert = document.getElementById('signalAlertStatus');
    const reconnectText = document.getElementById('reconnectPolicyText');
    const reconnectStatus = document.getElementById('reconnectPolicyStatus');

    if (signalAlert) {
        signalAlert.textContent = status === 'Stable' ? 'Stable' : 'Signal alert';
        signalAlert.style.color = status === 'Stable' ? 'var(--color-accent-highlight)' : '#fbbf24';
    }

    if (reconnectText) {
        reconnectText.textContent = status === 'Stable' ? 'Queued' : 'Retry now';
    }

    if (reconnectStatus) {
        reconnectStatus.textContent = status === 'Stable' ? 'Ready' : 'Retrying';
        reconnectStatus.className = `badge ${status === 'Stable' ? 'bg-success' : 'bg-warning text-dark'} text-white`;
    }

    if (status !== 'Stable' && !autoReconnectLock) {
        autoReconnectLock = true;
        refreshUsbEventStream('Reconnecting');
        appendUsbConsole(`signal alert: board signal ${signal}% dropped below safe threshold`, 'warn');
        pushFarmNotification('Signal alert: USB board sedang mencoba reconnect otomatis.', 'bi-arrow-repeat');
        showToast('Signal alert: reconnecting board...', 'error');
    }

    if (status === 'Stable') {
        autoReconnectLock = false;
        refreshUsbEventStream('Connected');
    }
            appendUsbEvent(`signal alert: ${signal}%`, 'warn');
}

function startBoardHealthMonitor() {
    if (boardHealthMonitor) clearInterval(boardHealthMonitor);

    boardHealthMonitor = setInterval(() => {
        const connected = usbDeviceConnected || Boolean(document.querySelector('[data-sensor-connected="true"]'));
        const boardSignal = document.getElementById('boardSignalValue');
        const boardPort = document.getElementById('boardPortValue');
        const boardRetry = document.getElementById('boardRetryValue');
        const healthPill = document.getElementById('boardHealthPill');
        if (!boardSignal || !boardPort || !boardRetry || !healthPill || !connected) {
            updateHardwareMetrics();
            return;
        }

        const signal = 78 + Math.round(Math.sin(Date.now() / 1200) * 16 + Math.random() * 6);
        const clamped = Math.min(99, Math.max(62, signal));
        const status = clamped >= 90 ? 'Stable' : clamped >= 75 ? 'Watch' : 'Critical';
        const portState = clamped >= 75 ? 'Nominal' : 'Drifting';
        const retries = clamped >= 75 ? 1 : clamped >= 68 ? 2 : 3;

        boardSignal.textContent = `${clamped}%`;
        document.getElementById('usbSignalValue').textContent = `${clamped}%`;
        boardPort.textContent = portState;
        boardRetry.textContent = String(retries);
        healthPill.textContent = status;
        healthPill.className = `badge ${status === 'Stable' ? 'bg-success' : status === 'Watch' ? 'bg-warning text-dark' : 'bg-danger'} text-white`;
        handleReconnectionAlert(status, clamped);

        if (status !== 'Stable') {
            appendUsbConsole(`board health warning: signal ${clamped}% - ${status.toLowerCase()} connection`, 'warn');
        }
            appendUsbEvent('connection healthy: telemetry continuing');
    }, 6000);
}

function autoDetectUsbPort() {
    const resolvedPort = USB_PORT_NAME || 'USB-A';
    const boardName = USB_BOARD_NAME || 'ESP32';
    usbCandidateDetected = true;
    pairingSequenceState.board = true;
    pairingSequenceState.port = true;
    pairingSequenceState.sensor = false;
    updateUsbDetectionState();
    updatePairingProgress();
    appendUsbConsole(`usb candidate detected: ${boardName} on ${resolvedPort}`);
    appendUsbConsole(`serial port opened: ${resolvedPort}`);
    appendUsbConsole('sensor handshake: waiting for explicit pairing');
    pushFarmNotification(`Board ${boardName} otomatis terdeteksi pada port ${resolvedPort}.`, 'bi-usb-port');
    openBoardConfiguration();
    showToast(runtimeText('detail-usb-candidate', { port: resolvedPort }));
}

function updateCards(t) {
    if (!t) return;
    const metrics = t.metrics || {};
    const value = metric => metrics[metric]?.value ?? t[metric] ?? null;
    setCard('val-ph', value('ph') != null ? Number(value('ph')).toFixed(1) : '--');
    setCard('val-hum', value('moisture') != null ? Number(value('moisture')).toFixed(0) : '--');
    setCard('val-temp', value('temperature') != null ? Number(value('temperature')).toFixed(1) : '--');
    setCard('val-ec', value('ec') != null ? Number(value('ec')).toFixed(2) : '--');
    if (t.health?.score != null || t.health_score != null) {
        setCard('aiHealthScore', Math.round(t.health?.score ?? t.health_score));
        const label = t.decision?.tier ? `PRIORITAS ${t.decision.tier}` : (t.health_status || 'UNKNOWN').toUpperCase();
        setCard('aiHealthStatus', 'Status: ' + label);
    }
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
    el.style.cssText = `background:${type==='error'?'#ef4444':'var(--color-accent-highlight)'};color:#fff;padding:12px 20px;border-radius:12px;font-weight:600;font-size:.9rem;box-shadow:0 4px 20px rgba(0,0,0,.35);`;
    el.textContent = msg;
    wrap.appendChild(el);
    setTimeout(() => el.remove(), 3500);
}

function pushFarmNotification(message, icon = 'bi-bell') {
    if (typeof pushNotification === 'function') {
        pushNotification(message, icon);
        return;
    }

    const panel = document.getElementById('notificationList');
    const badge = document.getElementById('notificationBadge');
    if (!panel) return;

    const item = document.createElement('div');
    item.className = 'notification-item';
    item.innerHTML = `<div class="notification-item-icon"><i class="bi ${icon}"></i></div><div><p>${message}</p><small>Just now</small></div>`;
    panel.prepend(item);

    if (badge) {
        const count = panel.querySelectorAll('.notification-item').length;
        badge.textContent = count > 0 ? String(count) : '';
    }
}

function setSensorAvailability(connected) {
    document.querySelectorAll('[data-requires-sensor]').forEach(action => {
        action.classList.toggle('is-disabled', !connected);
        action.setAttribute('aria-disabled', connected ? 'false' : 'true');
    });
}

function showDisconnectedState() {
    setCard('val-ph', '--');
    setCard('val-hum', '--');
    setCard('val-temp', '--');
    setCard('val-ec', '--');
    setCard('aiHealthScore', '--');
    setCard('aiHealthStatus', detailCopy('disconnected'));
    const ai = document.getElementById('aiRecommendation');
    if (ai) ai.innerHTML = `<i class="bi bi-plug text-warning me-1"></i> <strong>${document.querySelector('[data-i18n="detail-insight"]')?.textContent || 'Insight'}:</strong> ${detailCopy('disconnected')}`;
}

async function loadLatestTelemetry() {
    try {
        applyMetricVisibility();
        const data = await apiFetch(`/taman/${TAMAN.id}/telemetry/latest`);
        setSensorAvailability(data.available_sensors?.length > 0);
        if (data.lifecycle === 'never_received' && !data.recorded_at) {
            showDisconnectedState();
            return;
        }
        updateCards(data);
        markTelemetryFresh(data.source === 'simulator' ? 'Simulasi' : 'Telemetry API');
        const ai = document.getElementById('aiRecommendation');
        if (ai && data.health?.score != null) {
            const score = Math.round(data.health.score);
            const tip = score >= 80 ? detailCopy('optimal') : score >= 55 ? detailCopy('warning') : detailCopy('critical');
            ai.innerHTML = `<i class="bi bi-lightbulb text-mint me-1"></i> <strong>${document.querySelector('[data-i18n="detail-insight"]')?.textContent || 'Insight'}:</strong> ${tip} (${detailCopy('score')}: ${score})`;
        }
    } catch (e) { console.warn('Telemetry load failed', e); }
}
updateUsbDetectionState();
updateHardwareMetrics();
startBoardHealthMonitor();
loadLatestTelemetry();
if (usbDeviceConnected || Boolean(document.querySelector('[data-sensor-connected="true"]'))) {
    startTelemetryStream();
}
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        stopTelemetryStream();
    } else {
        loadLatestTelemetry();
        if (usbDeviceConnected) startTelemetryStream();
    }
});
document.getElementById('btnTriggerWaterManual')?.addEventListener('click', async () => {
    try {
        showToast('Mengirim instruksi buka keran (Relay) ke ESP32...');
        const res = await apiFetch(`/taman/${TAMAN.id}/actions/water`, 'POST', { duration_sec: 10 });
        const relayDisplay = document.getElementById('relayStatusDisplay');
        if (relayDisplay) {
            relayDisplay.textContent = 'KERAN TERBUKA (10 DETIK)';
            relayDisplay.className = 'text-mint animate-pulse';
            setTimeout(() => {
                relayDisplay.textContent = 'SIAP (STANDBY)';
                relayDisplay.className = 'text-white';
            }, 10000);
        }
        showToast('Instruksi keran air berhasil dikirim!');
        loadLatestTelemetry();
    } catch (err) {
        showToast(err.message || 'Gagal mengirim instruksi keran', 'error');
    }
});

document.getElementById('btnSyncDataDirect')?.addEventListener('click', () => {
    loadLatestTelemetry();
    showToast('Data telemetri diperbarui.');
});

setInterval(() => {
    if (!document.hidden) loadLatestTelemetry();
}, 5000);

const sensorConfigModal = document.getElementById('sensorConfigModal');
const configForm = document.getElementById('sensorConfigForm');
const configWizardSteps = Array.from(document.querySelectorAll('[data-sensor-step]'));
let currentConfigStep = 1;

const updateSensorWizardStep = (nextStep) => {
    currentConfigStep = nextStep;
    configWizardSteps.forEach((step) => {
        step.classList.toggle('active', Number(step.dataset.sensorStep) === nextStep);
    });
};

const openBoardConfiguration = () => {
    updateSensorWizardStep(3);
    refreshSensorConfigSummary();
    sensorConfigModal?.classList.add('active');
    document.body.style.overflow = 'hidden';
    document.querySelector('#sensorConfigForm select[name="controller_type"]')?.focus();
};

const runtimeText = (key, params = {}) => {
    const language = window.currentLanguage || 'id';
    const template = window.languageDictionary?.[language]?.[key] || window.languageDictionary?.id?.[key] || key;
    return Object.entries(params).reduce((text, [name, value]) => text.replace(`{${name}}`, value), template);
};

document.addEventListener('nutrix:languagechange', () => {
    if (usbCandidateDetected) {
        const candidateText = document.getElementById('usbDetectionText');
        if (candidateText) candidateText.textContent = runtimeText('detail-usb-candidate', { port: USB_PORT_NAME || 'USB' });
    }
});

const refreshSensorConfigSummary = () => {
    const selected = Array.from(document.querySelectorAll('#sensorConfigForm input[name="sensor_types[]"]:checked')).map(input => input.value);
    const summary = document.getElementById('sensorConfigSummary');
    if (!summary) return;

    if (!selected.length) {
        summary.innerHTML = '<div class="wizard-summary-empty">Belum ada sensor dipilih. Pilih minimal satu sensor sebelum menyimpan.</div>';
        return;
    }

    const rows = selected.map(type => {
        const labels = { moisture: 'Kelembapan', temperature: 'Suhu', ph: 'pH', ec: 'EC' };
        const model = document.querySelector(`#sensorConfigForm select[name="sensor_models[${type}]"]`)?.value || 'Belum dipilih';
        return `<div class="wizard-summary-item"><span>${labels[type] || type}</span><strong>${model}</strong></div>`;
    }).join('');

    const controller = document.querySelector('#sensorConfigForm select[name="controller_type"]')?.value || 'esp32';
    const portLaptop = document.querySelector('#sensorConfigForm select[name="device_connection[computer_port]"]')?.value || 'USB-A';
    const portDevice = document.querySelector('#sensorConfigForm select[name="device_connection[device_port]"]')?.value || 'USB-C';

    summary.innerHTML = `
        <div class="wizard-summary-item"><span>Sensor</span><strong>${selected.length} tipe aktif</strong></div>
        ${rows}
        <div class="wizard-summary-item"><span>Board</span><strong>${controller.toUpperCase()}</strong></div>
        <div class="wizard-summary-item"><span>Koneksi</span><strong>${portLaptop} → ${portDevice}</strong></div>
    `;
};

document.querySelectorAll('.btn-sensor-step-next').forEach((button) => {
    button.addEventListener('click', () => {
        if (currentConfigStep < 4) updateSensorWizardStep(currentConfigStep + 1);
        refreshSensorConfigSummary();
    });
});

document.querySelectorAll('.btn-sensor-step-prev').forEach((button) => {
    button.addEventListener('click', () => {
        if (currentConfigStep > 1) updateSensorWizardStep(currentConfigStep - 1);
    });
});

document.querySelectorAll('#sensorConfigForm input[name="sensor_types[]"]').forEach((checkbox) => {
    checkbox.addEventListener('change', () => {
        const selected = Array.from(document.querySelectorAll('#sensorConfigForm input[name="sensor_types[]"]:checked')).map(item => item.value);
        document.querySelectorAll('#sensorConfigForm [data-sensor-row]').forEach((row) => {
            row.style.display = selected.includes(row.dataset.sensorRow) ? 'block' : 'none';
        });
        refreshSensorConfigSummary();
    });
});

document.querySelectorAll('#sensorConfigForm select[name^="sensor_models"], #sensorConfigForm select[name="controller_type"], #sensorConfigForm select[name="device_connection[computer_port]"], #sensorConfigForm select[name="device_connection[device_port]"]').forEach((select) => {
    select.addEventListener('change', refreshSensorConfigSummary);
});

document.getElementById('btnEditSensorConfig')?.addEventListener('click', () => {
    updateSensorWizardStep(1);
    refreshSensorConfigSummary();
    sensorConfigModal?.classList.add('active');
    document.body.style.overflow = 'hidden';
});

document.getElementById('closeSensorConfigModal')?.addEventListener('click', () => {
    sensorConfigModal?.classList.remove('active');
    document.body.style.overflow = '';
});

sensorConfigModal?.addEventListener('click', event => {
    if (event.target === sensorConfigModal) {
        sensorConfigModal.classList.remove('active');
        document.body.style.overflow = '';
    }
});

const connectSensorModal = document.getElementById('connectSensorModal');
const closeConnectSensor = () => {
    connectSensorModal?.classList.remove('active');
    document.body.style.overflow = '';
};
document.getElementById('btnConnectSensor')?.addEventListener('click', () => {
    connectSensorModal?.classList.add('active');
    document.body.style.overflow = 'hidden';
    document.getElementById('sensorIdInput')?.focus();
});

document.querySelectorAll('.sensor-connect-method').forEach((button) => {
    button.addEventListener('click', () => {
        const mode = button.dataset.connectMode;
        document.querySelectorAll('.sensor-connect-method').forEach((item) => item.classList.toggle('active', item === button));
        document.querySelectorAll('.sensor-connect-panel').forEach((panel) => {
            panel.classList.toggle('active', panel.dataset.connectPanel === mode);
        });

        if (mode === 'manual') {
            setTimeout(() => document.getElementById('sensorIdInputManual')?.focus(), 50);
        } else {
            setTimeout(() => document.getElementById('sensorIdInput')?.focus(), 50);
        }
    });
});

document.getElementById('closeConnectSensorModal')?.addEventListener('click', closeConnectSensor);
connectSensorModal?.addEventListener('click', event => { if (event.target === connectSensorModal) closeConnectSensor(); });
document.getElementById('confirmConnectSensor')?.addEventListener('click', async () => {
    const sensorId = document.getElementById('sensorIdInput')?.value.trim() || document.getElementById('sensorIdInputManual')?.value.trim();
    if (!sensorId) return showToast('Masukkan Sensor ID terlebih dahulu.', 'error');
    const boardType = document.querySelector('#sensorConfigForm select[name="controller_type"]')?.value || null;

    closeConnectSensor();

    try {
        const data = await apiFetch(`/taman/${TAMAN.id}/sensor/connect`, 'POST', { sensor_id: sensorId, board_type: boardType });
        if (data.success || data.connected) {
            beginPairingSequence(sensorId);
            return;
        }
        showToast('Sensor gagal dihubungkan.', 'error');
    } catch (error) {
        showToast(error.message || 'Pairing gagal. Periksa board dan koneksi API.', 'error');
    }
});
document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && sensorConfigModal?.classList.contains('active')) {
        sensorConfigModal.classList.remove('active');
        document.body.style.overflow = '';
    }
    if (event.key === 'Escape' && connectSensorModal?.classList.contains('active')) closeConnectSensor();
});

document.querySelector('.btn-reconnect-device')?.addEventListener('click', async () => {
    const sensorId = document.querySelector('.device-status-card strong')?.textContent?.trim();
    refreshUsbEventStream('Reconnecting');
    showToast('Menyambungkan ulang perangkat...');
    try {
        const boardType = document.querySelector('#sensorConfigForm select[name="controller_type"]')?.value || null;
        const payload = { sensor_id: sensorId && sensorId !== 'Belum dipasangkan' ? sensorId : 'SENSOR-RECONNECT-001', board_type: boardType };
        const data = await apiFetch(`/taman/${TAMAN.id}/sensor/connect`, 'POST', payload);
        if (data.success || data.connected) {
            usbDeviceConnected = true;
            updateUsbDetectionState();
            updateHardwareMetrics();
            appendUsbConsole('reconnect success: board restored on usb channel');
            refreshUsbEventStream('Connected');
            pushFarmNotification('Koneksi sensor berhasil dipulihkan.', 'bi-arrow-repeat');
            showToast('Koneksi berhasil dipulihkan.');
            window.location.reload();
        } else {
            showToast('Gagal melakukan reconnect.', 'error');
        }
    } catch (error) {
        showToast(error.message || 'Reconnect gagal.', 'error');
    }
});

document.querySelector('.btn-reset-device')?.addEventListener('click', async () => {
    showToast('Mereset koneksi perangkat...');
    try {
        const data = await apiFetch(`/taman/${TAMAN.id}/sensor`, 'DELETE');
        if (data.success || data.connected === false) {
            pushFarmNotification('Koneksi sensor diputus. Menunggu pairing ulang.', 'bi-plug');
            showToast('Koneksi berhasil direset.', 'error');
            window.location.reload();
        } else {
            showToast('Gagal mereset koneksi.', 'error');
        }
    } catch (error) {
        showToast(error.message || 'Reset koneksi gagal.', 'error');
    }
});

document.getElementById('btnDetectUsbBoard')?.addEventListener('click', () => {
    autoDetectUsbPort();
    updateHardwareMetrics();
});

document.getElementById('btnAutoDetectUsbPort')?.addEventListener('click', () => {
    autoDetectUsbPort();
    updateHardwareMetrics();
});

document.getElementById('btnSimulateUsbRefresh')?.addEventListener('click', () => {
    appendUsbConsole(`refreshing usb state on ${USB_PORT_NAME || 'USB-A'}`);
    appendUsbConsole(`status: ${usbDeviceConnected ? 'board connected' : 'board not connected'}`);
    if (!usbDeviceConnected) {
        showToast('USB state refreshed. Board belum terdeteksi.', 'error');
    } else {
        showToast('USB state refreshed. Board terdeteksi.');
    }
});

document.getElementById('btnDisconnectUsbBoard')?.addEventListener('click', () => {
    usbDeviceConnected = false;
    updateUsbDetectionState();
    updateHardwareMetrics();
    appendUsbConsole(`usb disconnect: ${USB_PORT_NAME} lost connection`, 'warn');
    pushFarmNotification(`Board ${USB_BOARD_NAME} terputus dari port ${USB_PORT_NAME}.`, 'bi-plug');
    showToast('Board terputus dari USB.', 'error');
});

document.getElementById('btnSyncData')?.addEventListener('click', async () => {
    showToast('Menyinkronkan sensor...');
    try {
        const data = await apiFetch(`/taman/${TAMAN.id}/sync`, 'POST');
        if (data.success) {
            updateCards(data.telemetry);
            pushFarmNotification('Sinkronisasi sensor selesai.', 'bi-arrow-repeat');
            showToast('Sinkronisasi berhasil!');
        } else showToast('Gagal sync.', 'error');
    } catch { showToast('Gagal terhubung ke server.', 'error'); }
});

const farmActionModal = document.getElementById('farmActionModal');
let pendingFarmAction = null;
const closeFarmAction = () => {
    farmActionModal?.classList.remove('active');
    document.body.style.overflow = '';
    pendingFarmAction = null;
};

function openFarmAction(action) {
    pendingFarmAction = action;
    document.getElementById('farmActionTitle').textContent = action.title;
    document.getElementById('farmActionDescription').textContent = action.description;
    farmActionModal?.classList.add('active');
    document.body.style.overflow = 'hidden';
}

document.getElementById('btnSwapAction')?.addEventListener('click', () => openFarmAction({
    endpoint: `/taman/${TAMAN.id}/actions/water`,
    body: { duration_sec: 30 },
    title: 'Konfirmasi penyiraman',
    description: 'Instruksi simulasi akan mencatat penyiraman selama 30 detik ke riwayat taman.',
    success: 'Penyiraman dicatat!',
    icon: 'bi-droplet-fill'
}));

document.getElementById('btnFertilizeAction')?.addEventListener('click', () => openFarmAction({
    endpoint: `/taman/${TAMAN.id}/actions/fertilize`,
    body: { fertilizer_type: 'NPK', volume_ml: 200 },
    title: 'Konfirmasi pemupukan',
    description: 'Instruksi simulasi akan mencatat pemupukan NPK sebanyak 200 ml ke riwayat taman.',
    success: 'Pemupukan dicatat!',
    icon: 'bi-flower2'
}));

document.getElementById('confirmFarmAction')?.addEventListener('click', async () => {
    if (!pendingFarmAction) return;
    const action = pendingFarmAction;
    closeFarmAction();
    showToast('Menyimpan aksi...');
    try {
        const data = await apiFetch(action.endpoint, 'POST', action.body);
        if (data.success) {
            if (data.telemetry) {
                updateCards(data.telemetry);
                markTelemetryFresh('Manual action');
                const ai = document.getElementById('aiRecommendation');
                if (ai && data.telemetry.health_score != null) {
                    const score = Math.round(data.telemetry.health_score);
                    const tip = score >= 80 ? detailCopy('optimal') : score >= 55 ? detailCopy('warning') : detailCopy('critical');
                    ai.innerHTML = `<i class="bi bi-lightbulb text-mint me-1"></i> <strong>${document.querySelector('[data-i18n="detail-insight"]')?.textContent || 'Simulated insight'}:</strong> ${tip} (${detailCopy('score')}: ${score})`;
                }
            }
            pushFarmNotification(action.success, action.icon || 'bi-check-circle');
            showToast(action.success);
        }
    } catch (error) { showToast(error.message || 'Aksi gagal.', 'error'); }
});

document.getElementById('closeFarmActionModal')?.addEventListener('click', closeFarmAction);
document.getElementById('cancelFarmAction')?.addEventListener('click', closeFarmAction);
farmActionModal?.addEventListener('click', event => { if (event.target === farmActionModal) closeFarmAction(); });
document.addEventListener('keydown', event => { if (event.key === 'Escape' && farmActionModal?.classList.contains('active')) closeFarmAction(); });

document.getElementById('btnActivityLog')?.addEventListener('click', async () => {
    try {
        const data = await apiFetch(`/taman/${TAMAN.id}/activities`);
        const items = data.activities || [];
        const icons = { sync:'sync_icon', water:'droplet-fill', fertilize:'flower2', alert:'exclamation-triangle', export:'download' };
        const html = items.length === 0
            ? '<p style="color:var(--text-muted);font-size:.9rem;">Belum ada aktivitas.</p>'
            : items.map(a => `<div style="display:flex;gap:10px;padding:10px 0;border-bottom:1px solid var(--border-subtle);">
                    <i class="bi bi-${icons[a.type]||'journal'}" style="color:var(--color-accent-highlight);margin-top:3px;"></i>
                    <div>
                        <div style="font-weight:600;font-size:.88rem;">${a.title}</div>
                        <div style="font-size:.75rem;color:var(--text-muted);">${new Date(a.created_at).toLocaleString('id-ID')}</div>
                    </div></div>`).join('');

        let modal = document.getElementById('activityDrawer');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'activityDrawer';
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.7);backdrop-filter:blur(6px);z-index:9998;display:flex;align-items:flex-end;justify-content:center;';
            modal.innerHTML = `<div style="background:var(--bg-secondary);border-radius:24px 24px 0 0;width:100%;max-width:580px;max-height:70vh;padding:1.5rem;overflow-y:auto;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                    <h5 style="font-family:'Cinzel',serif;font-weight:700;margin:0;">Riwayat Aktivitas</h5>
                    <button onclick="document.getElementById('activityDrawer').remove()" style="background:var(--bg-primary);border:1px solid var(--border-subtle);border-radius:50%;width:32px;height:32px;cursor:pointer;color:var(--text-muted);">x</button>
                </div>
                <div id="activityDrawerBody"></div></div>`;
            document.body.appendChild(modal);
            modal.addEventListener('click', e => { if (e.target === modal) modal.remove(); });
        }
        document.getElementById('activityDrawerBody').innerHTML = html;
    } catch { showToast('Gagal memuat riwayat.', 'error'); }
});

document.getElementById('btnExportData')?.addEventListener('click', () => {
    window.location.href = `/api/taman/${TAMAN.id}/export.csv`;
});
</script>
@endsection
