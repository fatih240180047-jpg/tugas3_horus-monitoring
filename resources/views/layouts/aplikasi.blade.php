<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('judul', 'Dasbor') - Horus SCM Intelligence</title>
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js & Chart.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --warna-merah: #991b1b;
            --warna-merah-terang: #ef4444;
            --warna-merah-gelap: #7f1d1d;
            --warna-charcoal: #0d1117;
            --warna-charcoal-terang: #1f2937;
            --warna-charcoal-border: #374151;
            --warna-emas: #d97706;
            --warna-emas-terang: #fbbf24;
            --warna-teks-putih: #f9fafb;
            --warna-teks-abu: #9ca3af;
            --warna-kaca: rgba(17, 24, 39, 0.7);
            --blur-kaca: blur(12px);
            --warna-hijau: #16a34a;
            --warna-kuning: #eab308;
            --warna-oranye: #f97316;

            /* Sidebar dimensions */
            --sidebar-lebar: 260px;
            --sidebar-collapsed: 70px;
            --sidebar-speed: 0.28s;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--warna-charcoal);
            color: var(--warna-teks-putih);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* ================================================
         * SIDEBAR — Collapsible System
         * ================================================ */
        .sidebar {
            width: var(--sidebar-lebar);
            background: linear-gradient(180deg, #0a0e17 0%, #111827 60%, #0d1117 100%);
            border-right: 1px solid rgba(55, 65, 81, 0.4);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            z-index: 200;
            transition: width var(--sidebar-speed) cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed);
        }

        /* Header */
        .sidebar-header {
            padding: 18px 14px;
            border-bottom: 1px solid rgba(55, 65, 81, 0.35);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            min-height: 68px;
            flex-shrink: 0;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            overflow: hidden;
            min-width: 0;
        }

        .brand-icon {
            font-size: 20px;
            color: var(--warna-merah-terang);
            flex-shrink: 0;
        }

        .sidebar-logo {
            font-family: 'Outfit', sans-serif;
            font-size: 17px;
            font-weight: 800;
            color: var(--warna-teks-putih);
            letter-spacing: 1px;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            opacity: 1;
            max-width: 160px;
            transition: opacity var(--sidebar-speed) ease, max-width var(--sidebar-speed) ease;
        }

        .sidebar-logo span { color: var(--warna-merah-terang); }

        .sidebar.collapsed .sidebar-logo {
            opacity: 0;
            max-width: 0;
        }

        /* Toggle Button */
        .sidebar-toggle {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(55, 65, 81, 0.5);
            color: var(--warna-teks-abu);
            width: 28px;
            height: 28px;
            border-radius: 7px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s ease;
            font-size: 11px;
        }

        .sidebar-toggle:hover {
            background: rgba(239, 68, 68, 0.12);
            border-color: rgba(239, 68, 68, 0.5);
            color: var(--warna-merah-terang);
        }

        /* Menu section label */
        .menu-section-label {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: rgba(156, 163, 175, 0.45);
            padding: 14px 12px 4px;
            white-space: nowrap;
            overflow: hidden;
            opacity: 1;
            transition: opacity var(--sidebar-speed) ease;
        }

        .sidebar.collapsed .menu-section-label { opacity: 0; }

        /* Menu */
        .sidebar-menu {
            flex-grow: 1;
            padding: 12px 10px;
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 3px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-menu::-webkit-scrollbar { width: 3px; }
        .sidebar-menu::-webkit-scrollbar-thumb { background: rgba(55, 65, 81, 0.4); border-radius: 3px; }

        .menu-item { position: relative; }

        .menu-item a {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 12px;
            color: var(--warna-teks-abu);
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            font-size: 13px;
            transition: all 0.2s ease;
            white-space: nowrap;
            overflow: hidden;
        }

        .menu-item a .menu-icon {
            font-size: 14px;
            flex-shrink: 0;
            width: 18px;
            text-align: center;
        }

        .menu-item a .menu-text {
            overflow: hidden;
            opacity: 1;
            max-width: 180px;
            transition: opacity var(--sidebar-speed) ease, max-width var(--sidebar-speed) ease;
        }

        .sidebar.collapsed .menu-item a .menu-text {
            opacity: 0;
            max-width: 0;
        }

        .menu-item a:hover {
            background: rgba(239, 68, 68, 0.08);
            color: var(--warna-teks-putih);
        }

        .menu-item.aktif a {
            background: linear-gradient(135deg, rgba(127, 29, 29, 0.7), rgba(153, 27, 27, 0.4));
            color: #fff;
            box-shadow: 0 3px 12px rgba(153, 27, 27, 0.25);
            border: 1px solid rgba(239, 68, 68, 0.25);
        }

        /* Tooltip saat Collapsed */
        .menu-item .sidebar-tooltip {
            position: absolute;
            left: calc(var(--sidebar-collapsed) + 6px);
            top: 50%;
            transform: translateY(-50%);
            background: #1a2332;
            border: 1px solid rgba(55, 65, 81, 0.7);
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.15s ease;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar.collapsed .menu-item:hover .sidebar-tooltip { opacity: 1; }

        /* Divider */
        .menu-divider {
            height: 1px;
            background: rgba(55, 65, 81, 0.3);
            margin: 8px 12px;
        }

        /* Footer */
        .sidebar-footer {
            padding: 12px 10px;
            border-top: 1px solid rgba(55, 65, 81, 0.35);
            display: flex;
            align-items: center;
            gap: 10px;
            overflow: hidden;
            min-height: 62px;
            flex-shrink: 0;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #991b1b, #7f1d1d);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            color: #fff;
            flex-shrink: 0;
            border: 2px solid rgba(239, 68, 68, 0.3);
        }

        .user-info-wrapper {
            flex-grow: 1;
            overflow: hidden;
            opacity: 1;
            max-width: 160px;
            transition: opacity var(--sidebar-speed) ease, max-width var(--sidebar-speed) ease;
        }

        .sidebar.collapsed .user-info-wrapper {
            opacity: 0;
            max-width: 0;
        }

        .user-name { font-weight: 600; font-size: 12px; color: var(--warna-teks-putih); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role { font-size: 10px; color: var(--warna-teks-abu); text-transform: uppercase; letter-spacing: 0.5px; }

        .logout-btn {
            background: none;
            border: none;
            color: var(--warna-teks-abu);
            font-size: 14px;
            cursor: pointer;
            transition: color 0.2s ease;
            flex-shrink: 0;
            padding: 5px;
            border-radius: 6px;
        }

        .logout-btn:hover {
            color: var(--warna-merah-terang);
            background: rgba(239, 68, 68, 0.08);
        }

        /* ================================================
         * KONTEN UTAMA
         * ================================================ */
        .konten-utama {
            margin-left: var(--sidebar-lebar);
            flex-grow: 1;
            padding: 32px 36px;
            min-height: 100vh;
            transition: margin-left var(--sidebar-speed) cubic-bezier(0.4, 0, 0.2, 1);
        }

        .konten-utama.expanded {
            margin-left: var(--sidebar-collapsed);
        }

        .header-konten {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }

        .judul-halaman {
            font-family: 'Outfit', sans-serif;
            font-size: 26px;
            font-weight: 700;
        }

        /* Alert / Notifikasi Flash */
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            font-size: 14px;
        }

        .alert-sukses {
            background: rgba(22, 163, 74, 0.1);
            border: 1px solid rgba(22, 163, 74, 0.35);
            color: #4ade80;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.35);
            color: #f87171;
        }

        /* Kartu & Container */
        .grid-kartu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }

        .kartu-metrik {
            background: var(--warna-kaca);
            backdrop-filter: var(--blur-kaca);
            border: 1px solid rgba(55, 65, 81, 0.5);
            padding: 22px;
            border-radius: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.25s ease;
        }

        .kartu-metrik:hover {
            transform: translateY(-3px);
            border-color: rgba(239, 68, 68, 0.35);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }

        .metrik-detail h3 { font-size: 13px; color: var(--warna-teks-abu); margin-bottom: 6px; font-weight: 500; }
        .metrik-detail .nilai { font-size: 30px; font-weight: 700; font-family: 'Outfit', sans-serif; }
        .metrik-icon { font-size: 32px; opacity: 0.65; }

        .card-panel {
            background: var(--warna-kaca);
            backdrop-filter: var(--blur-kaca);
            border: 1px solid rgba(55, 65, 81, 0.5);
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 28px;
        }

        .card-panel-title {
            font-family: 'Outfit', sans-serif;
            font-size: 17px;
            font-weight: 600;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid rgba(55, 65, 81, 0.4);
            padding-bottom: 12px;
        }

        /* Form Controls */
        .form-group { margin-bottom: 18px; }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            font-size: 13px;
            color: var(--warna-teks-abu);
        }

        .form-input {
            width: 100%;
            padding: 10px 14px;
            background: rgba(13, 17, 23, 0.8);
            border: 1px solid rgba(55, 65, 81, 0.6);
            border-radius: 8px;
            color: var(--warna-teks-putih);
            font-size: 14px;
            transition: border-color 0.2s ease;
        }

        .form-input:focus { outline: none; border-color: var(--warna-merah-terang); }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13.5px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            border: none;
        }

        .btn-primer {
            background: linear-gradient(135deg, var(--warna-merah), #dc2626);
            color: #fff;
        }

        .btn-primer:hover {
            background: linear-gradient(135deg, #dc2626, var(--warna-merah-terang));
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35);
        }

        .btn-sekunder {
            background: transparent;
            border: 1px solid rgba(55, 65, 81, 0.6);
            color: var(--warna-teks-putih);
        }

        .btn-sekunder:hover { background: rgba(255, 255, 255, 0.04); }

        /* Badge Styles */
        .badge { display: inline-block; padding: 3px 8px; border-radius: 5px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .badge-kritis { background: rgba(220, 38, 38, 0.15); border: 1px solid rgba(220, 38, 38, 0.4); color: #f87171; }
        .badge-tinggi { background: rgba(249, 115, 22, 0.15); border: 1px solid rgba(249, 115, 22, 0.4); color: #fdba74; }
        .badge-sedang { background: rgba(234, 179, 8, 0.15); border: 1px solid rgba(234, 179, 8, 0.4); color: #fef08a; }
        .badge-rendah { background: rgba(22, 163, 74, 0.15); border: 1px solid rgba(22, 163, 74, 0.4); color: #86efac; }

        @media (max-width: 768px) {
            .sidebar { width: var(--sidebar-collapsed); }
            .sidebar-logo, .user-info-wrapper, .menu-text { opacity: 0; max-width: 0; }
            .konten-utama { margin-left: var(--sidebar-collapsed); padding: 20px 16px; }
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
