<!DOCTYPE html>
<html lang="id" data-theme="emerald">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>NUTRIX - Smart Agriculture Web3 Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1e293b" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#f4f8f6" media="(prefers-color-scheme: light)">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <script>
        const validNutrixThemes = ['emerald', 'harvest', 'nordic', 'obsidian', 'midnight', 'hydro'];
        const storedNutrixTheme = localStorage.getItem('nutrix_theme');
        const initialNutrixTheme = validNutrixThemes.includes(storedNutrixTheme) ? storedNutrixTheme : 'emerald';
        document.documentElement.setAttribute('data-theme', initialNutrixTheme);
        document.documentElement.setAttribute('data-bs-theme', ['emerald', 'harvest', 'nordic'].includes(initialNutrixTheme) ? 'light' : 'dark');
        const initialNutrixLanguage = localStorage.getItem('nutrix_language') || 'id';
        document.documentElement.setAttribute('lang', initialNutrixLanguage);
        document.documentElement.setAttribute('dir', initialNutrixLanguage === 'ar' ? 'rtl' : 'ltr');
    </script>
    
    <!-- Tipografi Premium (dns-prefetch + preconnect) -->
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;900&family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <!-- FIXED: Bootstrap 5.3.3 (Stable Version) & Icons -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" as="style">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Web3 Styles -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="user-dashboard-shell">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg fixed-top web3-navbar">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('welcome') }}">
                <span class="brand-icon">🌱</span> NUTRIX
            </a>
            <div class="user-workspace-title d-none d-md-flex align-items-center">
                <span class="workspace-title-mark"><i class="bi bi-grid-1x2-fill"></i></span>
                <span><strong data-i18n="ui-my-farm">My Farm</strong><small data-i18n="ui-workspace">Workspace</small></span>
            </div>
            <div class="d-none d-lg-flex flex-grow-1 justify-content-center">
                <ul class="navbar-nav gap-4">
                    <li class="nav-item"><a class="nav-link workspace-nav-link active" href="{{ route('dashboard') }}#overview" data-workspace-target="overview" data-i18n="nav-overview">Overview</a></li>
                    <li class="nav-item"><a class="nav-link workspace-nav-link" href="{{ route('dashboard') }}#gardens" data-workspace-target="gardens" data-i18n="nav-gardens">My Gardens</a></li>
                    <li class="nav-item"><a class="nav-link workspace-nav-link" href="{{ route('dashboard') }}#activity" data-workspace-target="activity" data-i18n="nav-activity">Activity</a></li>
                </ul>
            </div>
            <div class="nav-actions d-flex align-items-center gap-3">
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle theme-menu-button" type="button" data-bs-toggle="dropdown" aria-label="Pilih tema">
                        <i class="bi bi-palette"></i> <span id="currentThemeLabel">Theme</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end theme-menu">
                        <li class="dropdown-header fw-bold" data-i18n="ui-theme-light">Light Mode</li>
                        <li><button type="button" class="dropdown-item py-2 px-3 fw-semibold theme-btn" data-theme-value="emerald"><i class="bi bi-tree-fill me-2" style="color: var(--color-accent-highlight);"></i> Emerald Field</button></li>
                        <li><button type="button" class="dropdown-item py-2 px-3 fw-semibold theme-btn" data-theme-value="harvest"><i class="bi bi-sun-fill me-2" style="color: var(--color-amber);"></i> Golden Harvest</button></li>
                        <li><button type="button" class="dropdown-item py-2 px-3 fw-semibold theme-btn" data-theme-value="nordic"><i class="bi bi-snow me-2" style="color: var(--color-accent-2);"></i> Nordic Clean</button></li>
                        <li><hr class="dropdown-divider m-0"></li>
                        <li class="dropdown-header fw-bold" data-i18n="ui-theme-dark">Dark Mode</li>
                        <li><button type="button" class="dropdown-item py-2 px-3 fw-semibold theme-btn" data-theme-value="obsidian"><i class="bi bi-moon-stars-fill me-2" style="color: var(--color-accent-highlight);"></i> Obsidian Matrix</button></li>
                        <li><button type="button" class="dropdown-item py-2 px-3 fw-semibold theme-btn" data-theme-value="midnight"><i class="bi bi-fire me-2" style="color: var(--color-amber);"></i> Midnight Soil</button></li>
                        <li><button type="button" class="dropdown-item py-2 px-3 fw-semibold theme-btn" data-theme-value="hydro"><i class="bi bi-droplet-fill me-2" style="color: var(--color-accent-2);"></i> Deep Hydro</button></li>
                    </ul>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle theme-menu-button" type="button" data-bs-toggle="dropdown" aria-label="Pilih bahasa">
                        <i class="bi bi-translate"></i> <span id="currentLanguageLabel">ID</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end theme-menu">
                        <li><button type="button" class="dropdown-item language-btn" data-lang="en-GB">English (UK)</button></li>
                        <li><button type="button" class="dropdown-item language-btn" data-lang="en-US">English (US)</button></li>
                        <li><button type="button" class="dropdown-item language-btn" data-lang="en-CA">English (CA)</button></li>
                        <li><button type="button" class="dropdown-item language-btn" data-lang="id">Bahasa Indonesia</button></li>
                        <li><button type="button" class="dropdown-item language-btn" data-lang="jv">Basa Jawa</button></li>
                        <li><button type="button" class="dropdown-item language-btn" data-lang="ja">&#26085;&#26412;&#35486;</button></li>
                        <li><button type="button" class="dropdown-item language-btn" data-lang="ar">&#1575;&#1604;&#1593;&#1585;&#1576;&#1610;&#1577;</button></li>
                        <li><button type="button" class="dropdown-item language-btn" data-lang="ms">Bahasa Melayu</button></li>
                    </ul>
                </div>
                <!-- Notification Center -->
                <div class="notification-wrapper" id="notificationWrapper">
                    <button class="notification-btn" id="notificationBtn" aria-label="Open notifications">
                        <i class="bi bi-bell"></i><span class="notification-badge" id="notificationBadge">0</span>
                    </button>
                    <div class="notification-panel" id="notificationPanel">
                        <div class="notification-panel-header"><strong data-i18n="ui-notifications">Notifications</strong><button id="btnReadNotifications" data-i18n="ui-mark-read">Mark all read</button></div>
                        <div id="notificationList"></div>
                    </div>
                </div>

                <!-- Auth Button (unauthenticated state) -->
                <button class="btn btn-connect-node" id="authBtn" data-i18n="ui-sign-in">Sign In</button>

                <!-- Account Avatar (authenticated state, hidden by default) -->
                <div class="account-avatar-wrapper" id="accountAvatarWrapper" style="display: none;">
                    <div class="account-avatar" id="accountAvatarBtn">
                        <div class="avatar-identicon" id="avatarIdenticon"></div>
                    </div>
                    <span class="user-profile-name d-none d-sm-inline" id="profileNameDisplay">Profil</span>
                    <!-- Expandable Account Panel -->
                    <div class="account-panel" id="accountPanel">
                        <div class="account-panel-header">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-identicon-lg" id="panelIdenticon"></div>
                                <div>
                                    <strong class="d-block user-panel-name" id="panelUsername">Profil</strong>
                                    <small class="text-muted user-panel-address" id="panelEmail">Email tersamarkan</small>
                                </div>
                            </div>
                        </div>
                        <div class="account-panel-body" id="accountPanelBody">
                            <div class="account-panel-label" data-i18n="ui-current-account">CURRENT ACCOUNT</div>
                            <!-- Dynamic account option will be appended here by JS -->
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
        @yield('content')
    </main>

    <!-- TOAST NOTIFICATION -->
    <div class="web3-toast" id="toastNotif">
        <div class="toast-icon"><i class="bi bi-arrow-repeat spin"></i></div>
        <div>
            <strong class="d-block toast-title">Transaction Pending</strong>
            <span class="toast-desc">Waiting for node execution...</span>
        </div>
    </div>

    <!-- AUTH MODAL: Sign In / Sign Up -->
    <div class="modal-overlay" id="authModal">
        <div class="web3-modal-box auth-modal-box">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                <div class="auth-tab-group">
                    <button class="auth-tab active" data-tab="signin" data-i18n="ui-sign-in">Sign In</button>
                    <button class="auth-tab" data-tab="signup" data-i18n="ui-sign-up">Sign Up</button>
                </div>
                <button class="btn-close-custom" id="closeAuthModal"><i class="bi bi-x-lg"></i></button>
            </div>

            <!-- Sign In Form -->
            <div class="auth-form" id="form-signin">
                <div class="auth-input-group">
                    <label data-i18n="ui-email">Email</label>
                    <input type="text" class="auth-input" placeholder="admin@nutrix.io" id="signinEmail">
                </div>
                <div class="auth-input-group">
                    <label data-i18n="ui-password">Password</label>
                    <input type="password" class="auth-input" placeholder="••••••••" id="signinPassword">
                </div>
                <button class="btn btn-connect-node w-100 mt-3" id="btnSignIn" data-i18n="ui-sign-in">Sign In</button>
                <p class="text-center text-muted mt-3 mb-0" style="font-size:0.85rem;"><span data-i18n="ui-sign-in-help">Don't have an account?</span> <a href="#" class="text-mint auth-switch" data-tab="signup" data-i18n="ui-sign-up">Sign Up</a></p>
            </div>

            <!-- Sign Up Form -->
            <div class="auth-form" id="form-signup" style="display:none;">
                <div class="auth-input-group">
                    <label data-i18n="ui-username">Username</label>
                    <input type="text" class="auth-input" placeholder="Farm Admin" id="signupName">
                </div>
                <div class="auth-input-group">
                    <label data-i18n="ui-email">Email</label>
                    <input type="text" class="auth-input" placeholder="admin@nutrix.io" id="signupEmail">
                </div>
                <div class="auth-input-group">
                    <label data-i18n="ui-password">Password</label>
                    <input type="password" class="auth-input" placeholder="••••••••" id="signupPassword">
                </div>
                <div class="auth-input-group">
                    <label data-i18n="ui-confirm-password">Confirm Password</label>
                    <input type="password" class="auth-input" placeholder="••••••••" id="signupPasswordConfirm">
                </div>
                <button class="btn btn-connect-node w-100 mt-3" id="btnSignUp" data-i18n="ui-create-account">Create Account</button>
                <p class="text-center text-muted mt-3 mb-0" style="font-size:0.85rem;"><span data-i18n="ui-sign-up-help">Already have an account?</span> <a href="#" class="text-mint auth-switch" data-tab="signin" data-i18n="ui-sign-in">Sign In</a></p>
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
                <h5 class="text-white mb-2" data-i18n="metric-definition">Definisi</h5>
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
                            <i class="bi bi-droplet-fill text-mint me-3"></i> Water Pump
                        </div>
                        <div class="custom-option" data-value="Biofertilizer Injection" data-fee="0.005" data-icon="bi-flower1">
                            <i class="bi bi-flower1 text-mint me-3"></i> Biofertilizer Injection
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
                <div><span>Garden</span><strong id="transactionNode">—</strong></div>
                <div><span>Network Fee</span><strong id="transactionFee">0.000 NTRX</strong></div>
                <div><span>Time</span><strong id="transactionTime">—</strong></div>
                <div><span>Transaction ID</span><code id="transactionId">—</code></div>
            </div>
            <button class="btn btn-outline-secondary" id="btnCopyTransaction" data-i18n="popup-copy-transaction"><i class="bi bi-copy me-1"></i> Copy Transaction ID</button>
        </div>
    </div>

    <!-- FIXED: Bootstrap JS 5.3.3 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Status login dibaca dari session Laravel yang sebenarnya, bukan
        // localStorage - kalau ini kosong padahal sudah login, cek apakah
        // AuthController benar-benar Auth::login() dan session persist.
        window.NUTRIX_USER = @auth {!! json_encode(['name' => auth()->user()->name, 'email' => auth()->user()->email]) !!} @else null @endauth;
    </script>
    <script src="{{ asset('js/script.js') }}?v={{ filemtime(public_path('js/script.js')) }}"></script>
    @yield('scripts')
</body>
</html>
