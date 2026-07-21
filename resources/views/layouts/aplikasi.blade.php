<!DOCTYPE html>
<html class="dark" lang="id">
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
    <!-- Material Symbols Outlined -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    
    <!-- Tailwind CSS Play CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <!-- Alpine.js, Collapse Plugin, & Chart.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.13.3/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #040e1f; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #45464d; }
        
        .zebra-table tr:nth-child(even) { background-color: rgba(15, 23, 42, 0.5); }
        .glass-card { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(8px); }
        
        /* Smooth transition for sidebar width & main margin */
        .sidebar-transition {
            transition: width 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .main-transition {
            transition: margin-left 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Marquee Animation */
        @keyframes scm-ticker-scroll { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
        .scm-ticker-track { display: flex; white-space: nowrap; position: absolute; animation: scm-ticker-scroll 60s linear infinite; }
        .scm-ticker-track:hover { animation-play-state: paused; }
    </style>

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-secondary-container": "#fecaca",
                        "tertiary-fixed": "#ffb4ab",
                        "surface-container-low": "#111c2d",
                        "tertiary-container": "#93000a",
                        "surface-container-highest": "#2a3548",
                        "inverse-on-surface": "#263143",
                        "on-primary-fixed-variant": "#ffdad6",
                        "error": "#ffb4ab",
                        "surface": "#081425",
                        "on-error-container": "#ffdad6",
                        "on-tertiary-container": "#ffdad6",
                        "outline-variant": "#334155", /* custom border color */
                        "secondary-container": "#3a4a5f",
                        "on-surface-variant": "#c6c6cd",
                        "primary-fixed-dim": "#ffb4ab",
                        "surface-dim": "#081425",
                        "on-primary-container": "#ffdad6",
                        "on-secondary-fixed-variant": "#7f1d1d",
                        "on-tertiary-fixed": "#3e0002",
                        "background": "#081425",
                        "on-tertiary": "#690005",
                        "on-surface": "#d8e3fb",
                        "error-container": "#93000a",
                        "on-background": "#d8e3fb",
                        "tertiary-fixed-dim": "#ff897d",
                        "surface-container-lowest": "#040e1f",
                        "inverse-primary": "#ffb4ab",
                        "on-secondary-fixed": "#3b0a0a",
                        "surface-variant": "#2a3548",
                        "tertiary": "#ff6b5b",
                        "surface-bright": "#2f3a4c",
                        "on-error": "#690005",
                        "surface-tint": "#ff4d4d",
                        "primary-container": "#450a0a",
                        "on-primary": "#ffffff",
                        "primary": "#ff4d4d",
                        "surface-container": "#152031",
                        "secondary": "#fca5a5",
                        "secondary-fixed": "#fee2e2",
                        "on-tertiary-fixed-variant": "#93000a",
                        "on-primary-fixed": "#410001",
                        "surface-container-high": "#1f2a3c",
                        "inverse-surface": "#d8e3fb",
                        "primary-fixed": "#ffdad6",
                        "outline": "#909097",
                        "secondary-fixed-dim": "#f87171",
                        "on-secondary": "#450a0a"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "spacing": {
                        "margin-mobile": "16px",
                        "unit": "4px",
                        "margin-desktop": "24px",
                        "container-max": "1440px",
                        "gutter": "16px"
                    },
                    "fontFamily": {
                        "headline-lg": ["Inter"],
                        "label-sm": ["JetBrains Mono"],
                        "table-data": ["Inter"],
                        "headline-md": ["Inter"],
                        "display-lg": ["Inter"],
                        "body-md": ["Inter"],
                        "headline-lg-mobile": ["Inter"],
                        "body-lg": ["Inter"]
                    },
                    "fontSize": {
                        "headline-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "600"}],
                        "label-sm": ["12px", {"lineHeight": "16px", "fontWeight": "500"}],
                        "table-data": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                        "headline-md": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                        "display-lg": ["48px", {"lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                        "headline-lg-mobile": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                        "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}]
                    }
                }
            }
        }
    </script>
    @yield('gaya_tambahan')
</head>
<body class="bg-background text-on-surface font-body-md overflow-x-hidden min-h-screen" x-data="{ sidebarOpen: true }">
    <!-- TopNavBar -->
    <header class="flex justify-between items-center h-16 w-full px-6 z-50 fixed top-0 bg-surface-container-low border-b border-outline-variant">
        <div class="flex items-center gap-4">
            <!-- Sidebar toggle button -->
            <button class="text-on-surface-variant hover:bg-surface-container-highest p-2 rounded-lg transition-colors flex items-center justify-center" @click="sidebarOpen = !sidebarOpen">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/Horus.png') }}" alt="Horus Icon" class="h-7 w-auto">
                <span class="text-xl font-bold tracking-tight text-primary font-headline-lg">HORUS MONITORING</span>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <span class="text-xs text-on-surface-variant uppercase tracking-wider hidden md:inline">SYSTEM OVERWATCH ACTIVE</span>
            <div class="h-2 w-2 rounded-full bg-tertiary animate-pulse hidden md:inline-block"></div>
            
            <div class="w-px h-6 bg-outline-variant hidden md:block"></div>
            
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-secondary-container border border-outline-variant flex items-center justify-center font-bold text-sm text-primary">
                    {{ strtoupper(substr(Auth::user()->nama, 0, 2)) }}
                </div>
                <div class="hidden lg:flex flex-col text-left">
                    <span class="text-xs font-semibold text-on-surface leading-none">{{ Auth::user()->nama }}</span>
                    <span class="text-[10px] text-on-surface-variant uppercase tracking-widest mt-0.5">{{ Auth::user()->peran->first()?->nama ?? 'Pengguna' }}</span>
                </div>
            </div>
        </div>
    </header>

    <!-- SideNavBar -->
    <aside class="fixed left-0 top-0 h-screen flex flex-col z-40 pt-16 bg-surface-container-lowest border-r border-outline-variant sidebar-transition" 
           :class="sidebarOpen ? 'w-64' : 'w-16'">
        <div class="p-4 border-b border-outline-variant flex items-center justify-between" x-show="sidebarOpen" x-transition>
            <div class="flex items-center gap-3">
                <div class="w-2.5 h-2.5 rounded-full bg-tertiary animate-pulse"></div>
                <div class="flex flex-col">
                    <span class="font-label-sm text-xs font-bold text-on-surface">Terminal v2.5</span>
                    <span class="text-[10px] text-on-surface-variant uppercase tracking-widest leading-none">Live Oversight</span>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto py-4 space-y-1">
            <!-- Navigation Section -->
            <div class="px-4 py-2" x-show="sidebarOpen" x-transition>
                <span class="text-[10px] font-bold text-outline uppercase tracking-widest">Navigasi Utama</span>
            </div>

            <!-- Dashboard Link -->
            <a class="flex items-center gap-3 px-4 py-3 text-sm transition-all hover:bg-surface-container-high {{ Request::routeIs('dasbor.indeks') ? 'bg-secondary-container text-on-secondary-container border-l-4 border-tertiary font-bold' : 'text-on-surface-variant hover:text-on-surface' }}" 
               href="{{ route('dasbor.indeks') }}" title="Control Center">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="font-label-sm text-xs" x-show="sidebarOpen" x-transition>Control Center</span>
            </a>

            <!-- Watchlist Link -->
            <a class="flex items-center gap-3 px-4 py-3 text-sm transition-all hover:bg-surface-container-high {{ Request::routeIs('favorit.indeks') ? 'bg-secondary-container text-on-secondary-container border-l-4 border-tertiary font-bold' : 'text-on-surface-variant hover:text-on-surface' }}" 
               href="{{ route('favorit.indeks') }}" title="Pemantauan Favorit">
                <span class="material-symbols-outlined">star</span>
                <div class="flex items-center justify-between w-full" x-show="sidebarOpen" x-transition>
                    <span class="font-label-sm text-xs">Pemantauan Favorit</span>
                    @php
                        $jmlFavorit = Auth::user()->favoritNegara()->count();
                    @endphp
                    @if($jmlFavorit > 0)
                    <span class="bg-error-container text-on-error-container text-[10px] font-bold px-2 py-0.5 rounded-full" id="sidebar-favorit-count">
                        {{ $jmlFavorit }}
                    </span>
                    @endif
                </div>
            </a>

            <!-- Komparasi Link -->
            <a class="flex items-center gap-3 px-4 py-3 text-sm transition-all hover:bg-surface-container-high {{ Request::routeIs('komparasi.indeks') ? 'bg-secondary-container text-on-secondary-container border-l-4 border-tertiary font-bold' : 'text-on-surface-variant hover:text-on-surface' }}" 
               href="{{ route('komparasi.indeks') }}" title="Komparasi Negara">
                <span class="material-symbols-outlined">compare_arrows</span>
                <span class="font-label-sm text-xs" x-show="sidebarOpen" x-transition>Komparasi Negara</span>
            </a>

            <div class="h-px bg-outline-variant my-2 mx-4"></div>

            <!-- SCM Risks Section -->
            <div class="px-4 py-2" x-show="sidebarOpen" x-transition>
                <span class="text-[10px] font-bold text-outline uppercase tracking-widest">Manajemen Risiko</span>
            </div>

            <!-- Mitigasi Link -->
            <a class="flex items-center gap-3 px-4 py-3 text-sm transition-all hover:bg-surface-container-high {{ Request::routeIs('risiko.rekomendasi.indeks') ? 'bg-secondary-container text-on-secondary-container border-l-4 border-tertiary font-bold' : 'text-on-surface-variant hover:text-on-surface' }}" 
               href="{{ route('risiko.rekomendasi.indeks') }}" title="Tindakan Mitigasi">
                <span class="material-symbols-outlined">shield</span>
                <span class="font-label-sm text-xs" x-show="sidebarOpen" x-transition>Tindakan Mitigasi</span>
            </a>

            <!-- Bobot Risiko (Admin Only) -->
            @if(Auth::user()->adalahSuperAdmin() || Auth::user()->mempunyaiPeran('administrator'))
            <a class="flex items-center gap-3 px-4 py-3 text-sm transition-all hover:bg-surface-container-high {{ Request::routeIs('risiko.bobot.form') ? 'bg-secondary-container text-on-secondary-container border-l-4 border-tertiary font-bold' : 'text-on-surface-variant hover:text-on-surface' }}" 
               href="{{ route('risiko.bobot.form') }}" title="Bobot Risiko">
                <span class="material-symbols-outlined">tune</span>
                <span class="font-label-sm text-xs" x-show="sidebarOpen" x-transition>Bobot Risiko</span>
            </a>
            @endif

            <div class="h-px bg-outline-variant my-2 mx-4"></div>

            <!-- Intelligence Section -->
            <div class="px-4 py-2" x-show="sidebarOpen" x-transition>
                <span class="text-[10px] font-bold text-outline uppercase tracking-widest">Intelijen Berita</span>
            </div>

            <!-- Artikel Link -->
            <a class="flex items-center gap-3 px-4 py-3 text-sm transition-all hover:bg-surface-container-high {{ Request::routeIs('analisis.*') ? 'bg-secondary-container text-on-secondary-container border-l-4 border-tertiary font-bold' : 'text-on-surface-variant hover:text-on-surface' }}" 
               href="{{ route('analisis.indeks') }}" title="Analisis Artikel">
                <span class="material-symbols-outlined">newspaper</span>
                <span class="font-label-sm text-xs" x-show="sidebarOpen" x-transition>Analisis Artikel</span>
            </a>

            <!-- Administration Section (Admin Only) -->
            @if(Auth::user()->adalahSuperAdmin() || Auth::user()->adalahAdmin())
            <div class="h-px bg-outline-variant my-2 mx-4"></div>
            <div class="px-4 py-2" x-show="sidebarOpen" x-transition>
                <span class="text-[10px] font-bold text-outline uppercase tracking-widest">Administrasi</span>
            </div>

            <!-- Kelola Pengguna Link -->
            <a class="flex items-center gap-3 px-4 py-3 text-sm transition-all hover:bg-surface-container-high {{ Request::routeIs('admin.pengguna.*') ? 'bg-secondary-container text-on-secondary-container border-l-4 border-tertiary font-bold' : 'text-on-surface-variant hover:text-on-surface' }}" 
               href="{{ route('admin.pengguna.indeks') }}" title="Kelola Pengguna">
                <span class="material-symbols-outlined">manage_accounts</span>
                <span class="font-label-sm text-xs" x-show="sidebarOpen" x-transition>Kelola Pengguna</span>
            </a>
            @endif
        </nav>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-outline-variant flex flex-col gap-2">
            <form action="{{ route('keluar') }}" method="POST" id="logout-form" class="w-full">
                @csrf
                <button type="submit" class="flex items-center gap-3 px-4 py-2 w-full text-left text-sm text-on-surface-variant hover:text-error hover:bg-surface-container-high transition-colors rounded">
                    <span class="material-symbols-outlined">logout</span>
                    <span class="font-label-sm text-xs" x-show="sidebarOpen" x-transition>Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Canvas -->
    <main class="pt-20 p-6 min-h-screen relative flex flex-col main-transition" 
          :class="sidebarOpen ? 'ml-64' : 'ml-16'">
        
        <!-- Live SCM Alert Marquee Ticker -->
        @php
            $beritaPanas = \App\Models\ArtikelBerita::with('negara')
                ->whereIn('keparahan', ['kritis', 'tinggi'])
                ->orderBy('diterbitkan_pada', 'desc')
                ->limit(10)
                ->get();
        @endphp
        @if($beritaPanas->count() > 0)
        <div class="relative overflow-hidden h-9 bg-surface-container-lowest border border-outline-variant rounded-lg flex items-center shadow-lg mb-6 z-10">
            <div class="bg-error-container text-on-error-container px-4 h-full flex items-center font-bold text-xs uppercase tracking-wider relative flex-shrink-0 z-20 shadow-md">
                <span class="material-symbols-outlined text-sm mr-1.5 animate-pulse">warning</span>
                Live Alert
            </div>
            <div class="flex-grow overflow-hidden relative h-full flex items-center z-10">
                <div class="scm-ticker-track flex items-center">
                    @foreach($beritaPanas as $b)
                        <a href="{{ $b->url_asli ?? $b->tautan ?? '#' }}" target="_blank" class="flex items-center gap-2 px-6 text-xs text-on-surface-variant hover:text-primary transition-colors">
                            @if($b->negara)
                                <img src="{{ $b->negara->bendera_url }}" class="h-3 w-4.5 rounded object-cover border border-outline-variant">
                            @endif
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase {{ $b->keparahan == 'kritis' ? 'bg-red-500/20 text-red-300 border border-red-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                                {{ $b->keparahan }}
                            </span>
                            <span class="font-medium truncate max-w-sm">{{ $b->judul }}</span>
                        </a>
                        <span class="text-outline-variant font-bold text-sm">•</span>
                    @endforeach
                    <!-- Duplicate for seamless looping -->
                    @foreach($beritaPanas as $b)
                        <a href="{{ $b->url_asli ?? $b->tautan ?? '#' }}" target="_blank" class="flex items-center gap-2 px-6 text-xs text-on-surface-variant hover:text-primary transition-colors">
                            @if($b->negara)
                                <img src="{{ $b->negara->bendera_url }}" class="h-3 w-4.5 rounded object-cover border border-outline-variant">
                            @endif
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase {{ $b->keparahan == 'kritis' ? 'bg-red-500/20 text-red-300 border border-red-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                                {{ $b->keparahan }}
                            </span>
                            <span class="font-medium truncate max-w-sm">{{ $b->judul }}</span>
                        </a>
                        <span class="text-outline-variant font-bold text-sm">•</span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Session Flash Alerts -->
        @if(session('sukses'))
            <div class="mb-5 p-4 bg-tertiary-container text-on-tertiary-container border-l-4 border-tertiary rounded flex items-center gap-3">
                <span class="material-symbols-outlined text-tertiary">check_circle</span>
                <span class="text-sm font-medium">{{ session('sukses') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 p-4 bg-error-container text-on-error-container border-l-4 border-red-500 rounded flex items-center gap-3">
                <span class="material-symbols-outlined text-red-400">error</span>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 border-b border-outline-variant pb-4">
            <div>
                <h1 class="text-headline-lg font-bold text-on-surface">@yield('judul')</h1>
                <p class="text-on-surface-variant text-xs mt-1">Platform Pemantauan & Analitik Risiko Rantai Pasok Global</p>
            </div>
            <div class="flex items-center gap-2 text-xs text-on-surface-variant bg-surface-container-high border border-outline-variant px-3 py-1.5 rounded">
                <span class="material-symbols-outlined text-sm">calendar_month</span>
                <span>{{ date('d M Y') }}</span>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-grow z-10">
            @yield('konten')
        </div>
    </main>

    <!-- Hover Micro-interactions -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.glass-card').forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.classList.add('border-outline');
                    card.style.transition = 'all 0.2s ease';
                });
                card.addEventListener('mouseleave', () => {
                    card.classList.remove('border-outline');
                });
            });
        });
    </script>
    @yield('skrip_tambahan')
</body>
</html>
