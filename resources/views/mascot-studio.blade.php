<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NUTRIX Mascot Studio - Interactive Director Playground</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        :root {
            --studio-bg: #0b0f19;
            --studio-panel: rgba(17, 24, 39, 0.82);
            --studio-border: rgba(255, 255, 255, 0.1);
            --studio-accent: #10b981;
            --studio-accent-glow: rgba(16, 185, 129, 0.35);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--studio-bg);
            color: #f3f4f6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Top Navbar */
        .studio-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 28px;
            background: rgba(11, 15, 25, 0.95);
            border-bottom: 1px solid var(--studio-border);
            backdrop-filter: blur(12px);
            z-index: 100;
        }

        .studio-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }

        .brand-badge {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 4px 10px;
            background: rgba(16, 185, 129, 0.18);
            border: 1px solid rgba(16, 185, 129, 0.4);
            border-radius: 999px;
            color: #34d399;
        }

        .studio-nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-nav {
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid var(--studio-border);
            background: rgba(255, 255, 255, 0.06);
            color: #e5e7eb;
        }

        .btn-nav:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.25);
            color: #fff;
        }

        /* Main Workspace Layout */
        .studio-workspace {
            display: grid;
            grid-template-columns: 1fr 440px;
            flex: 1;
            min-height: calc(100vh - 69px);
        }

        @media (max-width: 1024px) {
            .studio-workspace {
                grid-template-columns: 1fr;
            }
        }

        /* Stage Viewport */
        .studio-stage-container {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: radial-gradient(circle at center, #1e293b 0%, #0b0f19 70%);
            overflow: hidden;
            transition: background 0.4s ease;
        }

        /* Backdrop Presets */
        .studio-stage-container.bg-dark {
            background: radial-gradient(circle at center, #1e293b 0%, #0b0f19 70%);
        }
        .studio-stage-container.bg-greenscreen {
            background: #00ff00 !important;
        }
        .studio-stage-container.bg-bluescreen {
            background: #0000ff !important;
        }
        .studio-stage-container.bg-studio-light {
            background: radial-gradient(circle at center, #f8fafc 0%, #cbd5e1 100%);
        }
        .studio-stage-container.bg-cyberpunk {
            background: radial-gradient(circle at center, #3b0764 0%, #090214 80%);
        }

        /* Stage Overlay HUD */
        .stage-hud-top {
            position: absolute;
            top: 20px;
            left: 24px;
            right: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            pointer-events: none;
            z-index: 10;
        }

        .hud-status {
            pointer-events: auto;
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(15, 23, 42, 0.75);
            padding: 8px 16px;
            border-radius: 999px;
            border: 1px solid var(--studio-border);
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
        }

        .hud-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 10px #10b981;
            animation: pulse-dot 1.5s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.6; }
        }

        .stage-stage-pedestal {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: transform 0.25s ease;
        }

        /* Studio Mascot Wrapper Override */
        .studio-stage-pedestal .quiz-mascot-wrapper {
            position: relative !important;
            bottom: auto !important;
            right: auto !important;
            left: auto !important;
            top: auto !important;
            transform: none !important;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .studio-stage-pedestal .quiz-mascot-btn {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
            cursor: pointer;
            transition: transform 0.15s ease;
        }

        /* Shadow Platform */
        .studio-pedestal-shadow {
            width: 140px;
            height: 18px;
            background: radial-gradient(ellipse at center, rgba(0,0,0,0.45) 0%, rgba(0,0,0,0) 70%);
            border-radius: 50%;
            margin-top: -12px;
            transition: all 0.3s ease;
        }

        /* HUD Bottom Bar for Backdrop & Zoom */
        .stage-quickbar {
            position: absolute;
            bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(16px);
            padding: 8px 14px;
            border-radius: 16px;
            border: 1px solid var(--studio-border);
            z-index: 10;
        }

        .quickbar-group {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .quickbar-divider {
            width: 1px;
            height: 20px;
            background: rgba(255, 255, 255, 0.15);
            margin: 0 4px;
        }

        .q-btn {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid transparent;
            background: rgba(255, 255, 255, 0.05);
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.15s;
        }

        .q-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
        }

        .q-btn.active {
            color: #10b981;
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.35);
        }

        /* Right Control Deck / Sidebar */
        .studio-deck {
            background: var(--studio-panel);
            border-left: 1px solid var(--studio-border);
            backdrop-filter: blur(20px);
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            max-height: calc(100vh - 69px);
        }

        .deck-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--studio-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: rgba(17, 24, 39, 0.95);
            z-index: 5;
        }

        .deck-title {
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .deck-section {
            padding: 20px 24px;
            border-bottom: 1px solid var(--studio-border);
        }

        .section-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #94a3b8;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Master Director Sequence Box */
        .director-box {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.12), rgba(59, 130, 246, 0.08));
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 6px;
        }

        .btn-chain-play {
            width: 100%;
            padding: 12px 18px;
            border-radius: 10px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 14px var(--studio-accent-glow);
            transition: all 0.2s ease;
        }

        .btn-chain-play:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px var(--studio-accent-glow);
            filter: brightness(1.1);
        }

        .btn-chain-play.running {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4);
        }

        /* Animation Buttons Grid */
        .anim-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .btn-anim {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--studio-border);
            padding: 10px 12px;
            border-radius: 10px;
            color: #e2e8f0;
            font-size: 12.5px;
            font-weight: 600;
            text-align: left;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 9px;
            transition: all 0.18s ease;
        }

        .btn-anim:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            transform: translateY(-1px);
        }

        .btn-anim.active {
            background: rgba(16, 185, 129, 0.18);
            border-color: #10b981;
            color: #34d399;
            box-shadow: 0 0 12px rgba(16, 185, 129, 0.25);
        }

        .anim-icon {
            font-size: 16px;
            line-height: 1;
        }

        /* Toast / Flash Notification */
        .studio-toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: #0f172a;
            color: #fff;
            border: 1px solid #10b981;
            padding: 10px 20px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .studio-toast.show {
            transform: translateX(-50%) translateY(0);
        }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <header class="studio-nav">
        <a href="{{ route('welcome') }}" class="studio-brand">
            <span style="font-size: 20px;">🌱</span>
            <span style="font-size: 18px; font-weight: 800; letter-spacing: -0.02em;">NUTRIX <span style="color: #10b981;">STUDIO</span></span>
            <span class="brand-badge">DIRECTOR MODE</span>
        </a>
        <div class="studio-nav-actions">
            <button type="button" class="btn-nav" id="btnResetMascot" title="Reset ke pose tenang default">
                <span>🔄</span> Reset Pose
            </button>
            <a href="{{ route('welcome') }}" class="btn-nav" style="border-color: rgba(16, 185, 129, 0.4); color: #34d399;">
                <span>⬅️</span> Kembali ke Beranda
            </a>
        </div>
    </header>

    <!-- Main Studio Workspace -->
    <main class="studio-workspace">
        
        <!-- Stage Viewport (Panggung Maskot) -->
        <div class="studio-stage-container bg-dark" id="stageContainer">
            
            <!-- Top HUD Info -->
            <div class="stage-hud-top">
                <div class="hud-status">
                    <div class="hud-dot"></div>
                    <span id="hudStateLabel">STATE: CALM</span>
                </div>
                <div class="hud-status" style="font-size: 11px; opacity: 0.85;">
                    <span id="hudInfoLabel">Mascot Interactive Playground</span>
                </div>
            </div>

            <!-- Pedestal + Mascot -->
            <div class="studio-stage-pedestal" id="mascotPedestal" style="transform: scale(2.2);">
                
                <div id="studioMascotWrapper" class="quiz-mascot-wrapper mascot-calm">
                    <button type="button" id="studioMascotBtn" class="quiz-mascot-btn" aria-label="Maskot Nutrix">
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
                                <rect x="47" y="22" width="6" height="12" rx="3" fill="#059669"/>
                                <path class="mascot-leaf mascot-leaf-left" d="M 48 24 C 30 18 22 8 34 5 C 46 2 48 18 48 24 Z" fill="url(#mascotLeafGrad)"/>
                                <path class="mascot-leaf mascot-leaf-right" d="M 52 24 C 70 18 78 8 66 5 C 54 2 52 18 52 24 Z" fill="url(#mascotLeafGrad)"/>
                            </g>

                            <!-- Main Body Group -->
                            <g class="mascot-body-group">
                                <polygon class="mascot-body" points="50,28 78,36 88,64 72,90 28,90 12,64 22,36" 
                                         fill="url(#mascotBodyGrad)" stroke="#047857" stroke-width="2.5" stroke-linejoin="round"/>
                                
                                <path d="M 28 42 Q 50 36 72 42 Q 80 62 72 78 Q 50 84 28 78 Q 20 62 28 42 Z" 
                                      fill="rgba(255, 255, 255, 0.18)" stroke="rgba(255, 255, 255, 0.4)" stroke-width="1.2"/>

                                <!-- Cat Whiskers -->
                                <g class="mascot-cat-whiskers">
                                    <line x1="22" y1="58" x2="8" y2="54" stroke="#1e293b" stroke-width="1.5" stroke-linecap="round"/>
                                    <line x1="22" y1="63" x2="6" y2="64" stroke="#1e293b" stroke-width="1.5" stroke-linecap="round"/>
                                    <line x1="78" y1="58" x2="92" y2="54" stroke="#1e293b" stroke-width="1.5" stroke-linecap="round"/>
                                    <line x1="78" y1="63" x2="94" y2="64" stroke="#1e293b" stroke-width="1.5" stroke-linecap="round"/>
                                </g>

                                <!-- Blushes -->
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

                                <!-- Mouth -->
                                <path id="studioMouthPath" class="mascot-mouth" d="M 43 69 Q 50 74 57 69"/>

                                <!-- Cookie -->
                                <g class="mascot-eating-prop">
                                    <circle cx="50" cy="74" r="5.5" fill="#d97706" stroke="#92400e" stroke-width="1"/>
                                    <circle cx="48" cy="73" r="1" fill="#78350f"/>
                                    <circle cx="52" cy="74" r="1" fill="#78350f"/>
                                </g>

                                <!-- Sleep Zzz -->
                                <g class="mascot-zzz">
                                    <text x="64" y="38" font-size="9" font-weight="900" fill="#93c5fd" class="zzz-item z1">Z</text>
                                    <text x="73" y="27" font-size="12" font-weight="900" fill="#60a5fa" class="zzz-item z2">Z</text>
                                    <text x="82" y="15" font-size="15" font-weight="900" fill="#3b82f6" class="zzz-item z3">Z</text>
                                </g>

                                <!-- Sunglasses -->
                                <g class="mascot-sunglasses-prop">
                                    <path d="M 26 48 L 48 48 L 48 59 L 45 62 L 29 62 L 26 59 Z" fill="#0f172a" stroke="#ffffff" stroke-width="1"/>
                                    <path d="M 52 48 L 74 48 L 74 59 L 71 62 L 55 62 L 52 59 Z" fill="#0f172a" stroke="#ffffff" stroke-width="1"/>
                                    <rect x="47" y="50" width="6" height="3.5" fill="#0f172a"/>
                                    <polygon points="30,51 36,51 32,59 28,59" fill="#ffffff" opacity="0.75"/>
                                    <polygon points="56,51 62,51 58,59 54,59" fill="#ffffff" opacity="0.75"/>
                                </g>

                                <!-- Hearts -->
                                <g class="mascot-hearts-prop">
                                    <path class="heart-item h1" d="M 46 25 C 46 22 41 18 36 21 C 31 24 36 30 46 35 C 56 30 61 24 56 21 C 51 18 46 22 46 25 Z" fill="#f43f5e"/>
                                    <path class="heart-item h2" d="M 72 35 C 72 32 68 28 64 30 C 60 32 64 36 72 40 C 80 36 84 32 80 30 C 76 28 72 32 72 35 Z" fill="#fb7185"/>
                                </g>
                            </g>

                            <!-- Shock Sparks -->
                            <g class="mascot-shock-sparks">
                                <path d="M 16 35 L 8 45 L 18 47 L 10 60" stroke="#facc15" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                <path d="M 84 35 L 92 45 L 82 47 L 90 60" stroke="#facc15" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                <path d="M 44 8 L 50 16 L 46 18 L 54 26" stroke="#facc15" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                            </g>

                            <!-- Anvil 100t -->
                            <g class="mascot-anvil-prop">
                                <path d="M 28 8 L 72 8 L 84 14 L 70 18 L 60 18 L 62 26 L 38 26 L 40 18 L 26 18 Z" fill="#334155" stroke="#0f172a" stroke-width="1.5" stroke-linejoin="round"/>
                                <path d="M 34 26 L 66 26 L 74 34 L 26 34 Z" fill="#1e293b" stroke="#0f172a" stroke-width="1.5" stroke-linejoin="round"/>
                                <text x="50" y="24" font-size="5.5" font-weight="900" fill="#94a3b8" text-anchor="middle">100t</text>
                            </g>

                            <!-- CAPOO PROPS -->
                            <g class="mascot-melt-prop">
                                <ellipse cx="50" cy="92" rx="30" ry="5" fill="#34d399" opacity="0.5"/>
                                <ellipse cx="50" cy="92" rx="22" ry="3.5" fill="#10b981" opacity="0.6"/>
                            </g>

                            <g class="mascot-dizzy-prop">
                                <text class="dizzy-star ds1" x="22" y="38" font-size="10" fill="#fbbf24">★</text>
                                <text class="dizzy-star ds2" x="72" y="32" font-size="8" fill="#f59e0b">★</text>
                                <text class="dizzy-star ds3" x="48" y="22" font-size="6" fill="#fcd34d">✦</text>
                            </g>

                            <g class="mascot-sneeze-prop">
                                <circle class="sneeze-p sp1" cx="78" cy="66" r="2" fill="#fde68a"/>
                                <circle class="sneeze-p sp2" cx="84" cy="60" r="2.5" fill="#fef3c7"/>
                                <circle class="sneeze-p sp3" cx="90" cy="68" r="1.5" fill="#fde68a"/>
                                <circle class="sneeze-p sp4" cx="86" cy="72" r="1.8" fill="#fef9c3"/>
                                <path class="sneeze-burst" d="M 70 65 L 95 55 M 70 68 L 95 68 M 70 71 L 92 78" stroke="#fde68a" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                            </g>

                            <g class="mascot-ghost-prop">
                                <path d="M 54 18 C 54 12 64 8 68 14 L 70 20" stroke="none" fill="rgba(255,255,255,0.35)"/>
                                <text x="62" y="16" font-size="8" fill="rgba(255,255,255,0.6)">👻</text>
                            </g>

                            <g class="mascot-music-prop">
                                <text class="music-note mn1" x="18" y="30" font-size="10" fill="#a78bfa">♪</text>
                                <text class="music-note mn2" x="76" y="24" font-size="12" fill="#8b5cf6">♫</text>
                                <text class="music-note mn3" x="30" y="16" font-size="8" fill="#c4b5fd">♬</text>
                            </g>

                            <g class="mascot-sweat-prop">
                                <path class="sweat-drop sd1" d="M 22 42 Q 20 48 22 52 Q 24 48 22 42 Z" fill="#60a5fa" opacity="0.8"/>
                                <path class="sweat-drop sd2" d="M 78 40 Q 76 46 78 50 Q 80 46 78 40 Z" fill="#60a5fa" opacity="0.8"/>
                                <path class="sweat-drop sd3" d="M 18 55 Q 16 60 18 64 Q 20 60 18 55 Z" fill="#93c5fd" opacity="0.6"/>
                            </g>

                            <g class="mascot-question-prop">
                                <text class="q-mark qm1" x="28" y="25" font-size="12" font-weight="900" fill="#fb923c">?</text>
                                <text class="q-mark qm2" x="66" y="20" font-size="15" font-weight="900" fill="#f97316">?</text>
                            </g>

                            <g class="mascot-eureka-prop">
                                <circle cx="50" cy="10" r="8" fill="#fef3c7" stroke="#fbbf24" stroke-width="1.5"/>
                                <text x="50" y="14" font-size="10" text-anchor="middle" fill="#f59e0b">💡</text>
                                <line class="eureka-ray er1" x1="50" y1="1" x2="50" y2="-4" stroke="#fbbf24" stroke-width="1.5" stroke-linecap="round"/>
                                <line class="eureka-ray er2" x1="42" y1="4" x2="38" y2="-1" stroke="#fbbf24" stroke-width="1.5" stroke-linecap="round"/>
                                <line class="eureka-ray er3" x1="58" y1="4" x2="62" y2="-1" stroke="#fbbf24" stroke-width="1.5" stroke-linecap="round"/>
                            </g>

                            <g class="mascot-crown-prop">
                                <path d="M 32 30 L 35 18 L 42 26 L 50 14 L 58 26 L 65 18 L 68 30 Z" fill="#fbbf24" stroke="#f59e0b" stroke-width="1.5" stroke-linejoin="round"/>
                                <circle cx="42" cy="24" r="2" fill="#ef4444"/>
                                <circle cx="50" cy="19" r="2" fill="#3b82f6"/>
                                <circle cx="58" cy="24" r="2" fill="#10b981"/>
                            </g>

                            <g class="mascot-balloon-prop">
                                <path d="M 50 28 Q 48 18 50 10" stroke="#94a3b8" stroke-width="1" fill="none"/>
                                <ellipse cx="50" cy="4" rx="8" ry="9" fill="#f472b6" stroke="#ec4899" stroke-width="1"/>
                                <ellipse cx="48" cy="2" rx="2.5" ry="3" fill="rgba(255,255,255,0.3)"/>
                            </g>

                            <g class="mascot-flower-prop">
                                <line x1="50" y1="28" x2="50" y2="14" stroke="#22c55e" stroke-width="2" stroke-linecap="round"/>
                                <circle cx="50" cy="11" r="5" fill="#fbbf24" stroke="#f59e0b" stroke-width="1"/>
                                <circle cx="46" cy="8" r="3" fill="#fb7185"/>
                                <circle cx="54" cy="8" r="3" fill="#fb7185"/>
                                <circle cx="46" cy="14" r="3" fill="#fb7185"/>
                                <circle cx="54" cy="14" r="3" fill="#fb7185"/>
                            </g>
                        </svg>
                    </button>
                </div>

                <!-- Pedestal Floor Shadow -->
                <div class="studio-pedestal-shadow" id="pedestalShadow"></div>
            </div>

            <!-- Stage Bottom Toolbar (Zoom & Background) -->
            <div class="stage-quickbar">
                <div class="quickbar-group">
                    <span style="font-size: 11px; font-weight: 700; color: #94a3b8;">ZOOM:</span>
                    <button type="button" class="q-btn" onclick="setZoom(1.2)">1.2x</button>
                    <button type="button" class="q-btn active" onclick="setZoom(2.2)">2.2x</button>
                    <button type="button" class="q-btn" onclick="setZoom(3.2)">3.2x</button>
                    <button type="button" class="q-btn" onclick="setZoom(4.2)">4.2x (Close-up)</button>
                </div>
                <div class="quickbar-divider"></div>
                <div class="quickbar-group">
                    <span style="font-size: 11px; font-weight: 700; color: #94a3b8;">BACKDROP:</span>
                    <button type="button" class="q-btn active" onclick="setBackdrop('bg-dark')">🌑 Dark</button>
                    <button type="button" class="q-btn" onclick="setBackdrop('bg-greenscreen')">🟢 Green Screen</button>
                    <button type="button" class="q-btn" onclick="setBackdrop('bg-bluescreen')">🔵 Blue Screen</button>
                    <button type="button" class="q-btn" onclick="setBackdrop('bg-studio-light')">⚪ Light</button>
                    <button type="button" class="q-btn" onclick="setBackdrop('bg-cyberpunk')">🟣 Cyber</button>
                </div>
            </div>

        </div>

        <!-- Right Side: Control Deck / Director Studio -->
        <aside class="studio-deck">
            
            <div class="deck-header">
                <div class="deck-title">
                    <span>🎛️</span> Director Control Deck
                </div>
                <span style="font-size: 11px; font-weight: 700; color: #10b981;">22 ANIMATIONS</span>
            </div>

            <!-- Chain / Combo Reel Player -->
            <div class="deck-section">
                <div class="section-label">
                    <span>🎬 ALL-IN-ONE CHAIN PLAYER</span>
                    <span style="color: #34d399;">DEMO KONTEN</span>
                </div>
                <div class="director-box">
                    <p style="font-size: 12px; color: #94a3b8; margin-bottom: 12px; line-height: 1.5;">
                        Putar seluruh animasi secara berurutan otomatis untuk perekaman video reels/TikTok tanpa jeda!
                    </p>
                    <button type="button" class="btn-chain-play" id="btnPlayChain">
                        <span id="chainIcon">▶</span>
                        <span id="chainText">Play All Animations Sequence</span>
                    </button>
                </div>
            </div>

            <!-- Category 1: Bugcat Capoo Cartoon Gags -->
            <div class="deck-section">
                <div class="section-label">
                    <span>🐱 BUGCAT CAPOO GAGS</span>
                    <span style="color: #f472b6;">14 ANIMATIONS</span>
                </div>
                <div class="anim-grid">
                    <button type="button" class="btn-anim" data-anim="melt"><span class="anim-icon">🫠</span> Melt Puddle</button>
                    <button type="button" class="btn-anim" data-anim="dance"><span class="anim-icon">💃</span> Capoo Dance</button>
                    <button type="button" class="btn-anim" data-anim="wiggle"><span class="anim-icon">🍑</span> Butt Wiggle</button>
                    <button type="button" class="btn-anim" data-anim="dizzy"><span class="anim-icon">💫</span> Dizzy Stars</button>
                    <button type="button" class="btn-anim" data-anim="sneeze"><span class="anim-icon">🤧</span> Sneeze Achoo</button>
                    <button type="button" class="btn-anim" data-anim="dead"><span class="anim-icon">👻</span> Playing Dead</button>
                    <button type="button" class="btn-anim" data-anim="panic"><span class="anim-icon">😰</span> Panic Sweat</button>
                    <button type="button" class="btn-anim" data-anim="confused"><span class="anim-icon">❓</span> Confused ??</button>
                    <button type="button" class="btn-anim" data-anim="eureka"><span class="anim-icon">💡</span> Eureka Idea</button>
                    <button type="button" class="btn-anim" data-anim="king"><span class="anim-icon">👑</span> King Swagger</button>
                    <button type="button" class="btn-anim" data-anim="balloon"><span class="anim-icon">🎈</span> Float Balloon</button>
                    <button type="button" class="btn-anim" data-anim="flower"><span class="anim-icon">🌸</span> Flower Grow</button>
                    <button type="button" class="btn-anim" data-anim="faint"><span class="anim-icon">😵</span> Fainted X_X</button>
                    <button type="button" class="btn-anim" data-anim="hiccup"><span class="anim-icon">🫢</span> Hiccup Hic!</button>
                </div>
            </div>

            <!-- Category 2: Classic Gags & Props -->
            <div class="deck-section">
                <div class="section-label">
                    <span>🎭 CLASSIC CARTOON GAGS</span>
                    <span style="color: #60a5fa;">8 ANIMATIONS</span>
                </div>
                <div class="anim-grid">
                    <button type="button" class="btn-anim" data-anim="shock"><span class="anim-icon">⚡</span> Shock Kesetrum</button>
                    <button type="button" class="btn-anim" data-anim="sleepy"><span class="anim-icon">💤</span> Sleepy Zzz</button>
                    <button type="button" class="btn-anim" data-anim="cat"><span class="anim-icon">😺</span> Kitty Cat :3</button>
                    <button type="button" class="btn-anim" data-anim="rolling"><span class="anim-icon">🌀</span> Rolling Spin</button>
                    <button type="button" class="btn-anim" data-anim="eating"><span class="anim-icon">🍪</span> Eating Cookie</button>
                    <button type="button" class="btn-anim" data-anim="anvil"><span class="anim-icon">🔨</span> Anvil 100t Squish</button>
                    <button type="button" class="btn-anim" data-anim="cool"><span class="anim-icon">🕶️</span> Thug Life Cool</button>
                    <button type="button" class="btn-anim" data-anim="love"><span class="anim-icon">💖</span> Love Heart Eyes</button>
                </div>
            </div>

            <!-- Category 3: Base Emotions -->
            <div class="deck-section">
                <div class="section-label">
                    <span>😊 BASE EMOTIONS</span>
                    <span style="color: #fbbf24;">5 EMOTIONS</span>
                </div>
                <div class="anim-grid">
                    <button type="button" class="btn-anim active" data-anim="calm"><span class="anim-icon">🌿</span> Calm Neutral</button>
                    <button type="button" class="btn-anim" data-anim="happy"><span class="anim-icon">😄</span> Happy Bounce</button>
                    <button type="button" class="btn-anim" data-anim="thinking"><span class="anim-icon">🤔</span> Thinking</button>
                    <button type="button" class="btn-anim" data-anim="sad"><span class="anim-icon">😢</span> Sad Shake</button>
                    <button type="button" class="btn-anim" data-anim="explaining"><span class="anim-icon">🗣️</span> Explaining</button>
                </div>
            </div>

        </aside>

    </main>

    <!-- Notification Toast -->
    <div class="studio-toast" id="studioToast">
        <span id="toastIcon">✨</span>
        <span id="toastMsg">Action triggered</span>
    </div>

    <script>
    (function() {
        const wrapper = document.getElementById('studioMascotWrapper');
        const mouthPath = document.getElementById('studioMouthPath');
        const hudStateLabel = document.getElementById('hudStateLabel');
        const pedestal = document.getElementById('mascotPedestal');
        const stageContainer = document.getElementById('stageContainer');
        const btnPlayChain = document.getElementById('btnPlayChain');
        const chainIcon = document.getElementById('chainIcon');
        const chainText = document.getElementById('chainText');
        const animButtons = document.querySelectorAll('.btn-anim');
        const toast = document.getElementById('studioToast');
        const toastIcon = document.getElementById('toastIcon');
        const toastMsg = document.getElementById('toastMsg');

        // Mouth geometries dictionary
        const mouthShapes = {
            calm: "M 43 69 Q 50 74 57 69",
            happy: "M 39 67 Q 50 80 61 67",
            thinking: "M 44 70 L 56 70",
            sad: "M 44 73 Q 50 67 56 73",
            explaining: "M 42 68 Q 50 77 58 68",
            shock: "M 46 72 Q 50 64 54 72",
            sleepy: "M 45 70 Q 50 72 55 70",
            cat: "M 42 68 Q 46 72 50 69 Q 54 72 58 68",
            eating: "M 44 72 Q 50 77 56 72",
            rolling: "M 42 67 Q 50 78 58 67",
            anvil: "M 36 74 L 64 74",
            curious: "M 45 68 Q 52 74 57 70",
            shy: "M 46 71 Q 50 74 54 71",
            cool: "M 43 70 Q 50 75 57 70",
            love: "M 40 68 Q 50 79 60 68",
            poke: "M 45 72 A 5 5 0 1 0 55 72 A 5 5 0 1 0 45 72",
            // Capoo
            melt: "M 40 72 Q 50 78 60 72",
            dizzy: "M 42 70 Q 47 74 50 70 Q 53 74 58 70",
            sneeze: "M 44 66 Q 50 60 56 66",
            dead: "M 44 71 L 56 71",
            dance: "M 39 67 Q 50 80 61 67",
            panic: "M 44 68 Q 50 62 56 68",
            confused: "M 44 71 Q 48 68 52 71 Q 56 74 58 70",
            eureka: "M 42 67 Q 50 79 58 67",
            king: "M 40 68 Q 50 76 60 68",
            balloon: "M 45 72 A 5 5 0 1 0 55 72 A 5 5 0 1 0 45 72",
            flower: "M 39 67 Q 50 80 61 67",
            faint: "M 44 71 L 56 71",
            hiccup: "M 45 72 A 4 4 0 1 0 55 72 A 4 4 0 1 0 45 72",
            wiggle: "M 42 68 Q 46 72 50 69 Q 54 72 58 68"
        };

        const allExpressionClasses = [
            'mascot-calm', 'mascot-happy', 'mascot-thinking', 'mascot-sad', 'mascot-explaining',
            'mascot-shock', 'mascot-sleepy', 'mascot-cat', 'mascot-rolling', 'mascot-eating', 'mascot-anvil',
            'mascot-curious', 'mascot-shy', 'mascot-cool', 'mascot-love', 'mascot-poke',
            'mascot-melt', 'mascot-dizzy', 'mascot-sneeze', 'mascot-dead', 'mascot-dance',
            'mascot-panic', 'mascot-confused', 'mascot-eureka', 'mascot-king', 'mascot-balloon',
            'mascot-flower', 'mascot-faint', 'mascot-hiccup', 'mascot-wiggle'
        ];

        let currentAnim = 'calm';
        let isChainRunning = false;
        let chainTimeout = null;

        function showToast(icon, text) {
            toastIcon.textContent = icon;
            toastMsg.textContent = text;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2200);
        }

        function setExpression(name) {
            currentAnim = name;
            allExpressionClasses.forEach(cls => wrapper.classList.remove(cls));
            wrapper.classList.add('mascot-' + name);
            if (mouthPath && mouthShapes[name]) {
                mouthPath.setAttribute('d', mouthShapes[name]);
            }
            hudStateLabel.textContent = 'STATE: ' + name.toUpperCase();

            // Update active state in grid
            animButtons.forEach(btn => {
                if (btn.dataset.anim === name) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        }

        // Attach click listeners to all animation buttons
        animButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                if (isChainRunning) stopChain();
                const anim = btn.dataset.anim;
                setExpression(anim);
                showToast(btn.querySelector('.anim-icon').textContent, 'Playing: ' + anim.toUpperCase());
            });
        });

        // Reset button
        document.getElementById('btnResetMascot').addEventListener('click', () => {
            if (isChainRunning) stopChain();
            setExpression('calm');
            showToast('🔄', 'Reset ke posisi tenang');
        });

        // Click mascot to trigger wiggle/happy
        document.getElementById('studioMascotBtn').addEventListener('click', () => {
            setExpression('wiggle');
            showToast('🍑', 'Mascot Wiggle Poke!');
            setTimeout(() => {
                if (currentAnim === 'wiggle') setExpression('calm');
            }, 2500);
        });

        // All-in-One Chain Sequence List
        const chainSequence = [
            { anim: 'dance',    duration: 3200 },
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
            { anim: 'cat',      duration: 3200 },
            { anim: 'eating',   duration: 3000 },
            { anim: 'rolling',  duration: 1500 },
            { anim: 'anvil',    duration: 2800 },
            { anim: 'dead',     duration: 3000 },
            { anim: 'cool',     duration: 3500 },
            { anim: 'love',     duration: 3000 },
            { anim: 'sleepy',   duration: 3500 },
            { anim: 'calm',     duration: 2000 }
        ];

        function playChainStep(index) {
            if (!isChainRunning) return;
            if (index >= chainSequence.length) {
                // Loop back or finish
                showToast('🎉', 'Sequence Selesai! Mengulang kembali...');
                index = 0;
            }

            const step = chainSequence[index];
            setExpression(step.anim);
            showToast('🎬', `[${index + 1}/${chainSequence.length}] Sequence: ${step.anim.toUpperCase()}`);

            chainTimeout = setTimeout(() => {
                playChainStep(index + 1);
            }, step.duration);
        }

        function startChain() {
            isChainRunning = true;
            btnPlayChain.classList.add('running');
            chainIcon.textContent = '⏹';
            chainText.textContent = 'Stop Sequence Recording';
            playChainStep(0);
        }

        function stopChain() {
            isChainRunning = false;
            if (chainTimeout) clearTimeout(chainTimeout);
            btnPlayChain.classList.remove('running');
            chainIcon.textContent = '▶';
            chainText.textContent = 'Play All Animations Sequence';
            setExpression('calm');
            showToast('⏹', 'Sequence Dihentikan');
        }

        btnPlayChain.addEventListener('click', () => {
            if (isChainRunning) {
                stopChain();
            } else {
                startChain();
            }
        });

        // Global controls (Zoom & Backdrop)
        window.setZoom = function(scale) {
            pedestal.style.transform = `scale(${scale})`;
            document.querySelectorAll('.stage-quickbar .quickbar-group:first-child .q-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.textContent.includes(scale.toString())) btn.classList.add('active');
            });
            showToast('🔍', `Zoom set to ${scale}x`);
        };

        window.setBackdrop = function(className) {
            stageContainer.className = 'studio-stage-container ' + className;
            document.querySelectorAll('.stage-quickbar .quickbar-group:last-child .q-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            showToast('🎨', `Backdrop diubah ke ${className.replace('bg-', '')}`);
        };

        // Initialize with Calm
        setExpression('calm');

    })();
    </script>
</body>
</html>
