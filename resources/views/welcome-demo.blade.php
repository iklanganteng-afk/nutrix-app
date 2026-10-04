<!DOCTYPE html>
<html lang="id" data-theme="emerald">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NUTRIX - Smart Farming Masa Depan</title>
    
    <!-- Tipografi & Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;900&family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Style -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    <!-- Anti-FOUC: Memastikan tema dari LocalStorage diload sebelum HTML render -->
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

    <style>
        /* CSS Khusus Welcome Page yang adaptif dengan Style.css */
        .welcome-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px clamp(1.25rem, 4vw, 8%);
            background: color-mix(in srgb, var(--bg-primary) 82%, transparent);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1100;
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border-bottom: 1px solid var(--border-subtle);
        }

        .welcome-brand {
            font-size: clamp(1.5rem, 2vw, 2rem);
            font-weight: 900;
            color: var(--text-title);
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: -0.5px;
        }

        .welcome-nav-link {
            color: var(--text-muted);
            opacity: 0.9;
            transition: color 0.2s ease, opacity 0.2s ease;
        }

        .welcome-nav-link:hover {
            color: var(--color-accent-highlight);
            opacity: 1;
        }

        .theme-menu-button {
            border: 1px solid var(--border-subtle);
            border-radius: 50px;
            padding: 8px 16px;
            color: var(--text-body);
            font-weight: 600;
            background: var(--bg-secondary);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .theme-menu-button:hover,
        .theme-menu-button:focus {
            border-color: var(--border-glow);
            box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.12);
        }

        .theme-menu {
            background: var(--bg-secondary);
            border: 1px solid var(--border-subtle);
            border-radius: 16px;
            overflow: hidden;
            padding: 0;
            box-shadow: var(--card-shadow);
        }

        .theme-menu .dropdown-header {
            font-size: 0.72rem;
            text-transform: uppercase;
            color: var(--text-muted);
            background: var(--bg-primary);
            padding: 8px 16px;
        }

        .theme-menu .dropdown-item {
            color: var(--text-body);
            background: transparent;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .theme-menu .dropdown-item:hover,
        .theme-menu .dropdown-item:focus {
            background: var(--bg-primary);
            color: var(--text-title);
        }

        .theme-menu .dropdown-divider {
            border-color: var(--border-subtle);
        }

        .calc-section {
            padding: clamp(4.5rem, 10vw, 7rem) clamp(1.25rem, 5vw, 8%) 4rem;
            background: var(--bg-secondary);
            text-align: center;
        }

        .calc-box {
            max-width: 820px;
            margin: 0 auto;
            background: var(--bg-primary);
            padding: clamp(1.5rem, 4vw, 3.5rem);
            border-radius: 32px;
            border: 1px solid var(--border-subtle);
            box-shadow: var(--card-shadow);
        }

        .calc-box h2 {
            font-size: clamp(2rem, 4vw, 2.8rem);
            font-weight: 800;
            margin-bottom: 15px;
            letter-spacing: -1px;
            color: var(--text-title);
        }

        .calc-box p {
            color: var(--text-muted);
            margin-bottom: clamp(1.5rem, 4vw, 2.5rem);
        }

        .slider-container {
            margin-bottom: clamp(1.5rem, 4vw, 2.5rem);
        }

        .range-slider {
            width: 100%;
            appearance: none;
            height: 8px;
            background: linear-gradient(90deg, var(--color-accent-highlight) 0%, var(--color-accent-highlight) var(--slider-fill, 50%), var(--border-subtle) var(--slider-fill, 50%), var(--border-subtle) 100%);
            border-radius: 10px;
            outline: none;
        }

        /* Khusus Bahasa Arab (RTL): indikator slider berjalan dari kanan ke kiri */
        [dir="rtl"] .range-slider,
        [lang="ar"] .range-slider {
            background: linear-gradient(270deg, var(--color-accent-highlight) 0%, var(--color-accent-highlight) var(--slider-fill, 50%), var(--border-subtle) var(--slider-fill, 50%), var(--border-subtle) 100%);
        }

        .range-slider::-webkit-slider-thumb {
            appearance: none;
            width: 24px;
            height: 24px;
            background: var(--color-accent-highlight);
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 4px 10px var(--glow-ambient);
            border: 2px solid rgba(255, 255, 255, 0.7);
        }

        .range-slider::-moz-range-thumb {
            width: 24px;
            height: 24px;
            border: 2px solid rgba(255, 255, 255, 0.7);
            background: var(--color-accent-highlight);
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 4px 10px var(--glow-ambient);
        }

        .result-box {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: clamp(0.75rem, 2vw, 1.5rem);
            background: var(--bg-secondary);
            padding: clamp(1.25rem, 2.5vw, 2rem);
            border-radius: 24px;
            border: 1px solid var(--border-subtle);
            align-items: center;
        }

        .result-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            min-width: 0;
            padding: 0.25rem;
        }

        .result-item h3 {
            font-size: clamp(0.85rem, 1.2vw, 1rem);
            color: var(--text-muted);
            margin-bottom: 8px;
            font-weight: 600;
            white-space: nowrap;
        }

        .result-val {
            font-size: clamp(1.4rem, 2.6vw, 2.4rem);
            font-weight: 900;
            color: var(--text-title);
            white-space: nowrap;
            letter-spacing: -0.5px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            line-height: 1.15;
            width: 100%;
        }

        .result-item .highlight-val {
            color: var(--color-accent-highlight);
        }

        @media (max-width: 767.98px) {
            .welcome-nav {
                padding-top: 18px;
                gap: 0.75rem;
            }

            .result-box {
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }

            .result-item {
                text-align: center;
            }
        }

        /* Sign In ghost button in welcome nav */
        .welcome-nav-signin {
            border: 1px solid var(--border-subtle);
            background: transparent;
            color: var(--text-body);
            border-radius: 50px;
            padding: 8px 18px;
            font-weight: 600;
            font-size: 0.875rem;
            transition: border-color 0.2s ease, color 0.2s ease, background 0.2s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .welcome-nav-signin:hover {
            border-color: var(--color-accent-highlight);
            color: var(--color-accent-highlight);
            background: color-mix(in srgb, var(--color-accent-highlight) 8%, transparent);
        }

        /* Auth Modal Styles */
        .welcome-auth-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.75);
            backdrop-filter: blur(6px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            overflow-y: auto;
            overscroll-behavior: contain;
        }
        .welcome-auth-overlay.active {
            display: flex;
        }
        .welcome-auth-box {
            background: var(--bg-secondary);
            border: 1px solid var(--border-subtle);
            border-radius: 24px;
            padding: 2rem;
            width: 100%;
            max-width: 420px;
            max-height: calc(100vh - 2rem);
            max-height: calc(100dvh - 2rem);
            overflow-y: auto;
            box-sizing: border-box;
            position: relative;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            animation: authSlideIn 0.3s ease;
        }

        @media (max-width: 991.98px) {
            .welcome-nav {
                flex-wrap: wrap;
                gap: 0.75rem;
                padding-top: 18px;
                padding-bottom: 18px;
            }

            .welcome-nav > .d-flex.align-items-center.gap-3 {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
            }

            .split-hero-inner {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .split-hero-copy,
            .split-hero-visual {
                max-width: 640px;
                margin: 0 auto;
            }

            .split-hero-visual {
                margin-top: 1rem;
            }

            .split-hero-sensor-card {
                width: min(100%, 420px);
                margin: 0 auto;
            }
        }

        @media (max-width: 767.98px) {
            .welcome-brand {
                width: 100%;
                justify-content: center;
            }

            .welcome-nav {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .welcome-nav > .d-flex.align-items-center.gap-3 {
                gap: 0.75rem;
            }

            .welcome-nav-signin,
            #welcomeSignUpBtn {
                flex: 1 1 150px;
                justify-content: center;
            }

            .split-hero-cta {
                width: min(100%, 260px);
            }

            .hero-kinetic-text {
                font-size: clamp(2.6rem, 10vw, 4rem);
            }

            .welcome-auth-box {
                width: min(92vw, 420px);
                max-height: calc(100vh - 1.5rem);
                max-height: calc(100dvh - 1.5rem);
                padding: 1.5rem 1rem 1.2rem;
                border-radius: 18px;
            }

            .auth-input {
                font-size: 0.92rem;
            }

            #w-otpCode {
                letter-spacing: 6px;
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .welcome-nav {
                padding-top: 14px;
                padding-bottom: 14px;
            }

            .auth-tab-group {
                gap: 2px;
            }

            .auth-tab {
                padding: 8px 10px;
                font-size: 0.78rem;
            }

            .welcome-auth-box {
                width: min(94vw, 360px);
            }

            .split-hero-data-row {
                font-size: 0.76rem;
            }

            .theme-menu-button {
                padding: 7px 10px;
            }
        }
        @keyframes authSlideIn {
            from { opacity: 0; transform: translateY(20px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .welcome-auth-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--bg-primary);
            border: 1px solid var(--border-subtle);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-muted);
            transition: color 0.2s;
        }
        .welcome-auth-close:hover { color: var(--text-title); }
        .auth-tab-group {
            display: flex;
            gap: 4px;
            background: var(--bg-primary);
            border-radius: 50px;
            padding: 4px;
            margin-bottom: 1.5rem;
        }
        .auth-tab {
            flex: 1;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 50px;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease;
        }
        .auth-tab.active {
            background: var(--color-accent-highlight);
            color: #fff;
        }
        .auth-input-group label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
            display: block;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .auth-input {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg-primary);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            color: var(--text-body);
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s ease;
        }
        .auth-input:focus {
            border-color: var(--color-accent-highlight);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-accent-highlight) 15%, transparent);
        }
        .auth-form { display: none; }
        .auth-form.active { display: block; }
        .auth-switch { color: var(--color-accent-highlight); font-weight: 600; text-decoration: none; }
        .auth-switch:hover { text-decoration: underline; }
        .auth-error-msg {
            display: none;
            color: #ef4444;
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }
        .otp-timer-badge {
            background: rgba(239,68,68,0.15);
            color: #ef4444;
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 8px;
            padding: 4px 12px;
            font-size: 1rem;
            font-family: monospace;
            font-weight: 700;
        }
    <style>
        /* === DIRECTOR PANEL (LEFT SIDEBAR FOR CONTENT CREATOR) === */
        :root {
            --demo-dock-width: 320px;
        }

        /* Left boundary guide line for easy video editing/cropping */
        .crop-guide-line {
            position: fixed;
            top: 0;
            bottom: 0;
            left: var(--demo-dock-width);
            width: 3px;
            background: linear-gradient(to bottom, #10b981, #3b82f6, #ec4899);
            box-shadow: 0 0 12px rgba(16, 185, 129, 0.7);
            z-index: 99999;
            pointer-events: none;
        }

        .crop-guide-tag {
            position: absolute;
            top: 14px;
            left: 8px;
            background: rgba(16, 185, 129, 0.9);
            color: #fff;
            font-size: 10px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 6px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        /* Left Floating Director Dock */
        .demo-director-dock {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--demo-dock-width);
            height: 100vh;
            background: rgba(10, 15, 26, 0.94);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.12);
            z-index: 99998;
            display: flex;
            flex-direction: column;
            box-shadow: 10px 0 35px rgba(0,0,0,0.6);
            overflow: hidden;
            font-family: 'Outfit', sans-serif;
            color: #f1f5f9;
        }

        .dock-header {
            padding: 16px 18px;
            background: rgba(15, 23, 42, 0.98);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .dock-title {
            font-size: 14px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #fff;
        }

        .dock-content {
            padding: 14px;
            overflow-y: auto;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .dock-label {
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
        }

        .dock-btn-group {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
        }

        .dock-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
            padding: 8px 10px;
            border-radius: 8px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }

        .dock-btn:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.25);
            color: #fff;
        }

        .dock-btn.active {
            background: rgba(16, 185, 129, 0.2);
            border-color: #10b981;
            color: #34d399;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.3);
        }

        .dock-chain-btn {
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 12.5px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .dock-chain-btn.running {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        /* Shift the main welcome page to the right by dock width so nothing gets clipped */
        body.welcome-page {
            margin-left: var(--demo-dock-width) !important;
            width: calc(100% - var(--demo-dock-width)) !important;
            transition: margin-left 0.3s ease, width 0.3s ease;
        }

        /* Navbar shifted accordingly */
        body.welcome-page .welcome-nav {
            left: var(--demo-dock-width) !important;
            width: calc(100% - var(--demo-dock-width)) !important;
        }
    </style>
</head>
<body class="welcome-page">

    <!-- CROP BOUNDARY GUIDE LINE (Batas untuk crop video saat edit) -->
    <div class="crop-guide-line">
        <span class="crop-guide-tag">✂️ BATAS CROP VIDEO</span>
    </div>

    <!-- LEFT FLOATING DIRECTOR DOCK -->
    <aside class="demo-director-dock" id="demoDirectorDock">
        <div class="dock-header">
            <div class="dock-title">
                <span>🎬</span> Nutrix Director Panel
            </div>
            <a href="{{ route('welcome') }}" style="font-size: 11px; color: #10b981; text-decoration: none; font-weight: 700;">Ori View ➜</a>
        </div>

        <div class="dock-content">
            
            <!-- Quick Status -->
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); padding: 10px; border-radius: 8px;">
                <div style="font-size: 10px; font-weight: 700; color: #34d399; text-transform: uppercase; margin-bottom: 2px;">STATUS AKTIF MASKOT:</div>
                <div id="dockActiveState" style="font-size: 14px; font-weight: 800; color: #fff;">CALM</div>
                <div style="font-size: 10.5px; color: #94a3b8; margin-top: 4px;">
                    💡 Bagian kiri ini bisa kamu <strong>crop habis</strong> saat editing video!
                </div>
            </div>

            <!-- Auto Chain Sequence -->
            <div>
                <div class="dock-label">
                    <span>🎬 AUTO CHAIN PLAYER</span>
                    <span style="color: #34d399;">DEMO REEL</span>
                </div>
                <button type="button" class="dock-chain-btn" id="dockBtnPlayChain">
                    <span id="dockChainIcon">▶</span>
                    <span id="dockChainText">Play All Animations Sequence</span>
                </button>
            </div>

            <!-- Capoo Cartoon Gags -->
            <div>
                <div class="dock-label">
                    <span>🐱 BUGCAT CAPOO GAGS</span>
                    <span style="color: #f472b6;">14 REAKSI</span>
                </div>
                <div class="dock-btn-group">
                    <button type="button" class="dock-btn" data-anim="dance">💃 Dance</button>
                    <button type="button" class="dock-btn" data-anim="wiggle">🍑 Wiggle</button>
                    <button type="button" class="dock-btn" data-anim="melt">🫠 Melt</button>
                    <button type="button" class="dock-btn" data-anim="dizzy">💫 Dizzy</button>
                    <button type="button" class="dock-btn" data-anim="sneeze">🤧 Sneeze</button>
                    <button type="button" class="dock-btn" data-anim="dead">👻 Ghost</button>
                    <button type="button" class="dock-btn" data-anim="panic">😰 Panic</button>
                    <button type="button" class="dock-btn" data-anim="confused">❓ Confused</button>
                    <button type="button" class="dock-btn" data-anim="eureka">💡 Eureka</button>
                    <button type="button" class="dock-btn" data-anim="king">👑 King</button>
                    <button type="button" class="dock-btn" data-anim="balloon">🎈 Balloon</button>
                    <button type="button" class="dock-btn" data-anim="flower">🌸 Flower</button>
                    <button type="button" class="dock-btn" data-anim="faint">😵 Faint</button>
                    <button type="button" class="dock-btn" data-anim="hiccup">🫢 Hiccup</button>
                </div>
            </div>

            <!-- Classic Gags -->
            <div>
                <div class="dock-label">
                    <span>🎭 CLASSIC CARTOON</span>
                    <span style="color: #60a5fa;">8 REAKSI</span>
                </div>
                <div class="dock-btn-group">
                    <button type="button" class="dock-btn" data-anim="shock">⚡ Shock</button>
                    <button type="button" class="dock-btn" data-anim="sleepy">💤 Sleepy</button>
                    <button type="button" class="dock-btn" data-anim="cat">😺 Cat :3</button>
                    <button type="button" class="dock-btn" data-anim="rolling">🌀 Rolling</button>
                    <button type="button" class="dock-btn" data-anim="eating">🍪 Eating</button>
                    <button type="button" class="dock-btn" data-anim="anvil">🔨 Anvil 100t</button>
                    <button type="button" class="dock-btn" data-anim="cool">🕶️ Thug Life</button>
                    <button type="button" class="dock-btn" data-anim="love">💖 Heart Eyes</button>
                </div>
            </div>

            <!-- Base Emotions -->
            <div>
                <div class="dock-label">
                    <span>😊 BASE EMOTIONS</span>
                </div>
                <div class="dock-btn-group">
                    <button type="button" class="dock-btn active" data-anim="calm">🌿 Calm</button>
                    <button type="button" class="dock-btn" data-anim="happy">😄 Happy</button>
                    <button type="button" class="dock-btn" data-anim="thinking">🤔 Thinking</button>
                    <button type="button" class="dock-btn" data-anim="sad">😢 Sad</button>
                    <button type="button" class="dock-btn" data-anim="explaining">🗣️ Explaining</button>
                    <button type="button" class="dock-btn" data-anim="poke">😲 Poke</button>
                </div>
            </div>

            <!-- Reset -->
            <button type="button" class="dock-btn" id="dockBtnReset" style="width: 100%; justify-content: center; background: rgba(255,255,255,0.08);">
                🔄 Reset ke Calm Neutral
            </button>

        </div>
    </aside>

    <div class="scroll-progress" id="scrollProgress"></div>

    <!-- NAVBAR DENGAN THEME SWITCHER -->
    <nav class="welcome-nav">
        <div class="welcome-brand">🌱 NUTRIX</div>
        <div class="d-none d-md-flex align-items-center gap-4">
            <a href="#architecture" class="text-decoration-none fw-semibold welcome-nav-link" data-i18n="nav-architecture">Architecture</a>
            <a href="#team" class="text-decoration-none fw-semibold welcome-nav-link" data-i18n="nav-developers">Developers</a>
            <a href="{{ url('/landingpage') }}" class="text-decoration-none fw-semibold welcome-nav-link" data-i18n="nav-workspace">Workspace</a>
        </div>
        <div class="d-flex align-items-center gap-3">
            <!-- Theme Switcher Dropdown -->
            <div class="dropdown">
                <button class="btn btn-sm dropdown-toggle d-flex align-items-center gap-2 theme-menu-button" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-palette"></i> <span id="currentThemeLabel">Theme</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow theme-menu">
                    <li class="dropdown-header fw-bold" data-i18n="ui-theme-light">Light Mode</li>
                    <li><button class="dropdown-item py-2 px-3 fw-semibold theme-btn" data-theme-value="emerald"><i class="bi bi-tree-fill me-2" style="color: var(--color-accent-highlight);"></i> Emerald Field</button></li>
                    <li><button class="dropdown-item py-2 px-3 fw-semibold theme-btn" data-theme-value="harvest"><i class="bi bi-sun-fill me-2" style="color: var(--color-amber);"></i> Golden Harvest</button></li>
                    <li><button class="dropdown-item py-2 px-3 fw-semibold theme-btn" data-theme-value="nordic"><i class="bi bi-snow me-2" style="color: var(--color-accent-2);"></i> Nordic Clean</button></li>
                    <li><hr class="dropdown-divider m-0"></li>
                    <li class="dropdown-header fw-bold" data-i18n="ui-theme-dark">Dark Mode</li>
                    <li><button class="dropdown-item py-2 px-3 fw-semibold theme-btn" data-theme-value="obsidian"><i class="bi bi-moon-stars-fill me-2" style="color: var(--color-accent-highlight);"></i> Obsidian Matrix</button></li>
                    <li><button class="dropdown-item py-2 px-3 fw-semibold theme-btn" data-theme-value="midnight"><i class="bi bi-fire me-2" style="color: var(--color-amber);"></i> Midnight Soil</button></li>
                    <li><button class="dropdown-item py-2 px-3 fw-semibold theme-btn" data-theme-value="hydro"><i class="bi bi-droplet-fill me-2" style="color: var(--color-accent-2);"></i> Deep Hydro</button></li>
                </ul>
            </div>
            <div class="dropdown">
                <button class="btn btn-sm dropdown-toggle d-flex align-items-center gap-2 theme-menu-button" type="button" data-bs-toggle="dropdown" aria-label="Choose language">
                    <i class="bi bi-translate"></i> <span id="currentLanguageLabel">ID</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow theme-menu">
                    <li><button type="button" class="dropdown-item py-2 px-3 fw-semibold language-btn" data-lang="en-GB">English (UK)</button></li>
                    <li><button type="button" class="dropdown-item py-2 px-3 fw-semibold language-btn" data-lang="en-US">English (US)</button></li>
                    <li><button type="button" class="dropdown-item py-2 px-3 fw-semibold language-btn" data-lang="en-CA">English (CA)</button></li>
                    <li><button type="button" class="dropdown-item py-2 px-3 fw-semibold language-btn" data-lang="id">Bahasa Indonesia</button></li>
                    <li><button type="button" class="dropdown-item py-2 px-3 fw-semibold language-btn" data-lang="jv">Basa Jawa</button></li>
                    <li><button type="button" class="dropdown-item py-2 px-3 fw-semibold language-btn" data-lang="ja">日本語</button></li>
                    <li><button type="button" class="dropdown-item py-2 px-3 fw-semibold language-btn" data-lang="ar">العربية</button></li>
                    <li><button type="button" class="dropdown-item py-2 px-3 fw-semibold language-btn" data-lang="ms">Bahasa Melayu</button></li>
                </ul>
            </div>
            <!-- Auth Buttons -->
            <button class="welcome-nav-signin" id="welcomeSignInBtn" onclick="openWelcomeAuth('signin')">
                <i class="bi bi-box-arrow-in-right"></i> Sign In
            </button>
            <button class="btn-connect-node" id="welcomeSignUpBtn" onclick="openWelcomeAuth('signup')">
                <i class="bi bi-person-plus"></i> Sign Up
            </button>
        </div>
    </nav>

    <!-- 1. HERO SECTION -->
    <section class="hero-wrapper split-hero" id="hero">
        <div class="split-hero-inner">
            <div class="split-hero-copy">
                <span class="split-hero-eyebrow" data-i18n="hero-live-badge">Live precision agriculture</span>
                <h1 class="hero-kinetic-text" data-i18n="hero-tagline">WHERE<br>YOUR CROPS<br>THRIVE</h1>
                <p class="split-hero-description" data-i18n="hero-description">Smart soil monitoring that turns field data into confident growing decisions.</p>
                <button class="btn-connect-node split-hero-cta" data-i18n="hero-cta" onclick="openWelcomeAuth('signup')">Start Monitoring</button>
            </div>

            <div class="split-hero-visual">
                <div class="hero-center-card" id="hero-logo-container">
            <!-- SVG Vector Math Plant (Cyber-Sprout Maskot) -->
            <svg id="hero-center-logo" class="math-plant-svg" viewBox="0 0 400 500" xmlns="http://www.w3.org/2000/svg" width="320" height="400">
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
                        <feMerge>
                            <feMergeNode in="coloredBlur"/>
                            <feMergeNode in="SourceGraphic"/>
                        </feMerge>
                    </filter>
                </defs>

                <!-- Ambient Glow Circle -->
                <circle cx="200" cy="230" r="140" fill="var(--glow-ambient)"/>

                <!-- Stem (Bezier curve) -->
                <path d="M200,450 C200,400 195,350 200,280" stroke="url(#stemGrad)" stroke-width="6" fill="none" stroke-linecap="round" filter="url(#glowFilter)"/>

                <!-- Main Leaf (Left) - Bezier Geometry -->
                <path d="M200,280 C180,250 120,230 80,180 C60,155 70,120 100,100 C130,80 170,100 190,140 C195,150 198,170 200,200 Z" 
                      fill="url(#leafGrad1)" opacity="0.85" filter="url(#glowFilter)">
                    <animate attributeName="opacity" values="0.85;0.95;0.85" dur="4s" repeatCount="indefinite"/>
                </path>

                <!-- Main Leaf (Right) - Mirrored Bezier -->
                <path d="M200,280 C220,250 280,230 320,180 C340,155 330,120 300,100 C270,80 230,100 210,140 C205,150 202,170 200,200 Z" 
                      fill="url(#leafGrad2)" opacity="0.85" filter="url(#glowFilter)">
                    <animate attributeName="opacity" values="0.85;0.95;0.85" dur="4s" begin="1s" repeatCount="indefinite"/>
                </path>

                <!-- Glow particle dots (floating circuit nodes) -->
                <circle cx="100" cy="100" r="3" fill="var(--color-accent-highlight)" opacity="0.6" filter="url(#glowFilter)">
                    <animate attributeName="cy" values="100;90;100" dur="3s" repeatCount="indefinite"/>
                    <animate attributeName="opacity" values="0.6;1;0.6" dur="3s" repeatCount="indefinite"/>
                </circle>
                <circle cx="300" cy="100" r="3" fill="var(--color-accent-highlight)" opacity="0.6" filter="url(#glowFilter)">
                    <animate attributeName="cy" values="100;90;100" dur="3s" begin="1s" repeatCount="indefinite"/>
                    <animate attributeName="opacity" values="0.6;1;0.6" dur="3s" begin="1s" repeatCount="indefinite"/>
                </circle>
            </svg>
            <div class="scroll-indicator mt-5">
                <span class="mouse"><span class="wheel" style="background: var(--color-accent-highlight);"></span></span>
            </div>
        </div>
                <div class="split-hero-sensor-card">
                    <div class="split-hero-card-topline">
                        <span class="split-hero-live-dot"></span>
                        <span data-i18n="hero-live">Live</span>
                    </div>
                    <div class="split-hero-tabs" role="tablist" aria-label="Sensor preview">
                        <button type="button" class="split-hero-tab active" data-sensor="corn" aria-selected="true" data-i18n="hero-tab-corn">Corn Field</button>
                        <button type="button" class="split-hero-tab" data-sensor="greenhouse" aria-selected="false" data-i18n="hero-tab-greenhouse">Greenhouse</button>
                    </div>
                    <div class="split-hero-data-row"><span data-i18n="hero-soil-status">Soil Status</span><strong id="heroSensorStatus">Optimal</strong></div>
                    <div class="split-hero-data-row"><span data-i18n="hero-ph">pH Level</span><strong id="heroSensorPh">6.8</strong></div>
                    <div class="split-hero-data-row"><span data-i18n="hero-moisture">Soil Moisture</span><strong id="heroSensorMoisture">65%</strong></div>
                </div>
            </div>
        </div>
    </section>

    <!-- KALKULATOR EFISIENSI SECTION -->
    <section class="calc-section">
        <div class="calc-box">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 15px;">
                <h2 style="margin-bottom: 0;" data-i18n="calc-title">Kalkulator Efisiensi NUTRIX</h2>
                <div class="d-inline-flex align-items-center gap-2">
                    <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 700;" data-i18n="calc-currency">Mata Uang</span>
                    <div class="custom-select-wrapper custom-select-sm" id="currencySelectWrapper" style="min-width: 175px;">
                        <div class="custom-select-trigger" style="color: var(--text-body);">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold" style="color: var(--color-accent-highlight);" id="triggerCurrencyCode">IDR</span>
                                <span id="triggerCurrencyText" style="font-size: 0.84rem;">Rupiah</span>
                            </div>
                            <i class="bi bi-chevron-down text-muted chevron"></i>
                        </div>
                        <div class="custom-options-container">
                            <div class="custom-option selected" data-value="IDR" data-code="IDR" data-label="Rupiah">
                                <span class="fw-bold me-2" style="color: var(--color-accent-highlight);">IDR</span>
                                <span class="text-truncate">Rupiah (Indonesia)</span>
                            </div>
                            <div class="custom-option" data-value="USD" data-code="USD" data-label="Dolar AS">
                                <span class="fw-bold me-2" style="color: var(--color-accent-highlight);">USD</span>
                                <span class="text-truncate">Dolar AS</span>
                            </div>
                            <div class="custom-option" data-value="SGD" data-code="SGD" data-label="Dolar SG">
                                <span class="fw-bold me-2" style="color: var(--color-accent-highlight);">SGD</span>
                                <span class="text-truncate">Dolar Singapura</span>
                            </div>
                            <div class="custom-option" data-value="CNY" data-code="CNY" data-label="Yuan">
                                <span class="fw-bold me-2" style="color: var(--color-accent-highlight);">CNY</span>
                                <span class="text-truncate">Yuan Tiongkok</span>
                            </div>
                            <div class="custom-option" data-value="JPY" data-code="JPY" data-label="Yen">
                                <span class="fw-bold me-2" style="color: var(--color-accent-highlight);">JPY</span>
                                <span class="text-truncate">Yen Jepang</span>
                            </div>
                            <div class="custom-option" data-value="MYR" data-code="MYR" data-label="Ringgit">
                                <span class="fw-bold me-2" style="color: var(--color-accent-highlight);">MYR</span>
                                <span class="text-truncate">Ringgit Malaysia</span>
                            </div>
                            <div class="custom-option" data-value="KWD" data-code="KWD" data-label="Dinar KW">
                                <span class="fw-bold me-2" style="color: var(--color-accent-highlight);">KWD</span>
                                <span class="text-truncate">Dinar Kuwait</span>
                            </div>
                            <div class="custom-option" data-value="SAR" data-code="SAR" data-label="Riyal">
                                <span class="fw-bold me-2" style="color: var(--color-accent-highlight);">SAR</span>
                                <span class="text-truncate">Riyal Saudi</span>
                            </div>
                        </div>
                        <input type="hidden" id="currency-selector" value="IDR">
                    </div>
                </div>
            </div>
            <p data-i18n="calc-description">Geser slider untuk melihat estimasi penghematan biaya pupuk NPK dengan sistem presisi kami.</p>
            
            <div class="slider-container">
                <h3 style="margin-bottom: 20px; color: var(--text-title); font-weight: 700;"><span data-i18n="calc-land">Luas Lahan</span>: <span id="luas-lahan" style="color: var(--color-accent-highlight);">1</span> <span data-i18n="calc-hectare">Hektar</span></h3>
                <input type="range" min="1" max="50" value="1" class="range-slider" id="slider-lahan" aria-label="Luas lahan untuk kalkulator efisiensi" oninput="hitungEfisiensi()">
            </div>
            
            <div class="result-box">
                <div class="result-item">
                    <h3 data-i18n="calc-traditional">Biaya Tradisional</h3>
                    <div class="result-val" id="biaya-lama-display">Rp 4.2 Juta</div>
                </div>
                <div class="result-item">
                    <h3 data-i18n="calc-nutrix">Dengan NUTRIX</h3>
                    <div class="result-val highlight-val" id="biaya-baru-display">Rp 2.9 Juta</div>
                </div>
                <div class="result-item">
                    <h3 data-i18n="calc-savings">Penghematan</h3>
                    <div class="result-val highlight-val" id="persen-hemat">
                        <span>~30%</span>
                        <small style="font-size: 0.45em; font-weight: 700; opacity: 0.8; display: block; letter-spacing: normal;">Hemat Rp 1.3 Juta</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. STICKY STACKING CARDS (Architecture) -->
    <section class="container py-5 my-5" id="architecture">
        <div class="text-center mb-5">
            <span class="badge-web3 mb-2" style="background: var(--bg-primary); color: var(--color-accent-highlight); border-color: var(--border-subtle);" data-i18n="architecture-badge">Technology</span>
            <h2 class="display-5 fw-bold" style="font-family: 'Cinzel', serif; color: var(--text-title);" data-i18n="architecture-title">System Architecture</h2>
        </div>
        <div class="stacking-cards-container">
            <div class="sticky-card interactive-arch" data-arch="npk">
                <div class="row align-items-center h-100">
                    <div class="col-lg-7">
                        <span class="badge-web3 mb-3" style="background: var(--bg-primary); color: var(--text-muted); border-color: var(--border-subtle);" data-i18n="arch-core-badge">Core Engine</span>
                        <h2 class="display-5 fw-bold mb-3" style="font-family: 'Cinzel', serif;" data-i18n="arch-npk-title">NPK Precision Matrix</h2>
                        <p class="lead" style="color: var(--text-muted);" data-i18n="arch-npk-description">By analyzing Electrical Conductivity (EC), pH, and Moisture, NUTRIX uses advanced mathematical mapping to estimate NPK levels without expensive electrochemical sensors.</p>
                        <span class="mt-3 d-inline-block fw-bold" style="color: var(--color-accent-highlight);"><i class="bi bi-arrow-right"></i> <span data-i18n="arch-details">Click for Details</span></span>
                    </div>
                    <div class="col-lg-5 text-center d-none d-lg-block">
                        <i class="bi bi-cpu display-1 opacity-50" style="color: var(--color-accent-highlight);"></i>
                    </div>
                </div>
            </div>
            
            <div class="sticky-card interactive-arch" data-arch="rs485">
                <div class="row align-items-center h-100">
                    <div class="col-lg-7">
                        <span class="badge-web3 mb-3" style="background: var(--bg-primary); color: var(--text-muted); border-color: var(--border-subtle);" data-i18n="arch-hardware-badge">Hardware</span>
                        <h2 class="display-5 fw-bold mb-3" style="font-family: 'Cinzel', serif;" data-i18n="arch-rs-title">RS-485 Modbus</h2>
                        <p class="lead" style="color: var(--text-muted);" data-i18n="arch-rs-description">Industrial-grade 4-wire transmission architecture guarantees high stability across massive agricultural fields, eliminating signal degradation.</p>
                        <span class="mt-3 d-inline-block fw-bold" style="color: var(--color-accent-highlight);"><i class="bi bi-arrow-right"></i> <span data-i18n="arch-details">Click for Details</span></span>
                    </div>
                    <div class="col-lg-5 text-center d-none d-lg-block">
                        <i class="bi bi-router display-1 opacity-50" style="color: var(--color-accent-highlight);"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. TEAM BENTO GRID -->
    <section class="container py-5 my-5" id="team">
        <div class="text-center mb-5">
            <span class="badge-web3 mb-2" style="background: var(--bg-primary); color: var(--color-accent-highlight); border-color: var(--border-subtle);" data-i18n="team-badge">The Creators</span>
            <h2 class="display-5 fw-bold" style="font-family: 'Cinzel', serif; color: var(--text-title);" data-i18n="team-title">SMAN 8 Malang Developers</h2>
            <p style="color: var(--text-muted);" data-i18n="team-description">Inovasi smart agritech yang dikembangkan oleh siswa SMA Negeri 8 Malang</p>
        </div>
        
        <div class="row g-4">
            <!-- Iklil -->
            <div class="col-lg-4 col-md-6">
                <div class="bento-card text-center h-100 position-relative">
                    <img src="{{ asset('assets/Iklil.jpeg') }}" class="team-avatar" alt="Iklil">
                    <h5 style="font-family: 'Cinzel', serif;" class="fw-bold mt-2">Iklil Naufal T.R.</h5>
                    <p class="mb-2 fw-semibold" style="color: var(--color-accent-highlight);"><small data-i18n="role-iot">IoT Developer</small></p>
                    <button class="btn btn-sm btn-outline-secondary w-100 mt-auto profile-toggle" type="button" data-profile-target="#desc-iklil" aria-controls="desc-iklil" aria-expanded="false" style="color: var(--text-body); border-color: var(--border-subtle);">
                        <span data-i18n="view-profile">View Profile</span> <i class="bi bi-chevron-down ms-1"></i>
                    </button>
                    <div class="collapse mt-3 text-start" id="desc-iklil">
                        <!-- BG-DARK-GLASS & TEXT-WHITE DIHAPUS -->
                        <div class="card card-body p-3" style="background: var(--bg-primary); border: 1px solid var(--border-subtle); border-radius: 12px;">
                            <p class="mb-1" style="color: var(--text-body);"><strong>Rank:</strong> <span style="color: var(--color-amber);">#8</span> (Semester 4)</p>
                            <p class="mb-0" style="color: var(--text-body);"><strong>Target:</strong> Geophysics Engineering</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Fabiel -->
            <div class="col-lg-4 col-md-6">
                <div class="bento-card text-center h-100 position-relative">
                    <img src="{{ asset('assets/Fabiel.jpeg') }}" class="team-avatar" alt="Fabiel">
                    <h5 style="font-family: 'Cinzel', serif;" class="fw-bold mt-2">Fabiel Syaindra P.</h5>
                    <p class="mb-2 fw-semibold" style="color: var(--color-accent-highlight);"><small data-i18n="role-hardware">Hardware Specialist</small></p>
                    <button class="btn btn-sm btn-outline-secondary w-100 mt-auto profile-toggle" type="button" data-profile-target="#desc-fabiel" aria-controls="desc-fabiel" aria-expanded="false" style="color: var(--text-body); border-color: var(--border-subtle);">
                        <span data-i18n="view-profile">View Profile</span> <i class="bi bi-chevron-down ms-1"></i>
                    </button>
                    <div class="collapse mt-3 text-start" id="desc-fabiel">
                        <div class="card card-body p-3" style="background: var(--bg-primary); border: 1px solid var(--border-subtle); border-radius: 12px;">
                            <p class="mb-1" style="color: var(--text-body);"><strong>Rank:</strong> <span style="color: var(--color-amber);">#1</span> (Semester 4)</p>
                            <p class="mb-0" style="color: var(--text-body);"><strong>Target:</strong> Civil Engineering</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Chicco -->
            <div class="col-lg-4 col-md-6">
                <div class="bento-card text-center h-100 position-relative">
                    <img src="{{ asset('assets/Chicco.jpeg') }}" class="team-avatar" alt="Chicco">
                    <h5 style="font-family: 'Cinzel', serif;" class="fw-bold mt-2">Fiorano Chicco J.S.</h5>
                    <p class="mb-2 fw-semibold" style="color: var(--color-accent-highlight);"><small data-i18n="role-leader">Project Leader</small></p>
                    <button class="btn btn-sm btn-outline-secondary w-100 mt-auto profile-toggle" type="button" data-profile-target="#desc-chicco" aria-controls="desc-chicco" aria-expanded="false" style="color: var(--text-body); border-color: var(--border-subtle);">
                        <span data-i18n="view-profile">View Profile</span> <i class="bi bi-chevron-down ms-1"></i>
                    </button>
                    <div class="collapse mt-3 text-start" id="desc-chicco">
                        <div class="card card-body p-3" style="background: var(--bg-primary); border: 1px solid var(--border-subtle); border-radius: 12px;">
                            <p class="mb-1" style="color: var(--text-body);"><strong>Rank:</strong> <span style="color: var(--color-amber);">#2</span> (Semester 4)</p>
                            <p class="mb-0" style="color: var(--text-body);"><strong>Target:</strong> Water Resources Engineering</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Hafizh -->
            <div class="col-lg-6 col-md-6">
                <div class="bento-card text-center h-100 position-relative">
                    <img src="{{ asset('assets/Hafizh.jpeg') }}" class="team-avatar" alt="Hafizh">
                    <h5 style="font-family: 'Cinzel', serif;" class="fw-bold mt-2">Hafizh Maulia</h5>
                    <p class="mb-2 fw-semibold" style="color: var(--color-accent-highlight);"><small data-i18n="role-ui">UI & Documentation</small></p>
                    <button class="btn btn-sm btn-outline-secondary w-100 mt-auto profile-toggle" type="button" data-profile-target="#desc-hafizh" aria-controls="desc-hafizh" aria-expanded="false" style="color: var(--text-body); border-color: var(--border-subtle);">
                        <span data-i18n="view-profile">View Profile</span> <i class="bi bi-chevron-down ms-1"></i>
                    </button>
                    <div class="collapse mt-3 text-start" id="desc-hafizh">
                        <div class="card card-body p-3" style="background: var(--bg-primary); border: 1px solid var(--border-subtle); border-radius: 12px;">
                            <p class="mb-1" style="color: var(--text-body);"><strong>Rank:</strong> <span style="color: var(--color-amber);">#36</span> (Semester 4)</p>
                            <p class="mb-0" style="color: var(--text-body);"><strong>Target:</strong> Civil Engineering</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Farrel -->
            <div class="col-lg-6 col-md-12">
                <div class="bento-card text-center h-100 position-relative">
                    <img src="{{ asset('assets/Farrel.jpeg') }}" class="team-avatar" alt="Farrel">
                    <h5 style="font-family: 'Cinzel', serif;" class="fw-bold mt-2">Gabriel M. Farrel</h5>
                    <p class="mb-2 fw-semibold" style="color: var(--color-accent-highlight);"><small data-i18n="role-engineer">Hardware Engineer</small></p>
                    <button class="btn btn-sm btn-outline-secondary w-100 mt-auto profile-toggle" type="button" data-profile-target="#desc-farrel" aria-controls="desc-farrel" aria-expanded="false" style="color: var(--text-body); border-color: var(--border-subtle);">
                        <span data-i18n="view-profile">View Profile</span> <i class="bi bi-chevron-down ms-1"></i>
                    </button>
                    <div class="collapse mt-3 text-start" id="desc-farrel">
                        <div class="card card-body p-3" style="background: var(--bg-primary); border: 1px solid var(--border-subtle); border-radius: 12px;">
                            <p class="mb-1" style="color: var(--text-body);"><strong>Rank:</strong> <span style="color: var(--color-amber);">#32</span> (Semester 4)</p>
                            <p class="mb-0" style="color: var(--text-body);"><strong>Target:</strong> Electrical Engineering</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =====================================================
         SECTION: SOIL & CROP DYNAMICS SIMULATOR
         ===================================================== -->
    <section class="sim-section" id="simulator">
        <div class="sim-container">
            <div class="sim-header">
                <span class="section-badge" data-i18n="sim-badge">Interactive Sandbox</span>
                <h2 class="sim-title" data-i18n="sim-title">Soil &amp; Crop Dynamics Simulator</h2>
                <p class="sim-desc" data-i18n="sim-desc">Adjust soil NPK and pH levels to simulate real-time plant physiological response and expected yield efficiency.</p>
            </div>
            <div class="sim-body">
                <!-- LEFT: Controls -->
                <div class="sim-controls">
                    <div class="sim-slider-group" id="simGroupN">
                        <div class="sim-slider-label">
                            <span data-i18n="sim-nitrogen">Nitrogen (N)</span>
                            <span class="sim-val" id="simValN">150</span> <small>mg/kg</small>
                        </div>
                        <input type="range" class="sim-range" id="sliderN" min="0" max="300" value="150" oninput="window.hitungSimulator()">
                    </div>
                    <div class="sim-slider-group" id="simGroupP">
                        <div class="sim-slider-label">
                            <span data-i18n="sim-phosphorus">Phosphorus (P)</span>
                            <span class="sim-val" id="simValP">50</span> <small>mg/kg</small>
                        </div>
                        <input type="range" class="sim-range sim-range-p" id="sliderP" min="0" max="100" value="50" oninput="window.hitungSimulator()">
                    </div>
                    <div class="sim-slider-group" id="simGroupK">
                        <div class="sim-slider-label">
                            <span data-i18n="sim-potassium">Potassium (K)</span>
                            <span class="sim-val" id="simValK">150</span> <small>mg/kg</small>
                        </div>
                        <input type="range" class="sim-range sim-range-k" id="sliderK" min="0" max="300" value="150" oninput="window.hitungSimulator()">
                    </div>
                    <div class="sim-slider-group" id="simGroupPh">
                        <div class="sim-slider-label">
                            <span data-i18n="sim-ph">Soil Acidity (pH)</span>
                            <span class="sim-val" id="simValPh">6.5</span>
                        </div>
                        <input type="range" class="sim-range sim-range-ph" id="sliderPh" min="40" max="90" value="65" oninput="window.hitungSimulator()">
                    </div>
                </div>

                <!-- CENTER: Plant Visual -->
                <div class="sim-plant-wrap">
                    <svg id="simPlantSvg" viewBox="0 0 200 260" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <radialGradient id="leafGradL" cx="60%" cy="40%" r="60%">
                                <stop offset="0%" stop-color="#5cb85c" id="leafColorL1"/>
                                <stop offset="100%" stop-color="#2d6a2d" id="leafColorL2"/>
                            </radialGradient>
                            <radialGradient id="leafGradR" cx="40%" cy="40%" r="60%">
                                <stop offset="0%" stop-color="#e8a84e" id="leafColorR1"/>
                                <stop offset="100%" stop-color="#b56a10" id="leafColorR2"/>
                            </radialGradient>
                            <filter id="simGlow">
                                <feGaussianBlur stdDeviation="4" result="blur"/>
                                <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
                            </filter>
                        </defs>
                        <!-- Stem -->
                        <line x1="100" y1="250" x2="100" y2="110" stroke="#8b6914" stroke-width="4" stroke-linecap="round" id="simStem"/>
                        <!-- Left Leaf -->
                        <path d="M100 160 Q55 100 30 80 Q70 90 100 130 Z" fill="url(#leafGradL)" id="simLeafL" filter="url(#simGlow)" opacity="0.95"/>
                        <!-- Right Leaf -->
                        <path d="M100 130 Q145 80 170 60 Q145 95 100 105 Z" fill="url(#leafGradR)" id="simLeafR" filter="url(#simGlow)" opacity="0.95"/>
                        <!-- Top bud -->
                        <ellipse cx="100" cy="105" rx="14" ry="20" fill="url(#leafGradL)" id="simBud" opacity="0.9"/>
                        <!-- Soil -->
                        <ellipse cx="100" cy="250" rx="55" ry="10" fill="var(--color-accent-highlight)" opacity="0.25"/>
                    </svg>
                    <!-- Yield ring -->
                    <div class="sim-yield-ring">
                        <svg viewBox="0 0 120 120" class="sim-ring-svg">
                            <circle cx="60" cy="60" r="50" fill="none" stroke="var(--border-subtle)" stroke-width="8"/>
                            <circle cx="60" cy="60" r="50" fill="none" stroke="var(--color-accent-highlight)" stroke-width="8"
                                stroke-linecap="round" stroke-dasharray="314" stroke-dashoffset="94"
                                id="simYieldRing" transform="rotate(-90 60 60)" style="transition: stroke-dashoffset 0.6s ease;"/>
                        </svg>
                        <div class="sim-yield-label">
                            <span id="simYieldPct">70%</span>
                            <small data-i18n="sim-expected-yield">Potential Yield</small>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Diagnosis Panel -->
                <div class="sim-diagnosis">
                    <div class="sim-diag-status-badge" id="simStatusBadge">Optimal Vitality</div>
                    <p class="sim-diag-label" data-i18n="sim-action-label">AI Agronomic Diagnosis</p>
                    <div class="sim-diag-text" id="simDiagText">Nutrient balance is harmonious. High root uptake with zero chemical runoff danger.</div>
                    <div class="sim-nutrix-tip">
                        <i class="bi bi-cpu-fill" style="color: var(--color-accent-highlight);"></i>
                        <span>NUTRIX precision micro-dosing eliminates guesswork and prevents exactly these imbalances.</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =====================================================
         SECTION: LIVE IOT NODE TOPOLOGY / MAP RADAR
         ===================================================== -->
    <section class="topo-section" id="iot-radar">
        <div class="topo-container">
            <div class="topo-header">
                <span class="section-badge" data-i18n="topo-badge">Distributed Mesh Network</span>
                <h2 class="topo-title" data-i18n="topo-title">Live Field IoT Node Topology</h2>
                <p class="topo-desc" data-i18n="topo-desc">Real-time telemetry and mesh radar coverage across agricultural field zones.</p>
            </div>
            <div class="topo-body">
                <!-- RADAR MAP -->
                <div class="topo-map-wrap">
                    <div class="topo-live-badge">
                        <span class="topo-pulse-dot"></span>
                        <span data-i18n="topo-status-live">Active Mesh Ping</span>
                    </div>
                    <div class="topo-map" id="topoMap">
                        <!-- Radar rings -->
                        <div class="topo-ring topo-ring-1"></div>
                        <div class="topo-ring topo-ring-2"></div>
                        <div class="topo-ring topo-ring-3"></div>
                        <!-- Gateway centre -->
                        <div class="topo-gateway" title="LoRa Gateway">
                            <i class="bi bi-router-fill"></i>
                            <span>GW</span>
                        </div>
                        <!-- Mesh connection lines (SVG) -->
                        <svg class="topo-lines" viewBox="0 0 400 400">
                            <line x1="200" y1="200" x2="90"  y2="110" class="topo-line" data-node="0"/>
                            <line x1="200" y1="200" x2="310" y2="120" class="topo-line" data-node="1"/>
                            <line x1="200" y1="200" x2="335" y2="255" class="topo-line" data-node="2"/>
                            <line x1="200" y1="200" x2="200" y2="320" class="topo-line" data-node="3"/>
                            <line x1="200" y1="200" x2="65"  y2="290" class="topo-line" data-node="4"/>
                            <line x1="200" y1="200" x2="130" y2="185" class="topo-line" data-node="5"/>
                        </svg>
                        <!-- Sensor Nodes -->
                        <div class="topo-node" data-node="0" style="top:22%;left:17%;" title="NX-NODE-01">
                            <i class="bi bi-broadcast-pin"></i>
                        </div>
                        <div class="topo-node" data-node="1" style="top:20%;left:73%;" title="NX-NODE-02">
                            <i class="bi bi-broadcast-pin"></i>
                        </div>
                        <div class="topo-node" data-node="2" style="top:57%;left:81%;" title="NX-NODE-03">
                            <i class="bi bi-broadcast-pin"></i>
                        </div>
                        <div class="topo-node" data-node="3" style="top:76%;left:47%;" title="NX-NODE-04">
                            <i class="bi bi-broadcast-pin"></i>
                        </div>
                        <div class="topo-node" data-node="4" style="top:67%;left:10%;" title="NX-NODE-05">
                            <i class="bi bi-broadcast-pin"></i>
                        </div>
                        <div class="topo-node" data-node="5" style="top:43%;left:27%;" title="NX-NODE-06">
                            <i class="bi bi-broadcast-pin"></i>
                        </div>
                    </div>
                </div>
                <!-- INSPECTION PANEL -->
                <div class="topo-panel" id="topoPanel">
                    <h4 class="topo-panel-title" data-i18n="topo-node-info">Node Telemetry Inspection</h4>
                    <p class="topo-panel-hint" id="topoPanelHint" data-i18n="topo-inspect-hint">Select any field node to inspect live sensory telemetry:</p>
                    <div class="topo-panel-data" id="topoPanelData" style="display:none;">
                        <div class="topo-panel-row">
                            <span>Sensor ID</span><strong id="topoNodeId">SENSOR-01</strong>
                        </div>
                        <div class="topo-panel-row">
                            <span data-i18n="topo-field-zone">Sector Zone</span><strong id="topoNodeZone">Alpha</strong>
                        </div>
                        <div class="topo-panel-row">
                            <span data-i18n="topo-battery">Solar Battery</span>
                            <strong id="topoNodeBattery" style="color: var(--color-accent-highlight);">94%</strong>
                        </div>
                        <div class="topo-panel-row">
                            <span data-i18n="topo-signal">RSSI Signal</span>
                            <strong id="topoNodeRssi">-68 dBm</strong>
                        </div>
                        <div class="topo-panel-row">
                            <span>Moisture</span><strong id="topoNodeMoisture">42%</strong>
                        </div>
                        <div class="topo-panel-row">
                            <span data-i18n="topo-uplink">LoRa Mesh Uplink</span>
                            <strong style="color: #6be97a;">Online</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =====================================================
         SECTION: FAQ ACCORDION
         ===================================================== -->
    <section class="faq-section" id="faq">
        <div class="faq-container">
            <div class="faq-header">
                <span class="section-badge" data-i18n="faq-badge">Knowledge Base</span>
                <h2 class="faq-title" data-i18n="faq-title">Frequently Asked Questions</h2>
                <p class="faq-desc" data-i18n="faq-desc">Everything you need to know about the NUTRIX precision hardware and IoT mesh network.</p>
            </div>
            <div class="faq-list" id="faqList">
                <div class="faq-item">
                    <button class="faq-q" data-i18n="faq-q1">How does NUTRIX transmit data if my field has no 4G or Wi-Fi?</button>
                    <div class="faq-a"><p data-i18n="faq-a1">NUTRIX leverages long-range LoRaWAN and sub-GHz mesh radio protocols.</p></div>
                </div>
                <div class="faq-item">
                    <button class="faq-q" data-i18n="faq-q2">Is the sensor hardware weatherproof for harsh tropical conditions?</button>
                    <div class="faq-a"><p data-i18n="faq-a2">Yes. NUTRIX enclosures are certified IP67 weatherproof.</p></div>
                </div>
                <div class="faq-item">
                    <button class="faq-q" data-i18n="faq-q3">How long does the battery last and what powers each field probe?</button>
                    <div class="faq-a"><p data-i18n="faq-a3">Each field node integrates high-efficiency monocrystalline solar cells.</p></div>
                </div>
                <div class="faq-item">
                    <button class="faq-q" data-i18n="faq-q4">Can telemetry data be exported or integrated with third-party software?</button>
                    <div class="faq-a"><p data-i18n="faq-a4">Absolutely. The NUTRIX dashboard provides instant CSV/Excel export.</p></div>
                </div>
                <div class="faq-item">
                    <button class="faq-q" data-i18n="faq-q5">How soon can a farm realize tangible fertilizer cost savings?</button>
                    <div class="faq-a"><p data-i18n="faq-a5">Growers typically witness a 25% to 35% reduction in the first cycle.</p></div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->

    <footer class="py-5 text-center border-top mt-5" style="border-color: var(--border-subtle) !important;">
        <p class="mb-1" style="color: var(--text-muted);" data-i18n="footer-copy">© 2026 NUTRIX — Smart Farming IoT & Precision Agriculture</p>
        <small style="color: var(--text-muted);" data-i18n="footer-dev">Developed by SMAN 8 Malang Tech Innovators</small>
    </footer>

    <!-- ARCHITECTURE FULL-PANEL POPUP MODAL -->
    <div class="modal-overlay" id="archModal">
        <div class="web3-modal-box" style="max-width: 700px; width: 90%;">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3" style="border-color: var(--border-subtle) !important;">
                <h4 class="mb-0 fw-bold" id="archModalTitle" style="font-family: 'Cinzel', serif; color: var(--text-title);">Architecture Model</h4>
                <button class="btn-close-custom" id="closeArchModal"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="row align-items-center">
                <div class="col-md-4 text-center mb-4 mb-md-0">
                    <i class="display-1 opacity-75" id="archModalIcon" style="color: var(--color-accent-highlight);"></i>
                </div>
                <div class="col-md-8">
                    <!-- CLASS TEXT-WHITE & TEXT-SECONDARY DIHAPUS -->
                    <h5 class="mb-3" style="color: var(--text-title);" data-i18n="modal-specifications">Technical Specifications</h5>
                    <p id="archModalDesc" style="color: var(--text-body); line-height: 1.6;"></p>
                    
                    <!-- CLASS BG-DARK & BORDER-SECONDARY DIHAPUS -->
                    <div class="p-3 rounded-3 mt-4" style="background: var(--bg-primary); border: 1px solid var(--border-subtle);">
                        <strong class="d-block mb-1" style="color: var(--color-accent-highlight);" data-i18n="modal-impact">Impact on Ecosystem:</strong>
                        <span id="archModalImpact" style="color: var(--text-body); font-size: 0.95rem;"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BACK TO TOP BUTTON -->
    <button class="back-to-top" id="backToTop" aria-label="Kembali ke atas" title="Kembali ke atas">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Model Ekonomi Pertanian Nyata (Biaya input pupuk kimia NPK per hektar/musim)
        // Standar nyata: Rp 4.200.000 / ha (Urea + SP-36 + KCl konvensional)
        // Penghematan presisi IoT: 32% (mengurangi pupuk berlebih & mencegah pencucian hara)
        const currencyConfig = {
            IDR: { symbol: 'Rp', rate: 1, costPerHa: 4200000, fmt: (v) => v >= 1000000000 ? `${(v / 1000000000).toFixed(2)} Miliar` : v >= 1000000 ? `${(v / 1000000).toFixed(1)} Juta` : new Intl.NumberFormat('id-ID').format(Math.round(v)) },
            USD: { symbol: '$', rate: 1 / 15800, costPerHa: 270, fmt: (v) => v >= 1000 ? `${(v / 1000).toFixed(1)}k` : new Intl.NumberFormat('en-US').format(Math.round(v)) },
            SGD: { symbol: 'S$', rate: 1 / 11800, costPerHa: 360, fmt: (v) => v >= 1000 ? `${(v / 1000).toFixed(1)}k` : new Intl.NumberFormat('en-SG').format(Math.round(v)) },
            CNY: { symbol: '¥', rate: 1 / 2200, costPerHa: 1950, fmt: (v) => v >= 10000 ? `${(v / 10000).toFixed(1)}万` : new Intl.NumberFormat('zh-CN').format(Math.round(v)) },
            JPY: { symbol: '¥', rate: 1 / 105, costPerHa: 41000, fmt: (v) => v >= 10000 ? `${(v / 10000).toFixed(1)}万` : new Intl.NumberFormat('ja-JP').format(Math.round(v)) },
            MYR: { symbol: 'RM', rate: 1 / 3550, costPerHa: 1200, fmt: (v) => v >= 1000 ? `${(v / 1000).toFixed(1)}k` : new Intl.NumberFormat('ms-MY').format(Math.round(v)) },
            KWD: { symbol: 'KD', rate: 1 / 51500, costPerHa: 82, fmt: (v) => new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 1 }).format(v) },
            SAR: { symbol: 'SR', rate: 1 / 4200, costPerHa: 1000, fmt: (v) => v >= 1000 ? `${(v / 1000).toFixed(1)}k` : new Intl.NumberFormat('ar-SA').format(Math.round(v)) }
        };

        window.hitungEfisiensi = function hitungEfisiensi() {
            const slider = document.getElementById('slider-lahan');
            const currencyKey = document.getElementById('currency-selector')?.value || 'IDR';
            const curr = currencyConfig[currencyKey] || currencyConfig.IDR;
            
            const lahan = Number(slider.value || 1);
            
            // Perhitungan psikologis & realistis:
            // Efisiensi meningkat seiring skala lahan (30% pada 1 Ha hingga 36% pada 50 Ha berkat presisi zona)
            const persenHemat = Math.min(36, Math.round(30 + (lahan / 50) * 6));
            const rasioHemat = persenHemat / 100;

            const biayaTradisional = lahan * curr.costPerHa;
            const biayaNutrix = biayaTradisional * (1 - rasioHemat);
            const nominalHemat = biayaTradisional - biayaNutrix;

            const sliderFill = ((lahan - Number(slider.min || 1)) / (Number(slider.max || 50) - Number(slider.min || 1))) * 100;
            slider.style.setProperty('--slider-fill', `${sliderFill}%`);

            document.getElementById('luas-lahan').innerText = lahan;
            
            // Format angka rapi, proporsional, dan estetik
            const isRtl = document.documentElement.dir === 'rtl' || document.documentElement.lang === 'ar';
            const prefix = isRtl ? '' : curr.symbol + ' ';
            const suffix = isRtl ? ' ' + curr.symbol : '';

            // Dapatkan terjemahan kata 'Hemat' sesuai bahasa aktif
            const lang = window.currentLanguage || document.documentElement.lang || 'id';
            const saveLabel = (window.languageDictionary && window.languageDictionary[lang] && window.languageDictionary[lang]['calc-save-label']) 
                || (typeof window.t === 'function' ? window.t('calc-save-label') : null)
                || (lang === 'ar' ? 'توفير' : (lang === 'ja' ? '削減' : (lang === 'jv' ? 'Irit' : (lang === 'ms' ? 'Jimat' : (lang.startsWith('en') ? 'Save' : 'Hemat')))));

            document.getElementById('biaya-lama-display').innerHTML = `${prefix}${curr.fmt(biayaTradisional)}${suffix}`;
            document.getElementById('biaya-baru-display').innerHTML = `${prefix}${curr.fmt(biayaNutrix)}${suffix}`;
            document.getElementById('persen-hemat').innerHTML = `<span>~${persenHemat}%</span> <small style="font-size: 0.45em; font-weight: 700; opacity: 0.8; display: block; letter-spacing: normal;">${saveLabel} ${prefix}${curr.fmt(nominalHemat)}${suffix}</small>`;
        }

        // Setup Custom Dropdown Mata Uang
        document.addEventListener('DOMContentLoaded', () => {
            const currWrapper = document.getElementById('currencySelectWrapper');
            if (currWrapper) {
                const trigger = currWrapper.querySelector('.custom-select-trigger');
                const options = currWrapper.querySelectorAll('.custom-option');
                const hiddenInput = document.getElementById('currency-selector');
                const triggerCode = document.getElementById('triggerCurrencyCode');
                const triggerText = document.getElementById('triggerCurrencyText');

                trigger?.addEventListener('click', (e) => {
                    document.querySelectorAll('.custom-select-wrapper').forEach(w => {
                        if (w !== currWrapper) w.classList.remove('open');
                    });
                    currWrapper.classList.toggle('open');
                    e.stopPropagation();
                });

                options.forEach(opt => {
                    opt.addEventListener('click', () => {
                        options.forEach(o => o.classList.remove('selected'));
                        opt.classList.add('selected');

                        const val = opt.dataset.value;
                        const code = opt.dataset.code;
                        const label = opt.dataset.label || opt.querySelector('.text-truncate')?.innerText.split(' ')[0] || val;

                        if (hiddenInput) hiddenInput.value = val;
                        if (triggerCode) triggerCode.innerText = code;
                        if (triggerText) triggerText.innerText = label;

                        currWrapper.classList.remove('open');
                        hitungEfisiensi();
                    });
                });

                document.addEventListener('click', () => currWrapper.classList.remove('open'));
            }
        });

        const archModal = document.getElementById('archModal');
        const closeArchModal = document.getElementById('closeArchModal');
        const architectureIcons = { npk: 'bi-cpu', rs485: 'bi-router' };

        const openArchitectureModal = (key) => {
            const translated = typeof window.getArchitectureCopy === 'function'
                ? window.getArchitectureCopy(key)
                : null;
            if (!archModal || !translated) return;

            const titleNode = document.getElementById('archModalTitle');
            const iconNode = document.getElementById('archModalIcon');
            const descNode = document.getElementById('archModalDesc');
            const impactNode = document.getElementById('archModalImpact');

            if (titleNode) titleNode.textContent = translated.title;
            if (iconNode) {
                iconNode.className = `display-1 opacity-75 bi ${architectureIcons[key] || 'bi-cpu'}`;
                iconNode.style.color = 'var(--color-accent-highlight)';
            }
            if (descNode) descNode.textContent = translated.desc;
            if (impactNode) impactNode.textContent = translated.impact;

            archModal.classList.add('active');
        };

        document.querySelectorAll('.interactive-arch').forEach(card => {
            card.addEventListener('click', () => openArchitectureModal(card.dataset.arch));
        });

        if (closeArchModal) {
            closeArchModal.addEventListener('click', () => archModal.classList.remove('active'));
        }
        if (archModal) {
            archModal.addEventListener('click', (e) => {
                if (e.target === archModal) archModal.classList.remove('active');
            });
        }

        window.addEventListener('DOMContentLoaded', () => hitungEfisiensi());
        </script>
    <!-- Link ke script utama untuk animasi 3D dan Theme Switcher -->
    <script src="{{ asset('js/script.js') }}?v={{ filemtime(public_path('js/script.js')) }}"></script>

    <script>
    /* ========================================================
       SOIL & CROP DYNAMICS SIMULATOR
       ======================================================== */
    window.hitungSimulator = function() {
        const N   = Number(document.getElementById('sliderN')?.value || 150);
        const P   = Number(document.getElementById('sliderP')?.value || 50);
        const K   = Number(document.getElementById('sliderK')?.value || 150);
        const phR = Number(document.getElementById('sliderPh')?.value || 65);
        const ph  = phR / 10;

        // Update display values
        document.getElementById('simValN').textContent  = N;
        document.getElementById('simValP').textContent  = P;
        document.getElementById('simValK').textContent  = K;
        document.getElementById('simValPh').textContent = ph.toFixed(1);

        // Update slider fills
        ['N','P','K'].forEach(key => {
            const el  = document.getElementById('slider' + key);
            const max = Number(el.max); const min = Number(el.min || 0);
            const pct = ((Number(el.value) - min) / (max - min)) * 100;
            el.style.setProperty('--slider-fill', pct + '%');
        });
        const phEl = document.getElementById('sliderPh');
        const phPct = ((phR - 40) / 50) * 100;
        phEl.style.setProperty('--slider-fill', phPct + '%');

        // Determine health state
        const lang = window.currentLanguage || 'id';
        const dict = (window.languageDictionary && window.languageDictionary[lang]) || {};
        const t = key => dict[key] || key;

        const nOk  = N  >= 80  && N  <= 250;
        const pOk  = P  >= 20  && P  <= 80;
        const kOk  = K  >= 80  && K  <= 250;
        const phOk = ph >= 6.0 && ph <= 7.5;
        const overN = N > 270; const overP = P > 88; const overK = K > 270;
        const phAcid = ph < 6.0; const phAlk = ph > 7.5;

        let stateKey, diagKey, badgeClass;
        if (phAcid) {
            stateKey = 'sim-status-acidic'; diagKey = 'sim-diag-acidic'; badgeClass = 'danger';
        } else if (phAlk) {
            stateKey = 'sim-status-alkaline'; diagKey = 'sim-diag-alkaline'; badgeClass = 'warning';
        } else if (overN || overP || overK) {
            stateKey = 'sim-status-excess'; diagKey = 'sim-diag-excess'; badgeClass = 'danger';
        } else if (!nOk || !pOk || !kOk) {
            stateKey = 'sim-status-deficiency'; diagKey = 'sim-diag-deficiency'; badgeClass = 'warning';
        } else {
            stateKey = 'sim-status-optimal'; diagKey = 'sim-diag-optimal'; badgeClass = '';
        }

        // Yield calculation (0–100%)
        const nScore  = Math.max(0, 1 - Math.abs(N  - 160) / 200);
        const pScore  = Math.max(0, 1 - Math.abs(P  - 50)  / 80);
        const kScore  = Math.max(0, 1 - Math.abs(K  - 160) / 200);
        const phScore = Math.max(0, 1 - Math.abs(ph - 6.75) / 3.5);
        const yieldPct = Math.round((nScore * 0.3 + pScore * 0.2 + kScore * 0.25 + phScore * 0.25) * 100);

        // Update DOM
        const badge  = document.getElementById('simStatusBadge');
        const diag   = document.getElementById('simDiagText');
        const yieldPctEl = document.getElementById('simYieldPct');
        const ring   = document.getElementById('simYieldRing');

        if (badge) {
            badge.textContent = t(stateKey);
            badge.className   = 'sim-diag-status-badge' + (badgeClass ? ' ' + badgeClass : '');
        }
        if (diag) diag.textContent = t(diagKey);
        if (yieldPctEl) yieldPctEl.textContent = yieldPct + '%';

        // Ring: circumference = 314; dashoffset = 314 * (1 - yield/100)
        if (ring) {
            ring.style.strokeDashoffset = (314 * (1 - yieldPct / 100)).toFixed(1);
            ring.style.stroke = badgeClass === 'danger' ? '#ff7070' : badgeClass === 'warning' ? 'var(--color-amber)' : 'var(--color-accent-highlight)';
        }

        // Plant colour response
        const leafL1 = document.getElementById('leafColorL1');
        const leafL2 = document.getElementById('leafColorL2');
        const leafR1 = document.getElementById('leafColorR1');
        const leafR2 = document.getElementById('leafColorR2');
        const bud    = document.getElementById('simBud');
        if (yieldPct >= 80) {
            if (leafL1) leafL1.setAttribute('stop-color', '#4caf50');
            if (leafL2) leafL2.setAttribute('stop-color', '#1b5e20');
            if (leafR1) leafR1.setAttribute('stop-color', '#66bb6a');
            if (leafR2) leafR2.setAttribute('stop-color', '#2e7d32');
            if (bud)    bud.style.opacity = '0.95';
        } else if (yieldPct >= 50) {
            if (leafL1) leafL1.setAttribute('stop-color', '#cddc39');
            if (leafL2) leafL2.setAttribute('stop-color', '#827717');
            if (leafR1) leafR1.setAttribute('stop-color', '#ffcc80');
            if (leafR2) leafR2.setAttribute('stop-color', '#e65100');
            if (bud)    bud.style.opacity = '0.7';
        } else {
            if (leafL1) leafL1.setAttribute('stop-color', '#e0e0e0');
            if (leafL2) leafL2.setAttribute('stop-color', '#9e9e9e');
            if (leafR1) leafR1.setAttribute('stop-color', '#ffab91');
            if (leafR2) leafR2.setAttribute('stop-color', '#bf360c');
            if (bud)    bud.style.opacity = '0.4';
        }
    };

    // Init simulator on load
    document.addEventListener('DOMContentLoaded', () => {
        window.hitungSimulator();

        /* ========================================================
           IOT NODE TOPOLOGY
           ======================================================== */
        const nodeData = [
            { id: 'NX-NODE-01', zone: 'Alpha',   battery: '94%', rssi: '-68 dBm', moisture: '62%' },
            { id: 'NX-NODE-02', zone: 'Beta',    battery: '87%', rssi: '-74 dBm', moisture: '55%' },
            { id: 'NX-NODE-03', zone: 'Gamma',   battery: '100%',rssi: '-61 dBm', moisture: '78%' },
            { id: 'NX-NODE-04', zone: 'Delta',   battery: '72%', rssi: '-81 dBm', moisture: '44%' },
            { id: 'NX-NODE-05', zone: 'Epsilon', battery: '91%', rssi: '-65 dBm', moisture: '68%' },
            { id: 'NX-NODE-06', zone: 'Zeta',    battery: '83%', rssi: '-77 dBm', moisture: '51%' },
        ];

        document.querySelectorAll('.topo-node').forEach(node => {
            node.addEventListener('click', () => {
                document.querySelectorAll('.topo-node').forEach(n => n.classList.remove('active'));
                node.classList.add('active');

                const idx  = Number(node.dataset.node);
                const data = nodeData[idx];
                const hint = document.getElementById('topoPanelHint');
                const panel= document.getElementById('topoPanelData');

                if (hint)  hint.style.display  = 'none';
                if (panel) panel.style.display  = 'block';

                document.getElementById('topoNodeId').textContent       = data.id;
                document.getElementById('topoNodeZone').textContent     = data.zone;
                document.getElementById('topoNodeBattery').textContent  = data.battery;
                document.getElementById('topoNodeRssi').textContent     = data.rssi;
                document.getElementById('topoNodeMoisture').textContent = data.moisture;
            });
        });

        /* ========================================================
           FAQ ACCORDION
           ======================================================== */
        document.querySelectorAll('.faq-q').forEach(btn => {
            btn.addEventListener('click', () => {
                const item    = btn.closest('.faq-item');
                const isOpen  = item.classList.contains('open');
                // Close all
                document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
                // Toggle current
                if (!isOpen) item.classList.add('open');
            });
        });

        /* ========================================================
           TEAM PROFILE COLLAPSE
           One explicit Bootstrap instance per card prevents the
           double-toggle behaviour caused by duplicate data handlers.
           ======================================================== */
        document.querySelectorAll('.profile-toggle').forEach(button => {
            const panel = document.querySelector(button.dataset.profileTarget);
            if (!panel || !window.bootstrap?.Collapse) return;

            const collapse = window.bootstrap.Collapse.getOrCreateInstance(panel, { toggle: false });
            button.addEventListener('click', () => collapse.toggle());
            panel.addEventListener('shown.bs.collapse', () => button.setAttribute('aria-expanded', 'true'));
            panel.addEventListener('hidden.bs.collapse', () => button.setAttribute('aria-expanded', 'false'));
        });
    });
    </script>

    <!-- WELCOME PAGE AUTH MODAL -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="welcome-auth-overlay" id="welcomeAuthModal">
        <div class="welcome-auth-box">
            <button class="welcome-auth-close" id="closeWelcomeAuth"><i class="bi bi-x"></i></button>

            <!-- Tabs -->
            <div class="auth-tab-group" id="welcomeAuthTabGroup">
                <button class="auth-tab active" data-tab="signin" data-i18n="ui-sign-in">Sign In</button>
                <button class="auth-tab" data-tab="signup" data-i18n="ui-sign-up">Sign Up</button>
            </div>

            <!-- Error message -->
            <div class="auth-error-msg" id="welcomeAuthError"></div>

            <!-- STEP 1: Sign In -->
            <div class="auth-form active" id="w-form-signin">
                <div class="auth-input-group mb-3">
                    <label data-i18n="ui-email">Email Address</label>
                    <input type="email" class="auth-input" id="w-signinEmail" autocomplete="off" placeholder="admin@nutrix.io">
                </div>
                <div class="auth-input-group mb-3">
                    <label data-i18n="ui-password">Password</label>
                    <input type="password" class="auth-input" id="w-signinPassword" autocomplete="new-password" placeholder="••••••••">
                </div>
                <button class="btn-connect-node w-100 mt-1" style="width:100%;" id="w-btnSignIn">
                    <span data-i18n="auth-request-signin">Minta Kode OTP (Sign In)</span>
                </button>
                <p style="text-align:center; color: var(--text-muted); font-size: 0.85rem; margin-top: 1rem; margin-bottom: 0;">
                    <span data-i18n="ui-sign-in-help">Belum punya akun?</span> <a href="#" class="auth-switch" data-tab="signup" id="w-switchToSignup" data-i18n="ui-sign-up">Sign Up</a>
                </p>
            </div>

            <!-- STEP 1: Sign Up -->
            <div class="auth-form" id="w-form-signup">
                <div class="auth-input-group mb-3">
                    <label data-i18n="ui-username">Nama Lengkap</label>
                    <input type="text" class="auth-input" id="w-signupName" placeholder="Farmer Alpha">
                </div>
                <div class="auth-input-group mb-3">
                    <label data-i18n="ui-email">Email Address</label>
                    <input type="email" class="auth-input" id="w-signupEmail" autocomplete="off" placeholder="farmer@nutrix.io">
                </div>
                <div class="auth-input-group mb-3">
                    <label data-i18n="ui-password">Password (Min. 8 Karakter)</label>
                    <input type="password" class="auth-input" id="w-signupPassword" autocomplete="new-password" placeholder="••••••••">
                </div>
                <div class="auth-input-group mb-3">
                    <label data-i18n="ui-confirm-password">Konfirmasi Password</label>
                    <input type="password" class="auth-input" id="w-signupPasswordConfirm" autocomplete="new-password" placeholder="••••••••">
                </div>
                <button class="btn-connect-node" style="width:100%;" id="w-btnSignUp">
                    <span data-i18n="auth-request-signup">Minta Kode OTP (Sign Up)</span>
                </button>
                <p style="text-align:center; color: var(--text-muted); font-size: 0.85rem; margin-top: 1rem; margin-bottom: 0;">
                    <span data-i18n="ui-sign-up-help">Sudah punya akun?</span> <a href="#" class="auth-switch" data-tab="signin" id="w-switchToSignin" data-i18n="ui-sign-in">Sign In</a>
                </p>
            </div>

            <!-- STEP 2: OTP Verification -->
            <div class="auth-form" id="w-form-otp">
                <div style="text-align:center; margin-bottom: 1.25rem;">
                    <div style="display:inline-flex; padding: 16px; border-radius:50%; background: var(--bg-primary); border: 1px solid var(--border-subtle); margin-bottom: 12px;">
                        <i class="bi bi-envelope-open-heart" style="color: var(--color-accent-highlight); font-size: 1.75rem;"></i>
                    </div>
                    <h5 style="font-weight:700; color: var(--text-title); margin-bottom: 4px;" id="w-otpTitle" data-i18n="auth-email-verified">Verifikasi Email Anda</h5>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 4px;" data-i18n="auth-code-sent">Kode 6 digit dikirim ke:</p>
                    <div style="color: var(--color-accent-highlight); font-weight:700; font-family: monospace;" id="w-otpEmail">user@example.com</div>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; background: var(--bg-primary); border: 1px solid var(--border-subtle); border-radius: 12px; padding: 10px 14px; margin-bottom: 1rem;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;"><i class="bi bi-hourglass-split" style="color:#f59e0b;"></i> <span data-i18n="auth-time-left">Sisa waktu:</span></span>
                    <span class="otp-timer-badge" id="w-otpTimer">02:00</span>
                </div>
                <div class="auth-input-group mb-3" style="text-align:center;">
                    <label data-i18n="auth-invalid">6 Digit Kode Verifikasi</label>
                    <input type="text" class="auth-input" id="w-otpCode" autocomplete="one-time-code" maxlength="6" placeholder="000000" style="text-align:center; font-size: 1.75rem; font-family: monospace; letter-spacing: 10px; font-weight:700;">
                </div>
                <button class="btn-connect-node" style="width:100%; margin-bottom: 0.75rem;" id="w-btnVerifyOtp">
                    <span data-i18n="auth-verify">Verifikasi &amp; Masuk</span>
                </button>
                <div style="display:flex; justify-content:space-between; align-items:center; padding-top: 0.75rem; border-top: 1px solid var(--border-subtle);">
                    <button type="button" style="background:none; border:none; color: var(--text-muted); cursor:pointer; font-size: 0.85rem; padding: 0;" id="w-btnBackOtp">
                        <i class="bi bi-arrow-left"></i> <span data-i18n="auth-back">Kembali</span>
                    </button>
                    <button type="button" style="background:none; border:none; color: var(--color-accent-highlight); cursor:pointer; font-size: 0.85rem; font-weight: 600; padding: 0;" id="w-btnResendOtp" disabled>
                        <i class="bi bi-arrow-clockwise"></i> <span data-i18n="auth-resend">Kirim Ulang</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    // ============================================================
    //  WELCOME PAGE AUTH MODAL (SELF-CONTAINED)
    // ============================================================
    const wModal       = document.getElementById('welcomeAuthModal');
    const wCloseBtn    = document.getElementById('closeWelcomeAuth');
    const wTabGroup    = document.getElementById('welcomeAuthTabGroup');
    const wErrorEl     = document.getElementById('welcomeAuthError');
    const wForms       = { signin: 'w-form-signin', signup: 'w-form-signup', otp: 'w-form-otp' };
    let wCurrentFlow   = 'signin'; // Mode autentikasi: 'signin' | 'signup'
    let wOtpInterval   = null;

    function openWelcomeAuth(tab = 'signin') {
        wShowForm(tab);
        wModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function wCloseModal() {
        wModal.classList.remove('active');
        document.body.style.overflow = '';
        wResetModal();
    }

    function wResetModal() {
        ['w-signinEmail', 'w-signinPassword', 'w-signupName', 'w-signupEmail', 'w-signupPassword', 'w-signupPasswordConfirm', 'w-otpCode'].forEach(id => {
            const input = document.getElementById(id);
            if (input) input.value = '';
        });
        wCurrentFlow = 'signin';
        wStopTimer();
        wShowForm('signin');
        wClearError();
        const timer = document.getElementById('w-otpTimer');
        if (timer) timer.textContent = '02:00';
        const resend = document.getElementById('w-btnResendOtp');
        if (resend) resend.disabled = true;
        ['w-btnSignIn', 'w-btnSignUp', 'w-btnVerifyOtp'].forEach(id => wSetLoading(id, false));
    }

    function wShowForm(tab) {
        Object.values(wForms).forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.remove('active');
        });
        const target = document.getElementById(wForms[tab]);
        if (target) target.classList.add('active');
        // Sync tab buttons
        wTabGroup.querySelectorAll('.auth-tab').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.tab === tab);
        });
        // Tampilan OTP bukan mode autentikasi. Menimpa mode di sini membuat
        // OTP pendaftaran salah dikirim ke endpoint login.
        if (tab === 'signin' || tab === 'signup') wCurrentFlow = tab;
    }

    function wShowError(msg) {
        wErrorEl.textContent = msg;
        wErrorEl.style.display = 'block';
    }
    function wClearError() { wErrorEl.style.display = 'none'; wErrorEl.textContent = ''; }

    function wSetLoading(btnId, loading) {
        const btn = document.getElementById(btnId);
        if (!btn) return;
        if (loading) {
            btn.disabled = true;
            const loadingKey = btnId === 'w-btnSignIn' ? 'auth-sending' : btnId === 'w-btnSignUp' ? 'auth-processing' : 'auth-verifying';
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${window.nutrixText(loadingKey)}`;
        }
        else {
            btn.disabled = false;
            const texts = { 'w-btnSignIn': 'auth-request-signin', 'w-btnSignUp': 'auth-request-signup', 'w-btnVerifyOtp': 'auth-verify' };
            btn.innerHTML = `<span data-i18n="${texts[btnId] || 'auth-verify'}">${window.nutrixText(texts[btnId] || 'auth-verify')}</span>`;
        }
    }

    document.addEventListener('nutrix:languagechange', () => {
        ['w-btnSignIn', 'w-btnSignUp', 'w-btnVerifyOtp'].forEach(id => {
            const button = document.getElementById(id);
            if (button && !button.disabled) wSetLoading(id, false);
        });
        const otpTitle = document.getElementById('w-otpTitle');
        if (otpTitle && wCurrentFlow) {
            otpTitle.textContent = `${window.nutrixText('auth-email-verified')} ${window.nutrixText(wCurrentFlow === 'signup' ? 'ui-sign-up' : 'ui-sign-in')}`;
        }
    });

    // Tab switching
    wTabGroup.querySelectorAll('.auth-tab').forEach(btn => {
        btn.addEventListener('click', () => wShowForm(btn.dataset.tab));
    });
    document.getElementById('w-switchToSignup').addEventListener('click', e => { e.preventDefault(); wShowForm('signup'); });
    document.getElementById('w-switchToSignin').addEventListener('click', e => { e.preventDefault(); wShowForm('signin'); });
    wCloseBtn.addEventListener('click', wCloseModal);
    wModal.addEventListener('click', e => { if (e.target === wModal) wCloseModal(); });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && wModal.classList.contains('active')) wCloseModal();
    });

    // Sign In - Request OTP
    document.getElementById('w-btnSignIn').addEventListener('click', async () => {
        wClearError();
        const email    = document.getElementById('w-signinEmail').value.trim();
        const password = document.getElementById('w-signinPassword').value;
        if (!email || !password) return wShowError('Isi email dan password terlebih dahulu.');
        wSetLoading('w-btnSignIn', true);
        try {
            const res = await fetch('/auth/login/request-otp', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                },
                body: JSON.stringify({ email, password })
            });
            let data = {};
            try { data = await res.json(); } catch { data = {}; }
            if (res.ok && data.status === 'success') {
                wCurrentFlow = 'signin';
                document.getElementById('w-otpEmail').textContent = email;
                document.getElementById('w-otpTitle').textContent = `${window.nutrixText('auth-email-verified')} ${window.nutrixText('ui-sign-in')}`;
                wShowForm('otp');
                wStartTimer();
                setTimeout(() => document.getElementById('w-otpCode')?.focus(), 150);
            } else {
                const errorMsg = data.errors ? Object.values(data.errors).flat().join('<br>') : (data.message || 'Akun tidak ditemukan atau password salah. Pastikan Anda telah Sign Up.');
                wShowError(errorMsg);
            }
        } catch { wShowError('Terjadi gangguan koneksi. Silakan coba lagi.'); }
        finally { wSetLoading('w-btnSignIn', false); }
    });

    // Sign Up - Request OTP
    document.getElementById('w-btnSignUp').addEventListener('click', async () => {
        wClearError();
        const name     = document.getElementById('w-signupName').value.trim();
        const email    = document.getElementById('w-signupEmail').value.trim();
        const password = document.getElementById('w-signupPassword').value;
        const passwordConfirmation = document.getElementById('w-signupPasswordConfirm').value;
        if (!name || !email || !password || !passwordConfirmation) return wShowError('Lengkapi semua kolom terlebih dahulu.');
        if (password.length < 8) return wShowError('Password minimal 8 karakter.');
        if (password !== passwordConfirmation) return wShowError('Konfirmasi password tidak cocok.');
        wSetLoading('w-btnSignUp', true);
        try {
            const res = await fetch('/auth/register/request-otp', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                },
                body: JSON.stringify({ name, email, password, password_confirmation: passwordConfirmation })
            });
            let data = {};
            try { data = await res.json(); } catch { data = {}; }
            if (res.ok && data.status === 'success') {
                wCurrentFlow = 'signup';
                document.getElementById('w-otpEmail').textContent = email;
                document.getElementById('w-otpTitle').textContent = `${window.nutrixText('auth-email-verified')} ${window.nutrixText('ui-sign-up')}`;
                wShowForm('otp');
                wStartTimer();
                setTimeout(() => document.getElementById('w-otpCode')?.focus(), 150);
            } else {
                const errorMsg = data.errors ? Object.values(data.errors).flat().join('<br>') : (data.message || 'Gagal mendaftar. Silakan periksa kembali data Anda.');
                wShowError(errorMsg);
            }
        } catch { wShowError('Terjadi gangguan koneksi. Silakan coba lagi.'); }
        finally { wSetLoading('w-btnSignUp', false); }
    });

    // Enter key submit untuk Sign In & Sign Up
    ['w-signinEmail', 'w-signinPassword'].forEach(id => {
        document.getElementById(id)?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('w-btnSignIn')?.click();
            }
        });
    });

    ['w-signupName', 'w-signupEmail', 'w-signupPassword', 'w-signupPasswordConfirm'].forEach(id => {
        document.getElementById(id)?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('w-btnSignUp')?.click();
            }
        });
    });

    // Otomatis verifikasi saat 6 digit OTP terisi
    const wDemoOtpInput = document.getElementById('w-otpCode');
    if (wDemoOtpInput) {
        wDemoOtpInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 6);
            if (e.target.value.length === 6) {
                const btn = document.getElementById('w-btnVerifyOtp');
                if (btn && !btn.disabled) btn.click();
            }
        });
        wDemoOtpInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('w-btnVerifyOtp')?.click();
            }
        });
    }

    // Verify OTP
    document.getElementById('w-btnVerifyOtp').addEventListener('click', async () => {
        wClearError();
        const code  = document.getElementById('w-otpCode').value.trim();
        const email = document.getElementById('w-otpEmail').textContent.trim();
        if (!code || code.length !== 6) return wShowError('Masukkan kode 6 digit yang valid.');
        wSetLoading('w-btnVerifyOtp', true);
        const endpoint = wCurrentFlow === 'signup' ? '/auth/register/verify-otp' : '/auth/login/verify-otp';
        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                },
                body: JSON.stringify({ email, otp: code })
            });
            let data = {};
            try { data = await res.json(); } catch { data = {}; }
            if (res.ok && data.status === 'success') {
                wStopTimer();
                window.location.href = data.redirect || '/landingpage';
            } else {
                const errorMsg = data.errors ? Object.values(data.errors).flat().join('<br>') : (data.message || 'Kode OTP salah atau sudah kedaluwarsa.');
                wShowError(errorMsg);
            }
        } catch { wShowError('Terjadi gangguan koneksi. Silakan coba lagi.'); }
        finally { wSetLoading('w-btnVerifyOtp', false); }
    });

    // Back from OTP
    document.getElementById('w-btnBackOtp').addEventListener('click', () => {
        wStopTimer();
        wShowForm(wCurrentFlow === 'signup' ? 'signup' : 'signin');
    });

    // Resend OTP
    document.getElementById('w-btnResendOtp').addEventListener('click', async () => {
        const email = document.getElementById('w-otpEmail').textContent.trim();
        try {
            const response = await fetch('/auth/resend-otp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                body: JSON.stringify({ email, action: wCurrentFlow === 'signup' ? 'register' : 'login' })
            });
            const data = await response.json();
            if (!response.ok || data.status !== 'success') return wShowError(data.message || 'Kode OTP gagal dikirim ulang.');
            wClearError();
            wStartTimer();
        } catch {
            wShowError('Koneksi terputus. Kode OTP belum dikirim ulang.');
        }
    });

    // OTP Timer
    function wStartTimer() {
        wStopTimer();
        let seconds = 120;
        const timerEl    = document.getElementById('w-otpTimer');
        const resendBtn  = document.getElementById('w-btnResendOtp');
        resendBtn.disabled = true;
        function tick() {
            const m = String(Math.floor(seconds / 60)).padStart(2, '0');
            const s = String(seconds % 60).padStart(2, '0');
            timerEl.textContent = `${m}:${s}`;
            if (seconds <= 0) {
                wStopTimer();
                timerEl.textContent = '00:00';
                resendBtn.disabled = false;
            } else { seconds--; }
        }
        tick();
        wOtpInterval = setInterval(tick, 1000);
    }
    function wStopTimer() {
        if (wOtpInterval) { clearInterval(wOtpInterval); wOtpInterval = null; }
    }
    </script>

    <!-- =====================================================
         ASISTEN KUIS — MASKOT GEOMETRI MATEMATIKA & STATE MACHINE
         ===================================================== -->
    <div id="nutrixQuizWrapper" class="quiz-mascot-wrapper mascot-calm">
        <!-- Quiz Panel Card -->
        <div id="nutrixQuizPanel" class="quiz-panel" role="region" aria-label="Asisten Kuis Nutrix">
            <div class="quiz-panel-header" id="quizPanelDragHandle" title="Tahan dan geser untuk memindahkan kuis">
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size: 1.1rem;">🌱</span>
                    <div>
                        <div class="quiz-panel-title">Asisten Kuis Nutrix</div>
                        <div class="quiz-drag-hint">⋮⋮ Geser / Drag kuis</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div id="quizScoreBadge" class="quiz-score-badge">Soal 1/15 • Skor: 0</div>
                    <button type="button" id="quizBtnCloseX" class="quiz-close" aria-label="Tutup kuis" title="Tutup kuis" style="font-size:1.3rem; border:none; background:none; color:var(--text-muted); cursor:pointer; line-height:1; padding:0 4px;">&times;</button>
                </div>
            </div>
            <div class="quiz-progress">
                <div id="quizProgressBar" class="quiz-progress-fill"></div>
            </div>
            <div id="quizQuestionText" class="quiz-question">Memuat pertanyaan...</div>
            <div id="quizOptionsContainer" class="quiz-options">
                <!-- Option buttons dynamically generated -->
            </div>
            <div id="quizExplanation" class="quiz-explanation"></div>
            <div class="quiz-controls">
                <button type="button" id="quizBtnNext" class="quiz-btn quiz-btn-next" disabled>
                    <span>Lanjut ➜</span>
                </button>
                <button type="button" id="quizBtnReveal" class="quiz-btn quiz-btn-reveal" style="display:none;">
                    <span>🔓 Kunci</span>
                </button>
                <button type="button" id="quizBtnShuffle" class="quiz-btn quiz-btn-shuffle" title="Kocok ulang seluruh bank soal dan mulai dari nomor 1">
                    <span>🔀 Acak Soal</span>
                </button>
                <button type="button" id="quizBtnEnd" class="quiz-btn quiz-btn-end" title="Tutup kuis dan reset progres">
                    <span>✕ Akhiri Kuis</span>
                </button>
                <a href="{{ route('mascot.studio') }}" class="quiz-btn" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.4); text-decoration: none; display: inline-flex; align-items: center; justify-content: center;" title="Buka Studio Animasi & Konten Maskot">
                    <span>🎬 Mascot Studio</span>
                </a>
            </div>
        </div>

        <!-- Mascot Button (Draggable + Click to Toggle) -->
        <button type="button" id="nutrixMascotBtn" class="quiz-mascot-btn" aria-label="Buka atau Tutup Asisten Kuis" title="Tanya Asisten Kuis Nutrix">
            <svg class="quiz-mascot-svg" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="mascotBodyGrad" x1="15" y1="15" x2="85" y2="85" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#34d399"/>
                        <stop offset="50%" stop-color="#10b981"/>
                        <stop offset="100%" stop-color="#059669"/>
                    </linearGradient>
                    <linearGradient id="mascotLeafGrad" x1="50" y1="5" x2="50" y2="30" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#6ee7b7"/>
                        <stop offset="100%" stop-color="#10b981"/>
                    </linearGradient>
                    <filter id="mascotGlow" x="-10" y="-10" width="120" height="120" filterUnits="userSpaceOnUse">
                        <feGaussianBlur stdDeviation="3" result="blur"/>
                        <feComposite in="SourceGraphic" in2="blur" operator="over"/>
                    </filter>
                </defs>

                <!-- Cat Ears (Hidden by default, shown during .mascot-cat) -->
                <g class="mascot-cat-ears">
                    <polygon points="26,35 15,12 36,25" fill="#10b981" stroke="#047857" stroke-width="2"/>
                    <polygon points="25,32 18,17 32,26" fill="#f9a8d4"/>
                    <polygon points="74,35 85,12 64,25" fill="#10b981" stroke="#047857" stroke-width="2"/>
                    <polygon points="75,32 82,17 68,26" fill="#f9a8d4"/>
                </g>

                <!-- Plant Leaves / Math Antennas -->
                <g class="mascot-leaves">
                    <!-- Stem -->
                    <rect x="47" y="22" width="6" height="12" rx="3" fill="#059669"/>
                    <!-- Left Leaf (Bezier curved) -->
                    <path class="mascot-leaf mascot-leaf-left" d="M 48 24 C 30 18 22 8 34 5 C 46 2 48 18 48 24 Z" fill="url(#mascotLeafGrad)"/>
                    <!-- Right Leaf (Bezier curved) -->
                    <path class="mascot-leaf mascot-leaf-right" d="M 52 24 C 70 18 78 8 66 5 C 54 2 52 18 52 24 Z" fill="url(#mascotLeafGrad)"/>
                </g>

                <!-- Main Body Group (Animates breathe & tilt) -->
                <g class="mascot-body-group">
                    <!-- Mathematical Hexagonal/Superellipse Body -->
                    <polygon class="mascot-body" points="50,28 78,36 88,64 72,90 28,90 12,64 22,36" 
                             fill="url(#mascotBodyGrad)" stroke="#047857" stroke-width="2.5" stroke-linejoin="round"/>
                    
                    <!-- Decorative Geometric Inner Ring/Visor -->
                    <path d="M 28 42 Q 50 36 72 42 Q 80 62 72 78 Q 50 84 28 78 Q 20 62 28 42 Z" 
                          fill="rgba(255, 255, 255, 0.18)" stroke="rgba(255, 255, 255, 0.4)" stroke-width="1.2"/>

                    <!-- Cat Whiskers (Shown during .mascot-cat) -->
                    <g class="mascot-cat-whiskers">
                        <line x1="22" y1="58" x2="8" y2="54" stroke="#1e293b" stroke-width="1.5" stroke-linecap="round"/>
                        <line x1="22" y1="63" x2="6" y2="64" stroke="#1e293b" stroke-width="1.5" stroke-linecap="round"/>
                        <line x1="78" y1="58" x2="92" y2="54" stroke="#1e293b" stroke-width="1.5" stroke-linecap="round"/>
                        <line x1="78" y1="63" x2="94" y2="64" stroke="#1e293b" stroke-width="1.5" stroke-linecap="round"/>
                    </g>

                    <!-- Blushes (Cheeks) -->
                    <ellipse class="mascot-blush" cx="29" cy="67" rx="5.5" ry="3.5"/>
                    <ellipse class="mascot-blush" cx="71" cy="67" rx="5.5" ry="3.5"/>

                    <!-- Left Eye -->
                    <g class="mascot-eye-blink mascot-left-eye" style="transform-origin: 38px 54px;">
                        <circle class="mascot-eye-white" cx="38" cy="54" r="8.5"/>
                        <circle class="mascot-pupil" cx="39" cy="54" r="4.2"/>
                        <circle cx="37" cy="51.5" r="1.8" fill="#ffffff"/>
                    </g>

                    <!-- Right Eye -->
                    <g class="mascot-eye-blink mascot-right-eye" style="transform-origin: 62px 54px;">
                        <circle class="mascot-eye-white" cx="62" cy="54" r="8.5"/>
                        <circle class="mascot-pupil" cx="61" cy="54" r="4.2"/>
                        <circle cx="59" cy="51.5" r="1.8" fill="#ffffff"/>
                    </g>

                    <!-- Dynamic Mouth (Path controlled by expressions) -->
                    <path id="mascotMouthPath" class="mascot-mouth" d="M 43 69 Q 50 74 57 69"/>

                    <!-- Eating Cookie / Leaf prop -->
                    <g class="mascot-eating-prop">
                        <circle cx="50" cy="74" r="5.5" fill="#d97706" stroke="#92400e" stroke-width="1"/>
                        <circle cx="48" cy="73" r="1" fill="#78350f"/>
                        <circle cx="52" cy="74" r="1" fill="#78350f"/>
                    </g>

                    <!-- Sleep Zzz particles -->
                    <g class="mascot-zzz">
                        <text x="64" y="38" font-size="9" font-weight="900" fill="#93c5fd" class="zzz-item z1">Z</text>
                        <text x="73" y="27" font-size="12" font-weight="900" fill="#60a5fa" class="zzz-item z2">Z</text>
                        <text x="82" y="15" font-size="15" font-weight="900" fill="#3b82f6" class="zzz-item z3">Z</text>
                    </g>

                    <!-- Pixel / Thug Life Cool Sunglasses (Shown during .mascot-cool) -->
                    <g class="mascot-sunglasses-prop">
                        <path d="M 26 48 L 48 48 L 48 59 L 45 62 L 29 62 L 26 59 Z" fill="#0f172a" stroke="#ffffff" stroke-width="1"/>
                        <path d="M 52 48 L 74 48 L 74 59 L 71 62 L 55 62 L 52 59 Z" fill="#0f172a" stroke="#ffffff" stroke-width="1"/>
                        <rect x="47" y="50" width="6" height="3.5" fill="#0f172a"/>
                        <!-- White glare reflections -->
                        <polygon points="30,51 36,51 32,59 28,59" fill="#ffffff" opacity="0.75"/>
                        <polygon points="56,51 62,51 58,59 54,59" fill="#ffffff" opacity="0.75"/>
                    </g>

                    <!-- Floating Love Hearts (Shown during .mascot-love) -->
                    <g class="mascot-hearts-prop">
                        <path class="heart-item h1" d="M 46 25 C 46 22 41 18 36 21 C 31 24 36 30 46 35 C 56 30 61 24 56 21 C 51 18 46 22 46 25 Z" fill="#f43f5e"/>
                        <path class="heart-item h2" d="M 72 35 C 72 32 68 28 64 30 C 60 32 64 36 72 40 C 80 36 84 32 80 30 C 76 28 72 32 72 35 Z" fill="#fb7185"/>
                    </g>
                </g>

                <!-- Electric Shocks (Shown during .mascot-shock) -->
                <g class="mascot-shock-sparks">
                    <path d="M 16 35 L 8 45 L 18 47 L 10 60" stroke="#facc15" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    <path d="M 84 35 L 92 45 L 82 47 L 90 60" stroke="#facc15" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    <path d="M 44 8 L 50 16 L 46 18 L 54 26" stroke="#facc15" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                </g>

                <!-- Heavy Cartoon Anvil (Falls from top during .mascot-anvil) -->
                <g class="mascot-anvil-prop">
                    <!-- Top horn & flat face -->
                    <path d="M 28 8 L 72 8 L 84 14 L 70 18 L 60 18 L 62 26 L 38 26 L 40 18 L 26 18 Z" fill="#334155" stroke="#0f172a" stroke-width="1.5" stroke-linejoin="round"/>
                    <!-- Heavy Base -->
                    <path d="M 34 26 L 66 26 L 74 34 L 26 34 Z" fill="#1e293b" stroke="#0f172a" stroke-width="1.5" stroke-linejoin="round"/>
                    <text x="50" y="24" font-size="5.5" font-weight="900" fill="#94a3b8" text-anchor="middle">100t</text>
                </g>

                <!-- === NEW CAPOO-INSPIRED PROPS === -->

                <!-- Melting Puddle (Shown during .mascot-melt) -->
                <g class="mascot-melt-prop">
                    <ellipse cx="50" cy="92" rx="30" ry="5" fill="#34d399" opacity="0.5"/>
                    <ellipse cx="50" cy="92" rx="22" ry="3.5" fill="#10b981" opacity="0.6"/>
                </g>

                <!-- Dizzy Stars (Shown during .mascot-dizzy) -->
                <g class="mascot-dizzy-prop">
                    <text class="dizzy-star ds1" x="22" y="38" font-size="10" fill="#fbbf24">★</text>
                    <text class="dizzy-star ds2" x="72" y="32" font-size="8" fill="#f59e0b">★</text>
                    <text class="dizzy-star ds3" x="48" y="22" font-size="6" fill="#fcd34d">✦</text>
                </g>

                <!-- Sneeze Particles (Shown during .mascot-sneeze) -->
                <g class="mascot-sneeze-prop">
                    <circle class="sneeze-p sp1" cx="78" cy="66" r="2" fill="#fde68a"/>
                    <circle class="sneeze-p sp2" cx="84" cy="60" r="2.5" fill="#fef3c7"/>
                    <circle class="sneeze-p sp3" cx="90" cy="68" r="1.5" fill="#fde68a"/>
                    <circle class="sneeze-p sp4" cx="86" cy="72" r="1.8" fill="#fef9c3"/>
                    <path class="sneeze-burst" d="M 70 65 L 95 55 M 70 68 L 95 68 M 70 71 L 92 78" stroke="#fde68a" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                </g>

                <!-- Ghost (Playing Dead / .mascot-dead) -->
                <g class="mascot-ghost-prop">
                    <path d="M 54 18 C 54 12 64 8 68 14 L 70 20" stroke="none" fill="rgba(255,255,255,0.35)"/>
                    <text x="62" y="16" font-size="8" fill="rgba(255,255,255,0.6)">👻</text>
                </g>

                <!-- Musical Notes (Dancing / .mascot-dance) -->
                <g class="mascot-music-prop">
                    <text class="music-note mn1" x="18" y="30" font-size="10" fill="#a78bfa">♪</text>
                    <text class="music-note mn2" x="76" y="24" font-size="12" fill="#8b5cf6">♫</text>
                    <text class="music-note mn3" x="30" y="16" font-size="8" fill="#c4b5fd">♬</text>
                </g>

                <!-- Sweat Drops (Panic / .mascot-panic) -->
                <g class="mascot-sweat-prop">
                    <path class="sweat-drop sd1" d="M 22 42 Q 20 48 22 52 Q 24 48 22 42 Z" fill="#60a5fa" opacity="0.8"/>
                    <path class="sweat-drop sd2" d="M 78 40 Q 76 46 78 50 Q 80 46 78 40 Z" fill="#60a5fa" opacity="0.8"/>
                    <path class="sweat-drop sd3" d="M 18 55 Q 16 60 18 64 Q 20 60 18 55 Z" fill="#93c5fd" opacity="0.6"/>
                </g>

                <!-- Question Marks (Confused / .mascot-confused) -->
                <g class="mascot-question-prop">
                    <text class="q-mark qm1" x="28" y="25" font-size="12" font-weight="900" fill="#fb923c">?</text>
                    <text class="q-mark qm2" x="66" y="20" font-size="15" font-weight="900" fill="#f97316">?</text>
                </g>

                <!-- Exclamation / Lightbulb (Eureka / .mascot-eureka) -->
                <g class="mascot-eureka-prop">
                    <circle cx="50" cy="10" r="8" fill="#fef3c7" stroke="#fbbf24" stroke-width="1.5"/>
                    <text x="50" y="14" font-size="10" text-anchor="middle" fill="#f59e0b">💡</text>
                    <line class="eureka-ray er1" x1="50" y1="1" x2="50" y2="-4" stroke="#fbbf24" stroke-width="1.5" stroke-linecap="round"/>
                    <line class="eureka-ray er2" x1="42" y1="4" x2="38" y2="-1" stroke="#fbbf24" stroke-width="1.5" stroke-linecap="round"/>
                    <line class="eureka-ray er3" x1="58" y1="4" x2="62" y2="-1" stroke="#fbbf24" stroke-width="1.5" stroke-linecap="round"/>
                </g>

                <!-- Crown (King / Flex Mode / .mascot-king) -->
                <g class="mascot-crown-prop">
                    <path d="M 32 30 L 35 18 L 42 26 L 50 14 L 58 26 L 65 18 L 68 30 Z" fill="#fbbf24" stroke="#f59e0b" stroke-width="1.5" stroke-linejoin="round"/>
                    <circle cx="42" cy="24" r="2" fill="#ef4444"/>
                    <circle cx="50" cy="19" r="2" fill="#3b82f6"/>
                    <circle cx="58" cy="24" r="2" fill="#10b981"/>
                </g>

                <!-- Balloon String (Float Away / .mascot-balloon) -->
                <g class="mascot-balloon-prop">
                    <path d="M 50 28 Q 48 18 50 10" stroke="#94a3b8" stroke-width="1" fill="none"/>
                    <ellipse cx="50" cy="4" rx="8" ry="9" fill="#f472b6" stroke="#ec4899" stroke-width="1"/>
                    <ellipse cx="48" cy="2" rx="2.5" ry="3" fill="rgba(255,255,255,0.3)"/>
                </g>

                <!-- Flower (Happy flower grow / .mascot-flower) -->
                <g class="mascot-flower-prop">
                    <line x1="50" y1="28" x2="50" y2="14" stroke="#22c55e" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="50" cy="11" r="5" fill="#fbbf24" stroke="#f59e0b" stroke-width="1"/>
                    <circle cx="46" cy="8" r="3" fill="#fb7185"/>
                    <circle cx="54" cy="8" r="3" fill="#fb7185"/>
                    <circle cx="46" cy="14" r="3" fill="#fb7185"/>
                    <circle cx="54" cy="14" r="3" fill="#fb7185"/>
                </g>

                <!-- X Eyes (Dead / Fainted / .mascot-faint) -->
                <!-- This is handled via CSS on the existing eyes -->

            </svg>
        </button>
    </div>

    <script>
    (function() {
        // --- 15 Pertanyaan Terkurasi: IoT, NPK, Pertanian Presisi & Nutrix ---
        const quizBank = [
            {
                q: "Apa fungsi utama unsur Nitrogen (N) bagi pertumbuhan tanaman?",
                opts: ["Mempercepat pembentukan bunga & buah", "Merangsang pertumbuhan vegetatif dan klorofil daun", "Memperkuat daya tahan batang terhadap hama", "Membantu penyerapan air di perakaran"],
                ans: 1,
                exp: "Nitrogen (N) adalah komponen inti klorofil yang sangat krusial untuk pertumbuhan daun hijau dan proses fotosintesis."
            },
            {
                q: "Unsur hara 'Fosfor' (P) pada pupuk NPK terutama berperan penting dalam...",
                opts: ["Perkembangan akar yang kuat serta pembungaan", "Mengatur penguapan air melalui stomata", "Mencegah daun cepat menguning", "Meningkatkan rasa manis pada hasil panen"],
                ans: 0,
                exp: "Fosfor (P) berfungsi merangsang pembelahan sel, pemanjangan perakaran bibit muda, dan mempercepat fase pembungaan."
            },
            {
                q: "Peran utama Kalium (K) pada tanaman budidaya adalah...",
                opts: ["Membuat daun tanaman menjadi ungu gelap", "Regulasi osmotik, transportasi nutrisi, dan imunitas tanaman", "Menggantikan peran cahaya matahari", "Menurunkan kadar gula buah"],
                ans: 1,
                exp: "Kalium (K) bertindak sebagai aktivator berbagai enzim, mengatur pembukaan stomata, dan menguatkan daya tahan tanaman terhadap penyakit."
            },
            {
                q: "Mengapa pemantauan pH tanah penting dalam pertanian presisi?",
                opts: ["pH tidak berpengaruh pada ketersediaan pupuk", "pH tanah menentukan ketersediaan hara yang dapat diserap oleh akar", "Semakin asam tanah, semakin subur tanaman", "pH netral selalu membunuh bakteri baik"],
                ans: 1,
                exp: "Pada pH tanah yang terlalu asam (< 5.5) atau terlalu basa (> 7.5), unsur hara seperti P, N, dan mikro terkunci dan tidak dapat diserap akar."
            },
            {
                q: "Sensor apa yang digunakan oleh NUTRIX untuk membaca kadar kelembaban tanah secara otomatis?",
                opts: ["Soil Moisture Sensor (Kapasitif / Resistif)", "Ultrasonic Distance Sensor", "Barometer BMP280", "PIR Motion Sensor"],
                ans: 0,
                exp: "Soil Moisture Sensor membaca kadar air dalam matriks tanah secara realtime agar penyiraman berlangsung presisi."
            },
            {
                q: "Protokol transmisi data ringan yang populer digunakan pada perangkat IoT pertanian adalah...",
                opts: ["FTP", "MQTT / HTTP REST", "Telnet", "SMTP"],
                ans: 1,
                exp: "MQTT dan HTTP REST API sangat efisien untuk mengirimkan telemetri sensor dengan konsumsi daya dan bandwidth rendah."
            },
            {
                q: "Jika daun tanaman muda berwarna pucat atau menguning (klorosis) mulai dari daun tua, tanaman kemungkinan kekurangan...",
                opts: ["Nitrogen (N)", "Boron (B)", "Kalsium (Ca)", "Tembaga (Cu)"],
                ans: 0,
                exp: "Nitrogen bersifat mobile; saat defisiensi, tanaman merelokasi N dari daun tua ke daun muda sehingga daun tua menguning lebih dulu."
            },
            {
                q: "Apa keuntungan utama sistem irigasi tetes (drip irrigation) presisi?",
                opts: ["Menghabiskan lebih banyak air", "Mengalirkan air & pupuk cair langsung ke perakaran dengan efisiensi tinggi", "Membuat daun selalu basah kuyup", "Menaikkan kelembaban udara berlebihan"],
                ans: 1,
                exp: "Drip irrigation meminimalkan penguapan dan mengalirkan nutrisi langsung ke zona perakaran aktif (fertigasi presisi)."
            },
            {
                q: "Suhu tanah yang ideal untuk sebagian besar tanaman hortikultura tropis berkisar antara...",
                opts: ["5°C - 12°C", "20°C - 30°C", "45°C - 55°C", "0°C - 5°C"],
                ans: 1,
                exp: "Suhu tanah 20°C - 30°C mengoptimalkan metabolisme akar, penyerapan hara, dan aktivitas mikroorganisme tanah yang menguntungkan."
            },
            {
                q: "Konsep utama 'Smart Farming 4.0' adalah...",
                opts: ["Mengganti semua petani dengan robot semata", "Pemanfaatan IoT, analitik data, dan sensor cerdas untuk efisiensi & hasil optimal", "Pertanian tradisional tanpa listrik sama sekali", "Penebangan hutan untuk lahan masif"],
                ans: 1,
                exp: "Smart Farming 4.0 mengintegrasikan sensor, otomatisasi, dan data-driven insight untuk meningkatkan hasil panen secara berkelanjutan."
            },
            {
                q: "Apa dampak dari kelebihan dosis pupuk kimia sintetis secara berlebihan pada tanah?",
                opts: ["Tanah menjadi semakin gembur", "Kerusakan struktur tanah, salinitas meningkat, dan membunuh mikroba tanah", "Tanaman kebal terhadap segala hama", "pH tanah selalu stabil 7.0"],
                ans: 1,
                exp: "Over-fertilization menyebabkan akumulasi garam kimia (salinitas tinggi) yang dapat membakar akar tanaman dan mematikan mikroflora tanah."
            },
            {
                q: "Sensor NPK optik/konduktivitas tanah dalam sistem cerdas berfungsi untuk...",
                opts: ["Mendeteksi intensitas sinar matahari", "Memperkirakan konsentrasi ion Nitrogen, Fosfor, dan Kalium di perakaran", "Mengusir burung pengganggu", "Mengukur kecepatan angin"],
                ans: 1,
                exp: "Sensor NPK mendeteksi kadar konsentrasi hara esensial dalam larutan tanah untuk rekomendasi pemupukan tepat takaran."
            },
            {
                q: "Apa singkatan dari IoT dalam konteks platform NUTRIX?",
                opts: ["Internet of Tools", "Internet of Things", "Input Output Testing", "Integrated Operating Task"],
                ans: 1,
                exp: "Internet of Things (IoT) adalah jaringan objek fisik yang terhubung ke internet dan saling bertukar data telemetri."
            },
            {
                q: "Bahan organik seperti kompos berfungsi krusial untuk tanah karena...",
                opts: ["Membuat tanah menjadi padat dan kedap air", "Memperbaiki struktur remah tanah, kapasitas simpan air, dan biologi tanah", "Menghilangkan semua cacing tanah", "Menghentikan pertumbuhan akar"],
                ans: 1,
                exp: "Kompos menyumbang humus yang memperbaiki porositas tanah, menahan kelembaban, dan menjadi rumah mikroba bermanfaat."
            },
            {
                q: "Tujuan utama pemanfaatan dashboard telemetri NUTRIX bagi pembudidaya adalah...",
                opts: ["Hanya sebagai hiasan tampilan layar", "Memantau kondisi lahan secara real-time dan mengambil keputusan secara presisi", "Membatasi konektivitas ke kebun", "Meningkatkan biaya operasional tanpa data"],
                ans: 1,
                exp: "Dashboard NUTRIX memberikan visibilitas real-time terhadap suhu, kelembaban, dan kondisi tanah demi pengambilan keputusan budidaya yang akurat."
            }
        ];

        // --- State Variables ---
        let shuffledIndices = [];
        let currentIndex = 0;
        let score = 0;
        let selectedOption = null;
        let isPanelOpen = false;

        // Elements
        const wrapper = document.getElementById('nutrixQuizWrapper');
        const mascotBtn = document.getElementById('nutrixMascotBtn');
        const panel = document.getElementById('nutrixQuizPanel');
        const badgeEl = document.getElementById('quizScoreBadge');
        const progressEl = document.getElementById('quizProgressBar');
        const questionEl = document.getElementById('quizQuestionText');
        const optionsEl = document.getElementById('quizOptionsContainer');
        const explanationEl = document.getElementById('quizExplanation');
        const btnNext = document.getElementById('quizBtnNext');
        const btnReveal = document.getElementById('quizBtnReveal');
        const btnShuffle = document.getElementById('quizBtnShuffle');
        const btnEnd = document.getElementById('quizBtnEnd');
        const mouthPath = document.getElementById('mascotMouthPath');

        // Mouth geometries for expressions & funny idle states
        const mouthShapes = {
            calm: "M 43 69 Q 50 74 57 69",
            happy: "M 39 67 Q 50 80 61 67",
            thinking: "M 44 70 L 56 70",
            sad: "M 44 73 Q 50 67 56 73",
            explaining: "M 42 68 Q 50 77 58 68",
            shock: "M 46 72 Q 50 64 54 72",
            sleepy: "M 45 70 Q 50 72 55 70",
            cat: "M 42 68 Q 46 72 50 69 Q 54 72 58 68", // :3 kitty mouth
            eating: "M 44 72 Q 50 77 56 72",
            rolling: "M 42 67 Q 50 78 58 67",
            anvil: "M 36 74 L 64 74", // squished wide flat mouth
            curious: "M 45 68 Q 52 74 57 70",
            shy: "M 46 71 Q 50 74 54 71",
            cool: "M 43 70 Q 50 75 57 70",
            love: "M 40 68 Q 50 79 60 68",
            poke: "M 45 72 A 5 5 0 1 0 55 72 A 5 5 0 1 0 45 72", // surprised gasp "O"
            // --- Capoo-inspired ---
            melt: "M 40 72 Q 50 78 60 72",       // droopy melting grin
            dizzy: "M 42 70 Q 47 74 50 70 Q 53 74 58 70", // wavy dizzy mouth
            sneeze: "M 44 66 Q 50 60 56 66",     // wide open sneeze
            dead: "M 44 71 L 56 71",              // flat line dead mouth
            dance: "M 39 67 Q 50 80 61 67",       // big happy dance grin
            panic: "M 44 68 Q 50 62 56 68",       // wobbly scared mouth
            confused: "M 44 71 Q 48 68 52 71 Q 56 74 58 70", // wavy confused
            eureka: "M 42 67 Q 50 79 58 67",      // big excited smile
            king: "M 40 68 Q 50 76 60 68",        // smug confident grin
            balloon: "M 45 72 A 5 5 0 1 0 55 72 A 5 5 0 1 0 45 72", // surprised O
            flower: "M 39 67 Q 50 80 61 67",       // happy bloom
            faint: "M 44 71 L 56 71",              // flat fainted
            hiccup: "M 45 72 A 4 4 0 1 0 55 72 A 4 4 0 1 0 45 72", // small O hiccup
            wiggle: "M 42 68 Q 46 72 50 69 Q 54 72 58 68" // :3 butt wiggle
        };

        const allExpressionClasses = [
            'mascot-calm', 'mascot-happy', 'mascot-thinking', 'mascot-sad', 'mascot-explaining',
            'mascot-shock', 'mascot-sleepy', 'mascot-cat', 'mascot-rolling', 'mascot-eating', 'mascot-anvil',
            'mascot-curious', 'mascot-shy', 'mascot-cool', 'mascot-love', 'mascot-poke',
            'mascot-melt', 'mascot-dizzy', 'mascot-sneeze', 'mascot-dead', 'mascot-dance',
            'mascot-panic', 'mascot-confused', 'mascot-eureka', 'mascot-king', 'mascot-balloon',
            'mascot-flower', 'mascot-faint', 'mascot-hiccup', 'mascot-wiggle'
        ];

        let currentActiveExpression = 'calm';
        let isSpecialIdleActive = false;

        function setMascotExpression(expr) {
            currentActiveExpression = expr;
            allExpressionClasses.forEach(c => wrapper.classList.remove(c));
            wrapper.classList.add('mascot-' + expr);
            if (mouthPath && mouthShapes[expr]) {
                mouthPath.setAttribute('d', mouthShapes[expr]);
            }
        }

        // --- Fisher-Yates Shuffle Algorithm ---
        function shuffleDeck() {
            shuffledIndices = Array.from({ length: quizBank.length }, (_, i) => i);
            for (let i = shuffledIndices.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [shuffledIndices[i], shuffledIndices[j]] = [shuffledIndices[j], shuffledIndices[i]];
            }
            currentIndex = 0;
            renderQuestion();
            setMascotExpression('thinking');
        }

        function renderQuestion() {
            if (shuffledIndices.length === 0) return;
            const qIndex = shuffledIndices[currentIndex];
            const item = quizBank[qIndex];

            // Update badge & progress bar
            const total = quizBank.length;
            const currentNum = currentIndex + 1;
            badgeEl.textContent = `Soal ${currentNum}/${total} • Skor: ${score}`;
            const pct = Math.round(((currentNum - 1) / total) * 100);
            progressEl.style.width = pct + '%';

            // Question text
            questionEl.textContent = `${currentNum}. ${item.q}`;

            // Reset options & buttons
            selectedOption = null;
            explanationEl.classList.remove('show');
            explanationEl.textContent = '';
            btnNext.disabled = true;
            btnReveal.style.display = 'none';

            optionsEl.innerHTML = '';
            item.opts.forEach((optText, idx) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'quiz-option';
                btn.textContent = `${String.fromCharCode(65 + idx)}. ${optText}`;
                btn.addEventListener('click', () => handleSelectOption(idx, item));
                optionsEl.appendChild(btn);
            });

            setMascotExpression('thinking');
        }

        function handleSelectOption(chosenIdx, item) {
            if (selectedOption !== null) return; // already answered
            selectedOption = chosenIdx;

            const optionButtons = optionsEl.querySelectorAll('.quiz-option');
            const isCorrect = (chosenIdx === item.ans);

            if (isCorrect) {
                score++;
                setMascotExpression('happy');
                optionButtons[chosenIdx].classList.add('correct');
            } else {
                setMascotExpression('sad');
                optionButtons[chosenIdx].classList.add('wrong');
                btnReveal.style.display = 'inline-flex';
            }

            // Disable further clicks
            optionButtons.forEach(b => b.classList.add('disabled'));

            // Enable next button
            btnNext.disabled = false;

            // Update score badge
            const currentNum = currentIndex + 1;
            badgeEl.textContent = `Soal ${currentNum}/${quizBank.length} • Skor: ${score}`;
        }

        function revealAnswer() {
            if (shuffledIndices.length === 0) return;
            const qIndex = shuffledIndices[currentIndex];
            const item = quizBank[qIndex];
            const optionButtons = optionsEl.querySelectorAll('.quiz-option');

            if (optionButtons[item.ans]) {
                optionButtons[item.ans].classList.add('correct');
            }
            explanationEl.textContent = `💡 Penjelasan: ${item.exp}`;
            explanationEl.classList.add('show');
            setMascotExpression('explaining');
            btnReveal.style.display = 'none';
        }

        function nextQuestion() {
            if (currentIndex < quizBank.length - 1) {
                currentIndex++;
                renderQuestion();
            } else {
                // Selesai seluruh soal
                progressEl.style.width = '100%';
                badgeEl.textContent = `Selesai! Skor: ${score}/${quizBank.length}`;
                questionEl.textContent = `🎉 Selamat! Kamu telah menyelesaikan seluruh 15 soal Asisten Kuis NUTRIX dengan skor ${score}/${quizBank.length}!`;
                optionsEl.innerHTML = '';
                explanationEl.classList.remove('show');
                btnNext.disabled = true;
                btnReveal.style.display = 'none';
                setMascotExpression('calm');
            }
        }

        function endQuiz() {
            panel.classList.remove('open');
            isPanelOpen = false;
            score = 0;
            currentIndex = 0;
            setMascotExpression('calm');
        }

        function toggleQuizPanel() {
            isPanelOpen = !isPanelOpen;
            if (isPanelOpen) {
                panel.classList.add('open');
                if (shuffledIndices.length === 0) {
                    shuffleDeck();
                } else {
                    setMascotExpression('thinking');
                }
            } else {
                panel.classList.remove('open');
                setMascotExpression('calm');
            }
        }

        const panelDragHandle = document.getElementById('quizPanelDragHandle');
        const btnCloseX = document.getElementById('quizBtnCloseX');

        if (btnCloseX) {
            btnCloseX.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleQuizPanel();
            });
        }

        // --- Bulletproof Pointer Events Drag & Click Logic (Scroll-Proof & Viewport-Safe) ---
        let isDragging = false;
        let activePointerId = null;
        let dragStartX = 0;
        let dragStartY = 0;
        let wrapperStartX = 0;
        let wrapperStartY = 0;
        let hasMoved = false;
        let dragSource = null; // 'mascot' or 'panel'

        function onPointerStart(e, source) {
            // Only primary pointer button (left click or touch)
            if (e.button !== undefined && e.button !== 0) return;
            
            // If clicking close button or interactive inputs in panel, don't drag
            if (e.target.closest('#quizBtnCloseX') || e.target.closest('.quiz-btn') || e.target.closest('.quiz-option')) return;

            isDragging = true;
            hasMoved = false;
            dragSource = source;
            activePointerId = e.pointerId;
            dragStartX = e.clientX;
            dragStartY = e.clientY;

            const rect = wrapper.getBoundingClientRect();
            wrapperStartX = rect.left;
            wrapperStartY = rect.top;

            // Ensure wrapper is placed in fixed viewport pixel coordinates immediately
            wrapper.style.left = wrapperStartX + 'px';
            wrapper.style.top = wrapperStartY + 'px';
            wrapper.style.right = 'auto';
            wrapper.style.bottom = 'auto';

            // Capture pointer on window so drag never gets lost regardless of scroll or fast movements
            try {
                if (e.target.setPointerCapture) {
                    e.target.setPointerCapture(e.pointerId);
                }
            } catch (err) {}

            e.preventDefault();
        }

        function onPointerMove(e) {
            if (!isDragging) return;
            if (activePointerId !== null && e.pointerId !== activePointerId) return;

            const dx = e.clientX - dragStartX;
            const dy = e.clientY - dragStartY;

            const threshold = (dragSource === 'panel') ? 3 : 5;
            if (!hasMoved && (Math.hypot(dx, dy) > threshold)) {
                hasMoved = true;
                wrapper.classList.add('dragging');
            }

            if (hasMoved) {
                let newLeft = wrapperStartX + dx;
                let newTop = wrapperStartY + dy;

                // Batasan pergerakan yang proporsional & rapi (Atas/bawah dibatasi rapi, samping dilebarkan leluasa)
                const viewportW = window.innerWidth || document.documentElement.clientWidth;
                const viewportH = window.innerHeight || document.documentElement.clientHeight;

                const wrapperW = wrapper.offsetWidth || 80;
                const wrapperH = wrapper.offsetHeight || 80;

                // Samping dilebarkan (margin tipis 12px dari tepi kiri & kanan layar)
                const minLeft = 12;
                const maxLeft = Math.max(minLeft, viewportW - wrapperW - 12);

                // Atas & bawah dibatasi nyaman (minimal 75px di bawah navbar, dan minimal 24px di atas dasar layar)
                const minTop = 75;
                const maxTop = Math.max(minTop, viewportH - wrapperH - 24);

                newLeft = Math.max(minLeft, Math.min(newLeft, maxLeft));
                newTop = Math.max(minTop, Math.min(newTop, maxTop));

                wrapper.style.left = newLeft + 'px';
                wrapper.style.top = newTop + 'px';
                wrapper.style.right = 'auto';
                wrapper.style.bottom = 'auto';

                // Automatically flip panel below mascot if placed near top of screen (< 460px)
                if (newTop < 460) {
                    wrapper.classList.add('panel-down');
                } else {
                    wrapper.classList.remove('panel-down');
                }
            }
        }

        function onPointerEnd(e) {
            if (!isDragging) return;
            if (activePointerId !== null && e.pointerId !== activePointerId) return;

            const wasMoved = hasMoved;
            const source = dragSource;

            try {
                if (e.target && e.target.releasePointerCapture) {
                    e.target.releasePointerCapture(e.pointerId);
                }
            } catch (err) {}

            isDragging = false;
            hasMoved = false;
            activePointerId = null;
            dragSource = null;
            wrapper.classList.remove('dragging');

            if (!wasMoved && source === 'mascot') {
                // Murni Klik (bukan drag) pada maskot -> Buka / Tutup Panel
                toggleQuizPanel();
            }
        }

        // Pointer event listeners on Mascot Button
        mascotBtn.addEventListener('pointerdown', (e) => onPointerStart(e, 'mascot'));

        // Pointer event listeners on Panel Header Handle
        if (panelDragHandle) {
            panelDragHandle.addEventListener('pointerdown', (e) => onPointerStart(e, 'panel'));
        }

        // Window level listeners guarantee drag tracks seamlessly anywhere across the document
        window.addEventListener('pointermove', onPointerMove, { passive: false });
        window.addEventListener('pointerup', onPointerEnd);
        window.addEventListener('pointercancel', onPointerEnd);

        // Event listeners for quiz buttons
        btnNext.addEventListener('click', nextQuestion);
        btnReveal.addEventListener('click', revealAnswer);
        btnShuffle.addEventListener('click', () => {
            shuffleDeck();
        });
        btnEnd.addEventListener('click', endQuiz);

        // --- RANDOM FUNNY IDLE BEHAVIOR SCHEDULER ---
        // Events: shock, sleepy, cat, rolling, eating, anvil, cool, love
        const funnyIdleEvents = [
            // Original 8
            { type: 'shock',    duration: 2400 },
            { type: 'sleepy',   duration: 4500 },
            { type: 'cat',      duration: 3800 },
            { type: 'rolling',  duration: 1200 },
            { type: 'eating',   duration: 3200 },
            { type: 'anvil',    duration: 2500 },
            { type: 'cool',     duration: 3500 },
            { type: 'love',     duration: 3000 },
            // Capoo-inspired new animations
            { type: 'melt',     duration: 4000 },  // melts into puddle
            { type: 'dizzy',    duration: 3000 },  // dizzy with stars
            { type: 'sneeze',   duration: 1800 },  // achoo!
            { type: 'dead',     duration: 3500 },  // plays dead with ghost
            { type: 'dance',    duration: 3500 },  // funky dance
            { type: 'panic',    duration: 2500 },  // sweating panic
            { type: 'confused', duration: 2800 },  // question marks
            { type: 'eureka',   duration: 2500 },  // lightbulb moment!
            { type: 'king',     duration: 3200 },  // crown flex
            { type: 'balloon',  duration: 4000 },  // float away
            { type: 'flower',   duration: 3500 },  // flower grows on head
            { type: 'faint',    duration: 3000 },  // x_x fainted
            { type: 'hiccup',   duration: 2200 },  // hic! hic!
            { type: 'wiggle',   duration: 2500 }   // capoo butt wiggle
        ];

        let idleTimer = null;

        function scheduleNextRandomIdle() {
            if (idleTimer) clearTimeout(idleTimer);
            // Random delay between 6s and 15s
            const delay = Math.floor(Math.random() * 9000) + 6000;
            idleTimer = setTimeout(() => {
                triggerRandomIdleEvent();
            }, delay);
        }

        function triggerRandomIdleEvent() {
            // Only trigger if panel is NOT open and mascot is NOT currently being dragged
            if (isPanelOpen || isDragging || isSpecialIdleActive) {
                scheduleNextRandomIdle();
                return;
            }

            const evt = funnyIdleEvents[Math.floor(Math.random() * funnyIdleEvents.length)];
            isSpecialIdleActive = true;
            setMascotExpression(evt.type);

            setTimeout(() => {
                isSpecialIdleActive = false;
                if (!isPanelOpen && !isDragging) {
                    setMascotExpression('calm');
                }
                scheduleNextRandomIdle();
            }, evt.duration);
        }

        // Start random idle scheduler
        scheduleNextRandomIdle();

        // --- CURSOR PROXIMITY REACTIONS & DYNAMIC EYE TRACKING ---
        let lastProximityExpr = null;
        const leftPupil = wrapper.querySelector('.mascot-left-eye .mascot-pupil');
        const rightPupil = wrapper.querySelector('.mascot-right-eye .mascot-pupil');

        window.addEventListener('mousemove', (e) => {
            // If dragging or in middle of a special cartoon gag, skip proximity tracking
            if (isDragging || isSpecialIdleActive) return;

            const rect = mascotBtn.getBoundingClientRect();
            const mascotCenterX = rect.left + rect.width / 2;
            const mascotCenterY = rect.top + rect.height / 2;

            const dx = e.clientX - mascotCenterX;
            const dy = e.clientY - mascotCenterY;
            const dist = Math.hypot(dx, dy);

            // 1. Dynamic Pupil Gaze Tracking towards mouse
            if (dist < 500 && leftPupil && rightPupil) {
                const angle = Math.atan2(dy, dx);
                const eyeRadius = Math.min(4, dist / 35);
                const pupilX = Math.cos(angle) * eyeRadius;
                const pupilY = Math.sin(angle) * eyeRadius;
                leftPupil.style.transform = `translate(${pupilX}px, ${pupilY}px)`;
                rightPupil.style.transform = `translate(${pupilX}px, ${pupilY}px)`;
            } else if (leftPupil && rightPupil) {
                leftPupil.style.transform = '';
                rightPupil.style.transform = '';
            }

            // 2. Multilevel reactions when panel is closed and mascot is calm
            if (!isPanelOpen) {
                if (dist < 38) {
                    // Terlalu dekat / disentuh kursor (Kaget terpental / Poke reaction)
                    if (lastProximityExpr !== 'poke') {
                        lastProximityExpr = 'poke';
                        setMascotExpression('poke');
                    }
                } else if (dist < 90) {
                    // Sangat dekat (Shy / Blushing berbinar & bergoyang)
                    if (lastProximityExpr !== 'shy') {
                        lastProximityExpr = 'shy';
                        setMascotExpression('shy');
                    }
                } else if (dist < 200) {
                    // Mendekat (Curious tilt / Mengamati pengguna)
                    if (lastProximityExpr !== 'curious') {
                        lastProximityExpr = 'curious';
                        setMascotExpression('curious');
                    }
                } else {
                    // Kursor menjauh -> kembali tenang
                    if (lastProximityExpr !== null) {
                        lastProximityExpr = null;
                        setMascotExpression('calm');
                    }
                }
            }
        });

        // ==========================================
        // DIRECTOR DOCK LOGIC FOR CONTENT CREATION
        // ==========================================
        const dockActiveLabel = document.getElementById('dockActiveState');
        const dockBtns = document.querySelectorAll('.dock-btn[data-anim]');
        const dockBtnReset = document.getElementById('dockBtnReset');
        const dockBtnPlayChain = document.getElementById('dockBtnPlayChain');
        const dockChainIcon = document.getElementById('dockChainIcon');
        const dockChainText = document.getElementById('dockChainText');

        let isDockChainRunning = false;
        let dockChainTimer = null;

        function updateDockUI(animName) {
            if (dockActiveLabel) dockActiveLabel.textContent = animName.toUpperCase();
            dockBtns.forEach(btn => {
                if (btn.dataset.anim === animName) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        }

        dockBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                if (isDockChainRunning) stopDockChain();
                const anim = btn.dataset.anim;
                // Override special idle lock so director can test immediately
                isSpecialIdleActive = true;
                setMascotExpression(anim);
                updateDockUI(anim);
            });
        });

        if (dockBtnReset) {
            dockBtnReset.addEventListener('click', () => {
                if (isDockChainRunning) stopDockChain();
                isSpecialIdleActive = false;
                setMascotExpression('calm');
                updateDockUI('calm');
            });
        }

        // Chain list for sequence recording
        const demoChainList = [
            { anim: 'dance',    duration: 3000 },
            { anim: 'wiggle',   duration: 2500 },
            { anim: 'melt',     duration: 3800 },
            { anim: 'dizzy',    duration: 2800 },
            { anim: 'sneeze',   duration: 2000 },
            { anim: 'shock',    duration: 2500 },
            { anim: 'panic',    duration: 2500 },
            { anim: 'confused', duration: 2500 },
            { anim: 'eureka',   duration: 2500 },
            { anim: 'king',     duration: 3200 },
            { anim: 'flower',   duration: 3000 },
            { anim: 'balloon',  duration: 3500 },
            { anim: 'cat',      duration: 3000 },
            { anim: 'eating',   duration: 3000 },
            { anim: 'rolling',  duration: 1500 },
            { anim: 'anvil',    duration: 2800 },
            { anim: 'dead',     duration: 3000 },
            { anim: 'cool',     duration: 3200 },
            { anim: 'love',     duration: 3000 },
            { anim: 'sleepy',   duration: 3500 },
            { anim: 'calm',     duration: 2000 }
        ];

        function playDockChainStep(idx) {
            if (!isDockChainRunning) return;
            if (idx >= demoChainList.length) idx = 0;
            const item = demoChainList[idx];
            isSpecialIdleActive = true;
            setMascotExpression(item.anim);
            updateDockUI(item.anim);
            dockChainTimer = setTimeout(() => {
                playDockChainStep(idx + 1);
            }, item.duration);
        }

        function startDockChain() {
            isDockChainRunning = true;
            if (dockBtnPlayChain) {
                dockBtnPlayChain.classList.add('running');
                dockChainIcon.textContent = '⏹';
                dockChainText.textContent = 'Stop Recording Chain';
            }
            playDockChainStep(0);
        }

        function stopDockChain() {
            isDockChainRunning = false;
            if (dockChainTimer) clearTimeout(dockChainTimer);
            if (dockBtnPlayChain) {
                dockBtnPlayChain.classList.remove('running');
                dockChainIcon.textContent = '▶';
                dockChainText.textContent = 'Play All Animations Sequence';
            }
            isSpecialIdleActive = false;
            setMascotExpression('calm');
            updateDockUI('calm');
        }

        if (dockBtnPlayChain) {
            dockBtnPlayChain.addEventListener('click', () => {
                if (isDockChainRunning) stopDockChain();
                else startDockChain();
            });
        }
    })();
    </script>
</body>
</html>

