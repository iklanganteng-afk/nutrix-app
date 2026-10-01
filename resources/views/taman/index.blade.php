@extends('layouts.app')

@section('content')
<section class="dashboard-section container standalone-dashboard" id="overview">

    @if($dashboardState === 'setup')
        <div class="dashboard-onboarding" id="dashboardOnboarding">
            <div class="onboarding-icon"><i class="bi bi-flower1"></i></div>
            <span class="badge-web3 mb-3" data-i18n="farm-empty-badge">Belum ada taman</span>
            <h3 data-i18n="farm-empty-title">Tambahkan taman pertama kamu</h3>
            <p data-i18n="farm-empty-description">Taman mewakili satu lahan/kebun yang mau kamu pantau kondisinya (pH, kelembapan, suhu, EC).</p>
            <button class="btn btn-connect-node" id="btnOpenAddTaman"><i class="bi bi-plus-lg me-2"></i><span data-i18n="farm-add">Tambah Taman</span></button>
        </div>
    @else
        <div class="telemetry-content" id="gardens">
            <div class="workspace-heading d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                <div>
                    <span class="badge-web3 mb-2" data-i18n="farm-workspace-label">My Farm Workspace</span>
                    <h2 class="section-title mb-0" style="font-family: 'Cinzel', serif;" data-i18n="farm-my-gardens">Taman Saya</h2>
                    <p class="text-muted mb-0 mt-2"><span data-i18n="farm-greeting">Selamat datang kembali,</span> {{ Auth::user()->name }}.</p>
                </div>
                <button class="btn btn-connect-node" id="btnOpenAddTaman"><i class="bi bi-plus-lg me-2"></i><span data-i18n="farm-add">Tambah Taman</span></button>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4"><div class="workspace-stat"><span class="workspace-stat-label" data-i18n="farm-total">Total taman</span><strong>{{ $tamans->count() }}</strong><i class="bi bi-flower1"></i></div></div>
                <div class="col-12 col-md-4"><div class="workspace-stat workspace-stat-good"><span class="workspace-stat-label" data-i18n="farm-optimal">Kondisi optimal</span><strong>{{ $healthCounts['optimal'] }}</strong><i class="bi bi-check-circle"></i></div></div>
                <div class="col-12 col-md-4"><div class="workspace-stat workspace-stat-warn"><span class="workspace-stat-label" data-i18n="farm-attention">Perlu perhatian</span><strong>{{ $healthCounts['warning'] + $healthCounts['critical'] }}</strong><i class="bi bi-exclamation-circle"></i></div></div>
            </div>

            <div class="row g-4">
                @foreach($tamans as $taman)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="bento-card h-100 text-start">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge-web3"><i class="bi bi-geo-alt me-1"></i>{{ ucfirst($taman->type) }}</span>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn-close-custom btn-edit-taman" title="Edit taman" data-i18n-title="farm-edit"
                                        data-action="{{ route('taman.update', $taman) }}"
                                        data-name="{{ $taman->name }}"
                                        data-type="{{ $taman->type }}"
                                        data-location="{{ $taman->location }}"><i class="bi bi-pencil"></i></button>
                                    <form action="{{ route('taman.destroy', $taman) }}" method="POST" onsubmit="return confirm(window.nutrixText('taman-confirm-delete') || 'Hapus taman ini secara permanen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-close-custom" title="Hapus taman" data-i18n-title="farm-delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                            <h5 class="fw-bold text-white mb-1" style="font-family: 'Cinzel', serif;">{{ $taman->name }}</h5>
                            <p class="text-muted mb-2" style="font-size: 0.85rem;">
                                @if($taman->location)
                                    {{ $taman->location }}
                                @else
                                    <span data-i18n="taman-no-location">Lokasi belum diisi</span>
                                @endif
                            </p>
                            @if($taman->latestTelemetry)
                                <div class="mb-3 d-flex justify-content-between text-muted" style="font-size: 0.8rem;">
                                    <span><span data-i18n="taman-health-label">Health:</span> <strong class="text-white">{{ round($taman->latestTelemetry->health_score) }}</strong></span>
                                    <span>{{ $taman->latestTelemetry->recorded_at->diffForHumans() }}</span>
                                </div>
                                <span class="workspace-health workspace-health-{{ $taman->latestTelemetry->health_status }} mb-3"><i class="bi bi-circle-fill"></i>{{ ucfirst($taman->latestTelemetry->health_status) }}</span>
                            @else
                                <span class="workspace-health workspace-health-unknown mb-3"><i class="bi bi-circle-fill"></i><span data-i18n="taman-no-telemetry">Belum ada telemetry</span></span>
                            @endif

                            <a href="{{ route('taman.show', $taman) }}" class="btn btn-action-trigger w-100"><span data-i18n="farm-open">Buka Dashboard</span> <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</section>

<section class="workspace-activity container" id="activity">
    <div class="workspace-activity-heading">
        <div>
            <span class="badge-web3" data-i18n="nav-activity">Activity</span>
            <h3 data-i18n="detail-activity">Aktivitas terbaru</h3>
        </div>
        <i class="bi bi-clock-history"></i>
    </div>
    <div class="workspace-activity-list">
        @forelse($recentActivities as $activity)
            <div class="workspace-activity-item">
                <span class="workspace-activity-icon"><i class="bi bi-{{ $activity->type === 'water' ? 'droplet' : ($activity->type === 'fertilize' ? 'flower2' : 'arrow-repeat') }}"></i></span>
                <div><strong>{{ $activity->title }}</strong><small>{{ $activity->taman?->name }} · {{ $activity->created_at->diffForHumans() }}</small></div>
                <span class="workspace-activity-status">{{ ucfirst($activity->status) }}</span>
            </div>
        @empty
            <p class="text-muted mb-0" data-i18n="farm-empty-activity">Belum ada aktivitas. Aktivitas sync dan aksi taman akan muncul di sini.</p>
        @endforelse
    </div>
</section>

<!-- ADD TAMAN MODAL -->
<div class="modal-overlay" id="addTamanModal">
    <div class="web3-modal-box add-taman-wizard-box">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
            <h4 class="mb-0 fw-bold" style="font-family: 'Cinzel', serif;" data-i18n="farm-add-title">Tambah Taman</h4>
            <button class="btn-close-custom" id="closeAddTamanModal"><i class="bi bi-x-lg"></i></button>
        </div>

        <div class="wizard-progress mb-4">
            <div class="wizard-progress-step active" data-progress-step="1">1</div>
            <div class="wizard-progress-line"></div>
            <div class="wizard-progress-step" data-progress-step="2">2</div>
            <div class="wizard-progress-line"></div>
            <div class="wizard-progress-step" data-progress-step="3">3</div>
            <div class="wizard-progress-line"></div>
            <div class="wizard-progress-step" data-progress-step="4">4</div>
            <div class="wizard-progress-line"></div>
            <div class="wizard-progress-step" data-progress-step="5">5</div>
        </div>

        <form action="{{ route('taman.store') }}" method="POST" id="addTamanForm">
            @csrf

            <div class="wizard-step active" data-step="1">
                <div class="auth-input-group">
                    <label data-i18n="farm-name">Nama Taman</label>
                    <input type="text" name="name" class="auth-input" data-i18n-placeholder="wizard-placeholder-name" placeholder="Kebun Jagung Utara" required>
                </div>
                <div class="auth-input-group">
                    <label data-i18n="farm-type">Tipe Lingkungan</label>
                    <div class="custom-select-wrapper" id="tamanTypeSelectWrapper">
                        <div class="custom-select-trigger">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-flower2 text-mint" id="triggerTamanTypeIcon"></i>
                                <span id="triggerTamanTypeText" data-i18n="type-corn">Ladang Jagung</span>
                            </div>
                            <i class="bi bi-chevron-down text-muted chevron"></i>
                        </div>
                        <div class="custom-options-container">
                            <div class="custom-option selected" data-value="corn" data-icon="bi-flower2">
                                <i class="bi bi-flower2 text-mint me-3"></i> <span data-i18n="type-corn">Ladang Jagung</span>
                            </div>
                            <div class="custom-option" data-value="greenhouse" data-icon="bi-building">
                                <i class="bi bi-building text-mint me-3"></i> <span data-i18n="type-greenhouse">Greenhouse</span>
                            </div>
                            <div class="custom-option" data-value="rice" data-icon="bi-water">
                                <i class="bi bi-water text-mint me-3"></i> <span data-i18n="type-rice">Sawah Padi</span>
                            </div>
                            <div class="custom-option" data-value="custom" data-icon="bi-grid-fill">
                                <i class="bi bi-grid-fill text-mint me-3"></i> <span data-i18n="type-custom">Lainnya</span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="type" id="tamanTypeInput" value="corn">
                </div>
                <div class="auth-input-group">
                    <label data-i18n="farm-location">Lokasi (opsional)</label>
                    <input type="text" name="location" class="auth-input" data-i18n-placeholder="wizard-placeholder-location" placeholder="Area A, Desa Contoh">
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="button" class="btn btn-connect-node btn-next-step" data-i18n="wizard-next">Lanjut</button>
                </div>
            </div>

            <div class="wizard-step" data-step="2">
                <div class="auth-input-group mb-3">
                    <label data-i18n="wizard-select-sensors">Pilih sensor yang akan ditambahkan</label>
                    <div class="sensor-check-grid">
                        <label class="sensor-check-card">
                            <input type="checkbox" name="sensor_types[]" value="moisture" checked>
                            <span><i class="bi bi-moisture"></i> <span data-i18n="sensor-moisture-label">Kelembapan</span></span>
                        </label>
                        <label class="sensor-check-card">
                            <input type="checkbox" name="sensor_types[]" value="temperature" checked>
                            <span><i class="bi bi-thermometer-half"></i> <span data-i18n="sensor-temperature-label">Suhu</span></span>
                        </label>
                        <label class="sensor-check-card">
                            <input type="checkbox" name="sensor_types[]" value="ph" checked>
                            <span><i class="bi bi-droplet-half"></i> <span data-i18n="sensor-ph-label">pH</span></span>
                        </label>
                        <label class="sensor-check-card">
                            <input type="checkbox" name="sensor_types[]" value="ec" checked>
                            <span><i class="bi bi-lightning-charge-fill"></i> <span data-i18n="sensor-ec-label">EC</span></span>
                        </label>
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-outline-secondary btn-prev-step" data-i18n="wizard-back">Kembali</button>
                    <button type="button" class="btn btn-connect-node btn-next-step" data-i18n="wizard-next">Lanjut</button>
                </div>
            </div>

            <div class="wizard-step" data-step="3">
                <div class="auth-input-group">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0 fw-bold text-white"><i class="bi bi-cpu-fill text-mint me-1"></i> Konfigurasi Sensor & Jumlah Node</label>
                        <span class="badge bg-dark text-mint border border-secondary" style="font-size:0.75rem;">Multi-Sensor Scopus Grade</span>
                    </div>
                    <p class="text-muted" style="font-size: 0.83rem;">Pilih apakah Anda ingin memasang <strong>1 sensor</strong> atau <strong>2 sensor sekaligus</strong> (misal Capacitive + Resistive untuk pembanding akurasi).</p>

                    <div id="sensorModelRows">
                        {{-- Kategori Kelembapan Tanah (Multi-sensor ready) --}}
                        <div class="sensor-model-row p-3 mb-3 border border-secondary rounded-3" data-sensor-type="moisture" style="background: rgba(255,255,255,0.02);">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="sensor-model-label fw-bold text-white">
                                    <i class="bi bi-moisture text-mint me-1"></i> <span data-i18n="sensor-moisture-label">Kelembapan Tanah</span>
                                </div>
                                <div class="btn-group btn-group-sm" role="group" id="moistureCountGroup">
                                    <input type="radio" class="btn-check" name="sensor_config_count[moisture]" id="moistCount1" value="1" autocomplete="off">
                                    <label class="btn btn-outline-secondary" for="moistCount1">1 Sensor</label>
                                    <input type="radio" class="btn-check" name="sensor_config_count[moisture]" id="moistCount2" value="2" checked autocomplete="off">
                                    <label class="btn btn-outline-mint" for="moistCount2">2 Sensor (Dual)</label>
                                </div>
                            </div>

                            <div class="row g-2 mt-1">
                                <div class="col-12 col-md-6">
                                    <label class="text-muted small mb-1">Slot Sensor 1 (Pin D34 [S]):</label>
                                    <select class="auth-input" name="sensor_models[moisture]" id="moistureModel1">
                                        <option value="Capacitive-V2" selected>Capacitive Soil Moisture V2.0 (Pin D34)</option>
                                        <option value="HD-38">Resistive Soil Moisture HD-38 (Pin D34)</option>
                                        <option value="SEN0193">SEN0193 Waterproof Analog</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6" id="slotMoisture2Wrapper">
                                    <label class="text-muted small mb-1">Slot Sensor 2 (Pin D35 [S]):</label>
                                    <select class="auth-input" name="sensor_models_slot2[moisture]" id="moistureModel2">
                                        <option value="HD-38" selected>Resistive Soil Moisture HD-38 (Pin D35)</option>
                                        <option value="Capacitive-V2">Capacitive Soil Moisture V2.0 (Pin D35)</option>
                                        <option value="YL-69">YL-69 Resistive Probe</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-2 p-2 rounded bg-black border border-secondary text-mint" style="font-size:0.75rem; font-family:monospace;">
                                <i class="bi bi-info-circle me-1"></i> <strong>Wiring Shield:</strong> Sensor 1 ke Baris <strong>D34 [G-V-S]</strong>, Sensor 2 ke Baris <strong>D35 [G-V-S]</strong>.
                            </div>
                        </div>

                        {{-- Kategori Suhu Lingkungan --}}
                        <div class="sensor-model-row p-3 mb-3 border border-secondary rounded-3" data-sensor-type="temperature" style="background: rgba(255,255,255,0.02);">
                            <div class="sensor-model-label fw-bold text-white mb-2"><i class="bi bi-thermometer-half text-warning me-1"></i> <span data-i18n="sensor-temperature-label">Suhu</span></div>
                            <select class="auth-input" name="sensor_models[temperature]">
                                <option value="DHT22">DHT22 - Temperature & Humidity (Digital)</option>
                                <option value="DS18B20">DS18B20 - Waterproof Temperature</option>
                                <option value="LM35">LM35 - Analog Temperature</option>
                            </select>
                        </div>

                        {{-- Kategori pH Tanah --}}
                        <div class="sensor-model-row p-3 mb-3 border border-secondary rounded-3" data-sensor-type="ph" style="background: rgba(255,255,255,0.02);">
                            <div class="sensor-model-label fw-bold text-white mb-2"><i class="bi bi-droplet-half text-info me-1"></i> <span data-i18n="sensor-ph-label">pH Tanah</span></div>
                            <select class="auth-input" name="sensor_models[ph]">
                                <option value="PH-4502C">PH-4502C - Analog pH Sensor Probe</option>
                                <option value="Atlas-pH">Atlas Scientific Lab pH</option>
                                <option value="PH-1">PH-1 - Industrial pH Module</option>
                            </select>
                        </div>

                        {{-- Kategori EC / Nutrisi --}}
                        <div class="sensor-model-row p-3 mb-3 border border-secondary rounded-3" data-sensor-type="ec" style="background: rgba(255,255,255,0.02);">
                            <div class="sensor-model-label fw-bold text-white mb-2"><i class="bi bi-lightning-charge-fill text-success me-1"></i> <span data-i18n="sensor-ec-label">EC / Konduktivitas</span></div>
                            <select class="auth-input" name="sensor_models[ec]">
                                <option value="DFRobot-EC">DFRobot Analog EC Meter</option>
                                <option value="Atlas-EC">Atlas Scientific K1.0 EC</option>
                                <option value="TDS-V1">TDS Meter Sensor V1.0</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-outline-secondary btn-prev-step" data-i18n="wizard-back">Kembali</button>
                    <button type="button" class="btn btn-connect-node btn-next-step" data-i18n="wizard-next">Lanjut</button>
                </div>
            </div>

            <div class="wizard-step" data-step="4">
                <div class="auth-input-group">
                    <label data-i18n="wizard-select-controller">Pilih otak perangkat</label>
                    <select class="auth-input" name="controller_type">
                        <option value="esp32">ESP32</option>
                        <option value="arduino">Arduino Uno / Nano</option>
                        <option value="esp8266">ESP8266</option>
                    </select>
                </div>
                <div class="row g-2">
                    <div class="col-6 auth-input-group">
                        <label data-i18n="wizard-laptop-port">Port laptop</label>
                        <select class="auth-input" name="device_connection[computer_port]">
                            <option value="USB-A">USB-A</option>
                            <option value="USB-C">USB-C</option>
                            <option value="USB-B">USB-B</option>
                        </select>
                    </div>
                    <div class="col-6 auth-input-group">
                        <label data-i18n="wizard-device-port">Port alat</label>
                        <select class="auth-input" name="device_connection[device_port]">
                            <option value="USB-C">USB-C</option>
                            <option value="USB-B">USB-B</option>
                            <option value="UART">UART</option>
                        </select>
                    </div>
                </div>
                <div class="auth-input-group">
                    <label data-i18n="wizard-connection-note">Catatan koneksi</label>
                    <textarea class="auth-input" name="device_connection[note]" rows="3" data-i18n-placeholder="wizard-placeholder-note" placeholder="Hubungkan laptop ke ESP32 melalui kabel USB-C. Semua sensor dipasang pada board yang sama."></textarea>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-outline-secondary btn-prev-step" data-i18n="wizard-back">Kembali</button>
                    <button type="button" class="btn btn-connect-node btn-next-step" data-i18n="wizard-review">Review</button>
                </div>
            </div>

            <div class="wizard-step" data-step="5">
                <div class="auth-input-group">
                    <label data-i18n="wizard-summary">Ringkasan konfigurasi</label>
                    <div class="wizard-summary" id="addTamanSummary"></div>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-outline-secondary btn-prev-step" data-i18n="wizard-back">Kembali</button>
                    <button type="submit" class="btn btn-connect-node" data-i18n="wizard-save">Simpan Taman</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="editTamanModal">
    <div class="web3-modal-box">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
            <h4 class="mb-0 fw-bold" style="font-family: 'Cinzel', serif;" data-i18n="farm-edit">Edit Taman</h4>
            <button class="btn-close-custom" id="closeEditTamanModal"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" id="editTamanForm">
            @csrf
            @method('PATCH')
            <div class="auth-input-group"><label data-i18n="farm-name">Nama Taman</label><input type="text" name="name" id="editTamanName" class="auth-input" required></div>
            <div class="auth-input-group"><label data-i18n="farm-type">Tipe Lingkungan</label><select name="type" id="editTamanType" class="auth-input"><option value="corn">Ladang Jagung</option><option value="greenhouse">Greenhouse</option><option value="rice">Sawah Padi</option><option value="custom">Lainnya</option></select></div>
            <div class="auth-input-group"><label data-i18n="farm-location">Lokasi (opsional)</label><input type="text" name="location" id="editTamanLocation" class="auth-input"></div>
            <button type="submit" class="btn btn-connect-node w-100 mt-2" data-i18n="farm-save">Simpan Perubahan</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Wiring modal Tambah Taman
    document.addEventListener('DOMContentLoaded', () => {
        const addTamanModal = document.getElementById('addTamanModal');
        const closeAddTamanModal = document.getElementById('closeAddTamanModal');
        const editTamanModal = document.getElementById('editTamanModal');
        const closeEditTamanModal = document.getElementById('closeEditTamanModal');
        const editTamanForm = document.getElementById('editTamanForm');
        const closeModal = modal => { modal?.classList.remove('active'); document.body.style.overflow = ''; };

        const wizardSteps = Array.from(document.querySelectorAll('.wizard-step'));
        const progressSteps = Array.from(document.querySelectorAll('.wizard-progress-step'));
        let currentWizardStep = 1;

        const updateWizardStep = (step) => {
            currentWizardStep = step;
            wizardSteps.forEach((element, index) => {
                element.classList.toggle('active', index + 1 === step);
            });
            progressSteps.forEach((element, index) => {
                const isActive = index + 1 <= step;
                element.classList.toggle('active', isActive);
            });
        };

        document.querySelectorAll('.btn-next-step').forEach(button => {
            button.addEventListener('click', () => {
                if (currentWizardStep < 5) updateWizardStep(currentWizardStep + 1);
            });
        });

        document.querySelectorAll('.btn-prev-step').forEach(button => {
            button.addEventListener('click', () => {
                if (currentWizardStep > 1) updateWizardStep(currentWizardStep - 1);
            });
        });

        const refreshSensorSummary = () => {
            const selected = Array.from(document.querySelectorAll('input[name="sensor_types[]"]:checked')).map(input => input.value);
            const summary = document.getElementById('addTamanSummary');
            if (!summary) return;

            const text = (key, params = {}) => window.nutrixText ? window.nutrixText(key, params) : key;
            if (!selected.length) {
                summary.innerHTML = `<div class="wizard-summary-empty">${text('wizard-no-sensor')}</div>`;
                return;
            }

            const rows = selected.map(type => {
                const typeKey = {
                    moisture: 'sensor-moisture-label',
                    temperature: 'sensor-temperature-label',
                    ph: 'sensor-ph-label',
                    ec: 'sensor-ec-label'
                }[type] || type;
                const model = document.querySelector(`select[name="sensor_models[${type}]"]`)?.value || text('wizard-not-selected');
                return `<div class="wizard-summary-item"><span>${text(typeKey)}</span><strong>${model}</strong></div>`;
            }).join('');

            const controller = document.querySelector('select[name="controller_type"]')?.value || 'esp32';
            const portLaptop = document.querySelector('select[name="device_connection[computer_port]"]')?.value || 'USB-A';
            const portDevice = document.querySelector('select[name="device_connection[device_port]"]')?.value || 'USB-C';

            summary.innerHTML = `
                <div class="wizard-summary-item"><span>${text('wizard-sensor')}</span><strong>${text('wizard-selected-sensors', { count: selected.length })}</strong></div>
                ${rows}
                <div class="wizard-summary-item"><span>${text('wizard-board')}</span><strong>${controller.toUpperCase()}</strong></div>
                <div class="wizard-summary-item"><span>${text('wizard-connection')}</span><strong>${portLaptop} → ${portDevice}</strong></div>
            `;
        };

        document.addEventListener('nutrix:languagechange', refreshSensorSummary);

        ['change', 'input'].forEach(eventName => {
            document.addEventListener(eventName, (event) => {
                if (event.target.matches('input[name="sensor_types[]"]')) {
                    const selected = Array.from(document.querySelectorAll('input[name="sensor_types[]"]:checked')).map(item => item.value);
                    document.querySelectorAll('.sensor-model-row').forEach(row => {
                        row.style.display = selected.includes(row.dataset.sensorType) ? 'block' : 'none';
                    });
                    refreshSensorSummary();
                }
                if (event.target.matches('input[name="sensor_config_count[moisture]"]')) {
                    const isDual = event.target.value === '2';
                    const slot2Wrapper = document.getElementById('slotMoisture2Wrapper');
                    if (slot2Wrapper) slot2Wrapper.style.display = isDual ? 'block' : 'none';
                    refreshSensorSummary();
                }
                if (event.target.matches('select[name="controller_type"], select[name="device_connection[computer_port]"], select[name="device_connection[device_port]"]') || event.target.matches('select[name="sensor_models[moisture]"], select[name="sensor_models_slot2[moisture]"], select[name="sensor_models[temperature]"], select[name="sensor_models[ph]"], select[name="sensor_models[ec]"]')) {
                    refreshSensorSummary();
                }
            });
        });

        document.querySelectorAll('#btnOpenAddTaman').forEach(btn => {
            btn.addEventListener('click', () => {
                updateWizardStep(1);
                refreshSensorSummary();
                addTamanModal?.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        });
        closeAddTamanModal?.addEventListener('click', () => closeModal(addTamanModal));
        closeEditTamanModal?.addEventListener('click', () => closeModal(editTamanModal));
        document.querySelectorAll('.btn-edit-taman').forEach(button => button.addEventListener('click', () => {
            editTamanForm.action = button.dataset.action;
            document.getElementById('editTamanName').value = button.dataset.name;
            document.getElementById('editTamanType').value = button.dataset.type;
            document.getElementById('editTamanLocation').value = button.dataset.location || '';
            editTamanModal?.classList.add('active');
            document.body.style.overflow = 'hidden';
        }));
        [addTamanModal, editTamanModal].forEach(modal => modal?.addEventListener('click', event => {
            if (event.target === modal) closeModal(modal);
        }));
        document.addEventListener('keydown', event => {
            if (event.key !== 'Escape') return;
            [addTamanModal, editTamanModal].forEach(modal => { if (modal?.classList.contains('active')) closeModal(modal); });
        });
        document.querySelectorAll('.workspace-nav-link').forEach(link => link.addEventListener('click', () => {
            document.querySelectorAll('.workspace-nav-link').forEach(item => item.classList.remove('active'));
            link.classList.add('active');
        }));
        const initialWorkspaceTarget = window.location.hash.replace('#', '');
        const initialWorkspaceLink = document.querySelector(`.workspace-nav-link[data-workspace-target="${initialWorkspaceTarget}"]`);
        if (initialWorkspaceLink) {
            document.querySelectorAll('.workspace-nav-link').forEach(item => item.classList.remove('active'));
            initialWorkspaceLink.classList.add('active');
        }
        
        const authBtn = document.getElementById('authBtn');
        if (authBtn) authBtn.style.display = 'none';
        
        const avatarWrapper = document.getElementById('accountAvatarWrapper');
        if (avatarWrapper) avatarWrapper.style.display = 'block';

        document.querySelectorAll('.sensor-model-row').forEach(row => {
            const isSelected = document.querySelector(`input[name="sensor_types[]"][value="${row.dataset.sensorType}"]`)?.checked;
            row.style.display = isSelected ? 'block' : 'none';
        });

        refreshSensorSummary();
    });
</script>
@endsection
