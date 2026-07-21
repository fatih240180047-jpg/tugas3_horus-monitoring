<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('judul', 'Dasbor') - Horus SCM Intelligence</title>
    <!-- Google Fonts: Inter, Outfit & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js & Chart.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* ================================================
         * HORUS DESIGN SYSTEM — Premium Dark Theme v2.0
         * ================================================ */
        :root {
            /* Core Colors */
            --c-bg:           #080c14;
            --c-surface:      #0d1420;
            --c-surface-2:    #111827;
            --c-surface-3:    #1a2235;
            --c-border:       rgba(255,255,255,0.07);
            --c-border-hover: rgba(255,255,255,0.14);

            /* Brand Accent */
            --c-red:          #e03131;
            --c-red-light:    #ff4d4f;
            --c-red-dim:      rgba(224, 49, 49, 0.12);
            --c-red-glow:     rgba(224, 49, 49, 0.25);

            /* Status Colors */
            --c-kritis:       #ff4d4f;
            --c-tinggi:       #ff7a45;
            --c-sedang:       #ffc53d;
            --c-rendah:       #52c41a;
            --c-blue:         #4096ff;

            /* Text */
            --c-text-1:       #f0f4f8;
            --c-text-2:       #8899aa;
            --c-text-3:       #4d6070;

            /* Glass */
            --glass-bg:       rgba(13, 20, 32, 0.75);
            --glass-border:   rgba(255,255,255,0.07);
            --glass-blur:     blur(16px);

            /* Sidebar */
            --sidebar-lebar:     258px;
            --sidebar-collapsed: 66px;
            --sidebar-speed:     0.26s;

            /* Legacy compat */
            --warna-merah:         var(--c-red);
            --warna-merah-terang:  var(--c-red-light);
            --warna-merah-gelap:   #7f1d1d;
            --warna-charcoal:      var(--c-bg);
            --warna-charcoal-terang: var(--c-surface-2);
            --warna-charcoal-border: rgba(255,255,255,0.09);
            --warna-emas:          #d97706;
            --warna-emas-terang:   #fbbf24;
            --warna-teks-putih:    var(--c-text-1);
            --warna-teks-abu:      var(--c-text-2);
            --warna-kaca:          var(--glass-bg);
            --blur-kaca:           var(--glass-blur);
            --warna-hijau:         var(--c-rendah);
            --warna-kuning:        var(--c-sedang);
            --warna-oranye:        var(--c-tinggi);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--c-bg);
            background-image:
                radial-gradient(ellipse 80% 50% at 10% 0%, rgba(224,49,49,0.04) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 90% 100%, rgba(64,150,255,0.03) 0%, transparent 60%);
            color: var(--c-text-1);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: var(--sidebar-lebar);
            background: linear-gradient(180deg, #060a10 0%, #0a0f1a 50%, #080c14 100%);
            border-right: 1px solid rgba(255,255,255,0.06);
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            z-index: 200;
            transition: width var(--sidebar-speed) cubic-bezier(0.4,0,0.2,1);
            overflow: hidden;
            box-shadow: 4px 0 24px rgba(0,0,0,0.4);
        }
        .sidebar.collapsed { width: var(--sidebar-collapsed); }

        /* Sidebar Header */
        .sidebar-header {
            padding: 16px 12px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            min-height: 64px;
            flex-shrink: 0;
        }
        .sidebar-brand { display: flex; align-items: center; gap: 10px; overflow: hidden; min-width: 0; }
        .brand-icon { font-size: 20px; color: var(--c-red-light); flex-shrink: 0; }
        .sidebar-logo {
            font-family: 'Outfit', sans-serif;
            font-size: 17px;
            font-weight: 800;
            color: var(--c-text-1);
            letter-spacing: 2px;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            opacity: 1;
            max-width: 160px;
            transition: opacity var(--sidebar-speed) ease, max-width var(--sidebar-speed) ease;
        }
        .sidebar.collapsed .sidebar-logo { opacity: 0; max-width: 0; }

        .sidebar-toggle {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            color: var(--c-text-3);
            width: 28px; height: 28px;
            border-radius: 7px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s ease;
            font-size: 11px;
        }
        .sidebar-toggle:hover {
            background: var(--c-red-dim);
            border-color: rgba(224,49,49,0.4);
            color: var(--c-red-light);
        }

        /* Sidebar Menu */
        .menu-section-label {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: rgba(136,153,170,0.35);
            padding: 14px 14px 4px;
            white-space: nowrap;
            overflow: hidden;
            opacity: 1;
            transition: opacity var(--sidebar-speed) ease;
        }
        .sidebar.collapsed .menu-section-label { opacity: 0; }

        .sidebar-menu {
            flex-grow: 1;
            padding: 10px 8px;
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 2px;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .sidebar-menu::-webkit-scrollbar { width: 2px; }
        .sidebar-menu::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.06); border-radius: 2px; }

        .menu-item { position: relative; }
        .menu-item a {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 9px 12px;
            color: var(--c-text-2);
            text-decoration: none;
            border-radius: 9px;
            font-weight: 500;
            font-size: 13px;
            transition: all 0.18s ease;
            white-space: nowrap;
            overflow: hidden;
            position: relative;
            border: 1px solid transparent;
        }
        .menu-item a .menu-icon {
            font-size: 13.5px;
            flex-shrink: 0;
            width: 18px;
            text-align: center;
            transition: color 0.18s ease;
        }
        .menu-item a .menu-text {
            overflow: hidden;
            opacity: 1;
            max-width: 180px;
            transition: opacity var(--sidebar-speed) ease, max-width var(--sidebar-speed) ease;
        }
        .sidebar.collapsed .menu-item a .menu-text { opacity: 0; max-width: 0; }

        .menu-item a:hover {
            background: rgba(255,255,255,0.04);
            color: var(--c-text-1);
            border-color: rgba(255,255,255,0.06);
            transform: translateX(2px);
        }
        .menu-item.aktif a {
            background: linear-gradient(135deg, rgba(224,49,49,0.18) 0%, rgba(224,49,49,0.08) 100%);
            color: #fff;
            border-color: rgba(224,49,49,0.3);
            box-shadow: inset 3px 0 0 var(--c-red-light), 0 2px 12px rgba(224,49,49,0.15);
            transform: none;
        }
        .menu-item.aktif a .menu-icon { color: var(--c-red-light); }

        /* Sidebar Tooltip */
        .menu-item .sidebar-tooltip {
            position: absolute;
            left: calc(var(--sidebar-collapsed) + 8px);
            top: 50%;
            transform: translateY(-50%);
            background: var(--c-surface-3);
            border: 1px solid rgba(255,255,255,0.1);
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.15s ease;
            box-shadow: 0 8px 24px rgba(0,0,0,0.5);
            z-index: 999;
        }
        .sidebar.collapsed .menu-item:hover .sidebar-tooltip { opacity: 1; }

        .menu-divider {
            height: 1px;
            background: rgba(255,255,255,0.05);
            margin: 6px 12px;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            padding: 10px 8px;
            border-top: 1px solid rgba(255,255,255,0.05);
            display: flex;
            align-items: center;
            gap: 10px;
            overflow: hidden;
            min-height: 60px;
            flex-shrink: 0;
        }
        .user-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e03131, #991b1b);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 13px; color: #fff;
            flex-shrink: 0;
            border: 2px solid rgba(224,49,49,0.4);
            box-shadow: 0 0 12px rgba(224,49,49,0.2);
        }
        .user-info-wrapper {
            flex-grow: 1; overflow: hidden;
            opacity: 1; max-width: 160px;
            transition: opacity var(--sidebar-speed) ease, max-width var(--sidebar-speed) ease;
        }
        .sidebar.collapsed .user-info-wrapper { opacity: 0; max-width: 0; }
        .user-name { font-weight: 600; font-size: 12px; color: var(--c-text-1); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role { font-size: 10px; color: var(--c-text-3); text-transform: uppercase; letter-spacing: 0.5px; }
        .logout-btn {
            background: none; border: none;
            color: var(--c-text-3); font-size: 14px;
            cursor: pointer; transition: color 0.2s ease;
            flex-shrink: 0; padding: 5px; border-radius: 6px;
        }
        .logout-btn:hover { color: var(--c-red-light); background: var(--c-red-dim); }

        /* ===== KONTEN UTAMA ===== */
        .konten-utama {
            margin-left: var(--sidebar-lebar);
            flex-grow: 1;
            padding: 28px 32px;
            min-height: 100vh;
            transition: margin-left var(--sidebar-speed) cubic-bezier(0.4,0,0.2,1);
        }
        .konten-utama.expanded { margin-left: var(--sidebar-collapsed); }

        .header-konten {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
        }
        .judul-halaman {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        /* ===== ALERTS ===== */
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            font-size: 13.5px;
        }
        .alert-sukses { background: rgba(82,196,26,0.08); border: 1px solid rgba(82,196,26,0.25); color: #95de64; }
        .alert-error  { background: rgba(255,77,79,0.08); border: 1px solid rgba(255,77,79,0.25); color: #ff7875; }

        /* ===== GRID KARTU METRIK ===== */
        .grid-kartu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .kartu-metrik {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            padding: 20px;
            border-radius: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.22s ease;
            position: relative;
            overflow: hidden;
        }
        .kartu-metrik::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.02) 0%, transparent 60%);
            pointer-events: none;
        }
        .kartu-metrik:hover {
            transform: translateY(-3px);
            border-color: rgba(255,255,255,0.12);
            box-shadow: 0 12px 32px rgba(0,0,0,0.35);
        }
        .metrik-detail h3 { font-size: 11px; color: var(--c-text-3); margin-bottom: 8px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.8px; }
        .metrik-detail .nilai { font-size: 28px; font-weight: 700; font-family: 'Outfit', sans-serif; }
        .metrik-icon { font-size: 30px; opacity: 0.55; }

        /* ===== CARD PANEL ===== */
        .card-panel {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            padding: 22px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }
        .card-panel::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            pointer-events: none;
        }
        .card-panel-title {
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 9px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            padding-bottom: 12px;
            letter-spacing: -0.1px;
        }

        /* ===== FORM ===== */
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; margin-bottom: 6px; font-weight: 500; font-size: 12px; color: var(--c-text-3); text-transform: uppercase; letter-spacing: 0.6px; }
        .form-input {
            width: 100%;
            padding: 9px 13px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 8px;
            color: var(--c-text-1);
            font-size: 13.5px;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-input:focus { outline: none; border-color: rgba(224,49,49,0.5); box-shadow: 0 0 0 3px rgba(224,49,49,0.1); }

        /* ===== BUTTONS ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            border: none;
            letter-spacing: 0.1px;
        }
        .btn-primer {
            background: linear-gradient(135deg, #c92a2a 0%, #e03131 100%);
            color: #fff;
            box-shadow: 0 2px 8px rgba(224,49,49,0.2);
        }
        .btn-primer:hover {
            background: linear-gradient(135deg, #e03131 0%, #ff4d4f 100%);
            box-shadow: 0 4px 18px rgba(224,49,49,0.4);
            transform: translateY(-1px);
        }
        .btn-sekunder {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            color: var(--c-text-1);
        }
        .btn-sekunder:hover { background: rgba(255,255,255,0.07); border-color: rgba(255,255,255,0.16); }

        /* ===== BADGES ===== */
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-kritis { background: rgba(255,77,79,0.12); border: 1px solid rgba(255,77,79,0.35); color: #ff7875; }
        .badge-tinggi { background: rgba(255,122,69,0.12); border: 1px solid rgba(255,122,69,0.35); color: #ff9c6e; }
        .badge-sedang { background: rgba(255,197,61,0.12); border: 1px solid rgba(255,197,61,0.35); color: #ffd666; }
        .badge-rendah { background: rgba(82,196,26,0.12);  border: 1px solid rgba(82,196,26,0.35);  color: #95de64; }

        /* ===== INFO LIST ===== */
        .info-list { list-style: none; }
        .info-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            font-size: 13px;
        }
        .info-list li:last-child { border-bottom: none; }
        .info-label { color: var(--c-text-3); font-size: 12px; }
        .info-value { font-weight: 600; color: var(--c-text-1); font-family: 'JetBrains Mono', monospace; font-size: 12.5px; }
        .grid-intelijen {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        /* ===== TICKER BAR ===== */
        .scm-ticker-container {
            background: rgba(6, 10, 16, 0.95);
            border: 1px solid rgba(224,49,49,0.18);
            border-radius: 10px;
            height: 36px;
            display: flex;
            align-items: center;
            overflow: hidden;
            position: relative;
            z-index: 10;
            box-shadow: 0 0 20px rgba(224,49,49,0.08), 0 4px 12px rgba(0,0,0,0.3);
            margin-bottom: 20px;
        }
        .scm-ticker-label {
            background: linear-gradient(90deg, #991b1b 0%, #e03131 100%);
            color: #fff;
            padding: 0 14px;
            height: 100%;
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 9.5px;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            white-space: nowrap;
            position: relative;
            flex-shrink: 0;
        }
        .scm-ticker-label::after {
            content: '';
            position: absolute;
            right: -8px;
            top: 0; bottom: 0;
            width: 8px;
            background: linear-gradient(90deg, #e03131, transparent);
        }
        .scm-ticker-content { flex-grow: 1; display: flex; align-items: center; overflow: hidden; position: relative; height: 100%; }
        .scm-ticker-track { display: flex; white-space: nowrap; position: absolute; animation: scm-ticker-scroll 55s linear infinite; }
        .scm-ticker-track:hover { animation-play-state: paused; }
        .scm-ticker-item {
            display: flex; align-items: center;
            padding: 0 18px;
            color: var(--c-text-2);
            font-size: 11px;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s ease;
            gap: 6px;
        }
        .scm-ticker-item:hover { color: var(--c-text-1); }
        .scm-ticker-separator { color: rgba(255,77,79,0.35); margin: 0 2px; font-size: 10px; }

        @keyframes scm-ticker-scroll { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
        @keyframes pulse-blink { 0% { opacity: 0.4; } 100% { opacity: 1; } }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .sidebar { width: var(--sidebar-collapsed); }
            .sidebar-logo, .user-info-wrapper, .menu-text { opacity: 0; max-width: 0; }
            .konten-utama { margin-left: var(--sidebar-collapsed); padding: 16px; }
        }
    </style>
    @yield('gaya_tambahan')
</head>
<body x-data="{ sidebarOpen: true }">

    <!-- Sidebar Menu -->
    <div class="sidebar" :class="{ 'collapsed': !sidebarOpen }">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <img src="{{ asset('images/Horus.png') }}" alt="Horus Icon" class="brand-icon" style="height: 24px; width: auto; margin-right: 10px;">
                <div class="sidebar-logo">Horus</div>
            </div>
            <button class="sidebar-toggle" @click="sidebarOpen = !sidebarOpen" :title="sidebarOpen ? 'Perkecil Panel' : 'Perluas Panel'">
                <i class="fa-solid" :class="sidebarOpen ? 'fa-chevron-left' : 'fa-chevron-right'"></i>
            </button>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-section-label">NAVIGASI UTAMA</li>

            <li class="menu-item {{ Request::routeIs('dasbor.indeks') ? 'aktif' : '' }}">
                <a href="{{ route('dasbor.indeks') }}">
                    <i class="fa-solid fa-earth-americas menu-icon"></i>
                    <span class="menu-text">Control Center</span>
                </a>
                <div class="sidebar-tooltip">Control Center</div>
            </li>

            <li class="menu-item {{ Request::routeIs('favorit.indeks') ? 'aktif' : '' }}">
                <a href="{{ route('favorit.indeks') }}" style="position:relative;">
                    <i class="fa-solid fa-star menu-icon"></i>
                    <span class="menu-text">Pemantauan Favorit</span>
                    @php
                        $jmlFavorit = Auth::user()->favoritNegara()->count();
                    @endphp
                    @if($jmlFavorit > 0)
                    <span id="sidebar-favorit-count" style="margin-left:auto;background:#991b1b;color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:20px;min-width:20px;text-align:center;">{{ $jmlFavorit }}</span>
                    @endif
                </a>
                <div class="sidebar-tooltip">Favorit ({{ Auth::user()->favoritNegara()->count() }})</div>
            </li>

            <li class="menu-item {{ Request::routeIs('komparasi.indeks') ? 'aktif' : '' }}">
                <a href="{{ route('komparasi.indeks') }}">
                    <i class="fa-solid fa-scale-balanced menu-icon"></i>
                    <span class="menu-text">Komparasi Negara</span>
                </a>
                <div class="sidebar-tooltip">Komparasi Negara</div>
            </li>

            <div class="menu-divider"></div>
            <li class="menu-section-label">MANAJEMEN RISIKO</li>

            <li class="menu-item {{ Request::routeIs('risiko.rekomendasi.indeks') ? 'aktif' : '' }}">
                <a href="{{ route('risiko.rekomendasi.indeks') }}">
                    <i class="fa-solid fa-hand-holding-hand menu-icon"></i>
                    <span class="menu-text">Tindakan Mitigasi</span>
                </a>
                <div class="sidebar-tooltip">Tindakan Mitigasi</div>
            </li>

            @if(Auth::user()->adalahSuperAdmin() || Auth::user()->mempunyaiPeran('administrator'))
            <li class="menu-item {{ Request::routeIs('risiko.bobot.form') ? 'aktif' : '' }}">
                <a href="{{ route('risiko.bobot.form') }}">
                    <i class="fa-solid fa-sliders menu-icon"></i>
                    <span class="menu-text">Bobot Risiko</span>
                </a>
                <div class="sidebar-tooltip">Bobot Risiko</div>
            </li>
            @endif

            <div class="menu-divider"></div>
            <li class="menu-section-label">INTELIJEN BERITA</li>

            <li class="menu-item {{ Request::routeIs('analisis.*') ? 'aktif' : '' }}">
                <a href="{{ route('analisis.indeks') }}">
                    <i class="fa-solid fa-newspaper menu-icon"></i>
                    <span class="menu-text">Analisis Artikel</span>
                </a>
                <div class="sidebar-tooltip">Analisis Artikel</div>
            </li>

            @if(Auth::user()->adalahSuperAdmin() || Auth::user()->adalahAdmin())
            <div class="menu-divider"></div>
            <li class="menu-section-label">ADMINISTRASI</li>

            <li class="menu-item {{ Request::routeIs('admin.pengguna.*') ? 'aktif' : '' }}">
                <a href="{{ route('admin.pengguna.indeks') }}">
                    <i class="fa-solid fa-users-gear menu-icon"></i>
                    <span class="menu-text">Kelola Pengguna</span>
                </a>
                <div class="sidebar-tooltip">Kelola Pengguna</div>
            </li>
            @endif
        </ul>

        <div class="sidebar-footer">
            <div class="user-avatar">{{ strtoupper(substr(Auth::user()->nama, 0, 2)) }}</div>
            <div class="user-info-wrapper">
                <div class="user-name">{{ Auth::user()->nama }}</div>
                <div class="user-role">{{ Auth::user()->peran->first()?->nama ?? 'Pengguna' }}</div>
            </div>
            <form action="{{ route('keluar') }}" method="POST" id="logout-form">
                @csrf
                <button type="submit" class="logout-btn" title="Keluar dari sistem">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Konten Utama -->
    <div class="konten-utama" :class="{ 'expanded': !sidebarOpen }">
        
        <!-- Marquee Berita Panas Global -->
        @php
            $beritaPanas = \App\Models\ArtikelBerita::with('negara')
                ->whereIn('keparahan', ['kritis', 'tinggi'])
                ->orderBy('diterbitkan_pada', 'desc')
                ->limit(10)
                ->get();
        @endphp
        @if($beritaPanas->count() > 0)
        <div class="scm-ticker-container">
            <div class="scm-ticker-label">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px; animation: pulse-blink 0.8s infinite alternate;"></i> Live SCM Alert
            </div>
            <div class="scm-ticker-content">
                <div class="scm-ticker-track">
                    @foreach($beritaPanas as $b)
                        <a href="{{ $b->url_asli ?? $b->tautan ?? '#' }}" target="_blank" class="scm-ticker-item">
                            @if($b->negara)
                                <img src="{{ $b->negara->bendera_url }}" style="height: 10px; width: 15px; border-radius: 1px; margin-right: 6px; object-fit: cover; border: 1px solid rgba(255,255,255,0.15);">
                            @endif
                            <span class="badge badge-{{ $b->keparahan }}" style="margin-right: 8px; font-size: 8px; padding: 1px 3px;">{{ $b->keparahan }}</span>
                            <span>{{ $b->judul }}</span>
                        </a>
                        <span class="scm-ticker-separator">•</span>
                    @endforeach
                    <!-- Duplikasi untuk seamless loop -->
                    @foreach($beritaPanas as $b)
                        <a href="{{ $b->url_asli ?? $b->tautan ?? '#' }}" target="_blank" class="scm-ticker-item">
                            @if($b->negara)
                                <img src="{{ $b->negara->bendera_url }}" style="height: 10px; width: 15px; border-radius: 1px; margin-right: 6px; object-fit: cover; border: 1px solid rgba(255,255,255,0.15);">
                            @endif
                            <span class="badge badge-{{ $b->keparahan }}" style="margin-right: 8px; font-size: 8px; padding: 1px 3px;">{{ $b->keparahan }}</span>
                            <span>{{ $b->judul }}</span>
                        </a>
                        <span class="scm-ticker-separator">•</span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <div class="header-konten">
            <h1 class="judul-halaman">@yield('judul')</h1>
            <div style="font-size: 13px; color: var(--warna-teks-abu); display: flex; align-items: center; gap: 8px;">
                <i class="fa-regular fa-calendar-days"></i> {{ date('d M Y') }}
            </div>
        </div>

        <!-- Tampilan Pesan Sukses / Gagal -->
        @if(session('sukses'))
            <div class="alert alert-sukses">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('sukses') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('konten')
    </div>

    @yield('skrip_tambahan')
</body>
</html>
