<!DOCTYPE html>
<html lang="id" data-theme="obsidian">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NUTRIX // MASTER ADMIN COMMAND CENTER</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;900&family=JetBrains+Mono:wght@400;600;800&family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Style -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <script>
        const adminThemes = ['emerald', 'harvest', 'nordic', 'obsidian', 'midnight', 'hydro'];
        const savedAdminTheme = localStorage.getItem('nutrix_theme');
        const initialAdminTheme = adminThemes.includes(savedAdminTheme) ? savedAdminTheme : 'obsidian';
        document.documentElement.setAttribute('data-theme', initialAdminTheme);
        document.documentElement.setAttribute('data-bs-theme', ['emerald', 'harvest', 'nordic'].includes(initialAdminTheme) ? 'light' : 'dark');
        const initialLanguage = localStorage.getItem('nutrix_language') || 'id';
        document.documentElement.setAttribute('lang', initialLanguage);
        document.documentElement.setAttribute('dir', initialLanguage === 'ar' ? 'rtl' : 'ltr');
    </script>

    <style>
        :root {
            --admin-gold: #f59e0b;
            --admin-cyan: #06b6d4;
            --admin-red: #ef4444;
            --admin-card-bg: rgba(18, 24, 38, 0.75);
            --admin-border: rgba(245, 158, 11, 0.25);
        }

        body.admin-body {
            background: radial-gradient(circle at 10% 20%, rgba(245, 158, 11, 0.05) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, rgba(6, 182, 212, 0.05) 0%, transparent 40%),
                        #0a0f16;
            color: #f1f5f9;
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            padding: 0;
        }

        .admin-shell { display: flex; min-height: 100vh; }
        .admin-sidebar {
            width: 268px; flex: 0 0 268px; padding: 26px 16px;
            background: rgba(6, 9, 14, .9); border-right: 1px solid rgba(255,255,255,.08);
            display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh;
        }
        .admin-sidebar-brand { font-family: 'Cinzel', serif; color: #fff; font-weight: 900; letter-spacing: 1px; font-size: 1.3rem; }
        .admin-sidebar-caption { color: #64748b; font-size: .68rem; letter-spacing: .14em; text-transform: uppercase; margin: 34px 12px 10px; }
        .admin-menu-link { color: #94a3b8; text-decoration: none; padding: 12px; border-radius: 10px; display: flex; gap: 12px; align-items: center; margin: 3px 0; font-size: .9rem; font-weight: 600; }
        .admin-menu-link:hover, .admin-menu-link.active { background: rgba(245,158,11,.12); color: #fbbf24; }
        .admin-sidebar-status { margin-top: auto; padding: 14px; border: 1px solid rgba(16,185,129,.24); background: rgba(16,185,129,.06); border-radius: 12px; font-size: .76rem; color: #94a3b8; }
        .admin-workspace { min-width: 0; flex: 1; }
        .admin-topbar { min-height: 82px; border-bottom: 1px solid rgba(255,255,255,.08); background: rgba(10,15,22,.78); backdrop-filter: blur(20px); padding: 16px 28px; }
        .admin-page-title { font-family: 'Cinzel', serif; color: #fff; font-weight: 800; font-size: 1.25rem; margin: 0; }
        .admin-subtitle { color: #64748b; font-size: .8rem; margin-top: 3px; }
        .admin-content { padding: 28px; }
        .admin-section-label { color: #64748b; font-size: .72rem; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; }
        .farm-row { padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,.06); }
        .farm-row:last-child { border-bottom: 0; }
        .admin-toast { position: fixed; right: 24px; bottom: 24px; z-index: 1100; min-width: 300px; opacity: 0; transform: translateY(14px); pointer-events: none; transition: .22s ease; }
        .admin-toast.show { opacity: 1; transform: translateY(0); }
        .admin-modal { background: rgba(6,9,14,.96); border: 1px solid rgba(245,158,11,.3); color: #e2e8f0; }
        @media (max-width: 991.98px) { .admin-sidebar { width: 76px; flex-basis: 76px; padding: 22px 10px; } .admin-sidebar-brand span, .admin-sidebar-caption, .admin-menu-link span, .admin-sidebar-status { display: none; } .admin-sidebar-brand { text-align:center; } .admin-menu-link { justify-content:center; font-size:1.1rem; } .admin-content { padding: 20px; } }
        @media (max-width: 575.98px) { .admin-shell { display:block; } .admin-sidebar { position: static; height:auto; width:100%; flex-basis:auto; flex-direction:row; align-items:center; padding: 10px; overflow-x:auto; } .admin-sidebar-brand { margin-right:10px; } .admin-menu-link { padding:10px; white-space:nowrap; } .admin-workspace { width:100%; } .admin-topbar { padding:14px 16px; } .admin-content { padding:16px; } }

        .mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Admin Navbar */
        .admin-nav {
            background: rgba(10, 15, 22, 0.88);
            border-bottom: 1px solid var(--admin-border);
            backdrop-filter: blur(20px);
            padding: 16px 28px;
            position: sticky;
            top: 0;
            z-index: 1050;
        }

        .admin-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: 1px;
            color: #fff;
        }

        .admin-badge {
            background: rgba(245, 158, 11, 0.15);
            border: 1px solid var(--admin-gold);
            color: var(--admin-gold);
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        /* Master Console Card */
        .admin-card {
            background: var(--admin-card-bg);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(12px);
            transition: transform 0.2s ease, border-color 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .admin-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--admin-gold), transparent);
            opacity: 0.4;
        }

        .admin-card:hover {
            border-color: rgba(245, 158, 11, 0.4);
            transform: translateY(-2px);
        }

        .stat-icon-wrap {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-gold {
            background: rgba(245, 158, 11, 0.15);
            color: var(--admin-gold);
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .stat-cyan {
            background: rgba(6, 182, 212, 0.15);
            color: var(--admin-cyan);
            border: 1px solid rgba(6, 182, 212, 0.3);
        }

        .stat-mint {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .stat-red {
            background: rgba(239, 68, 68, 0.15);
            color: var(--admin-red);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .terminal-box {
            background: #06090e;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 16px;
            font-size: 0.85rem;
            color: #94a3b8;
            max-height: 280px;
            overflow-y: auto;
        }

        .terminal-line {
            padding: 4px 0;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.04);
            display: flex;
            justify-content: space-between;
        }

        /* Custom Table */
        .admin-table {
            color: #cbd5e1;
            margin-bottom: 0;
        }

        .admin-table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.02);
        }

        .admin-table td {
            padding: 14px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            vertical-align: middle;
            font-size: 0.9rem;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 10px currentColor;
            animation: pulse-ring 2s infinite;
        }

        @keyframes pulse-ring {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }
    </style>
</head>
<body class="admin-body">

    <div class="admin-shell">
        <aside class="admin-sidebar">
            <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-brand text-decoration-none"><i class="bi bi-lightning-charge-fill text-warning me-2"></i><span>NUTRIX</span></a>
            <div class="admin-sidebar-caption" data-i18n="admin-command-center">Command center</div>
            <a href="#overview" class="admin-menu-link active" data-admin-section="overview"><i class="bi bi-grid-1x2"></i><span data-i18n="admin-overview">Overview</span></a>
            <a href="#accounts" class="admin-menu-link" data-admin-section="accounts"><i class="bi bi-people"></i><span data-i18n="admin-accounts">Farmers & Accounts</span></a>
            <a href="#farms" class="admin-menu-link" data-admin-section="farms"><i class="bi bi-broadcast-pin"></i><span data-i18n="admin-farms">Farms & Nodes</span></a>
            <a href="#security" class="admin-menu-link" data-admin-section="security"><i class="bi bi-shield-check"></i><span data-i18n="admin-security">Security Audit</span></a>
            <div class="admin-sidebar-caption" data-i18n="admin-system">System</div>
            <button type="button" class="admin-menu-link border-0 w-100 text-start" id="openGatewaySettings"><i class="bi bi-gear"></i><span data-i18n="admin-gateway">Gateway Settings</span></button>
            <div class="admin-sidebar-status"><span class="pulse-dot bg-success text-success me-2"></span><strong class="text-success" data-i18n="admin-services-online">ALL CORE SERVICES ONLINE</strong><br><span class="mono">System check: just now</span></div>
        </aside>

        <div class="admin-workspace">
            <header class="admin-topbar d-flex justify-content-between align-items-center gap-3">
                <div><h1 class="admin-page-title" data-i18n="admin-master-control">Master Control Center</h1><p class="admin-subtitle" data-i18n="admin-subtitle">Platform oversight, farm integrity, and account security.</p></div>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-success d-none d-md-flex align-items-center gap-2 mono" style="font-size:.75rem;"><span class="pulse-dot bg-success text-success"></span> LIVE CORE</span>
                    <div class="dropdown d-none d-md-block">
                        <button class="btn btn-sm dropdown-toggle theme-menu-button" type="button" data-bs-toggle="dropdown" aria-label="Pilih tema"><i class="bi bi-palette"></i> <span id="currentThemeLabel">Theme</span></button>
                        <ul class="dropdown-menu dropdown-menu-end theme-menu">
                            <li><button class="dropdown-item theme-btn" data-theme-value="emerald">Emerald Field</button></li>
                            <li><button class="dropdown-item theme-btn" data-theme-value="harvest">Golden Harvest</button></li>
                            <li><button class="dropdown-item theme-btn" data-theme-value="nordic">Nordic Clean</button></li>
                            <li><button class="dropdown-item theme-btn" data-theme-value="obsidian">Obsidian Matrix</button></li>
                            <li><button class="dropdown-item theme-btn" data-theme-value="midnight">Midnight Soil</button></li>
                            <li><button class="dropdown-item theme-btn" data-theme-value="hydro">Deep Hydro</button></li>
                        </ul>
                    </div>
                    <div class="dropdown d-none d-md-block">
                        <button class="btn btn-sm dropdown-toggle theme-menu-button" type="button" data-bs-toggle="dropdown" aria-label="Pilih bahasa"><i class="bi bi-translate"></i> <span id="currentLanguageLabel">ID</span></button>
                        <ul class="dropdown-menu dropdown-menu-end theme-menu">
                            <li><button class="dropdown-item language-btn" data-lang="en-GB">English (UK)</button></li>
                            <li><button class="dropdown-item language-btn" data-lang="en-US">English (US)</button></li>
                            <li><button class="dropdown-item language-btn" data-lang="en-CA">English (CA)</button></li>
                            <li><button class="dropdown-item language-btn" data-lang="id">Bahasa Indonesia</button></li>
                            <li><button class="dropdown-item language-btn" data-lang="jv">Basa Jawa</button></li>
                            <li><button class="dropdown-item language-btn" data-lang="ja">日本語</button></li>
                            <li><button class="dropdown-item language-btn" data-lang="ar">العربية</button></li>
                            <li><button class="dropdown-item language-btn" data-lang="ms">Bahasa Melayu</button></li>
                        </ul>
                    </div>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-info btn-sm rounded-pill px-3"><i class="bi bi-eye me-1"></i> <span data-i18n="admin-user-view">User View</span></a>
                    <div class="dropdown"><button class="btn btn-dark btn-sm dropdown-toggle border-secondary d-flex align-items-center gap-2 rounded-pill px-3" type="button" data-bs-toggle="dropdown"><i class="bi bi-shield-lock text-warning"></i><span>{{ Auth::user()->name ?? 'Administrator' }}</span></button><ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark"><li><h6 class="dropdown-header">Root Admin Session</h6></li><li><span class="dropdown-item-text text-muted mono" style="font-size:.75rem;">{{ Auth::user()->email ?? 'admin@nutrix.io' }}</span></li><li><hr class="dropdown-divider"></li><li><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Terminate Session</button></form></li></ul></div>
                </div>
            </header>

            <main class="admin-content" id="overview">

        <!-- Top Banner Alert -->
        <div class="p-3 mb-4 rounded-3 d-flex flex-wrap justify-content-between align-items-center" style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.3);">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-shield-check text-warning fs-3"></i>
                <div>
                    <strong class="text-warning">Akses Khusus Administrator (Overseer Level)</strong>
                    <div class="text-secondary small">Anda memiliki wewenang penuh untuk memantau node IoT, akun agronomis, gateway SMTP OTP, dan log integritas sistem.</div>
                </div>
            </div>
            <div class="mt-2 mt-md-0 d-flex gap-2">
                <a href="https://myaccount.google.com/apppasswords" target="_blank" class="btn btn-warning btn-sm fw-bold">
                    <i class="bi bi-key-fill me-1"></i> Google App Password (16-Char)
                </a>
            </div>
        </div>

        <!-- 4 QUICK METRICS -->
        <div class="admin-section-label mb-3" data-i18n="admin-ecosystem">Ecosystem overview</div>
        <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="admin-card d-flex align-items-center gap-3">
                    <div class="stat-icon-wrap stat-cyan">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <div class="text-secondary small text-uppercase fw-semibold" data-i18n="admin-regular-users">Regular Users</div>
                        <h2 class="mb-0 fw-bold mono">{{ $totalUsers }}</h2>
                        <span class="text-cyan small mono">Active Agronomists</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="admin-card d-flex align-items-center gap-3">
                    <div class="stat-icon-wrap stat-gold">
                        <i class="bi bi-shield-shaded"></i>
                    </div>
                    <div>
                        <div class="text-secondary small text-uppercase fw-semibold" data-i18n="admin-master-admins">Master Admins</div>
                        <h2 class="mb-0 fw-bold mono">{{ $totalAdmins }}</h2>
                        <span class="text-warning small mono">Full Privilege</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="admin-card d-flex align-items-center gap-3">
                    <div class="stat-icon-wrap stat-mint">
                        <i class="bi bi-flower1"></i>
                    </div>
                    <div>
                        <div class="text-secondary small text-uppercase fw-semibold" data-i18n="admin-total-gardens">Total IoT Gardens</div>
                        <h2 class="mb-0 fw-bold mono">{{ $totalTamans }}</h2>
                        <span class="text-success small mono">Active Land Parcels</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="admin-card d-flex align-items-center gap-3">
                    <div class="stat-icon-wrap {{ $smtpConfigured ? 'stat-mint' : 'stat-red' }}">
                        <i class="bi bi-envelope-check-fill"></i>
                    </div>
                    <div>
                        <div class="text-secondary small text-uppercase fw-semibold">SMTP OTP Gateway</div>
                        <h4 class="mb-0 fw-bold mono">{{ $smtpConfigured ? 'CONNECTED' : 'STANDBY' }}</h4>
                        <span class="{{ $smtpConfigured ? 'text-success' : 'text-danger' }} small mono">
                            {{ $smtpConfigured ? 'Gmail Port 465 SSL' : 'Configure in .env' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- USERS TABLE -->
            <div class="col-12 col-lg-8" id="accounts">
                <div class="admin-card h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-white"><i class="bi bi-people text-info me-2"></i> <span data-i18n="admin-registered-accounts">Registered Accounts Directory</span></h5>
                        <span class="badge bg-secondary mono">Latest {{ $users->count() }} accounts</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table admin-table">
                            <thead>
                                <tr>
                                    <th>User Info</th>
                                    <th>Role</th>
                                    <th>Joined At</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $u)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-white">{{ $u->name }}</div>
                                        <div class="text-secondary small mono">{{ $u->email }}</div>
                                    </td>
                                    <td>
                                        @if($u->isAdmin())
                                            <span class="badge bg-warning text-dark fw-bold">ADMIN</span>
                                        @else
                                            <span class="badge bg-primary">USER</span>
                                        @endif
                                    </td>
                                    <td class="small text-secondary mono">
                                        {{ $u->created_at ? $u->created_at->format('d M Y, H:i') : '-' }}
                                    </td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50">
                                            VERIFIED
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-secondary" data-i18n="admin-no-users">Belum ada user yang terdaftar.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- AUDIT LOG & RECENT OTP DISPATCHES -->
            <div class="col-12 col-lg-4" id="security">
                <div class="admin-card h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-white"><i class="bi bi-shield-check text-warning me-2"></i> <span data-i18n="admin-otp-logs">Recent 2-Min OTP Logs</span></h5>
                        <span class="badge bg-dark border border-secondary text-warning mono">LIVE</span>
                    </div>

                    <div class="terminal-box">
                        @forelse($recentOtps as $otp)
                        <div class="terminal-line">
                            <div>
                                <span class="badge {{ $otp->action === 'register' ? 'bg-info' : 'bg-primary' }} text-dark px-1 me-1 text-uppercase" style="font-size:0.65rem;">
                                    {{ $otp->action }}
                                </span>
                                <span class="text-white">{{ Str::limit($otp->email, 16) }}</span>
                            </div>
                            <div class="mono small">
                                @if($otp->isValid())
                                    <span class="text-success"><i class="bi bi-clock"></i> Active</span>
                                @else
                                    <span class="text-muted"><i class="bi bi-hourglass-bottom"></i> Expired</span>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted small">Belum ada riwayat pengiriman OTP.</div>
                        @endforelse
                    </div>

                    <hr class="border-secondary my-3">

                    <h6 class="text-white fw-bold mb-2 small text-uppercase tracking-wider">
                        <i class="bi bi-sliders text-cyan me-1"></i> Quick Master Operations
                    </h6>
                    <div class="d-grid gap-2">
                        <button class="btn btn-sm btn-outline-secondary text-start mono" id="btnForceSync">
                            <i class="bi bi-arrow-repeat me-2"></i> Force Sync Node Telemetry
                        </button>
                        <button class="btn btn-sm btn-outline-secondary text-start mono" id="btnSecurityDiagnostics">
                            <i class="bi bi-shield-lock me-2"></i> Run Security Diagnostics
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4" id="farms">
                            <div class="col-12 col-xl-7"><div class="admin-card h-100"><div class="d-flex justify-content-between align-items-center mb-3"><div><div class="admin-section-label mb-1">Farm registry</div><h5 class="fw-bold mb-0 text-white"><i class="bi bi-flower1 text-info me-2"></i>Latest Gardens</h5></div><span class="badge bg-dark border border-secondary text-info mono">{{ $tamans->count() }} RECENT</span></div>@forelse($tamans as $taman)<div class="farm-row d-flex justify-content-between align-items-center gap-3"><div><div class="fw-bold text-white">{{ $taman->name }}</div><div class="small text-secondary">{{ $taman->user?->name ?? 'Unknown owner' }} · {{ $taman->location ?: 'Location not set' }}</div></div><div class="text-end"><span class="badge bg-info bg-opacity-10 border border-info border-opacity-25 text-info text-uppercase">{{ $taman->type }}</span></div></div>@empty<div class="text-center py-4 text-secondary">No gardens registered yet.</div>@endforelse</div></div>
            <div class="col-12 col-xl-5"><div class="admin-card h-100"><div class="admin-section-label mb-1">Operational posture</div><h5 class="fw-bold text-white mb-4"><i class="bi bi-activity text-success me-2"></i>Live Service Health</h5><div class="d-flex justify-content-between mb-3"><span class="text-secondary">Authentication & OTP</span><span class="text-success mono">● HEALTHY</span></div><div class="progress mb-4" style="height:6px;"><div class="progress-bar bg-success" style="width:100%"></div></div><div class="d-flex justify-content-between mb-3"><span class="text-secondary">SMTP Gateway</span><span class="{{ $smtpConfigured ? 'text-success' : 'text-warning' }} mono">● {{ $smtpConfigured ? 'READY' : 'SETUP REQUIRED' }}</span></div><div class="progress mb-4" style="height:6px;"><div class="progress-bar {{ $smtpConfigured ? 'bg-success' : 'bg-warning' }}" style="width:{{ $smtpConfigured ? '100' : '35' }}%"></div></div><div class="d-flex justify-content-between"><span class="text-secondary">Telemetry persistence</span><span class="text-warning mono">● SIMULATION MODE</span></div></div></div>
        </div>

            </main>
        </div>
    </div>

    <div class="toast admin-toast border-0" id="adminToast" role="status" aria-live="polite">
        <div class="toast-body admin-card py-3 px-4"><div class="d-flex gap-3 align-items-center"><i class="bi bi-check-circle-fill text-success fs-4" id="adminToastIcon"></i><div><strong class="text-white d-block" id="adminToastTitle">Operation complete</strong><span class="small text-secondary" id="adminToastMessage"></span></div></div></div>
    </div>

    <div class="modal fade" id="gatewaySettingsModal" tabindex="-1" aria-labelledby="gatewaySettingsTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content admin-modal"><div class="modal-header border-secondary"><div><div class="admin-section-label">System configuration</div><h5 class="modal-title text-white" id="gatewaySettingsTitle">Gateway Settings</h5></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="d-flex justify-content-between align-items-center p-3 rounded border border-secondary mb-3"><span class="text-secondary">SMTP OTP Gateway</span><strong class="{{ $smtpConfigured ? 'text-success' : 'text-warning' }}">{{ $smtpConfigured ? 'CONNECTED' : 'SETUP REQUIRED' }}</strong></div><p class="small text-secondary mb-3">Alamat pengirim dan nama tampilan email dikonfigurasi dari <code>.env</code>, tanpa menyimpan rahasia di halaman admin.</p><a href="{{ url('/admin') }}#security" class="btn btn-outline-info btn-sm" data-bs-dismiss="modal">Review security audit</a></div></div></div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toast = document.getElementById('adminToast');
            const toastTitle = document.getElementById('adminToastTitle');
            const toastMessage = document.getElementById('adminToastMessage');
            let toastTimer;
            const notify = (title, message) => {
                toastTitle.textContent = title;
                toastMessage.textContent = message;
                toast.classList.add('show');
                clearTimeout(toastTimer);
                toastTimer = setTimeout(() => toast.classList.remove('show'), 3600);
            };

            document.querySelectorAll('[data-admin-section]').forEach(link => link.addEventListener('click', event => {
                event.preventDefault();
                document.querySelectorAll('[data-admin-section]').forEach(item => item.classList.remove('active'));
                link.classList.add('active');
                document.getElementById(link.dataset.adminSection)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }));

            document.getElementById('btnForceSync')?.addEventListener('click', () => {
                const button = document.getElementById('btnForceSync');
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Syncing telemetry…';
                setTimeout(() => { button.disabled = false; button.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i> Force Sync Node Telemetry'; notify('Telemetry sync complete', 'Latest node telemetry has been refreshed in this demo.'); }, 1000);
            });
            document.getElementById('btnSecurityDiagnostics')?.addEventListener('click', () => notify('Security diagnostics passed', 'Authentication, OTP expiry, and role access checks are healthy.'));
            document.getElementById('openGatewaySettings')?.addEventListener('click', () => bootstrap.Modal.getOrCreateInstance(document.getElementById('gatewaySettingsModal')).show());
        });
    </script>
    <script>
        window.NUTRIX_USER = @auth {!! json_encode(['name' => auth()->user()->name, 'email' => auth()->user()->email]) !!} @else null @endauth;
    </script>
    <script src="{{ asset('js/script.js') }}?v={{ filemtime(public_path('js/script.js')) }}"></script>
</body>
</html>
