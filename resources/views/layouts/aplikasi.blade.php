<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('judul', 'Dasbor') - Supply Chain Intelligence Platform</title>
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --warna-merah: #991b1b;
            --warna-merah-terang: #ef4444;
            --warna-merah-gelap: #7f1d1d;
            --warna-charcoal: #111827;
            --warna-charcoal-terang: #1f2937;
            --warna-charcoal-border: #374151;
            --warna-emas: #d97706;
            --warna-emas-terang: #fbbf24;
            --warna-teks-putih: #f9fafb;
            --warna-teks-abu: #9ca3af;
            --warna-kaca: rgba(31, 41, 55, 0.6);
            --blur-kaca: blur(12px);
            --warna-hijau: #16a34a;
            --warna-kuning: #eab308;
            --warna-oranye: #f97316;
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

        /* Sidebar Style */
        .sidebar {
            width: 280px;
            background-color: rgba(17, 24, 39, 0.95);
            border-right: 1px solid var(--warna-charcoal-border);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid var(--warna-charcoal-border);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-logo {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 800;
            color: var(--warna-teks-putih);
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .sidebar-logo span {
            color: var(--warna-merah-terang);
        }

        .sidebar-menu {
            flex-grow: 1;
            padding: 24px 16px;
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .menu-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--warna-teks-abu);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .menu-item a:hover, .menu-item.aktif a {
            background-color: var(--warna-merah-gelap);
            color: var(--warna-teks-putih);
            box-shadow: 0 4px 12px rgba(153, 27, 27, 0.3);
        }

        .sidebar-footer {
            padding: 24px;
            border-top: 1px solid var(--warna-charcoal-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--warna-teks-putih);
        }

        .user-role {
            font-size: 11px;
            color: var(--warna-teks-abu);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .logout-btn {
            background: none;
            border: none;
            color: var(--warna-teks-abu);
            font-size: 18px;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .logout-btn:hover {
            color: var(--warna-merah-terang);
        }

        /* Main Content Style */
        .konten-utama {
            margin-left: 280px;
            flex-grow: 1;
            padding: 40px;
            min-height: 100vh;
        }

        .header-konten {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .judul-halaman {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 700;
        }

        /* Notifikasi Flash */
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .alert-sukses {
            background-color: rgba(22, 163, 74, 0.2);
            border: 1px solid var(--warna-hijau);
            color: #4ade80;
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.2);
            border: 1px solid var(--warna-merah-terang);
            color: #f87171;
        }

        /* Kartu & Container */
        .grid-kartu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .kartu-metrik {
            background-color: var(--warna-kaca);
            backdrop-filter: var(--blur-kaca);
            border: 1px solid var(--warna-charcoal-border);
            padding: 24px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.3s ease;
        }

        .kartu-metrik:hover {
            transform: translateY(-4px);
            border-color: var(--warna-merah);
        }

        .metrik-detail h3 {
            font-size: 14px;
            color: var(--warna-teks-abu);
            margin-bottom: 8px;
            font-weight: 500;
        }

        .metrik-detail .nilai {
            font-size: 32px;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
        }

        .metrik-icon {
            font-size: 36px;
            opacity: 0.8;
        }

        .card-panel {
            background-color: var(--warna-kaca);
            backdrop-filter: var(--blur-kaca);
            border: 1px solid var(--warna-charcoal-border);
            border-radius: 12px;
            padding: 28px;
            margin-bottom: 32px;
        }

        .card-panel-title {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid var(--warna-charcoal-border);
            padding-bottom: 12px;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
            color: var(--warna-teks-abu);
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            background-color: var(--warna-charcoal);
            border: 1px solid var(--warna-charcoal-border);
            border-radius: 8px;
            color: var(--warna-teks-putih);
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--warna-merah-terang);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            border: none;
        }

        .btn-primer {
            background-color: var(--warna-merah);
            color: var(--warna-teks-putih);
        }

        .btn-primer:hover {
            background-color: var(--warna-merah-terang);
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4);
        }

        .btn-sekunder {
            background-color: transparent;
            border: 1px solid var(--warna-charcoal-border);
            color: var(--warna-teks-putih);
        }

        .btn-sekunder:hover {
            background-color: var(--warna-charcoal-terang);
        }

        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-kritis { background-color: rgba(220, 38, 38, 0.2); border: 1px solid #dc2626; color: #f87171; }
        .badge-tinggi { background-color: rgba(249, 115, 22, 0.2); border: 1px solid #f97316; color: #fdba74; }
        .badge-sedang { background-color: rgba(234, 179, 8, 0.2); border: 1px solid #eab308; color: #fef08a; }
        .badge-rendah { background-color: rgba(22, 163, 74, 0.2); border: 1px solid #16a34a; color: #86efac; }

        @media (max-width: 1024px) {
            .sidebar {
                width: 70px;
            }
            .sidebar-logo, .user-info, .sidebar-logo span {
                display: none;
            }
            .menu-item span {
                display: none;
            }
            .konten-utama {
                margin-left: 70px;
                padding: 24px;
            }
        }
    </style>
    @yield('gaya_tambahan')
</head>
<body>

    <!-- Sidebar Menu -->
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-shield-halved" style="color: var(--warna-merah-terang); font-size: 24px;"></i>
            <div class="sidebar-logo">Horus<span>监控</span></div>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-item {{ Request::routeIs('dasbor.indeks') ? 'aktif' : '' }}">
                <a href="{{ route('dasbor.indeks') }}">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dasbor Utama</span>
                </a>
            </li>
            <li class="menu-item {{ Request::routeIs('risiko.rekomendasi.indeks') ? 'aktif' : '' }}">
                <a href="{{ route('risiko.rekomendasi.indeks') }}">
                    <i class="fa-solid fa-hand-holding-hand"></i>
                    <span>Tindakan Mitigasi</span>
                </a>
            </li>
            @if(Auth::user()->adalahSuperAdmin() || Auth::user()->mempunyaiPeran('admin'))
            <li class="menu-item {{ Request::routeIs('risiko.bobot.form') ? 'aktif' : '' }}">
                <a href="{{ route('risiko.bobot.form') }}">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Bobot Risiko</span>
                </a>
            </li>
            @endif
        </ul>
        <div class="sidebar-footer">
            <div class="user-info">
                <span class="user-name">{{ Auth::user()->nama }}</span>
                <span class="user-role">{{ Auth::user()->peran->first()?->nama ?? 'Pengguna' }}</span>
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
    <div class="konten-utama">
        <div class="header-konten">
            <h1 class="judul-halaman">@yield('judul')</h1>
            <div style="font-size: 14px; color: var(--warna-teks-abu);">
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
