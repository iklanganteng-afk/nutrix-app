document.addEventListener("DOMContentLoaded", function () {

    // ============================================================
    // 0. KONFIGURASI GLOBAL NODE
    // ============================================================
    window.currentEnvironment = localStorage.getItem('nutrixEnvironment') || 'corn';

    const languageDictionary = {
        'id': {},
        'en-GB': {},
        'en-US': {},
        'en-CA': {},
        'jv': {},
        'ja': {},
        'ar': {},
        'ms': {}
    };

    const architectureTranslations = {
        'en-GB': {
            npk: { title: 'NPK Precision Matrix', desc: 'The core engine processes raw telemetry from conventional sensors. Multiple linear regression maps EC, pH, and temperature into estimated nitrogen, phosphorus, and potassium levels.', impact: 'Reduces reliance on costly electrochemical NPK sensors and lowers physical maintenance demands.' },
            rs485: { title: 'RS-485 Modbus Architecture', desc: 'An industrial wired communication topology using differential signalling and four data wires designed to resist electromagnetic interference.', impact: 'Maintains stable, packet-loss-resistant data transmission across large fields, with cable runs of up to 1.2 km between nodes.' }
        },
        'en-US': {
            npk: { title: 'NPK Precision Matrix', desc: 'The core engine processes raw telemetry from conventional sensors. Multiple linear regression maps EC, pH, and temperature into estimated nitrogen, phosphorus, and potassium levels.', impact: 'Reduces dependence on expensive electrochemical NPK sensors and lowers physical maintenance needs.' },
            rs485: { title: 'RS-485 Modbus Architecture', desc: 'An industrial wired communication topology using differential signaling and four data wires built to resist electromagnetic interference.', impact: 'Maintains stable, packet-loss-resistant data transmission across large fields, with cable runs up to 1.2 km between nodes.' }
        },
        'en-CA': {
            npk: { title: 'NPK Precision Matrix', desc: 'The core engine processes field telemetry from conventional sensors. It maps EC, pH, and temperature into estimated nitrogen, phosphorus, and potassium levels.', impact: 'Reduces dependence on costly electrochemical NPK sensors and simplifies long-term maintenance.' },
            rs485: { title: 'RS-485 Modbus Architecture', desc: 'An industrial four-wire communication topology using differential signalling to withstand electromagnetic interference.', impact: 'Keeps data transmission stable across large farms, supporting cable runs of up to 1.2 km between nodes.' }
        },
        id: {
            npk: { title: 'Matriks Presisi NPK', desc: 'Mesin inti memproses telemetri mentah dari sensor konvensional. Regresi linear berganda memetakan EC, pH, dan suhu menjadi estimasi kadar nitrogen, fosfor, dan kalium.', impact: 'Mengurangi ketergantungan pada sensor NPK elektrokimia yang mahal dan menurunkan kebutuhan perawatan fisik.' },
            rs485: { title: 'Arsitektur RS-485 Modbus', desc: 'Topologi komunikasi kabel industri dengan sinyal diferensial dan empat kabel data yang tahan terhadap gangguan elektromagnetik.', impact: 'Menjaga transmisi data tetap stabil tanpa packet-loss di lahan luas, dengan jarak kabel hingga 1,2 km antar node.' }
        },
        jv: {
            npk: { title: 'Matriks Presisi NPK', desc: 'Mesin inti ngolah telemetri mentah saka sensor konvensional. Regresi linear majemuk nggambarake EC, pH, lan suhu dadi perkiraan nitrogen, fosfor, lan kalium.', impact: 'Nyuda ketergantungan marang sensor NPK elektrokimia sing larang lan nyuda kabutuhan perawatan fisik.' },
            rs485: { title: 'Arsitektur RS-485 Modbus', desc: 'Topologi komunikasi kabel industri nganggo sinyal diferensial lan papat kabel data sing tahan gangguan elektromagnetik.', impact: 'Njaga transmisi data stabil tanpa packet-loss ing lahan amba, kanthi jarak kabel nganti 1,2 km antar node.' }
        },
        ja: {
            npk: { title: 'NPK精密マトリクス', desc: 'コアエンジンは従来センサーのテレメトリを処理し、EC、pH、温度から窒素・リン・カリウム濃度を推定します。', impact: '高価な電気化学式NPKセンサーへの依存を減らし、物理的な保守負担を軽減します。' },
            rs485: { title: 'RS-485 Modbusアーキテクチャ', desc: '差動信号と4本のデータ線を使用する産業用有線通信トポロジーで、電磁干渉に強い設計です。', impact: '広い農地でも安定したデータ通信を維持し、ノード間最大1.2kmの配線に対応します。' }
        },
        ar: {
            npk: { title: 'مصفوفة NPK الدقيقة', desc: 'يعالج المحرك الأساسي بيانات المستشعرات التقليدية، ويربط التوصيل الكهربائي ودرجة الحموضة والحرارة لتقدير مستويات النيتروجين والفوسفور والبوتاسيوم.', impact: 'يقلل الاعتماد على مستشعرات NPK الكهروكيميائية المكلفة ويخفف متطلبات الصيانة.' },
            rs485: { title: 'بنية RS-485 Modbus', desc: 'طوبولوجيا اتصال سلكية صناعية تستخدم الإشارة التفاضلية وأربعة أسلاك بيانات لمقاومة التداخل الكهرومغناطيسي.', impact: 'تحافظ على نقل بيانات مستقر عبر الحقول الواسعة، مع مسافة كابل تصل إلى 1.2 كم بين العقد.' }
        },
        ms: {
            npk: { title: 'Matriks Ketepatan NPK', desc: 'Enjin teras memproses telemetri daripada penderia konvensional, lalu memetakan EC, pH dan suhu kepada anggaran nitrogen, fosforus dan kalium.', impact: 'Mengurangkan kebergantungan pada penderia NPK elektrokimia yang mahal serta beban penyelenggaraan fizikal.' },
            rs485: { title: 'Seni Bina RS-485 Modbus', desc: 'Topologi komunikasi berwayar industri menggunakan isyarat pembezaan dan empat wayar data yang tahan gangguan elektromagnet.', impact: 'Mengekalkan penghantaran data yang stabil di ladang luas dengan kabel sehingga 1.2 km antara nod.' }
        }
    };
    // Expose dictionary globally so inline scripts (simulator etc.) can access it
    window.languageDictionary = languageDictionary;
    window.nutrixText = (key, params = {}) => {
        const language = window.currentLanguage || 'id';
        const template = languageDictionary[language]?.[key] || languageDictionary.id?.[key] || key;
        return Object.entries(params).reduce((text, [name, value]) => text.replace(`{${name}}`, value), template);
    };

    const literalTranslationKeys = {
        'Sign In': 'ui-sign-in',
        'Sign Up': 'ui-sign-up',
        'Activity': 'detail-activity',
        'Actions': 'detail-actions',
        'Action': 'detail-actions',
        'Fertilize': 'detail-fertilize',
        'Export': 'detail-export',
        'Sync': 'detail-sync',
        'Cancel': 'detail-cancel',
        'Confirm': 'detail-confirm',
        'Definisi': 'metric-definition',
        'Definition': 'metric-definition',
        'Impact on engine': 'metric-effect',
        'Pengaruh Terhadap Analisis Mesin': 'metric-effect',
        'Pengaruh terhadap analisis mesin': 'metric-effect',
        'Settings': 'ui-settings',
        'Email': 'ui-email',
        'Email Address': 'ui-email',
        'Password': 'ui-password',
        'Username': 'ui-username',
        'Nama Lengkap': 'ui-username',
        'Confirm Password': 'ui-confirm-password',
        'Konfirmasi Password': 'ui-confirm-password',
        'Create Account': 'ui-create-account',
        'Notifications': 'ui-notifications',
        'Mark all read': 'ui-mark-read',
        'CURRENT ACCOUNT': 'ui-current-account',
        'Save Changes': 'farm-save',
        'Back': 'auth-back',
        'Resend Code': 'auth-resend',
        'Kirim Ulang Kode': 'auth-resend',
        'Kembali': 'auth-back',
        'Kembali / Ganti': 'auth-back',
        'Verifikasi & Masuk': 'auth-verify',
        'Minta Kode OTP (Sign In)': 'auth-request-signin',
        'Minta Kode OTP (Sign Up)': 'auth-request-signup',
        'Loading...': 'auth-loading',
        'Mengirim Kode OTP...': 'auth-sending',
        'Memproses Akun...': 'auth-processing',
        'Memverifikasi...': 'auth-verifying',
        'OTP Kedaluwarsa': 'auth-expired',
        'Sisa waktu:': 'auth-time-left',
        'Sisa Waktu Berlaku:': 'auth-time-left',
        'Koden 6 digit dikirim ke:': 'auth-code-sent',
        'Kode 6 digit dikirim ke:': 'auth-code-sent',
        'Verifikasi Email Anda': 'auth-email-verified',
        'Optimal': 'hero-status-optimal',
        'Dry': 'hero-status-dry',
        'Live': 'hero-live',
        'Light Mode': 'ui-theme-light',
        'Dark Mode': 'ui-theme-dark',
        'No gardens yet': 'farm-empty-badge',
        'Belum ada taman': 'farm-empty-badge',
        'My Gardens': 'farm-my-gardens',
        'Taman Saya': 'farm-my-gardens',
        'Add Garden': 'farm-add',
        'Tambah Taman': 'farm-add',
        'Open Dashboard': 'farm-open',
        'Buka Dashboard': 'farm-open',
        'Needs attention': 'farm-attention',
        'Perlu perhatian': 'farm-attention',
        'Optimal condition': 'farm-optimal',
        'Kondisi optimal': 'farm-optimal',
        'Overview': 'nav-overview',
        'Ringkasan': 'nav-overview',
        'My Farm Workspace': 'farm-workspace-label',
        'Workspace': 'ui-workspace',
        'pH Level': 'sensor-ph',
        'Moisture': 'sensor-moisture',
        'Temperature': 'sensor-temperature',
        'Conductivity': 'sensor-conductivity',
        'Kelembapan': 'sensor-moisture',
        'Suhu': 'sensor-temperature',
        'Konduktivitas': 'sensor-conductivity'
        ,'Input Belum Lengkap': 'toast-input'
        ,'Autentikasi Gagal': 'toast-auth-failed'
        ,'Kode OTP Terkirim': 'toast-code-sent'
        ,'Gagal Menghubungi Server': 'toast-network-failed'
        ,'Form Belum Lengkap': 'toast-form-required'
        ,'Password Terlalu Pendek': 'toast-password-short'
        ,'Konfirmasi Password Tidak Cocok': 'toast-password-mismatch'
        ,'Format OTP Salah': 'toast-invalid-otp'
        ,'Sukses!': 'toast-success'
        ,'Gagal Mengirim Ulang': 'toast-resend-failed'
    };

    Object.values(languageDictionary).forEach(dictionary => {
        Object.entries(dictionary).forEach(([key, value]) => {
            if (typeof value === 'string' && value.length < 180 && !literalTranslationKeys[value]) {
                literalTranslationKeys[value] = key;
            }
        });
    });

    const literalNodeKeys = new WeakMap();
    let translatingLiteralNodes = false;

    function translateLiteralTextNodes() {
        if (translatingLiteralNodes || !document.body) return;
        translatingLiteralNodes = true;
        const dictionary = languageDictionary[window.currentLanguage || 'id'] || languageDictionary.id;
        const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
        const nodes = [];
        let node;
        while ((node = walker.nextNode())) nodes.push(node);
        nodes.forEach(textNode => {
            const parent = textNode.parentElement;
            if (!parent || ['SCRIPT', 'STYLE', 'TEXTAREA'].includes(parent.tagName) || parent.closest('[data-no-i18n]')) return;
            const source = textNode.nodeValue.trim();
            const key = literalNodeKeys.get(textNode) || literalTranslationKeys[source];
            if (!key || !dictionary[key]) return;
            literalNodeKeys.set(textNode, key);
            const translated = textNode.nodeValue.replace(source, dictionary[key]);
            if (translated !== textNode.nodeValue) textNode.nodeValue = translated;
        });
        translatingLiteralNodes = false;
    }

    const literalTextObserver = new MutationObserver(() => translateLiteralTextNodes());

    window.getArchitectureCopy = key => architectureTranslations[window.currentLanguage || 'id']?.[key] || architectureTranslations.id[key];

    const themeNames = {
        emerald: 'Emerald Field',
        harvest: 'Golden Harvest',
        nordic: 'Nordic Clean',
        obsidian: 'Obsidian Matrix',
        midnight: 'Midnight Soil',
        hydro: 'Deep Hydro'
    };

    function applyTheme(theme) {
        const validTheme = themeNames[theme] ? theme : 'emerald';
        document.documentElement.setAttribute('data-theme', validTheme);
        const isLightTheme = ['emerald', 'harvest', 'nordic'].includes(validTheme);
        document.documentElement.style.colorScheme = isLightTheme ? 'light' : 'dark';
        document.documentElement.setAttribute('data-bs-theme', isLightTheme ? 'light' : 'dark');
        localStorage.setItem('nutrix_theme', validTheme);
        document.querySelectorAll('#currentThemeLabel').forEach(label => {
            label.textContent = themeNames[validTheme];
        });
        document.dispatchEvent(new CustomEvent('nutrix:themechange', { detail: { theme: validTheme } }));
    }

    function applyLanguage(language) {
        const validLanguage = languageDictionary[language] ? language : 'id';
        window.currentLanguage = validLanguage;
        document.documentElement.lang = validLanguage;
        document.documentElement.dir = validLanguage === 'ar' ? 'rtl' : 'ltr';
        localStorage.setItem('nutrix_language', validLanguage);

        document.querySelectorAll('#currentLanguageLabel').forEach(label => {
            label.textContent = validLanguage.toUpperCase();
        });

        document.querySelectorAll('[data-i18n]').forEach(node => {
            const translation = languageDictionary[validLanguage]?.[node.dataset.i18n];
            if (!translation) return;
            node.innerHTML = translation.replace(/\n/g, '<br>');
        });

        document.querySelectorAll('[data-i18n-placeholder]').forEach(node => {
            const translation = languageDictionary[validLanguage]?.[node.dataset.i18nPlaceholder];
            if (translation) node.setAttribute('placeholder', translation);
        });

        document.querySelectorAll('[data-i18n-title]').forEach(node => {
            const translation = languageDictionary[validLanguage]?.[node.dataset.i18nTitle];
            if (translation) node.setAttribute('title', translation);
        });

        translateLiteralTextNodes();

        // Sync hero sensor status to active language
        const dict = languageDictionary[validLanguage] || {};
        const activeEnv = window.currentSensorEnv || 'corn';
        const statusKey = activeEnv === 'corn' ? 'hero-status-optimal' : 'hero-status-dry';
        const statusEl = document.getElementById('heroSensorStatus');
        if (statusEl && dict[statusKey]) statusEl.textContent = dict[statusKey];

        if (typeof window.hitungEfisiensi === 'function') {
            window.hitungEfisiensi();
        }

        // Refresh simulator diagnosis if visible
        if (typeof window.hitungSimulator === 'function') {
            window.hitungSimulator();
        }

        document.dispatchEvent(new CustomEvent('nutrix:languagechange', { detail: { language: validLanguage } }));
    }

    window.applyTheme = applyTheme;
    window.applyLanguage = applyLanguage;

    window.loadNutrixLocaleCatalog = async function (language = window.currentLanguage || 'id') {
        const validLanguage = languageDictionary[language] ? language : 'id';
        try {
            const response = await fetch(`/locales/${encodeURIComponent(validLanguage)}.json`, {
                headers: { Accept: 'application/json' },
                cache: 'no-store'
            });
            if (!response.ok) return false;
            const catalog = await response.json();
            Object.assign(languageDictionary[validLanguage], catalog);
            if (window.currentLanguage === validLanguage) applyLanguage(validLanguage);
            document.dispatchEvent(new CustomEvent('nutrix:catalogloaded', { detail: { language: validLanguage } }));
            return true;
        } catch (error) {
            console.warn('NUTRIX locale catalog could not be loaded.', error);
            return false;
        }
    };

    async function changeLanguage(lang) {
        const valid = languageDictionary[lang] ? lang : 'id';
        if (!languageDictionary[valid] || Object.keys(languageDictionary[valid]).length === 0) {
            await window.loadNutrixLocaleCatalog(valid);
        }
        applyLanguage(valid);
    }
    window.changeLanguage = changeLanguage;

    applyTheme(localStorage.getItem('nutrix_theme') || 'emerald');
    const initialLang = localStorage.getItem('nutrix_language') || 'id';
    window.loadNutrixLocaleCatalog(initialLang).then(() => {
        applyLanguage(initialLang);
    });
    literalTextObserver.observe(document.body, { childList: true, subtree: true, characterData: true });

    document.addEventListener('click', event => {
        const themeButton = event.target.closest('.theme-btn');
        const languageButton = event.target.closest('.language-btn');

        if (themeButton) {
            event.preventDefault();
            applyTheme(themeButton.dataset.themeValue);
        }

        if (languageButton) {
            event.preventDefault();
            changeLanguage(languageButton.dataset.lang);
        }
    });

    // Preferensi tetap sinkron bila user mengganti tema/bahasa dari tab lain.
    window.addEventListener('storage', event => {
        if (event.key === 'nutrix_theme') applyTheme(event.newValue || 'emerald');
        if (event.key === 'nutrix_language') changeLanguage(event.newValue || 'id');
    });

    const heroSensorData = {
        corn: { statusKey: 'hero-status-optimal', ph: '6.8', moisture: '65%' },
        greenhouse: { statusKey: 'hero-status-dry', ph: '5.9', moisture: '42%' }
    };
    window.currentSensorEnv = 'corn';

    document.addEventListener('click', event => {
        const sensorButton = event.target.closest('.split-hero-tab');
        if (!sensorButton) return;

        const env = sensorButton.dataset.sensor;
        const data = heroSensorData[env];
        if (!data) return;

        window.currentSensorEnv = env;

        document.querySelectorAll('.split-hero-tab').forEach(button => {
            const active = button === sensorButton;
            button.classList.toggle('active', active);
            button.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        const lang = window.currentLanguage || 'id';
        const dict = languageDictionary[lang] || {};
        const status = document.getElementById('heroSensorStatus');
        const ph = document.getElementById('heroSensorPh');
        const moisture = document.getElementById('heroSensorMoisture');
        if (status) status.textContent = dict[data.statusKey] || data.statusKey;
        if (ph) ph.textContent = data.ph;
        if (moisture) moisture.textContent = data.moisture;
    });

    /* ========================================================
       1. HERO 2.5D KINEMATICS LERP (MetaMask Smooth Scroll)
       ======================================================== */
    const heroLogo = document.querySelector('#hero-center-logo, #hero-logo');
    const heroCard = document.querySelector('.hero-center-card');
    const heroText = document.querySelector('.hero-kinetic-text');
    const heroWrapper = document.querySelector('.hero-wrapper');

    let targetRotateX = 0, targetRotateY = 0, targetScale = 1, targetOpacity = 1;
    let currentRotateX = 0, currentRotateY = 0, currentScale = 1, currentOpacity = 1;

    if (heroLogo && heroWrapper) {
        window.addEventListener('mousemove', (e) => {
            const centerX = window.innerWidth / 2;
            const centerY = window.innerHeight / 2;
            targetRotateX = (centerY - e.clientY) / 25;
            targetRotateY = (e.clientX - centerX) / 25;
        });

        window.addEventListener('mouseout', () => {
            targetRotateX = 0; targetRotateY = 0;
        });

        let scrollFrame = null;

        const updateHeroFromScroll = () => {
            scrollFrame = null;
            const scrollY = window.scrollY;
            const heroHeight = heroWrapper.offsetHeight;
            const fadeThreshold = Math.max(1, heroHeight - window.innerHeight);
            const fadeProgress = Math.min(1, Math.max(0, (scrollY - fadeThreshold) / Math.max(1, window.innerHeight * 0.65)));

            if (heroText && window.innerWidth > 992) {
                const subtleY = Math.min(60, scrollY * 0.15);
                heroText.style.transform = `translateY(-${subtleY}px)`;
            } else if (heroText) {
                heroText.style.transform = 'none';
            }

            targetScale = 1 + (scrollY * 0.0008);
            targetOpacity = 1 - fadeProgress;
        };

        window.addEventListener('scroll', () => {
            if (scrollFrame === null) {
                scrollFrame = requestAnimationFrame(updateHeroFromScroll);
            }
        }, { passive: true });

        updateHeroFromScroll();

        const lerp = (start, end, factor) => start + (end - start) * factor;

        function renderKinematics() {
            currentRotateX = lerp(currentRotateX, targetRotateX, 0.08);
            currentRotateY = lerp(currentRotateY, targetRotateY, 0.08);
            currentScale = lerp(currentScale, targetScale, 0.08);
            currentOpacity = lerp(currentOpacity, targetOpacity, 0.15);

            heroLogo.style.transform = `perspective(1000px) rotateX(${currentRotateX}deg) rotateY(${currentRotateY}deg) scale(${currentScale})`;

            heroCard.style.opacity = currentOpacity;
            heroLogo.style.opacity = currentOpacity;
            heroLogo.style.pointerEvents = currentOpacity <= 0.05 ? 'none' : 'auto';
            heroCard.style.visibility = currentOpacity < 0.05 ? 'hidden' : 'visible';

            requestAnimationFrame(renderKinematics);
        }

        renderKinematics();
    }


    /* ========================================================
       2. IOT DATA TELEMETRY SIMULATION, AI INSIGHTS & SPARKLINE
       ======================================================== */
    let historyPh = [40, 50, 45, 60, 50, 70];
    let historyHum = [30, 40, 35, 50, 45, 60];
    let historyTemp = [60, 55, 70, 65, 80, 75];
    let historyEc = [20, 40, 30, 60, 50, 80];

    function updateSparkline(elementId, dataArray, isHighlighted) {
        const container = document.getElementById(elementId);
        if (!container) return;
        const bars = container.querySelectorAll('.bar');

        bars.forEach((bar, index) => {
            bar.style.height = `${dataArray[index]}%`;

            if (index === dataArray.length - 1) {
                bar.classList.add('active');
            } else {
                bar.classList.remove('active');
            }
        });
    }

    /* ========================================================
       3. SHARED UI HELPERS: TOAST, ACTIVITY LOG & NOTIFICATIONS
       (Dipusatkan di sini agar tidak ada logic dobel di
        beberapa tempat seperti pada versi sebelumnya)
       ======================================================== */
    const toastNotif = document.getElementById('toastNotif');
    const toastIconWrap = document.querySelector('.toast-icon');
    const toastTitleEl = document.querySelector('.toast-title');
    const toastDescEl = document.querySelector('.toast-desc');

    function showToast(icon, title, description, success = true) {
        if (!toastNotif) return;
        const localize = value => window.nutrixText(literalTranslationKeys[value] || value);
        toastNotif.classList.toggle('success', success);
        if (toastIconWrap) toastIconWrap.innerHTML = `<i class="bi ${icon}"></i>`;
        if (toastTitleEl) toastTitleEl.innerText = localize(title);
        if (toastDescEl) toastDescEl.innerText = localize(description);
        toastNotif.classList.add('show');
        setTimeout(() => toastNotif.classList.remove('show'), 3500);
    }

    const activityModal = document.getElementById('activityModal');
    const activityList = document.getElementById('activityModalList');
    const sidebarActivityList = document.getElementById('sidebarActivityList');
    const activityEntries = JSON.parse(localStorage.getItem('nutrixActivity') || '[]');

    const activityTime = () => new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit' }).format(new Date());

    const activityMarkup = entry => {
        const statusClass = entry.status === 'Failed' ? 'text-danger' : entry.status === 'Pending' ? 'text-warning' : 'text-mint';
        const statusIcon = entry.status === 'Failed' ? 'bi-x-circle-fill' : entry.status === 'Pending' ? 'bi-hourglass-split' : 'bi-check-circle-fill';
        return `<div class="activity-entry d-flex align-items-center p-3 mb-2 rounded bg-dark border border-secondary" data-title="${entry.title}" data-detail="${entry.detail}" data-fee="${entry.fee}" data-status="${entry.status}" data-time="Today, ${entry.time}"><div class="me-3 fs-3 ${statusClass}"><i class="bi ${statusIcon}"></i></div><div class="flex-grow-1"><div class="fw-bold text-white">${entry.title}</div><div class="text-muted" style="font-size:.8rem;">${entry.detail} <span class="badge bg-secondary ms-1">${entry.status}</span></div></div><div class="text-end"><div class="text-white fw-bold">${entry.fee} FEE</div><div class="text-muted" style="font-size:.8rem;">Today, ${entry.time}</div></div></div>`;
    };

    function addActivity(title, detail, fee = '0.000', status = 'Confirmed') {
        const entry = { title, detail, fee, status, time: activityTime() };
        activityEntries.unshift(entry);
        localStorage.setItem('nutrixActivity', JSON.stringify(activityEntries));
        localStorage.removeItem('nutrixActivityCleared');

        const statusClass = status === 'Failed' ? 'text-danger' : status === 'Pending' ? 'text-warning' : 'text-mint';
        const statusIcon = status === 'Failed' ? 'bi-x-circle-fill' : status === 'Pending' ? 'bi-hourglass-split' : 'bi-check-circle-fill';

        if (activityList) activityList.insertAdjacentHTML('afterbegin', activityMarkup(entry));
        if (sidebarActivityList) {
            sidebarActivityList.innerHTML = `<div class="activity-item"><div class="icon bg-mint-transparent ${statusClass}"><i class="bi ${statusIcon}"></i></div><div class="details"><strong>${entry.title}</strong><small class="${statusClass} d-block">${entry.status}</small></div><div class="time">Now</div></div>`;
        }
    }

    // Restore riwayat aktivitas tersimpan (kecuali sudah pernah di-"Clear" pada sesi ini)
    if (activityList && localStorage.getItem('nutrixActivityCleared') === '1') {
        activityList.innerHTML = `<p class="text-center text-muted py-4 mb-0">${window.nutrixText('activity-no-session')}</p>`;
    } else if (activityEntries.length && activityList) {
        [...activityEntries].reverse().forEach(entry => activityList.insertAdjacentHTML('afterbegin', activityMarkup(entry)));
    }

    document.getElementById('btnActivityLog')?.addEventListener('click', () => activityModal?.classList.add('active'));
    document.getElementById('closeActivityModal')?.addEventListener('click', () => activityModal?.classList.remove('active'));
    document.getElementById('btnClearActivity')?.addEventListener('click', () => {
        activityEntries.length = 0;
        localStorage.removeItem('nutrixActivity');
        localStorage.setItem('nutrixActivityCleared', '1');
        if (activityList) activityList.innerHTML = `<p class="text-center text-muted py-4 mb-0">${window.nutrixText('activity-no-session')}</p>`;
        if (sidebarActivityList) sidebarActivityList.innerHTML = `<p class="text-muted small ps-2">${window.nutrixText('activity-no-recent')}</p>`;
    });

    // Detail transaksi: entry lama (seeded) maupun baru sama-sama pakai data attribute yang sama
    const transactionDetailModal = document.getElementById('transactionDetailModal');
    let activeTransactionId = '';
    const openTransactionDetail = entry => {
        const data = entry.dataset;
        activeTransactionId = `0xNTRX${Math.random().toString(16).slice(2, 10).toUpperCase()}`;
        document.getElementById('transactionTitle').innerText = data.title;
        document.getElementById('transactionNode').innerText = data.detail;
        document.getElementById('transactionFee').innerText = `${data.fee} NTRX`;
        document.getElementById('transactionTime').innerText = data.time;
        document.getElementById('transactionId').innerText = activeTransactionId;
        const status = document.getElementById('transactionStatus');
        status.innerText = data.status;
        status.classList.toggle('failed', data.status === 'Failed');
        transactionDetailModal?.classList.add('active');
    };
    activityList?.addEventListener('click', event => {
        const entry = event.target.closest('.activity-entry');
        if (entry) openTransactionDetail(entry);
    });
    document.getElementById('closeTransactionDetail')?.addEventListener('click', () => transactionDetailModal?.classList.remove('active'));
    document.getElementById('btnCopyTransaction')?.addEventListener('click', () => {
        navigator.clipboard?.writeText(activeTransactionId);
        showToast('bi-copy text-mint', window.nutrixText('toast-tx-copied'), activeTransactionId);
    });

    // Notification center
    const notificationWrapper = document.getElementById('notificationWrapper');
    const notificationList = document.getElementById('notificationList');
    const notificationBadge = document.getElementById('notificationBadge');
    const defaultNotifications = [
        { icon: 'bi-droplet', key: 'notification-moisture', time: '8 min ago' },
        { icon: 'bi-arrow-repeat', key: 'notification-telemetry', time: '25 min ago' },
        { icon: 'bi-cpu', key: 'notification-ai', time: '1 hr ago' }
    ];
    const notifications = JSON.parse(localStorage.getItem('nutrixNotifications') || JSON.stringify(defaultNotifications));
    const legacyNotificationKeys = {
        'Moisture is nearing the configured threshold.': 'notification-moisture',
        'Telemetry sync completed for Open Field A.': 'notification-telemetry',
        'AI recommendation is ready to review.': 'notification-ai'
    };
    const notificationText = item => item.key
        ? window.nutrixText(item.key, item.params || {})
        : window.nutrixText(legacyNotificationKeys[item.message] || item.message);

    function renderNotifications() {
        if (!notificationList) return;
        notificationList.innerHTML = notifications.length
            ? notifications.map(item => `<div class="notification-item"><div class="notification-item-icon"><i class="bi ${item.icon}"></i></div><div><p>${notificationText(item)}</p><small>${item.timeKey ? window.nutrixText(item.timeKey) : item.time}</small></div></div>`).join('')
            : `<p class="notification-empty">${window.nutrixText('notification-empty')}</p>`;
        if (notificationBadge) notificationBadge.innerText = notifications.length || '';
        localStorage.setItem('nutrixNotifications', JSON.stringify(notifications));
    }

    function pushNotification(message, icon = 'bi-check-circle', params = {}) {
        if (storedSettings.pushNotifications === false) return;
        notifications.unshift({ key: message, params, icon, timeKey: 'notification-just-now' });
        renderNotifications();
    }

    renderNotifications();
    document.getElementById('notificationBtn')?.addEventListener('click', () => notificationWrapper?.classList.toggle('open'));
    document.getElementById('btnReadNotifications')?.addEventListener('click', () => { notifications.length = 0; renderNotifications(); });
    document.addEventListener('click', event => {
        if (notificationWrapper && !notificationWrapper.contains(event.target)) notificationWrapper.classList.remove('open');
    });


    /* ========================================================
       4. MODAL & SIDEBAR WEB3 LOGIC (Connect Node - legacy, Sidebar)
       ======================================================== */
    const connectModal = document.getElementById('connectModal');
    const closeConnectModal = document.getElementById('closeConnectModal');

    const nodeSidebar = document.getElementById('nodeSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const closeSidebar = document.getElementById('closeSidebar');

    if (closeConnectModal) closeConnectModal.addEventListener('click', () => connectModal.classList.remove('active'));

    const hideSidebar = () => {
        if (sidebarOverlay) sidebarOverlay.classList.remove('active');
        if (nodeSidebar) nodeSidebar.classList.remove('active');
    };
    if (closeSidebar) closeSidebar.addEventListener('click', hideSidebar);
    if (sidebarOverlay) sidebarOverlay.addEventListener('click', hideSidebar);


    /* ========================================================
       5. SIGNATURE REQUEST & TOAST EXECUTION
       PERBAIKAN: selector sebelumnya (.btn-action-trigger:not(#id):not(#id))
       ikut menyeleksi tombol lain yang kebetulan berbagi class yang sama
       (mis. tombol "Review Action" di Swap modal & tombol di AI Connect
       modal), sehingga tombol itu ikut membuka Signature Request modal
       dengan data kosong. Sekarang hanya tombol yang benar-benar punya
       atribut data-cmd yang dipasangi listener ini.
       ======================================================== */
    const actionBtns = document.querySelectorAll('.btn-action-trigger[data-cmd]');
    const signModal = document.getElementById('signModal');
    const btnRejectSign = document.getElementById('btnRejectSign');
    const btnConfirmSign = document.getElementById('btnConfirmSign');

    actionBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const authBtnEl = document.getElementById('authBtn');
            const authModalEl = document.getElementById('authModal');
            if (authBtnEl && authBtnEl.style.display !== 'none') {
                if (authModalEl) authModalEl.classList.add('active');
                return;
            }

            const cmd = btn.getAttribute('data-cmd');
            const fee = btn.getAttribute('data-fee');
            document.getElementById('signActionName').innerText = `${cmd}()`;
            document.getElementById('signFee').innerText = `${fee} NTRX`;

            signModal.classList.add('active');
        });
    });

    if (btnRejectSign) {
        btnRejectSign.addEventListener('click', () => signModal.classList.remove('active'));
    }

    if (btnConfirmSign) {
        btnConfirmSign.addEventListener('click', () => {
            signModal.classList.remove('active');

            toastNotif.classList.remove('success');
            toastIconWrap.innerHTML = '<i class="bi bi-arrow-repeat spin text-white"></i>';
            toastTitleEl.innerText = window.nutrixText('toast-tx-pending');
            toastDescEl.innerText = window.nutrixText('toast-tx-pending-desc');
            toastNotif.classList.add('show');

            setTimeout(() => {
                toastNotif.classList.add('success');
                toastIconWrap.innerHTML = '<i class="bi bi-check-circle-fill text-mint"></i>';
                toastTitleEl.innerText = window.nutrixText('toast-tx-confirmed');
                toastDescEl.innerText = window.nutrixText('toast-tx-confirmed-desc');

                setTimeout(() => {
                    toastNotif.classList.remove('show');
                }, 4000);
            }, 3000);
        });
    }

    /* ========================================================
       6. FITUR 3: MULTI-FIELD NODE SWITCHER LOGIC
       ======================================================== */
    const nodeItems = document.querySelectorAll('.node-item');
    const activeNodeName = document.getElementById('activeNodeName');
    const activeNodeAddress = document.getElementById('activeNodeAddress');

    nodeItems.forEach(item => {
        item.addEventListener('click', function () {
            if (this.classList.contains('active')) return;

            nodeItems.forEach(n => {
                n.classList.remove('active');
                n.querySelector('.status-badge').style.display = 'none';
                n.querySelector('.check-icon').style.display = 'none';
                n.querySelector('.nav-icon').style.display = 'block';
            });

            this.classList.add('active');
            this.querySelector('.status-badge').style.display = 'inline-block';
            this.querySelector('.check-icon').style.display = 'block';
            this.querySelector('.nav-icon').style.display = 'none';

            const newName = this.getAttribute('data-name');
            const newAddress = this.getAttribute('data-address');
            const newEnv = this.getAttribute('data-env');

            if (activeNodeName) activeNodeName.innerText = newName;
            if (activeNodeAddress) activeNodeAddress.innerText = newAddress;

            window.currentEnvironment = newEnv;
            localStorage.setItem('nutrixEnvironment', newEnv);

            historyPh = [0, 0, 0, 0, 0, 0];
            historyHum = [0, 0, 0, 0, 0, 0];
            historyTemp = [0, 0, 0, 0, 0, 0];
            historyEc = [0, 0, 0, 0, 0, 0];

            if (toastNotif) {
                toastNotif.classList.remove('success');
                toastIconWrap.innerHTML = '<i class="bi bi-hdd-network spin text-white"></i>';
                toastTitleEl.innerText = window.nutrixText('toast-node-switching');
                toastDescEl.innerText = window.nutrixText('toast-node-connecting', { name: newName });
                toastNotif.classList.add('show');

                setTimeout(() => {
                    toastNotif.classList.add('success');
                    toastIconWrap.innerHTML = '<i class="bi bi-check-circle-fill text-mint"></i>';
                    toastTitleEl.innerText = window.nutrixText('toast-node-connected');
                    toastDescEl.innerText = window.nutrixText('toast-node-connected-desc');

                    setTimeout(() => toastNotif.classList.remove('show'), 3000);
                }, 1500);
            }
        });
    });


    /* ========================================================
       7. AUTH MODAL (Sign In / Sign Up)
       ======================================================== */
    const authBtn = document.getElementById('authBtn');
    const authModal = document.getElementById('authModal');
    const closeAuthModal = document.getElementById('closeAuthModal');
    const authTabs = document.querySelectorAll('.auth-tab');
    const authSwitches = document.querySelectorAll('.auth-switch');
    const accountAvatarWrapper = document.getElementById('accountAvatarWrapper');

    const readStoredUser = () => window.NUTRIX_USER || null;
    const maskProfileName = name => {
        if (!name) return 'Profil';
        return name.trim().split(/\s+/).map(part => part.length <= 2 ? `${part[0] || ''}*` : `${part.slice(0, 2)}***`).join(' ');
    };
    const maskProfileEmail = email => {
        if (!email || !email.includes('@')) return 'Email tersamarkan';
        const [localPart, domain] = email.split('@');
        return `${localPart.slice(0, 2)}***@${domain}`;
    };
    const setDashboardState = () => {
        const user = readStoredUser();
        document.body.dataset.dashboardState = user ? 'ready' : 'public';
        window.nutrixApplyDashboardState?.();
    };
    const applySessionUi = user => {
        const signedIn = Boolean(user);
        if (authBtn) authBtn.style.display = signedIn ? 'none' : 'block';
        if (accountAvatarWrapper) accountAvatarWrapper.style.display = signedIn ? 'block' : 'none';
        if (signedIn) {
            const panelUsername = document.getElementById('panelUsername');
            const panelEmail = document.getElementById('panelEmail');
            const profileName = document.querySelector('.user-profile-name');
            if (panelUsername) panelUsername.innerText = maskProfileName(user.name);
            if (panelEmail) panelEmail.innerText = maskProfileEmail(user.email);
            if (profileName) profileName.innerText = maskProfileName(user.name);
        }
        setDashboardState();
    };
    const startSession = user => applySessionUi(user);
    applySessionUi(readStoredUser());

    const btnOnboardingPrimary = document.getElementById('btnOnboardingPrimary');

    const openAuthModal = () => {
        if (authModal) authModal.classList.add('active');
    };

    if (authBtn) authBtn.addEventListener('click', openAuthModal);
    if (btnOnboardingPrimary) btnOnboardingPrimary.addEventListener('click', openAuthModal);

    if (closeAuthModal) {
        closeAuthModal.addEventListener('click', () => {
            authModal.classList.remove('active');
        });
    }

    let currentAuthAction = 'login'; // 'login' atau 'register'
    let currentAuthEmail = '';
    let otpCountdownInterval = null;
    const OTP_DURATION_SECONDS = 120; // 2 Menit

    const formSignIn = document.getElementById('form-signin');
    const formSignUp = document.getElementById('form-signup');
    const formOtp = document.getElementById('form-otp');
    const authTabGroup = document.getElementById('authTabGroup');
    const otpTimerDisplay = document.getElementById('otpTimerDisplay');
    const inputOtpCode = document.getElementById('inputOtpCode');
    const btnVerifyOtp = document.getElementById('btnVerifyOtp');
    const btnResendOtp = document.getElementById('btnResendOtp');
    const btnBackFromOtp = document.getElementById('btnBackFromOtp');
    const otpTargetEmail = document.getElementById('otpTargetEmail');
    const otpHeadingTitle = document.getElementById('otpHeadingTitle');

    function switchAuthTab(tabName) {
        currentAuthAction = tabName === 'signin' ? 'login' : 'register';
        authTabs.forEach(t => t.classList.remove('active'));
        document.querySelector(`.auth-tab[data-tab="${tabName}"]`)?.classList.add('active');
        if (formSignIn) formSignIn.style.display = tabName === 'signin' ? 'block' : 'none';
        if (formSignUp) formSignUp.style.display = tabName === 'signup' ? 'block' : 'none';
        if (formOtp) formOtp.style.display = 'none';
        if (authTabGroup) authTabGroup.style.display = 'flex';
        stopOtpTimer();
    }

    authTabs.forEach(tab => {
        tab.addEventListener('click', () => switchAuthTab(tab.dataset.tab));
    });
    authSwitches.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            switchAuthTab(link.dataset.tab);
        });
    });

    function showOtpStep(email, action, title) {
        currentAuthEmail = email;
        currentAuthAction = action;

        if (formSignIn) formSignIn.style.display = 'none';
        if (formSignUp) formSignUp.style.display = 'none';
        if (authTabGroup) authTabGroup.style.display = 'none';
        if (formOtp) formOtp.style.display = 'block';

        if (otpTargetEmail) otpTargetEmail.innerText = email;
        if (otpHeadingTitle) otpHeadingTitle.innerText = title || 'Verifikasi Email Anda';
        if (inputOtpCode) {
            inputOtpCode.value = '';
            setTimeout(() => inputOtpCode.focus(), 150);
        }

        startOtpTimer(OTP_DURATION_SECONDS);
    }

    function startOtpTimer(seconds) {
        stopOtpTimer();
        let remaining = seconds;
        updateTimerUi(remaining);

        if (btnResendOtp) {
            btnResendOtp.disabled = true;
            btnResendOtp.classList.add('text-secondary');
            btnResendOtp.classList.remove('text-mint');
        }

        otpCountdownInterval = setInterval(() => {
            remaining--;
            if (remaining <= 0) {
                stopOtpTimer();
                updateTimerUi(0);
                if (btnResendOtp) {
                    btnResendOtp.disabled = false;
                    btnResendOtp.classList.remove('text-secondary');
                    btnResendOtp.classList.add('text-mint');
                }
                showToast('bi-hourglass-bottom text-warning', window.nutrixText('auth-expired'), window.nutrixText('auth-resend'), false);
            } else {
                updateTimerUi(remaining);
            }
        }, 1000);
    }

    function stopOtpTimer() {
        if (otpCountdownInterval) {
            clearInterval(otpCountdownInterval);
            otpCountdownInterval = null;
        }
    }

    function updateTimerUi(seconds) {
        if (!otpTimerDisplay) return;
        const mins = Math.floor(seconds / 60).toString().padStart(2, '0');
        const secs = (seconds % 60).toString().padStart(2, '0');
        otpTimerDisplay.innerText = `${mins}:${secs}`;

        if (seconds <= 30) {
            otpTimerDisplay.className = 'badge bg-danger text-white fs-6 font-monospace';
        } else {
            otpTimerDisplay.className = 'badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 fs-6 font-monospace';
        }
    }

    if (btnBackFromOtp) {
        btnBackFromOtp.addEventListener('click', () => {
            switchAuthTab(currentAuthAction === 'register' ? 'signup' : 'signin');
        });
    }

    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // ================= STEP 1: MINTA OTP LOGIN =================
    const btnSignIn = document.getElementById('btnSignIn');
    if (btnSignIn) {
        btnSignIn.addEventListener('click', async () => {
            const email = document.getElementById('signinEmail')?.value.trim();
            const password = document.getElementById('signinPassword')?.value.trim();

            if (!email || !password) {
                showToast('bi-exclamation-circle text-warning', window.nutrixText('toast-input'), window.nutrixText('toast-signin-incomplete'), false);
                return;
            }

            btnSignIn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> ${window.nutrixText('auth-sending')}`;
            btnSignIn.disabled = true;

            try {
                const response = await fetch('/auth/login/request-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email,
                        password,
                        remember: document.getElementById('signinRemember')?.checked === true
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    const msg = data.errors ? Object.values(data.errors).flat().join('<br>') : (data.message || window.nutrixText('toast-login-failed'));
                    showToast('bi-x-circle text-danger', window.nutrixText('toast-auth-failed'), msg, false);
                    return;
                }

                showToast('bi-envelope-check text-mint', window.nutrixText('toast-code-sent'), data.message);
                showOtpStep(email, 'login', `${window.nutrixText('auth-email-verified')} ${window.nutrixText('ui-sign-in')}`);
            } catch (err) {
                showToast('bi-wifi-off text-danger', window.nutrixText('toast-network-failed'), window.nutrixText('toast-connection-failed'), false);
            } finally {
                btnSignIn.innerHTML = `<span data-i18n="auth-request-signin">${window.nutrixText('auth-request-signin')}</span>`;
                btnSignIn.disabled = false;
            }
        });
    }

    // ================= STEP 1: MINTA OTP REGISTER =================
    const btnSignUp = document.getElementById('btnSignUp');
    if (btnSignUp) {
        btnSignUp.addEventListener('click', async () => {
            const name = document.getElementById('signupName')?.value.trim();
            const email = document.getElementById('signupEmail')?.value.trim();
            const password = document.getElementById('signupPassword')?.value.trim();
            const passwordConfirmation = document.getElementById('signupPasswordConfirm')?.value;

            if (!name || !email || !password) {
                showToast('bi-exclamation-circle text-warning', window.nutrixText('toast-form-required'), window.nutrixText('toast-signup-incomplete'), false);
                return;
            }

            if (password.length < 8) {
                showToast('bi-shield-exclamation text-warning', window.nutrixText('toast-password-short'), window.nutrixText('toast-password-min'), false);
                return;
            }

            if (password !== passwordConfirmation) {
                showToast('bi-shield-exclamation text-warning', window.nutrixText('toast-password-mismatch'), window.nutrixText('toast-password-mismatch-desc'), false);
                return;
            }

            btnSignUp.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> ${window.nutrixText('auth-processing')}`;
            btnSignUp.disabled = true;

            try {
                const response = await fetch('/auth/register/request-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name, email, password, password_confirmation: passwordConfirmation })
                });

                const data = await response.json();

                if (!response.ok) {
                    const msg = data.errors ? Object.values(data.errors).flat().join('<br>') : (data.message || window.nutrixText('toast-signup-failed'));
                    showToast('bi-x-circle text-danger', window.nutrixText('toast-signup-rejected'), msg, false);
                    return;
                }

                showToast('bi-envelope-check text-mint', window.nutrixText('toast-code-sent'), data.message);
                showOtpStep(email, 'register', `${window.nutrixText('auth-email-verified')} ${window.nutrixText('ui-sign-up')}`);
            } catch (err) {
                showToast('bi-wifi-off text-danger', window.nutrixText('toast-network-failed'), window.nutrixText('toast-connection-failed-alt'), false);
            } finally {
                btnSignUp.innerHTML = `<span data-i18n="auth-request-signup">${window.nutrixText('auth-request-signup')}</span>`;
                btnSignUp.disabled = false;
            }
        });
    }

    // ================= STEP 2: VERIFIKASI KODE OTP 2 MENIT =================
    if (btnVerifyOtp) {
        btnVerifyOtp.addEventListener('click', async () => {
            const otp = inputOtpCode?.value.trim();

            if (!otp || otp.length !== 6) {
                showToast('bi-exclamation-circle text-warning', window.nutrixText('toast-invalid-otp'), window.nutrixText('toast-otp-instruction'), false);
                return;
            }

            btnVerifyOtp.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> ${window.nutrixText('auth-verifying')}`;
            btnVerifyOtp.disabled = true;

            const endpoint = currentAuthAction === 'register' 
                ? '/auth/register/verify-otp' 
                : '/auth/login/verify-otp';

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: currentAuthEmail,
                        otp: otp
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    if (data.status === 'expired') {
                        showToast('bi-clock-history text-danger', window.nutrixText('toast-code-expired'), data.message, false);
                        if (btnResendOtp) btnResendOtp.disabled = false;
                    } else {
                        showToast('bi-x-circle text-danger', window.nutrixText('toast-verify-failed'), data.message || window.nutrixText('toast-otp-mismatch'), false);
                    }
                    return;
                }

                stopOtpTimer();
                showToast('bi-check-circle-fill text-mint', 'Sukses!', data.message);

                if (data.user) {
                    startSession(data.user);
                }

                setTimeout(() => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.reload();
                    }
                }, 900);

            } catch (err) {
                showToast('bi-wifi-off text-danger', window.nutrixText('auth-verifying'), window.nutrixText('auth-invalid'), false);
            } finally {
                btnVerifyOtp.innerHTML = `<span data-i18n="auth-verify">${window.nutrixText('auth-verify')}</span>`;
                btnVerifyOtp.disabled = false;
            }
        });
    }

    // ================= KIRIM ULANG KODE OTP =================
    if (btnResendOtp) {
        btnResendOtp.addEventListener('click', async () => {
            if (btnResendOtp.disabled) return;

            btnResendOtp.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> ${window.nutrixText('auth-sending')}`;
            btnResendOtp.disabled = true;

            try {
                const response = await fetch('/auth/resend-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: currentAuthEmail,
                        action: currentAuthAction
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    showToast('bi-arrow-repeat text-mint', window.nutrixText('toast-code-resent'), data.message);
                    startOtpTimer(OTP_DURATION_SECONDS);
                } else {
                    showToast('bi-x-circle text-danger', window.nutrixText('toast-resend-failed'), data.message || window.nutrixText('toast-resend-retry'));
                    btnResendOtp.disabled = false;
                }
            } catch (err) {
                showToast('bi-wifi-off text-danger', window.nutrixText('toast-connection-lost'), window.nutrixText('toast-resend-failed-desc'));
                btnResendOtp.disabled = false;
            } finally {
                btnResendOtp.innerHTML = `<i class="bi bi-arrow-clockwise me-1"></i> <span data-i18n="auth-resend">${window.nutrixText('auth-resend')}</span>`;
            }
        });
    }


    /* ========================================================
       8. ACCOUNT PANEL (Expandable)
       ======================================================== */
    const accountAvatarBtn = document.getElementById('accountAvatarBtn');
    const accountPanel = document.getElementById('accountPanel');

    if (accountAvatarBtn && accountPanel) {
        accountAvatarBtn.addEventListener('click', () => {
            accountPanel.classList.toggle('open');
        });

        document.addEventListener('click', (e) => {
            if (!accountAvatarWrapper.contains(e.target)) {
                accountPanel.classList.remove('open');
            }
        });
    }

    const accountOptions = document.querySelectorAll('.account-option');
    accountOptions.forEach(opt => {
        opt.addEventListener('click', function () {
            accountOptions.forEach(o => o.classList.remove('active'));
            this.classList.add('active');

            const name = this.dataset.name;
            const addr = this.dataset.addr;
            const panelUsername = document.getElementById('panelUsername');
            const panelAddress = document.getElementById('panelAddress');

            if (panelUsername) panelUsername.innerText = maskProfileName(name);
            if (panelAddress) panelAddress.innerText = addr;

            showToast('bi-arrow-repeat', window.nutrixText('toast-account-switched'), window.nutrixText('toast-account-switched-desc', { name }));
        });
    });

    const btnLogout = document.getElementById('btnLogout');
    if (btnLogout) {
        btnLogout.addEventListener('click', async () => {
            accountPanel.classList.remove('open');
            localStorage.removeItem('nutrixSession');
            try {
                await fetch('/logout', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json'
                    }
                });
            } catch (e) {}
            applySessionUi(null);
            showToast('bi-box-arrow-right', window.nutrixText('toast-logged-out'), window.nutrixText('toast-logged-out-desc'), false);
            setTimeout(() => {
                window.location.href = '/';
            }, 600);
        });
    }


    /* ========================================================
       9. NETWORK SELECTOR (Environment Switcher)
       PERBAIKAN: sebelumnya ada 2 listener terpisah nempel di
       elemen yang sama (.network-option) - satu hanya update label,
       satu lagi simpan ke localStorage + efek loading tapi lupa
       update window.currentEnvironment & label. Sekarang digabung
       jadi satu handler yang lengkap dan konsisten.
       ======================================================== */
    const networkSelector = document.getElementById('networkSelector');
    const networkSelectorBtn = document.getElementById('networkSelectorBtn');
    const networkOptions = document.querySelectorAll('.network-option');

    if (networkSelectorBtn) {
        networkSelectorBtn.addEventListener('click', () => {
            networkSelector.classList.toggle('open');
        });

        document.addEventListener('click', (e) => {
            if (!networkSelector.contains(e.target)) {
                networkSelector.classList.remove('open');
            }
        });
    }

    networkOptions.forEach(opt => {
        opt.addEventListener('click', function () {
            networkOptions.forEach(o => o.classList.remove('active'));
            this.classList.add('active');

            const envKey = this.dataset.env;

            const envName = window.nutrixText(`network-${envKey === 'corn' ? 'open-field' : envKey === 'greenhouse' ? 'greenhouse' : 'rice'}`);
            window.currentEnvironment = envKey;
            localStorage.setItem('nutrixEnvironment', envKey);
            networkSelector.classList.remove('open');
            window.applyLanguage?.(window.currentLanguage || 'id');

            // Efek skeleton loading sesaat sebelum data telemetri baru "muncul"
            const cards = document.querySelectorAll('.metric-card');
            cards.forEach(card => card.classList.add('telemetry-loading'));
            setTimeout(() => cards.forEach(card => card.classList.remove('telemetry-loading')), 1200);

            showToast('bi-hdd-network', 'network-env-changed', window.nutrixText('network-switched', { environment: envName }));
        });
    });

    // Kembalikan environment tersimpan setelah reload halaman
    const savedEnvironment = localStorage.getItem('nutrixEnvironment');
    if (savedEnvironment) {
        const savedOption = document.querySelector(`.network-option[data-env="${savedEnvironment}"]`);
        if (savedOption) {
            document.querySelectorAll('.network-option').forEach(option => option.classList.toggle('active', option === savedOption));
            window.applyLanguage?.(window.currentLanguage || 'id');
        }
    }


    /* ========================================================
       10. CURSOR-TRACKING GLOW & MAGNETIC BUTTONS
       ======================================================== */
    const glowCards = document.querySelectorAll('.metric-card, .sticky-card, .bento-card');
    glowCards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            card.style.setProperty('--mouse-x', `${x}px`);
            card.style.setProperty('--mouse-y', `${y}px`);

            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const tiltX = ((y - centerY) / centerY) * -5;
            const tiltY = ((x - centerX) / centerX) * 5;

            card.style.setProperty('--tilt-x', `${tiltX}deg`);
            card.style.setProperty('--tilt-y', `${tiltY}deg`);
        });

        card.addEventListener('mouseleave', () => {
            card.style.setProperty('--tilt-x', `0deg`);
            card.style.setProperty('--tilt-y', `0deg`);
        });
    });

    const magneticBtns = document.querySelectorAll('.btn-action-trigger, .btn-connect-node');
    magneticBtns.forEach(btn => {
        btn.addEventListener('mousemove', (e) => {
            const rect = btn.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const moveX = ((x - centerX) / centerX) * 8;
            const moveY = ((y - centerY) / centerY) * 8;

            btn.style.transform = `translate(${moveX}px, ${moveY}px) scale(1.05)`;
        });

        btn.addEventListener('mouseleave', () => {
            btn.style.transform = `translate(0px, 0px) scale(1)`;
        });
    });


    /* ========================================================
       11. SETTINGS MODAL
       ======================================================== */
    const btnSettings = document.getElementById('btnSettings');
    const settingsModal = document.getElementById('settingsModal');
    const closeSettingsModal = document.getElementById('closeSettingsModal');
    const btnSaveSettings = document.getElementById('btnSaveSettings');
    const pushNotificationsSetting = document.getElementById('settingPushNotifications');
    const autoSyncSetting = document.getElementById('settingAutoSync');
    const profilePhotoInput = document.getElementById('profilePhotoInput');
    const profilePhotoPreview = document.getElementById('profilePhotoPreview');
    let profilePhotoPreviewUrl = null;
    const storedSettings = JSON.parse(localStorage.getItem('nutrixSettings') || '{}');

    profilePhotoInput?.addEventListener('change', () => {
        const file = profilePhotoInput.files?.[0];
        if (!file || !profilePhotoPreview) return;

        if (profilePhotoPreviewUrl) URL.revokeObjectURL(profilePhotoPreviewUrl);
        profilePhotoPreviewUrl = URL.createObjectURL(file);
        let previewImage = profilePhotoPreview.querySelector('img');
        if (!previewImage) {
            previewImage = document.createElement('img');
            previewImage.className = 'avatar-profile-photo';
            previewImage.alt = '';
            profilePhotoPreview.replaceChildren(previewImage);
        }
        previewImage.src = profilePhotoPreviewUrl;
    });

    const syncSettingsControls = () => {
        if (pushNotificationsSetting) pushNotificationsSetting.checked = storedSettings.pushNotifications !== false;
        if (autoSyncSetting) autoSyncSetting.checked = storedSettings.autoSync !== false;
    };
    syncSettingsControls();

    if (btnSettings && settingsModal) {
        btnSettings.addEventListener('click', () => {
            document.getElementById('accountPanel').classList.remove('open');
            syncSettingsControls();
            settingsModal.classList.add('active');
        });
    }

    if (closeSettingsModal) {
        closeSettingsModal.addEventListener('click', () => {
            settingsModal.classList.remove('active');
        });
    }

    if (btnSaveSettings) {
        btnSaveSettings.addEventListener('click', () => {
            storedSettings.pushNotifications = pushNotificationsSetting?.checked !== false;
            storedSettings.autoSync = autoSyncSetting?.checked !== false;
            localStorage.setItem('nutrixSettings', JSON.stringify(storedSettings));
            btnSaveSettings.innerHTML = '<span class="spinner-border spinner-border-sm"></span> ' + window.nutrixText('popup-save-changes');
            setTimeout(() => {
                settingsModal.classList.remove('active');
                btnSaveSettings.innerHTML = window.nutrixText('popup-save-changes');
                showToast('bi-check-circle-fill text-mint', 'notification-settings-saved', '');
            }, 800);
        });
    }


    /* ========================================================
       12. METRIC INFO POPUPS
       ======================================================== */
    const metricCards = document.querySelectorAll('.interactive-metric');
    const metricInfoModal = document.getElementById('metricInfoModal');
    const closeMetricModal = document.getElementById('closeMetricModal');

    const metricData = {
        ph: {
            labelKey: 'sensor-ph',
            icon: 'bi-droplet-half',
            defKey: 'metric-def-ph',
            effectKey: 'metric-effect-ph'
        },
        moisture: {
            labelKey: 'sensor-moisture',
            icon: 'bi-moisture',
            defKey: 'metric-def-moisture',
            effectKey: 'metric-effect-moisture'
        },
        temp: {
            labelKey: 'sensor-temperature',
            icon: 'bi-thermometer-half',
            defKey: 'metric-def-temp',
            effectKey: 'metric-effect-temp'
        },
        ec: {
            labelKey: 'sensor-conductivity',
            icon: 'bi-lightning-charge-fill',
            defKey: 'metric-def-ec',
            effectKey: 'metric-effect-ec'
        }
    };

    function renderMetricModal(metricKey) {
        const metric = metricData[metricKey];
        if (!metric || !metricInfoModal) return;
        document.getElementById('metricModalTitle').innerHTML = `<i class="bi ${metric.icon} text-mint me-2"></i> ${window.nutrixText(metric.labelKey)}`;
        document.getElementById('metricModalDef').innerText = window.nutrixText(metric.defKey);
        document.getElementById('metricModalEffect').innerText = window.nutrixText(metric.effectKey);
        metricInfoModal.dataset.metric = metricKey;
    }

    metricCards.forEach(card => {
        card.addEventListener('click', () => {
            const m = metricData[card.dataset.metric];
            if (m && metricInfoModal) {
                renderMetricModal(card.dataset.metric);
                metricInfoModal.classList.add('active');
            }
        });
    });

    if (closeMetricModal) {
        closeMetricModal.addEventListener('click', () => metricInfoModal.classList.remove('active'));
    }
    document.addEventListener('nutrix:languagechange', () => renderMetricModal(metricInfoModal?.dataset.metric));


    /* ========================================================
       14. REMOTE ACTION (SWAP) MODAL, SYNC, EXPORT,
           AI DAPP CONNECT & NODE WALLET MODAL
       ======================================================== */
    const swapModal = document.getElementById('swapActionModal');
    const swapForm = document.getElementById('swapActionForm');
    const swapProcessing = document.getElementById('swapProcessing');

    document.getElementById('btnSwapAction')?.addEventListener('click', () => swapModal?.classList.add('active'));
    document.getElementById('closeSwapActionModal')?.addEventListener('click', () => swapModal?.classList.remove('active'));

    // Custom dropdown (Action & Duration) di dalam Swap modal
    const setupCustomDropdown = (wrapperId, triggerTextId, triggerIconId, hiddenInputId, isAction) => {
        const wrapper = document.getElementById(wrapperId);
        if (!wrapper) return;

        const trigger = wrapper.querySelector('.custom-select-trigger');
        const options = wrapper.querySelectorAll('.custom-option');
        const hiddenInput = document.getElementById(hiddenInputId);
        const triggerText = document.getElementById(triggerTextId);
        const triggerIcon = triggerIconId ? document.getElementById(triggerIconId) : null;

        trigger.addEventListener('click', (e) => {
            document.querySelectorAll('.custom-select-wrapper').forEach(w => {
                if (w !== wrapper) w.classList.remove('open');
            });
            wrapper.classList.toggle('open');
            e.stopPropagation();
        });

        options.forEach(opt => {
            opt.addEventListener('click', () => {
                options.forEach(o => o.classList.remove('selected'));
                opt.classList.add('selected');

                hiddenInput.value = opt.dataset.value;
                triggerText.innerText = opt.innerText.trim();

                if (isAction) {
                    if (triggerIcon) triggerIcon.className = `bi ${opt.dataset.icon} text-mint`;
                    const fee = opt.dataset.fee;
                    document.getElementById('swapActionFee').value = fee;
                    document.getElementById('swapFee').innerText = `${fee} NTRX`;
                }
                wrapper.classList.remove('open');
            });
        });
    };

    setupCustomDropdown('actionSelectWrapper', 'triggerActionText', 'triggerActionIcon', 'swapActionType', true);
    setupCustomDropdown('durationSelectWrapper', 'triggerDurationText', null, 'swapDuration', false);
    setupCustomDropdown('tamanTypeSelectWrapper', 'triggerTamanTypeText', 'triggerTamanTypeIcon', 'tamanTypeInput', false);

    document.addEventListener('click', () => {
        document.querySelectorAll('.custom-select-wrapper').forEach(w => w.classList.remove('open'));
    });

    document.getElementById('btnReviewSwapAction')?.addEventListener('click', () => {
        const action = document.getElementById('swapActionType').value;
        const duration = document.getElementById('swapDuration').value;
        const fee = document.getElementById('swapActionFee').value;

        swapForm.hidden = true;
        swapProcessing.hidden = false;

        setTimeout(() => {
            swapProcessing.hidden = true;
            swapForm.hidden = false;
            swapModal.classList.remove('active');

            addActivity(action, window.nutrixText('activity-duration', { duration }), fee);
            pushNotification(window.nutrixText('activity-action-scheduled', { action, duration }), 'bi-shuffle');
            showToast('bi-check-circle-fill text-mint', window.nutrixText('activity-action-confirmed'), window.nutrixText('activity-action-scheduled', { action, duration }));
        }, 1700);
    });

    document.getElementById('btnSyncData')?.addEventListener('click', () => {
        showToast('bi-arrow-repeat spin text-white', window.nutrixText('toast-sync-title'), window.nutrixText('toast-sync-desc'), false);
        setTimeout(() => {
            addActivity(window.nutrixText('activity-synced'), window.nutrixText('activity-synced-source'));
            pushNotification('notification-telemetry', 'bi-arrow-repeat');
            showToast('bi-check-circle-fill text-mint', window.nutrixText('toast-synced'), window.nutrixText('toast-synced-desc'));
        }, 1100);
    });

    document.getElementById('btnExportData')?.addEventListener('click', () => {
        const rows = [
            ['Metric', 'Value'],
            ['pH', document.getElementById('val-ph')?.innerText || '—'],
            ['Moisture', document.getElementById('val-hum')?.innerText || '—'],
            ['Temperature', document.getElementById('val-temp')?.innerText || '—'],
            ['EC', document.getElementById('val-ec')?.innerText || '—']
        ];
        const blob = new Blob([rows.map(row => row.join(',')).join('\n')], { type: 'text/csv' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'nutrix-telemetry.csv';
        link.click();
        URL.revokeObjectURL(link.href);

        addActivity(window.nutrixText('activity-exported'), window.nutrixText('activity-exported-format'));
        showToast('bi-download text-mint', window.nutrixText('toast-export-ready'), window.nutrixText('toast-export-desc'));
    });

    const aiConnectModal = document.getElementById('aiConnectModal');
    const openAiConnect = () => aiConnectModal?.classList.add('active');
    document.getElementById('aiRecommendation')?.addEventListener('click', openAiConnect);
    document.getElementById('aiRecommendation')?.addEventListener('keydown', event => {
        if (event.key === 'Enter' || event.key === ' ') openAiConnect();
    });
    document.getElementById('btnRejectAiConnect')?.addEventListener('click', () => aiConnectModal?.classList.remove('active'));
    document.getElementById('btnConfirmAiConnect')?.addEventListener('click', () => {
        aiConnectModal?.classList.remove('active');
        addActivity(window.nutrixText('activity-ai-connected'), window.nutrixText('activity-ai-permission'));
        pushNotification('notification-ai', 'bi-robot');
        showToast('bi-robot text-mint', window.nutrixText('toast-ai-connected'), window.nutrixText('toast-ai-connected-desc'));
    });

    /* ========================================================
       15. TUTUP SEMUA MODAL SAAT KLIK DI LUAR AREA (GENERIK)
       Sebelumnya ini ditulis manual per-modal di beberapa tempat
       berbeda dengan daftar yang tidak lengkap/konsisten. Karena
       semua modal berbagi class ".modal-overlay", satu listener
       generik ini sudah mencakup semuanya.
       ======================================================== */
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) overlay.classList.remove('active');
        });
    });

    /* ========================================================
       16. TEAM FLIP CARDS
       ======================================================== */
    document.querySelectorAll('.flip-trigger').forEach(btn => {
        btn.addEventListener('click', () => {
            const inner = document.getElementById(btn.dataset.flipTarget);
            const card = inner?.closest('.flip-card');
            if (card) card.classList.toggle('flipped');
        });
    });


    /* ========================================================
       16. TEAM FLIP CARDS
       ======================================================== */
    document.querySelectorAll('.flip-trigger').forEach(btn => {
        btn.addEventListener('click', () => {
            const inner = document.getElementById(btn.dataset.flipTarget);
            const card = inner?.closest('.flip-card');
            if (card) card.classList.toggle('flipped');
        });
    });

    const backToTopBtn = document.getElementById('backToTop');
    if (backToTopBtn) {
        const toggleBackToTop = () => {
            backToTopBtn.classList.toggle('visible', window.scrollY > 500);
        };
        window.addEventListener('scroll', toggleBackToTop, { passive: true });
        toggleBackToTop();
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ========================================================
       17. SCROLL PROGRESS BAR
       ======================================================== */
    const scrollProgressBar = document.getElementById('scrollProgress');
    if (scrollProgressBar) {
        const updateScrollProgress = () => {
            const scrollTop = window.scrollY;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const pct = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
            scrollProgressBar.style.width = `${Math.min(100, Math.max(0, pct))}%`;
        };

        window.addEventListener('scroll', updateScrollProgress, { passive: true });
        window.addEventListener('resize', updateScrollProgress);
        updateScrollProgress();
    }

});

/* ========================================================
   GLOBAL FUNCTION: Simulate Node Connection
   (Dipanggil via inline onclick="" di HTML, harus tetap global)
   ======================================================== */
window.simulateConnection = function (element) {
    const spinner = element.querySelector('.spinner-border');
    if (spinner) spinner.style.display = 'block';

    setTimeout(() => {
        if (spinner) spinner.style.display = 'none';

        const connectModal = document.getElementById('connectModal');
        if (connectModal) connectModal.classList.remove('active');

        const connectBtn = document.getElementById('connectBtn');
        if (connectBtn) {
            connectBtn.innerHTML = '<span class="live-dot d-inline-block me-2"></span> 0x8F...e4C';
            connectBtn.classList.add('connected');
        }
    }, 1500);
};
