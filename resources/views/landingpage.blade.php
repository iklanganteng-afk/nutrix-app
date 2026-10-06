<!DOCTYPE html>
<html lang="id" data-theme="emerald">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NUTRIX - Smart Agriculture Web3 Dashboard</title>
    
    <!-- Tipografi Premium -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;900&family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <!-- FIXED: Bootstrap 5.3.3 (Stable Version) & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Web3 Styles -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        const validThemes = ['emerald', 'harvest', 'nordic', 'obsidian', 'midnight', 'hydro'];
        const savedTheme = localStorage.getItem('nutrix_theme');
        const initialTheme = validThemes.includes(savedTheme) ? savedTheme : 'emerald';
        document.documentElement.setAttribute('data-theme', initialTheme);
        document.documentElement.setAttribute('data-bs-theme', ['emerald', 'harvest', 'nordic'].includes(initialTheme) ? 'light' : 'dark');
        const initialLanguage = localStorage.getItem('nutrix_language') || 'id';
        document.documentElement.setAttribute('lang', initialLanguage);
        document.documentElement.setAttribute('dir', initialLanguage === 'ar' ? 'rtl' : 'ltr');
    </script>
</head>
<body class="landing-page">
 
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg fixed-top web3-navbar">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <span class="brand-icon">ðŸŒ±</span> NUTRIX
            </a>
            <div class="d-none d-lg-flex flex-grow-1 justify-content-center">
                <ul class="navbar-nav gap-4">
                    <li class="nav-item"><a class="nav-link" href="#dashboard" data-i18n="nav-dashboard">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="#architecture" data-i18n="nav-architecture-short">Architecture</a></li>
                    <li class="nav-item"><a class="nav-link" href="#team" data-i18n="nav-developers-short">Developers</a></li>
                </ul>
            </div>
            <div class="nav-actions d-flex align-items-center gap-3">
                <!-- Network Selector -->
                <div class="network-selector" id="networkSelector">
                    <div class="network-selector-btn" id="networkSelectorBtn">
                        <span class="net-dot"></span>
                        <span class="net-name" data-i18n="network-open-field">Open Field A</span>
                        <i class="bi bi-chevron-down net-chevron"></i>
                    </div>
                    <div class="network-dropdown" id="networkDropdown">
                        <div class="network-dropdown-title" data-i18n="select-environment">Select Environment</div>
                        <div class="network-option active" data-env="corn" data-name="Open Field A">
                            <span class="net-dot"></span> <span data-i18n="network-open-field">Open Field A</span>
                            <i class="bi bi-check-lg ms-auto"></i>
                        </div>
                        <div class="network-option" data-env="greenhouse" data-name="Greenhouse B">
                            <span class="net-dot dot-amber"></span> <span data-i18n="network-greenhouse">Greenhouse B</span>
                            <i class="bi bi-check-lg ms-auto"></i>
                        </div>
                        <div class="network-option" data-env="rice" data-name="Rice Paddy C">
                            <span class="net-dot dot-blue"></span> <span data-i18n="network-rice">Rice Paddy C</span>
                            <i class="bi bi-check-lg ms-auto"></i>
                        </div>
                    </div>
                </div>
 
                <!-- Notification Center -->
                <div class="notification-wrapper" id="notificationWrapper">
                    <button class="notification-btn" id="notificationBtn" aria-label="Open notifications">
                        <i class="bi bi-bell"></i><span class="notification-badge" id="notificationBadge">3</span>
                    </button>
                    <div class="notification-panel" id="notificationPanel">
                        <div class="notification-panel-header"><strong data-i18n="ui-notifications">Notifications</strong><button id="btnReadNotifications" data-i18n="ui-mark-read">Mark all read</button></div>
                        <div id="notificationList"></div>
                    </div>
                </div>

                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle theme-menu-button" type="button" data-bs-toggle="dropdown" aria-label="Choose theme">
                        <i class="bi bi-palette"></i> <span id="currentThemeLabel">Theme</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end theme-menu">
                        <li class="dropdown-header" data-i18n="ui-theme-light">Light Mode</li>
                        <li><button class="dropdown-item theme-btn" data-theme-value="emerald">Emerald Field</button></li>
                        <li><button class="dropdown-item theme-btn" data-theme-value="harvest">Golden Harvest</button></li>
                        <li><button class="dropdown-item theme-btn" data-theme-value="nordic">Nordic Clean</button></li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="dropdown-header" data-i18n="ui-theme-dark">Dark Mode</li>
                        <li><button class="dropdown-item theme-btn" data-theme-value="obsidian">Obsidian Matrix</button></li>
                        <li><button class="dropdown-item theme-btn" data-theme-value="midnight">Midnight Soil</button></li>
                        <li><button class="dropdown-item theme-btn" data-theme-value="hydro">Deep Hydro</button></li>
                    </ul>
                </div>

                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle theme-menu-button" type="button" data-bs-toggle="dropdown" aria-label="Choose language">
                        <i class="bi bi-translate"></i> <span id="currentLanguageLabel">ID</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end theme-menu">
                        <li><button class="dropdown-item language-btn" data-lang="en-GB">English (UK)</button></li>
                        <li><button class="dropdown-item language-btn" data-lang="en-US">English (US)</button></li>
                        <li><button class="dropdown-item language-btn" data-lang="en-CA">English (CA)</button></li>
                        <li><button class="dropdown-item language-btn" data-lang="id">Bahasa Indonesia</button></li>
                        <li><button class="dropdown-item language-btn" data-lang="jv">Basa Jawa</button></li>
                        <li><button class="dropdown-item language-btn" data-lang="ja">&#26085;&#26412;&#35486;</button></li>
                        <li><button class="dropdown-item language-btn" data-lang="ar">&#1575;&#1604;&#1593;&#1585;&#1576;&#1610;&#1577;</button></li>
                        <li><button class="dropdown-item language-btn" data-lang="ms">Bahasa Melayu</button></li>
                    </ul>
                </div>
 
                <!-- Auth Button (unauthenticated state) -->
                <button class="btn btn-connect-node" id="authBtn" data-i18n="ui-sign-in">Sign In</button>
 
                <!-- Account Avatar (authenticated state, hidden by default) -->
                <div class="account-avatar-wrapper" id="accountAvatarWrapper" style="display: none;">
                    <div class="account-avatar" id="accountAvatarBtn">
                        <div class="avatar-identicon" id="avatarIdenticon"></div>
                    </div>
                    <!-- Expandable Account Panel -->
                    <div class="account-panel" id="accountPanel">
                        <div class="account-panel-header">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-identicon-lg" id="panelIdenticon"></div>
                                <div>
                                    <strong class="d-block text-white" id="panelUsername">Admin NUTRIX</strong>
                                    <small class="text-muted" id="panelEmail">admin@nutrix.io</small>
                                </div>
                            </div>
                        </div>
                        <div class="account-panel-body">
                            <div class="account-panel-label" data-i18n="switch-account">SWITCH ACCOUNT</div>
                            <div class="account-option active" data-user="admin" data-name="Admin NUTRIX" data-addr="0x8F...e4C">
                                <div class="avatar-identicon-sm"></div>
                                <div class="flex-grow-1">
                                    <strong>Admin NUTRIX</strong>
                                    <small class="d-block text-muted">0x8F...e4C</small>
                                </div>
                                <i class="bi bi-check-circle-fill text-mint"></i>
                            </div>
                            <div class="account-option" data-user="farmer1" data-name="Petani 01" data-addr="0xA3...b9D">
                                <div class="avatar-identicon-sm"></div>
                                <div class="flex-grow-1">
                                    <strong>Petani 01</strong>
                                    <small class="d-block text-muted">0xA3...b9D</small>
                                </div>
                                <i class="bi bi-check-circle-fill text-mint"></i>
                            </div>
                        </div>
                        <div class="account-panel-footer">
                            <button class="account-panel-action" id="btnSettings"><i class="bi bi-gear"></i> <span data-i18n="ui-settings">Settings</span></button>
                            <button class="account-panel-action text-danger" id="btnLogout"><i class="bi bi-box-arrow-right"></i> <span data-i18n="ui-log-out">Log Out</span></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
 
    <main class="main-container">
        <!-- =====================================================
             WORKSPACE HERO: Welcome state (shown before auth)
             ===================================================== -->
        <section class="hero-wrapper" id="hero" style="min-height: 85vh; display:flex; align-items:center; justify-content:center; text-align:center; padding: 7rem 1.5rem 3rem;">
            <div style="max-width: 700px; margin: 0 auto;">
                <!-- Hero plant SVG -->
                <svg class="math-plant-svg" viewBox="0 0 400 500" xmlns="http://www.w3.org/2000/svg" width="180" height="220" style="margin-bottom: 1.5rem; display: block; margin-left: auto; margin-right: auto;">
                    <defs>
                        <linearGradient id="leafGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:var(--color-accent-highlight);stop-opacity:0.9" />
                            <stop offset="100%" style="stop-color:var(--color-accent-2);stop-opacity:0.7" />
                        </linearGradient>
                        <linearGradient id="leafGrad2" x1="100%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" style="stop-color:var(--color-accent-highlight);stop-opacity:0.8" />
                            <stop offset="100%" style="stop-color:var(--color-accent-1);stop-opacity:0.6" />
                        </linearGradient>
                        <linearGradient id="stemGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" style="stop-color:var(--color-accent-highlight);stop-opacity:0.8" />
                            <stop offset="100%" style="stop-color:var(--color-accent-1);stop-opacity:0.9" />
                        </linearGradient>
                        <filter id="glowFilter">
                            <feGaussianBlur stdDeviation="4" result="coloredBlur"/>
                            <feMerge><feMergeNode in="coloredBlur"/><feMergeNode in="SourceGraphic"/></feMerge>
                        </filter>
                    </defs>
                    <circle cx="200" cy="230" r="140" fill="var(--glow-ambient)"/>
                    <path d="M200,450 C200,400 195,350 200,280" stroke="url(#stemGrad)" stroke-width="6" fill="none" stroke-linecap="round" filter="url(#glowFilter)"/>
                    <path d="M200,280 C180,250 120,230 80,180 C60,155 70,120 100,100 C130,80 170,100 190,140 C195,150 198,170 200,200 Z" fill="url(#leafGrad1)" opacity="0.85" filter="url(#glowFilter)">
                        <animate attributeName="opacity" values="0.85;0.95;0.85" dur="4s" repeatCount="indefinite"/>
                    </path>
                    <path d="M200,280 C220,250 280,230 320,180 C340,155 330,120 300,100 C270,80 230,100 210,140 C205,150 202,170 200,200 Z" fill="url(#leafGrad2)" opacity="0.85" filter="url(#glowFilter)">
                        <animate attributeName="opacity" values="0.85;0.95;0.85" dur="4s" begin="1s" repeatCount="indefinite"/>
                    </path>
                    <circle cx="100" cy="100" r="3" fill="var(--color-accent-highlight)" opacity="0.6" filter="url(#glowFilter)">
                        <animate attributeName="cy" values="100;90;100" dur="3s" repeatCount="indefinite"/>
                    </circle>
                    <circle cx="300" cy="100" r="3" fill="var(--color-accent-highlight)" opacity="0.6" filter="url(#glowFilter)">
                        <animate attributeName="cy" values="100;90;100" dur="3s" begin="1s" repeatCount="indefinite"/>
                    </circle>
                </svg>

                <!-- Onboarding state (shown when not authenticated) -->
                <div id="dashboardOnboarding">
                    <span class="badge-web3 mb-3" id="onboardingBadge" data-i18n="landing-secure-access">Secure Farm Access</span>
                    <h1 id="onboardingTitle" style="font-family: 'Cinzel', serif; font-size: clamp(1.8rem, 4vw, 3rem); font-weight: 900; margin-bottom: 1rem; color: var(--text-title); letter-spacing: -1px;" data-i18n="landing-workspace">
                        NUTRIX Workspace
                    </h1>
                    <p id="onboardingDescription" style="color: var(--text-muted); font-size: 1.1rem; max-width: 500px; margin: 0 auto 2rem; line-height: 1.7;" data-i18n="landing-signin-access">
                        Sign in untuk mengakses sensor telemetri real-time, analisis NPK, dan kendali jarak jauh perangkat IoT kamu.
                    </p>
                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <button class="btn btn-connect-node" id="btnOnboardingPrimary">
                            <i class="bi bi-box-arrow-in-right me-2"></i><span data-i18n="landing-signin-workspace">Sign In ke Workspace</span>
                        </button>
                        <a href="{{ url('/') }}" class="btn" style="border: 1px solid var(--border-subtle); color: var(--text-body); padding: 12px 24px; border-radius: 50px; font-weight: 600; text-decoration: none; transition: border-color 0.2s, color 0.2s;">
                            <i class="bi bi-arrow-left me-2"></i><span data-i18n="landing-back-home">Kembali ke Beranda</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- =====================================================
             DASHBOARD: Sensor Telemetry (shown after auth)
             ===================================================== -->
        <section class="dashboard-section container" id="dashboard" style="padding-bottom: 4rem;">
            <div class="d-flex justify-content-between align-items-end mb-4 position-relative z-2 border-bottom border-secondary pb-3">
                <div>
                    <span class="badge-web3 mb-2" data-i18n="landing-live-network">Live Network</span>
                    <h2 class="section-title mb-0" style="font-family: 'Cinzel', serif;" data-i18n="landing-sensor-telemetry">Sensor Telemetry</h2>
                </div>
                <div class="live-indicator">
                    <div class="live-dot"></div>
                    <span data-i18n="landing-syncing">Syncing...</span>
                </div>
            </div>

            <!-- Metric Cards: 4 Sensor Values -->
            <div class="row g-4 position-relative z-2 telemetry-content">
                <!-- pH Card -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="metric-card interactive-metric" data-metric="ph">
                        <div class="metric-header d-flex justify-content-between">
                            <span><i class="bi bi-droplet-half"></i> <span data-i18n="metric-ph-level">pH Level</span></span>
                            <span class="trend-icon text-muted"><i class="bi bi-activity"></i></span>
                        </div>
                        <div class="metric-value"><span id="val-ph">0.0</span> <small>pH</small></div>
                        <div class="metric-sparkline" id="spark-ph">
                            <div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div>
                        </div>
                    </div>
                </div>
                <!-- Moisture Card -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="metric-card interactive-metric" data-metric="moisture">
                        <div class="metric-header d-flex justify-content-between">
                            <span><i class="bi bi-moisture"></i> <span data-i18n="metric-moisture">Moisture</span></span>
                            <span class="trend-icon text-muted"><i class="bi bi-activity"></i></span>
                        </div>
                        <div class="metric-value"><span id="val-hum">0</span> <small>%</small></div>
                        <div class="metric-sparkline" id="spark-hum">
                            <div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div>
                        </div>
                    </div>
                </div>
                <!-- Temperature Card -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="metric-card interactive-metric" data-metric="temp">
                        <div class="metric-header d-flex justify-content-between">
                            <span><i class="bi bi-thermometer-half"></i> <span data-i18n="metric-temperature">Temperature</span></span>
                            <span class="trend-icon text-muted"><i class="bi bi-activity"></i></span>
                        </div>
                        <div class="metric-value"><span id="val-temp">0.0</span> <small>Â°C</small></div>
                        <div class="metric-sparkline" id="spark-temp">
                            <div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div>
                        </div>
                    </div>
                </div>
                <!-- EC Card -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="metric-card interactive-metric" data-metric="ec">
                        <div class="metric-header d-flex justify-content-between">
                            <span class="text-mint"><i class="bi bi-lightning-charge-fill"></i> <span data-i18n="metric-conductivity">Conductivity</span></span>
                            <span class="trend-icon text-mint"><i class="bi bi-graph-up"></i></span>
                        </div>
                        <div class="metric-value text-mint"><span id="val-ec">0.00</span> <small class="text-mint">mS/cm</small></div>
                        <div class="metric-sparkline" id="spark-ec">
                            <div class="bar highlight"></div><div class="bar highlight"></div><div class="bar highlight"></div><div class="bar highlight"></div><div class="bar highlight"></div><div class="bar highlight"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Farm Health Score Panel -->
            <div class="row mt-5 mb-4 position-relative z-2 justify-content-center telemetry-content">
                <div class="col-12 col-md-8 col-lg-6 text-center">
                    <div class="portfolio-dashboard">
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                            <img src="{{ asset('assets/logo jadi.png') }}" alt="Logo" style="width: 24px; height: 24px; filter: drop-shadow(0 0 5px rgba(16, 185, 129, 0.8));">
                            <span class="text-secondary fw-bold" style="letter-spacing: 1px; font-size: 0.85rem;" data-i18n="detail-health">OVERALL FARM HEALTH</span>
                        </div>
                        <div class="display-1 fw-bold text-white mb-0 mt-3 d-flex align-items-center justify-content-center gap-2" style="font-family: 'Outfit', sans-serif;">
                            <span id="aiHealthScore">95</span> <span class="fs-4 text-mint">HTH</span>
                        </div>
                        <div class="text-muted mt-2 d-flex align-items-center justify-content-center gap-2">
                            <span id="aiHealthStatus">Status: OPTIMAL</span>
                            <span class="text-mint"><i class="bi bi-arrow-up-right"></i> +2.5% Today</span>
                        </div>
                        <!-- Quick Actions -->
                        <div class="d-flex justify-content-center gap-4 mt-4 pt-3">
                            <div class="quick-action-btn" id="btnSyncData" data-bs-toggle="tooltip" title="Sync Telemetry">
                                <div class="action-icon-circle"><i class="bi bi-arrow-down-up"></i></div>
                                <span data-i18n="detail-sync">Sync</span>
                            </div>
                            <div class="quick-action-btn" id="btnSwapAction" data-bs-toggle="tooltip" title="Remote Actions">
                                <div class="action-icon-circle text-amber border-amber"><i class="bi bi-shuffle"></i></div>
                                <span class="text-amber" data-i18n="detail-actions">Action</span>
                            </div>
                            <div class="quick-action-btn" id="btnActivityLog" data-bs-toggle="tooltip" title="View History">
                                <div class="action-icon-circle"><i class="bi bi-clock-history"></i></div>
                                <span data-i18n="detail-activity">Activity</span>
                            </div>
                            <div class="quick-action-btn" id="btnExportData" data-bs-toggle="tooltip" title="Export CSV">
                                <div class="action-icon-circle"><i class="bi bi-download"></i></div>
                                <span data-i18n="detail-export">Export</span>
                            </div>
                        </div>
                        <!-- AI Insight -->
                        <div class="mt-4 pt-3 border-top border-secondary text-start">
                            <p class="text-secondary mb-0" id="aiRecommendation" style="font-size: 0.85rem; line-height: 1.5;">
                                <i class="bi bi-robot text-mint me-1"></i> <strong>AI Insight:</strong> Menghitung data telemetri awal untuk rekomendasi NPK...
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
 
    <!-- METAMASK-STYLE SIDEBAR -->
    <div class="sidebar-body">
            <div class="profile-summary text-center my-4">
                <div class="node-avatar"><i class="bi bi-hdd-network"></i></div>
                <h3 class="mt-3 mb-1" id="activeNodeName" style="font-family: 'Cinzel', serif;">NUTRIX Node 01</h3>
                <div class="address-pill"><span id="activeNodeAddress">0x8F...e4C</span> <i class="bi bi-copy ms-2 cursor-pointer"></i></div>
            </div>
 
            <!-- ==========================================
                 FITUR 3: MULTI-FIELD NODE SWITCHER
                 ========================================== -->
            <div class="node-switcher-container mb-4">
                <h6 class="text-muted fw-bold mb-3 ps-2" style="font-size: 0.75rem; letter-spacing: 1px;" data-i18n="landing-available-nodes">AVAILABLE NODES</h6>
                <div class="node-list">
                    <!-- Node 1 (Default) -->
                    <div class="node-item active" data-node="01" data-name="NUTRIX Node 01" data-address="0x8F...e4C" data-env="corn">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-white">Node 01 (Alpha)</strong> 
                                <span class="badge bg-mint-transparent text-mint ms-2 status-badge" style="font-size: 0.6rem;">ACTIVE</span>
                                <small class="d-block text-muted mt-1"><i class="bi bi-geo-alt"></i> Corn Field - Area A</small>
                            </div>
                            <i class="bi bi-check-circle-fill text-mint check-icon"></i>
                            <i class="bi bi-chevron-right text-muted nav-icon"></i>
                        </div>
                    </div>
                    <!-- Node 2 (Greenhouse) -->
                    <div class="node-item mt-2" data-node="02" data-name="NUTRIX Node 02" data-address="0xA3...b9D" data-env="greenhouse">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-white">Node 02 (Beta)</strong>
                                <span class="badge bg-mint-transparent text-mint ms-2 status-badge" style="font-size: 0.6rem; display: none;">ACTIVE</span>
                                <small class="d-block text-muted mt-1"><i class="bi bi-geo-alt"></i> Greenhouse - Area B</small>
                            </div>
                            <i class="bi bi-check-circle-fill text-mint check-icon" style="display: none;"></i>
                            <i class="bi bi-chevron-right text-muted nav-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
 
            <div class="d-flex justify-content-center gap-4 mb-4 pb-4 border-bottom border-secondary">
                <div class="action-icon-group"><div class="icon-circle"><i class="bi bi-arrow-down"></i></div><span data-i18n="landing-action-receive">Receive</span></div>
                <div class="action-icon-group"><div class="icon-circle"><i class="bi bi-arrow-up"></i></div><span data-i18n="landing-action-send">Send</span></div>
                <div class="action-icon-group"><div class="icon-circle"><i class="bi bi-arrow-repeat"></i></div><span data-i18n="detail-sync">Sync</span></div>
            </div>
 
            <h6 class="text-muted fw-bold mb-3 ps-2" data-i18n="landing-recent-activity">RECENT ACTIVITY</h6>
            <div class="activity-list" id="sidebarActivityList">
                <div class="activity-item">
                    <div class="icon bg-mint-transparent text-mint"><i class="bi bi-check2-circle"></i></div>
                    <div class="details"><strong data-i18n="landing-activity-synced">Data Synced</strong><small class="text-mint d-block" data-i18n="landing-activity-confirmed">Confirmed</small></div>
                    <div class="time" data-i18n="landing-activity-justnow">Just now</div>
                </div>
            </div>
        </div>
 
    <!-- CONNECT NODE MODAL -->
    <div class="modal-overlay" id="connectModal">
        <div class="web3-modal-box">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                <h4 class="mb-0 fw-bold" style="font-family: 'Cinzel', serif;" data-i18n="landing-connect-node">Connect Node</h4>
                <button class="btn-close-custom" id="closeConnectModal"><i class="bi bi-x-lg"></i></button>
            </div>
            
            <div class="connect-option" onclick="simulateConnection(this)">
                <div class="d-flex align-items-center gap-3">
                    <div class="provider-icon bg-dark">ðŸ¦Š</div>
                    <span class="fw-bold fs-5" data-i18n="landing-local-network">Local Network</span>
                </div>
                <div class="spinner-border text-mint spinner-border-sm" role="status" style="display:none;"></div>
            </div>
        </div>
    </div>
 
    <!-- SIGNATURE REQUEST MODAL -->
    <div class="modal-overlay" id="signModal">
        <div class="web3-modal-box">
            <div class="d-flex align-items-center gap-2 mb-4 border-bottom border-secondary pb-3">
                <div class="live-dot"></div>
                <h4 class="mb-0 fw-bold" style="font-family: 'Cinzel', serif;" data-i18n="landing-signature-req">Signature Request</h4>
            </div>
            
            <div class="contract-details p-3 mb-4 rounded-3 bg-black border border-secondary">
                <small class="text-secondary text-uppercase fw-bold">Function Call:</small>
                <div class="font-monospace fs-5 text-white mb-3" id="signActionName">EXECUTE_CMD()</div>
                <div class="d-flex justify-content-between border-top border-secondary pt-2">
                    <span class="text-secondary">Network Energy</span>
                    <strong class="text-amber font-monospace" id="signFee">0.000 NTRX</strong>
                </div>
            </div>
 
            <div class="row g-2">
                <div class="col-6"><button class="btn btn-action-trigger outline w-100" id="btnRejectSign" data-i18n="landing-btn-reject">Reject</button></div>
                <div class="col-6"><button class="btn btn-action-trigger w-100" id="btnConfirmSign" data-i18n="landing-btn-confirm">Confirm</button></div>
            </div>
        </div>
    </div>
 
    <!-- TOAST NOTIFICATION -->
    <div class="web3-toast" id="toastNotif">
        <div class="toast-icon"><i class="bi bi-arrow-repeat spin"></i></div>
        <div>
            <strong class="d-block toast-title">Memproses...</strong>
            <span class="toast-desc">Menghubungkan ke node IoT Nutrix...</span>
        </div>
    </div>
 
    <!-- AUTH MODAL: Sign In / Sign Up with 2-Minute Email OTP -->
    <div class="modal-overlay" id="authModal">
        <div class="web3-modal-box auth-modal-box">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary pb-3">
                <div class="auth-tab-group" id="authTabGroup">
                    <button class="auth-tab active" data-tab="signin" data-i18n="auth-sign-in-tab">Sign In</button>
                    <button class="auth-tab" data-tab="signup" data-i18n="auth-sign-up-tab">Sign Up</button>
                </div>
                <button class="btn-close-custom" id="closeAuthModal"><i class="bi bi-x-lg"></i></button>
            </div>

            <!-- BANTUAN GOOGLE APP PASSWORD 16-KARAKTER & DEMO ADMIN -->
            <div class="p-2 mb-3 rounded-2 bg-dark border border-secondary" style="font-size: 0.78rem;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-secondary"><i class="bi bi-shield-lock text-warning me-1"></i> <span data-i18n="auth-google-smtp">Sandi SMTP Google:</span></span>
                    <a href="https://myaccount.google.com/apppasswords" target="_blank" class="text-mint text-decoration-none fw-bold">
                        Buat Sandi 16 Karakter <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                </div>
                <div class="text-muted d-flex justify-content-between align-items-center">
                    <span>Demo Admin: <code class="text-warning">admin@nutrix.io</code></span>
                    <span>Pass: <code class="text-warning">NutrixAdmin#2026</code></span>
                </div>
            </div>
 
            <!-- STEP 1: Sign In Form -->
            <div class="auth-form" id="form-signin">
                <div class="auth-input-group mb-2">
                    <label data-i18n="auth-email-address">Email Address</label>
                    <input type="email" class="auth-input" placeholder="admin@nutrix.io" id="signinEmail" required>
                </div>
                <div class="auth-input-group mb-2">
                    <label data-i18n="auth-password">Password</label>
                    <input type="password" class="auth-input" placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢" id="signinPassword" required>
                </div>
                <button class="btn btn-connect-node w-100 mt-2" id="btnSignIn">
                    <span data-i18n="auth-req-otp-signin">Minta Kode OTP (Sign In)</span>
                </button>
                <p class="text-center text-muted mt-3 mb-0" style="font-size:0.85rem;"><span data-i18n="auth-no-account">Belum punya akun?</span> <a href="#" class="text-mint auth-switch" data-tab="signup" data-i18n="auth-sign-up-tab">Sign Up</a></p>
            </div>
 
            <!-- STEP 1: Sign Up Form -->
            <div class="auth-form" id="form-signup" style="display:none;">
                <div class="auth-input-group mb-2">
                    <label data-i18n="auth-fullname-username">Nama Lengkap / Username</label>
                    <input type="text" class="auth-input" placeholder="Farmer Alpha" id="signupName" required>
                </div>
                <div class="auth-input-group mb-2">
                    <label data-i18n="auth-email-address">Email Address</label>
                    <input type="email" class="auth-input" placeholder="farmer@nutrix.io" id="signupEmail" required>
                </div>
                <div class="auth-input-group mb-2">
                    <label data-i18n="auth-password-min-8">Password (Min. 8 Karakter)</label>
                    <input type="password" class="auth-input" placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢" id="signupPassword" required>
                </div>
                <button class="btn btn-connect-node w-100 mt-2" id="btnSignUp">
                    <span data-i18n="auth-req-otp-signup">Minta Kode OTP (Sign Up)</span>
                </button>
                <p class="text-center text-muted mt-3 mb-0" style="font-size:0.85rem;"><span data-i18n="auth-has-account">Sudah punya akun?</span> <a href="#" class="text-mint auth-switch" data-tab="signin" data-i18n="auth-sign-in-tab">Sign In</a></p>
            </div>

            <!-- STEP 2: VERIFIKASI OTP 2 MENIT (DYNAMIC) -->
            <div class="auth-form" id="form-otp" style="display:none;">
                <div class="text-center mb-3">
                    <div class="d-inline-flex p-3 rounded-circle bg-dark border border-secondary mb-2">
                        <i class="bi bi-envelope-open-heart text-mint fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-1 text-white" id="otpHeadingTitle" data-i18n="auth-verify-email-title">Verifikasi Email Anda</h5>
                    <p class="text-muted small mb-0">Kode verifikasi 6 digit telah dikirimkan ke:</p>
                    <div class="fw-bold text-mint font-monospace small" id="otpTargetEmail">user@example.com</div>
                </div>

                <!-- Live 2-Minute Timer Box -->
                <div class="d-flex justify-content-between align-items-center p-2 mb-3 rounded-3 bg-dark border border-secondary">
                    <span class="text-secondary small"><i class="bi bi-hourglass-split me-1 text-warning"></i> <span data-i18n="auth-time-left">Sisa Waktu Berlaku:</span></span>
                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 fs-6 font-monospace" id="otpTimerDisplay">
                        02:00
                    </span>
                </div>

                <div class="auth-input-group mb-3 text-center">
                    <label class="text-secondary small mb-2 d-block" data-i18n="auth-enter-6-digit">Masukkan 6 Digit Kode Verifikasi</label>
                    <input type="text" class="auth-input text-center fw-bold fs-3 font-monospace tracking-wider" 
                           maxlength="6" placeholder="000000" id="inputOtpCode" style="letter-spacing: 8px;">
                </div>

                <button class="btn btn-connect-node w-100 mb-2" id="btnVerifyOtp">
                    <span data-i18n="auth-verify-login">Verifikasi & Masuk</span>
                </button>

                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-secondary">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none p-0 small" id="btnBackFromOtp">
                        <i class="bi bi-arrow-left me-1"></i> <span data-i18n="auth-back-change">Kembali / Ganti</span>
                    </button>
                    <button type="button" class="btn btn-link text-mint text-decoration-none p-0 small fw-bold" id="btnResendOtp" disabled>
                        <i class="bi bi-arrow-clockwise me-1"></i> <span data-i18n="auth-resend-code">Kirim Ulang Kode</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
 
    <!-- SETTINGS MODAL -->
    <div class="modal-overlay" id="settingsModal">
        <div class="web3-modal-box">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                <h4 class="mb-0 fw-bold"><i class="bi bi-gear text-mint me-2"></i> <span data-i18n="popup-settings-title">User Settings</span></h4>
                <button class="btn-close-custom" id="closeSettingsModal"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="text-muted mb-4" data-i18n="popup-settings-description">Manage your dashboard preferences and network configurations here.</div>
            
            <div class="d-flex justify-content-between align-items-center mb-3 bg-dark p-3 rounded-3">
                <span data-i18n="popup-push-notifications">Push Notifications</span>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="settingPushNotifications" checked>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4 bg-dark p-3 rounded-3">
                <span data-i18n="popup-auto-sync">Auto-Sync Telemetry</span>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="settingAutoSync" checked>
                </div>
            </div>
            
            <button class="btn btn-connect-node w-100" id="btnSaveSettings" data-i18n="popup-save-changes">Save Changes</button>
        </div>
    </div>
 
    <!-- METRIC INFO POPUP MODAL -->
    <div class="modal-overlay" id="metricInfoModal">
        <div class="web3-modal-box">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                <h4 class="mb-0 fw-bold" id="metricModalTitle"><i class="bi bi-info-circle text-mint me-2"></i> <span data-i18n="popup-metric-details">Metric Details</span></h4>
                <button class="btn-close-custom" id="closeMetricModal"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="metric-info-content">
                <h5 class="text-white mb-2" data-i18n="landing-metric-def">Definisi</h5>
                <p class="text-secondary" id="metricModalDef" data-i18n="popup-definition-default">Definisi parameter.</p>
                
                <h5 class="text-mint mt-4 mb-2"><i class="bi bi-cpu"></i> <span data-i18n="popup-engine-impact">Pengaruh Terhadap Analisis Mesin</span></h5>
                <div class="bg-dark p-3 rounded-3 border border-secondary">
                    <p class="text-secondary mb-0" id="metricModalEffect" data-i18n="popup-effect-default">Efek terhadap matriks sistem.</p>
                </div>
            </div>
        </div>
    </div>
 
    <!-- ARCHITECTURE FULL-PANEL POPUP MODAL -->
    <div class="modal-overlay" id="archModal">
        <div class="web3-modal-box" style="max-width: 700px; width: 90%;">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                <h4 class="mb-0 fw-bold" id="archModalTitle" style="font-family: 'Cinzel', serif;"><span data-i18n="popup-architecture-model">Architecture Model</span></h4>
                <button class="btn-close-custom" id="closeArchModal"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="row align-items-center">
                <div class="col-md-4 text-center mb-4 mb-md-0">
                    <i class="display-1 text-mint opacity-75" id="archModalIcon"></i>
                </div>
                <div class="col-md-8">
                    <h5 class="text-white mb-3" data-i18n="popup-technical-specifications">Technical Specifications</h5>
                    <p class="text-secondary" id="archModalDesc"></p>
                    <div class="bg-dark p-3 rounded-3 border border-secondary mt-3">
                        <strong class="text-mint d-block mb-1" data-i18n="popup-ecosystem-impact">Impact on Ecosystem:</strong>
                        <span class="text-secondary" id="archModalImpact"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
 
    <!-- ACTIVITY LOG MODAL (MetaMask Style) -->
    <div class="modal-overlay" id="activityModal">
        <div class="web3-modal-box">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                <h4 class="mb-0 fw-bold"><i class="bi bi-clock-history text-mint me-2"></i> <span data-i18n="popup-activity-log">Activity Log</span></h4>
                <button class="btn-close-custom" id="closeActivityModal"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="activity-list" id="activityModalList" style="max-height: 400px; overflow-y: auto;">
                <!-- Activity Item 1 -->
                <div class="activity-entry d-flex align-items-center p-3 mb-2 rounded bg-dark border border-secondary" data-title="Water Pump Activated" data-detail="To: 0x8F...e4C" data-fee="0.002" data-status="Confirmed" data-time="Today, 08:30 AM">
                    <div class="me-3 fs-3 text-mint"><i class="bi bi-check-circle-fill"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-white">Water Pump Activated</div>
                        <div class="text-muted" style="font-size: 0.8rem;">To: 0x8F...e4C <span class="badge bg-secondary ms-1">Confirmed</span></div>
                    </div>
                    <div class="text-end">
                        <div class="text-white fw-bold">0.002 FEE</div>
                        <div class="text-muted" style="font-size: 0.8rem;">Today, 08:30 AM</div>
                    </div>
                </div>
                <!-- Activity Item 2 -->
                <div class="activity-entry d-flex align-items-center p-3 mb-2 rounded bg-dark border border-secondary" data-title="Telemetry Synced" data-detail="From: Main Node" data-fee="0.000" data-status="Confirmed" data-time="Today, 07:15 AM">
                    <div class="me-3 fs-3 text-mint"><i class="bi bi-check-circle-fill"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-white">Telemetry Synced</div>
                        <div class="text-muted" style="font-size: 0.8rem;">From: Main Node <span class="badge bg-secondary ms-1">Confirmed</span></div>
                    </div>
                    <div class="text-end">
                        <div class="text-white fw-bold">0.000 FEE</div>
                        <div class="text-muted" style="font-size: 0.8rem;">Today, 07:15 AM</div>
                    </div>
                </div>
                <!-- Activity Item 3 -->
                <div class="activity-entry d-flex align-items-center p-3 mb-2 rounded bg-dark border border-secondary" data-title="Biofertilizer Inject" data-detail="To: 0x8F...e4C" data-fee="0.005" data-status="Failed" data-time="Yesterday">
                    <div class="me-3 fs-3 text-danger"><i class="bi bi-x-circle-fill"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-white">Biofertilizer Inject</div>
                        <div class="text-muted" style="font-size: 0.8rem;">To: 0x8F...e4C <span class="badge bg-danger ms-1">Failed</span></div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted text-decoration-line-through fw-bold">0.005 FEE</div>
                        <div class="text-muted" style="font-size: 0.8rem;">Yesterday</div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-3">
                <button class="activity-clear-btn" id="btnClearActivity" data-i18n="popup-clear-history">Clear session history</button>
            </div>
        </div>
    </div>
 
    <!-- REMOTE ACTION / SWAP MODAL -->
    <div class="modal-overlay" id="swapActionModal">
        <div class="web3-modal-box swap-modal-box">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                <div>
                    <h4 class="mb-1 fw-bold"><i class="bi bi-shuffle text-amber me-2"></i> <span data-i18n="popup-remote-action">Remote Action</span></h4>
                    <small class="text-muted" data-i18n="popup-remote-action-help">Prepare an IoT instruction for review.</small>
                </div>
                <button class="btn-close-custom" id="closeSwapActionModal"><i class="bi bi-x-lg"></i></button>
            </div>
            <div id="swapActionForm">
                <label class="form-label text-muted small fw-bold" data-i18n="popup-select-action">SELECT ACTION</label>
 
                <!-- Custom Dropdown Action -->
                <div class="custom-select-wrapper mb-3" id="actionSelectWrapper">
                    <div class="custom-select-trigger">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-droplet-fill text-mint" id="triggerActionIcon"></i>
                            <span id="triggerActionText">Water Pump</span>
                        </div>
                        <i class="bi bi-chevron-down text-muted chevron"></i>
                    </div>
                    <div class="custom-options-container">
                        <div class="custom-option selected" data-value="Water Pump" data-fee="0.002" data-icon="bi-droplet-fill">
                            <i class="bi bi-droplet-fill text-mint me-3"></i> <span data-i18n="landing-opt-water">Water Pump</span>
                        </div>
                        <div class="custom-option" data-value="Biofertilizer Injection" data-fee="0.005" data-icon="bi-flower1">
                            <i class="bi bi-flower1 text-mint me-3"></i> <span data-i18n="landing-opt-biofert">Biofertilizer Injection</span>
                        </div>
                    </div>
                </div>
                <!-- Hidden Input Action -->
                <input type="hidden" id="swapActionType" value="Water Pump">
                <input type="hidden" id="swapActionFee" value="0.002">
 
                <div class="swap-arrow"><i class="bi bi-arrow-down"></i></div>
 
                <label class="form-label text-muted small fw-bold mt-2" data-i18n="popup-execution-window">EXECUTION WINDOW</label>
 
                <!-- Custom Dropdown Duration -->
                <div class="custom-select-wrapper mb-2" id="durationSelectWrapper">
                    <div class="custom-select-trigger">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-stopwatch text-amber"></i>
                            <span id="triggerDurationText">10 seconds</span>
                        </div>
                        <i class="bi bi-chevron-down text-muted chevron"></i>
                    </div>
                    <div class="custom-options-container">
                        <div class="custom-option selected" data-value="10">
                            <i class="bi bi-clock text-amber me-3"></i> 10 seconds
                        </div>
                        <div class="custom-option" data-value="30">
                            <i class="bi bi-clock text-amber me-3"></i> 30 seconds
                        </div>
                        <div class="custom-option" data-value="60">
                            <i class="bi bi-clock text-amber me-3"></i> 60 seconds
                        </div>
                    </div>
                </div>
                <!-- Hidden Input Duration -->
                <input type="hidden" id="swapDuration" value="10">
 
                <div class="d-flex justify-content-between px-1 mb-4 mt-3 small text-muted">
                    <span data-i18n="popup-network-energy">Network energy</span><span id="swapFee">0.002 NTRX</span>
                </div>
                <button class="btn btn-action-trigger w-100" id="btnReviewSwapAction" data-i18n="popup-review-action">Review Action</button>
            </div>
            <div class="swap-processing text-center" id="swapProcessing" hidden>
                <div class="swap-loader mb-3"><i class="bi bi-cpu"></i></div>
                <h5 class="text-white" data-i18n="popup-validating">Validating instruction</h5>
                <p class="text-muted mb-0" data-i18n="popup-broadcasting">Broadcasting command to the field node...</p>
            </div>
        </div>
    </div>
 
    <!-- AI DAPP CONNECTION CONFIRMATION -->
    <div class="modal-overlay" id="aiConnectModal">
        <div class="web3-modal-box ai-connect-modal-box">
            <div class="text-center mb-4">
                <div class="ai-connect-icon"><i class="bi bi-robot"></i></div>
                <h4 class="fw-bold mb-1" data-i18n="popup-ai-connect">Connect Smart AI Analytics?</h4>
                <p class="text-muted mb-0" data-i18n="popup-ai-connect-help">nutrix-ai.local requests access to this node.</p>
            </div>
            <div class="contract-details p-3 mb-4 rounded-3 bg-black border border-secondary">
                <small class="text-secondary text-uppercase fw-bold" data-i18n="popup-requested-permission">Requested permission</small>
                <div class="text-white mt-2"><i class="bi bi-broadcast-pin text-mint me-2"></i><span data-i18n="popup-read-soil">Read soil telemetry and farm health trends</span></div>
            </div>
            <div class="row g-2"><div class="col-6"><button class="btn btn-action-trigger outline w-100" id="btnRejectAiConnect" data-i18n="detail-cancel">Cancel</button></div><div class="col-6"><button class="btn btn-action-trigger w-100" id="btnConfirmAiConnect" data-i18n="popup-connect">Connect</button></div></div>
        </div>
    </div>
 
    <!-- TRANSACTION DETAIL MODAL -->
    <div class="modal-overlay" id="transactionDetailModal">
        <div class="web3-modal-box transaction-detail-box">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                <h4 class="mb-0 fw-bold" id="transactionTitle"><i class="bi bi-receipt text-mint me-2"></i> <span data-i18n="popup-transaction-detail">Transaction Detail</span></h4>
                <button class="btn-close-custom" id="closeTransactionDetail"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="transaction-status" id="transactionStatus">Confirmed</div>
            <div class="transaction-details-list">
                <div><span>Node</span><strong id="transactionNode">â€”</strong></div>
                <div><span>Network Fee</span><strong id="transactionFee">0.000 NTRX</strong></div>
                <div><span>Time</span><strong id="transactionTime">â€”</strong></div>
                <div><span>Transaction ID</span><code id="transactionId">â€”</code></div>
            </div>
            <button class="btn btn-outline-secondary" id="btnCopyTransaction" data-i18n="popup-copy-transaction"><i class="bi bi-copy me-1"></i> Copy Transaction ID</button>
        </div>
    </div>
 
    <!-- FIXED: Bootstrap JS 5.3.3 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/script.js') }}?v={{ filemtime(public_path('js/script.js')) }}"></script>
</body>
</html>
