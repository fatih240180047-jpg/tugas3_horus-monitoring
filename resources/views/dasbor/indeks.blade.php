@extends('layouts.aplikasi')

@section('judul', 'Global Control Center')

@section('gaya_tambahan')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    [x-cloak] { display: none !important; }

    #peta-sig {
        height: 100%;
        width: 100%;
        background-color: #040e1f;
    }

    /* Leaflet Dark Theme Tooltip Override */
    .dark-tooltip {
        background: rgba(17, 28, 45, 0.95) !important;
        border: 1px solid #334155 !important;
        color: #d8e3fb !important;
        font-family: 'Inter', sans-serif !important;
        font-size: 12px !important;
        border-radius: 6px !important;
        box-shadow: 0 8px 24px rgba(0,0,0,0.6) !important;
        padding: 8px 12px !important;
    }
    .dark-tooltip::before { display: none !important; }

    /* Animated Route Line (Pulsing moving dash) */
    .animated-route-line {
        stroke-dasharray: 8, 8;
        animation: dash-animation 15s linear infinite;
    }
    @keyframes dash-animation {
        to { stroke-dashoffset: -500; }
    }

    /* User Pulse Geolocation Animation */
    .user-pulse-container { position: absolute !important; }
    .user-pulse-marker {
        width: 14px; height: 14px;
        background: #3b82f6;
        border: 2px solid #ffffff;
        border-radius: 50%;
        box-shadow: 0 0 16px #3b82f6;
    }
    .user-pulse-container::after {
        content: '';
        width: 32px; height: 32px;
        border: 2px solid rgba(59, 130, 246, 0.6);
        border-radius: 50%;
        position: absolute;
        top: -9px; left: -9px;
        animation: user-pulse-anim 2s infinite ease-out;
        opacity: 0;
        pointer-events: none;
    }
    @keyframes user-pulse-anim {
        0% { transform: scale(0.3); opacity: 0.8; }
        100% { transform: scale(1.3); opacity: 0; }
    }

    /* Pulse risk animations */
    .risk-pulse-container { position: absolute !important; }
    .risk-pulse-kritis { animation: pulse-kritis 1.4s infinite; }
    .risk-pulse-tinggi  { animation: pulse-tinggi  1.6s infinite; }
    .risk-pulse-sedang  { animation: pulse-sedang  1.8s infinite; }
    .risk-pulse-rendah  { animation: pulse-rendah  2.2s infinite; }

    @keyframes pulse-kritis {
        0%  { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.8); }
        70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
        100%{ box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }
    @keyframes pulse-tinggi {
        0%  { box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.8); }
        70% { box-shadow: 0 0 0 10px rgba(249, 115, 22, 0); }
        100%{ box-shadow: 0 0 0 0 rgba(249, 115, 22, 0); }
    }
    @keyframes pulse-sedang {
        0%  { box-shadow: 0 0 0 0 rgba(234, 179, 8, 0.8); }
        70% { box-shadow: 0 0 0 10px rgba(234, 179, 8, 0); }
        100%{ box-shadow: 0 0 0 0 rgba(234, 179, 8, 0); }
    }
    @keyframes pulse-rendah {
        0%  { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
        100%{ box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
</style>
@endsection

@section('konten')
<div x-data="dashboardSPA()" x-init="initPeta()" class="flex flex-col gap-6">
    
    <!-- Bagian Atas: Peta Leaflet HUD (Lebar Penuh) -->
    <div class="w-full flex flex-col relative bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-2xl h-[550px] group">
        
        <!-- HUD Overlays (Top Floating Toolbar) -->
        <div class="absolute top-4 left-4 right-4 z-[1000] flex flex-wrap gap-2 items-center pointer-events-none">
            <!-- Dropdown Pencarian Cepat Negara -->
            <select class="bg-surface-container-low/95 backdrop-blur-md text-on-surface border border-outline-variant px-3 py-2 rounded-lg text-xs min-w-[180px] shadow-lg pointer-events-auto cursor-pointer focus:ring-1 focus:ring-primary focus:outline-none" 
                    x-model="selectedIso" @change="fetchDataNegara(selectedIso)">
                <option value="">-- Cari Negara SCM --</option>
                @foreach($negaraList as $n)
                    <option value="{{ $n->kode_iso }}">{{ $n->nama }} ({{ $n->kode_iso }})</option>
                @endforeach
            </select>

            <!-- Dropdown Pencarian Pelabuhan -->
            <select class="bg-surface-container-low/95 backdrop-blur-md text-on-surface border border-outline-variant px-3 py-2 rounded-lg text-xs min-w-[180px] shadow-lg pointer-events-auto cursor-pointer focus:ring-1 focus:ring-primary focus:outline-none" 
                    x-model="selectedPort" @change="flyToPort()">
                <option value="">-- Cari Pelabuhan SCM --</option>
                @foreach(collect($dataPelabuhan)->sortBy('nama') as $p)
                    <option value="{{ $p['lintang'] }},{{ $p['bujur'] }}">{{ $p['nama'] }} ({{ $p['kode_locode'] }})</option>
                @endforeach
            </select>
            
            <!-- Quick Stat node -->
            <div class="bg-surface-container-low/95 backdrop-blur-md border border-outline-variant px-3 py-1.5 rounded-lg shadow-lg pointer-events-auto hidden sm:flex items-center gap-2">
                <span class="material-symbols-outlined text-[14px] text-primary">hub</span>
                <span class="text-[10px] text-on-surface-variant uppercase tracking-wider">Node Dipantau: <strong class="text-on-surface">{{ $negaraList->count() }}</strong></span>
            </div>
        </div>

        <!-- Leaflet Map Container -->
        <div id="peta-sig" class="flex-grow z-0"></div>

        <!-- Map Controls Legend & Visibility Filter (Bottom Right) -->
        <div class="absolute bottom-4 right-4 z-[1000] bg-surface-container-low/95 backdrop-blur-md border border-outline-variant p-3.5 rounded-lg shadow-2xl pointer-events-auto w-52 flex flex-col gap-2 transition-all">
            <div class="flex justify-between items-center border-b border-outline-variant pb-1.5 mb-1 cursor-pointer select-none" @click="showMapControls = !showMapControls">
                <h4 class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Map & Controls</h4>
                <span class="material-symbols-outlined text-[14px] text-on-surface-variant transition-transform" :class="showMapControls ? 'rotate-180' : ''">expand_more</span>
            </div>
            
            <div x-show="showMapControls" x-collapse class="flex flex-col gap-2">
                <button @click="focusUser()" class="flex items-center gap-2 text-[11px] font-semibold text-on-surface bg-surface-container-high hover:bg-surface-container-highest border border-outline-variant px-2 py-1.5 rounded transition-all">
                    <span class="material-symbols-outlined text-[14px] text-primary">my_location</span>
                    Fokus Lokasi Saya
                </button>
                
                <button @click="resetPeta()" class="flex items-center gap-2 text-[11px] font-semibold text-on-surface bg-surface-container-high hover:bg-surface-container-highest border border-outline-variant px-2 py-1.5 rounded transition-all">
                    <span class="material-symbols-outlined text-[14px] text-secondary">public</span>
                    Reset View Peta
                </button>

            <button @click="clearRoute()" x-show="routeLine" class="flex items-center gap-2 text-[11px] font-semibold text-error bg-error-container/20 hover:bg-error-container/40 border border-error/30 px-2 py-1.5 rounded transition-all" x-cloak>
                <span class="material-symbols-outlined text-[14px]">link_off</span>
                Putuskan Rute Link
            </button>

                <div class="h-px bg-outline-variant my-1"></div>

                <label class="flex items-center gap-2 text-[11px] text-on-surface-variant hover:text-on-surface cursor-pointer">
                    <input type="checkbox" x-model="showCountryMarkers" @change="toggleCountryMarkers()" 
                           class="rounded border-outline-variant bg-surface-container-lowest text-primary focus:ring-primary focus:ring-offset-0 w-3.5 h-3.5">
                    Tampilkan Negara
                </label>

                <label class="flex items-center gap-2 text-[11px] text-on-surface-variant hover:text-on-surface cursor-pointer">
                    <input type="checkbox" x-model="showPortMarkers" @change="togglePortMarkers()" 
                           class="rounded border-outline-variant bg-surface-container-lowest text-primary focus:ring-primary focus:ring-offset-0 w-3.5 h-3.5">
                    Tampilkan Pelabuhan
                </label>
            </div>
        </div>

        <!-- Map Info Overlay (Bottom Left) -->
        <div class="absolute bottom-4 left-4 z-[1000] bg-surface-container-low/90 backdrop-blur-sm border border-outline-variant p-3 rounded-lg shadow-xl pointer-events-auto max-w-xs hidden md:flex md:flex-col transition-all">
            <div class="flex justify-between items-center cursor-pointer select-none mb-1 gap-4" @click="showLegend = !showLegend">
                <h5 class="text-[9px] font-extrabold text-outline uppercase tracking-wider">Live Map Legend</h5>
                <span class="material-symbols-outlined text-[14px] text-on-surface-variant transition-transform" :class="showLegend ? 'rotate-180' : ''">expand_more</span>
            </div>
            
            <div x-show="showLegend" x-collapse class="flex flex-col gap-1.5 pt-1">
                <div class="flex items-center gap-2 text-[10px] text-on-surface-variant">
                    <span class="w-2.5 h-2.5 rounded-full bg-error animate-pulse shadow-[0_0_6px_#ef4444]"></span>
                    Anomali Risiko Kritis
                </div>
                <div class="flex items-center gap-2 text-[10px] text-on-surface-variant">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-[0_0_6px_#f59e0b]"></span>
                    Tingkat Risiko Sedang/Tinggi
                </div>
                <div class="flex items-center gap-2 text-[10px] text-on-surface-variant">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_6px_#10b981]"></span>
                    Operasi Aman/Rendah
                </div>
                <div class="flex items-center gap-2 text-[10px] text-on-surface-variant">
                    <span class="w-5 h-5 rounded-full border border-outline-variant bg-surface-container-highest flex items-center justify-center">
                        <i class="fa-solid fa-anchor text-[9px] text-primary"></i>
                    </span>
                    Pelabuhan Hub Utama SCM
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian Bawah: Panel Kendali / Detail SCM -->
    <div class="w-full bg-surface-container-low border border-outline-variant rounded-xl p-5 flex flex-col shadow-2xl relative min-h-[300px]">
        
        <!-- Loading Overlay -->
        <div x-show="isLoading" class="absolute inset-0 bg-surface-container-lowest/80 backdrop-blur-md flex flex-col justify-center items-center gap-3 z-50 rounded-xl" x-cloak x-transition>
            <div class="w-10 h-10 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
            <div class="text-xs text-on-surface-variant font-label-sm uppercase tracking-wider animate-pulse">Menghubungkan Intelijen SCM...</div>
        </div>

        <!-- Simulator Bobot Risiko Collapsible Panel -->
        <div class="bg-red-500/5 border border-red-500/20 rounded-lg p-3.5 mb-5 hover:border-red-500/30 transition-all max-w-3xl mx-auto w-full">
            <div class="flex justify-between items-center cursor-pointer select-none" @click="showSimulator = !showSimulator">
                <span class="flex items-center gap-2 text-xs font-bold text-red-200">
                    <span class="material-symbols-outlined text-[16px] text-error">tune</span>
                    Simulator Bobot Risiko Global SCM
                </span>
                <span class="material-symbols-outlined text-xs text-on-surface-variant transition-transform duration-200" 
                      :class="showSimulator ? 'rotate-180' : ''">expand_more</span>
            </div>
            
            <div x-show="showSimulator" class="mt-4 border-t border-red-500/10 pt-4" x-cloak x-transition>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <div class="flex justify-between text-[11px]">
                            <span class="text-on-surface-variant">Anomali Cuaca</span>
                            <strong class="text-primary font-label-sm" x-text="simBobotCuaca + '%'"></strong>
                        </div>
                        <input type="range" min="0" max="100" x-model="simBobotCuaca" @input="recalculateRisks()" 
                               class="w-full bg-surface-container-highest h-1 rounded-lg appearance-none cursor-pointer accent-error">
                    </div>
                    
                    <div class="flex flex-col gap-1.5">
                        <div class="flex justify-between text-[11px]">
                            <span class="text-on-surface-variant">Ekonomi &amp; Inflasi</span>
                            <strong class="text-primary font-label-sm" x-text="simBobotEkonomi + '%'"></strong>
                        </div>
                        <input type="range" min="0" max="100" x-model="simBobotEkonomi" @input="recalculateRisks()" 
                               class="w-full bg-surface-container-highest h-1 rounded-lg appearance-none cursor-pointer accent-error">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <div class="flex justify-between text-[11px]">
                            <span class="text-on-surface-variant">Volatilitas Valas</span>
                            <strong class="text-primary font-label-sm" x-text="simBobotKurs + '%'"></strong>
                        </div>
                        <input type="range" min="0" max="100" x-model="simBobotKurs" @input="recalculateRisks()" 
                               class="w-full bg-surface-container-highest h-1 rounded-lg appearance-none cursor-pointer accent-error">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <div class="flex justify-between text-[11px]">
                            <span class="text-on-surface-variant">Radar Sentimen Berita</span>
                            <strong class="text-primary font-label-sm" x-text="simBobotBerita + '%'"></strong>
                        </div>
                        <input type="range" min="0" max="100" x-model="simBobotBerita" @input="recalculateRisks()" 
                               class="w-full bg-surface-container-highest h-1 rounded-lg appearance-none cursor-pointer accent-error">
                    </div>
                </div>

                <div class="text-[9px] text-on-surface-variant border-t border-outline-variant/30 pt-2 mt-3 flex justify-between">
                    <span>Total Bobot Dinamis: <strong class="text-on-surface font-label-sm" x-text="totalBobot() + '%'"></strong></span>
                    <span class="italic text-outline">Sisa didistribusikan ke logistik/politik</span>
                </div>
            </div>
        </div>

        <!-- STATE 1: Default Global Overview (Jika belum ada negara terpilih) -->
        <div x-show="!selectedIso && !isLoading" class="flex flex-col" x-transition.opacity>
            <div class="border-b border-outline-variant pb-3 mb-5 flex justify-between items-end">
                <div>
                    <div class="font-headline-md text-sm font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-primary">public</span>
                        Ringkasan Intelijen SCM Global
                    </div>
                    <p class="text-[11px] text-on-surface-variant mt-0.5">Pilih marker titik di peta untuk memulai analisis metrik negara terperinci</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Widget Stats -->
                <div class="flex flex-col gap-3">
                    <div class="bg-error/5 border border-error/20 p-5 rounded-xl relative overflow-hidden flex items-center justify-between">
                        <div>
                            <div class="text-[10px] font-bold text-error uppercase tracking-wider mb-1">Status Risiko Kritis</div>
                            <div class="text-4xl font-extrabold text-error font-label-sm leading-none">{{ $statistik['kritis'] }} <span class="text-sm font-normal">Node</span></div>
                        </div>
                        <div class="opacity-20">
                            <span class="material-symbols-outlined text-5xl text-error">gavel</span>
                        </div>
                    </div>

                    <div class="bg-amber-500/5 border border-amber-500/20 p-5 rounded-xl relative overflow-hidden flex items-center justify-between">
                        <div>
                            <div class="text-[10px] font-bold text-amber-500 uppercase tracking-wider mb-1">Tingkat Risiko Sedang/Tinggi</div>
                            <div class="text-4xl font-extrabold text-amber-500 font-label-sm leading-none">{{ $statistik['tinggi'] + $statistik['sedang'] }} <span class="text-sm font-normal">Node</span></div>
                        </div>
                        <div class="opacity-20">
                            <span class="material-symbols-outlined text-5xl text-amber-500">warning</span>
                        </div>
                    </div>
                </div>

                <!-- Global News Feed -->
                <div class="lg:col-span-2 flex flex-col">
                    <h3 class="text-xs font-bold text-on-surface uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-primary">campaign</span>
                        Radar Berita Kritis Terbaru
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 overflow-y-auto max-h-[300px] pr-1">
                        @forelse($beritaTerbaru as $berita)
                        <div class="bg-surface-container-lowest border border-outline-variant p-3.5 rounded-lg hover:border-primary/30 transition-all flex flex-col gap-2">
                            <div class="flex justify-between items-center text-[10px] text-on-surface-variant">
                                <div class="flex items-center gap-1.5">
                                    @if($berita->negara)
                                        <img src="{{ $berita->negara->bendera_url }}" class="h-3 w-4.5 object-cover rounded border border-outline-variant">
                                        <span class="font-semibold">{{ $berita->negara->nama }}</span>
                                    @endif
                                </div>
                                <span>{{ date('d M, H:i', strtotime($berita->diterbitkan_pada)) }}</span>
                            </div>
                            
                            @if($berita->url_asli)
                                <a href="{{ $berita->url_asli }}" target="_blank" class="text-xs font-semibold text-on-surface hover:text-primary transition-colors leading-relaxed flex items-center justify-between gap-2">
                                    <span>{{ $berita->judul }}</span>
                                    <span class="material-symbols-outlined text-[14px] text-on-surface-variant flex-shrink-0">open_in_new</span>
                                </a>
                            @else
                                <div class="text-xs font-semibold text-on-surface leading-relaxed">{{ $berita->judul }}</div>
                            @endif
                            
                            <div class="flex justify-between items-center mt-auto pt-2 border-t border-outline-variant/30">
                                <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase {{ $berita->keparahan == 'kritis' ? 'bg-red-500/20 text-red-400 border border-red-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30' }}">
                                    {{ $berita->keparahan }}
                                </span>
                                <span class="text-[10px] text-outline italic">Sumber: {{ $berita->sumber }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-full text-xs text-on-surface-variant text-center py-6 border border-dashed border-outline-variant rounded-lg">
                            Tidak ada berita kritis dalam radar pemantauan global.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- STATE 2: Detail Negara Terpilih -->
        <div x-show="selectedIso && dataNegara && !isLoading" class="flex flex-col gap-5" x-cloak x-transition.opacity>
            
            <!-- Detail Header (Bendera, Nama, Kode) -->
            <div class="border-b border-outline-variant pb-4 flex flex-col md:flex-row md:justify-between md:items-end gap-4">
                <div class="flex items-center gap-4">
                    <img :src="dataNegara?.negara?.bendera_url" class="h-10 w-16 object-cover rounded shadow-lg border border-outline-variant" :alt="dataNegara?.negara?.nama">
                    <div>
                        <h2 class="font-headline-md text-2xl font-bold text-on-surface leading-tight" x-text="dataNegara?.negara?.nama"></h2>
                        <div class="flex items-center gap-3 text-[11px] text-on-surface-variant font-label-sm mt-1">
                            <span x-text="'Kode ISO: ' + dataNegara?.negara?.kode_iso"></span>
                            <span>•</span>
                            <div x-show="userDistance" class="flex items-center gap-1 font-semibold text-secondary" x-cloak>
                                <span class="material-symbols-outlined text-[13px]">distance</span>
                                <span x-text="Number(userDistance).toLocaleString('id-ID') + ' km dari Anda'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 border-t md:border-t-0 md:border-l border-outline-variant pt-3 md:pt-0 md:pl-5">
                    <div class="text-right" x-show="dataNegara?.risiko">
                        <div class="text-[9px] text-on-surface-variant uppercase tracking-wider mb-0.5">SCM RISK SCORE</div>
                        <div class="text-3xl font-extrabold font-label-sm leading-none"
                             :class="getWarnaRisikoText(simLevelTerkini || dataNegara?.risiko?.level)"
                             x-text="(simSkorTerkini !== null ? simSkorTerkini : dataNegara?.risiko?.skor) + '/100'"></div>
                        <span class="inline-block px-2 py-0.5 text-[9px] font-extrabold rounded uppercase mt-1" 
                              :class="getBgRisikoBadge(simLevelTerkini || dataNegara?.risiko?.level)"
                              x-text="simLevelTerkini || dataNegara?.risiko?.level"></span>
                    </div>

                    <div class="flex flex-col gap-2">
                        <button @click="toggleFavoritDasbor(dataNegara?.negara?.id)"
                                class="flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border transition-all shadow-sm"
                                :class="favoritIds.includes(dataNegara?.negara?.id) ? 'bg-amber-500/10 text-amber-400 border-amber-500/30 hover:bg-amber-500/20' : 'bg-surface-container-high text-on-surface hover:bg-surface-container-highest border-outline-variant'">
                            <span class="material-symbols-outlined text-[16px]" :class="favoritIds.includes(dataNegara?.negara?.id) ? 'fill-1' : ''">star</span>
                            <span class="text-xs font-semibold" x-text="favoritIds.includes(dataNegara?.negara?.id) ? 'Difavoritkan' : 'Pantau'"></span>
                        </button>
                        <a :href="'/negara/' + dataNegara?.negara?.kode_iso" 
                           class="flex items-center justify-center gap-1.5 px-3 py-1.5 bg-primary/10 hover:bg-primary/20 text-primary border border-primary/30 font-semibold text-xs rounded-lg transition-all shadow-sm">
                            <span>Laporan Penuh</span>
                            <span class="material-symbols-outlined text-[14px]">arrow_outward</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Grid Modules -->
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-4">
                
                <!-- Modul Prakiraan Cuaca -->
                <div class="bg-surface-container-lowest border border-outline-variant p-4 rounded-xl flex flex-col gap-3 shadow-inner">
                    <div class="flex justify-between items-center text-xs font-bold text-primary">
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">cloud_sync</span>
                            Kondisi Prakiraan Cuaca
                        </span>
                    </div>

                    <div class="flex justify-between items-center border-b border-outline-variant/30 pb-3">
                        <div>
                            <div class="text-3xl font-bold font-label-sm text-on-surface" x-text="(dataNegara?.cuaca?.terkini?.suhu || '--') + ' °C'"></div>
                            <div class="text-[11px] text-on-surface-variant font-semibold mt-1" x-text="(dataNegara?.cuaca?.terkini?.kondisi_cuaca || 'N/A').toUpperCase()"></div>
                        </div>
                        <div class="text-right text-[10px] text-on-surface-variant space-y-1">
                            <div>Curah Hujan: <strong class="text-on-surface" x-text="(dataNegara?.cuaca?.terkini?.curah_hujan || '0') + ' mm'"></strong></div>
                            <div>Kecepatan Angin: <strong class="text-on-surface" x-text="(dataNegara?.cuaca?.terkini?.kecepatan_angin || '0') + ' km/h'"></strong></div>
                        </div>
                    </div>

                    <!-- 7 Day forecast -->
                    <div class="grid grid-cols-7 gap-1" x-show="dataNegara?.cuaca?.prakiraan?.length > 0" x-cloak>
                        <template x-for="hari in dataNegara?.cuaca?.prakiraan.slice(0,7)">
                            <div class="bg-surface-container-high/40 p-1 text-center rounded border border-outline-variant/20 flex flex-col items-center">
                                <span class="text-[8px] text-on-surface-variant uppercase" x-text="new Date(hari.tanggal_observasi).toLocaleDateString('id-ID', {weekday:'short'})"></span>
                                <span class="text-[10px] font-bold text-on-surface mt-1" x-text="(hari.suhu_max || hari.suhu || '--') + '°'"></span>
                                <span class="text-[8px] text-secondary font-semibold" x-text="(hari.suhu_min || '--') + '°'"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Insight -->
                    <div x-show="dataNegara?.cuaca?.insight" class="bg-primary/5 border-l-2 border-primary p-2.5 rounded-r text-[10px] text-on-surface-variant mt-auto" x-cloak>
                        <strong class="text-primary block mb-0.5">Insight SCM:</strong>
                        <span x-html="formatSaran(dataNegara?.cuaca?.insight)"></span>
                    </div>
                </div>

                <!-- Modul Valas / Forex -->
                <div class="bg-surface-container-lowest border border-outline-variant p-4 rounded-xl flex flex-col gap-3 shadow-inner">
                    <div class="flex justify-between items-center text-xs font-bold text-amber-400">
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">payments</span>
                            Fluktuasi Kurs (<span x-text="dataNegara?.forex?.mata_uang"></span>/USD)
                        </span>
                    </div>
                    
                    <div class="text-2xl font-extrabold font-label-sm text-on-surface" 
                         x-text="Number(dataNegara?.forex?.terkini?.nilai_tukar || 0).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 4})"></div>
                    
                    <div class="h-28 w-full border border-outline-variant/30 rounded p-1">
                        <canvas id="forexChart"></canvas>
                    </div>

                    <div x-show="dataNegara?.forex?.insight" class="bg-amber-400/5 border-l-2 border-amber-400/80 p-2.5 rounded-r text-[10px] text-on-surface-variant mt-auto" x-cloak>
                        <strong class="text-amber-400 block mb-0.5">Analisis Valas:</strong>
                        <span x-html="formatSaran(dataNegara?.forex?.insight)"></span>
                    </div>
                </div>

                <!-- Modul Pelabuhan Maritim SCM -->
                <div class="bg-surface-container-lowest border border-outline-variant p-4 rounded-xl flex flex-col gap-3 shadow-inner">
                    <div class="flex justify-between items-center text-xs font-bold text-tertiary border-b border-outline-variant/30 pb-2">
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">anchor</span>
                            Infrastruktur Pelabuhan
                        </span>
                        <span class="text-[10px] bg-tertiary/10 text-tertiary px-2 py-0.5 rounded"
                              x-text="portsForSelectedCountry.length + ' Port'"></span>
                    </div>

                    <div class="space-y-2 flex-1 overflow-y-auto max-h-[220px] pr-1 custom-scrollbar">
                        <template x-for="port in portsForSelectedCountry">
                            <div class="bg-surface-container-high/40 border border-outline-variant/30 hover:border-tertiary/40 rounded p-2.5 transition-all cursor-pointer flex flex-col gap-1.5"
                                 @click="flyToPortCoords(port.lintang, port.bujur, port.nama)">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="text-[11px] font-bold text-on-surface" x-text="port.nama + ' (' + port.kode_locode + ')'"></h4>
                                        <span class="text-[9px] text-outline uppercase" x-text="port.jenis"></span>
                                    </div>
                                    <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded"
                                          :style="'background: ' + port.warna + '20; color: ' + port.warna + '; border: 1px solid ' + port.warna + '30;'"
                                          x-text="port.tingkat_kepadatan + '% (' + port.label_kepadatan + ')'"></span>
                                </div>
                                <div class="flex justify-between text-[10px] text-on-surface-variant pt-1 border-t border-outline-variant/20">
                                    <span>Kapasitas: <strong class="text-on-surface" x-text="port.kapasitas_teu + ' TEU'"></strong></span>
                                    <span>Risk: <strong class="text-on-surface" x-text="port.skor_risiko + '/100'"></strong></span>
                                </div>
                            </div>
                        </template>
                        <div x-show="portsForSelectedCountry.length === 0" class="text-[11px] text-on-surface-variant text-center py-6 italic" x-cloak>
                            Tidak ada pelabuhan maritim terdaftar.
                        </div>
                    </div>
                </div>

                <!-- Modul Berita Geopolitik -->
                <div class="bg-surface-container-lowest border border-outline-variant p-4 rounded-xl flex flex-col gap-3 shadow-inner">
                    <div class="flex justify-between items-center text-xs font-bold text-on-surface border-b border-outline-variant/30 pb-2">
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-on-surface-variant">satellite_dish</span>
                            Berita SCM Terkini
                        </span>
                    </div>

                    <div class="space-y-3 flex-1 overflow-y-auto max-h-[220px] pr-1 custom-scrollbar">
                        <template x-for="berita in dataNegara?.berita">
                            <div class="bg-surface-container-high/20 border border-outline-variant/20 p-2.5 rounded flex flex-col gap-1.5">
                                <div class="flex items-center justify-between text-[9px] text-on-surface-variant">
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase"
                                              :class="getBgSeverity(berita.keparahan)" x-text="berita.keparahan"></span>
                                        <span class="font-bold uppercase" :class="getSentimentColor(berita.sentimen)" x-text="berita.sentimen"></span>
                                    </div>
                                    <span x-text="new Date(berita.diterbitkan_pada).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'})"></span>
                                </div>

                                <a x-show="berita.url_asli" :href="berita.url_asli" target="_blank"
                                   class="text-[11px] font-bold text-on-surface hover:text-primary transition-colors leading-relaxed block" x-cloak x-text="berita.judul"></a>
                                <div x-show="!berita.url_asli" class="text-[11px] font-bold text-on-surface leading-relaxed" x-text="berita.judul"></div>

                                <div x-show="berita.dampak_scm" class="bg-red-500/5 border-l-2 border-red-500 p-2 text-[9px] text-on-surface-variant rounded-r mt-1" x-cloak>
                                    <strong class="text-red-400 block mb-0.5">Dampak:</strong>
                                    <span x-html="formatSaran(berita.dampak_scm)"></span>
                                </div>
                            </div>
                        </template>
                        
                        <div x-show="!dataNegara?.berita?.length" class="text-[11px] text-on-surface-variant text-center py-6 italic" x-cloak>
                            Tidak ada berita keamanan SCM saat ini.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('skrip_tambahan')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('dashboardSPA', () => ({
            selectedIso: '',
            selectedPort: '',
            isLoading: false,
            dataNegara: null,
            map: null,
            forexChartInstance: null,

            // Raw Data dari backend
            dataPetaServer: @json($dataPeta),
            ruteServer: @json($ruteEkspedisi),
            dataPelabuhan: @json($dataPelabuhan),
            favoritIds: @json($favoritIds),

            // Geolocation & Simulator State
            showSimulator: false,
            simBobotCuaca: 20,
            simBobotEkonomi: 25,
            simBobotKurs: 15,
            simBobotBerita: 20,
            simSkorTerkini: null,
            simLevelTerkini: null,

            // Marker lists
            markerList: [], // Country markers
            portMarkerList: [], // Port markers
            showCountryMarkers: true,
            showPortMarkers: true,
            
            // Map floating boxes toggles
            showLegend: true,
            showMapControls: true,

            // User Geolocation properties
            userCoords: null,
            userMarker: null,
            routeLine: null,
            userDistance: null,

            initPeta() {
                // Inisiasi Peta Leaflet (Tema Gelap ala Control Center)
                // Hitung minZoom dinamis ke integer terdekat ke atas (ceil) agar peta selalu menutupi lebar container
                const mapContainer = document.getElementById('peta-sig');
                const containerWidth = mapContainer ? mapContainer.offsetWidth : window.innerWidth;
                const minZoomLvl = Math.max(2, Math.ceil(Math.log2(containerWidth / 256)));

                // Batas peta dibatasi sedikit di antartika agar scroll vertikal tidak terlalu kosong
                const bounds = [[-85, -180], [85, 180]];
                this.map = L.map('peta-sig', { 
                    zoomControl: false,
                    maxBounds: bounds,
                    maxBoundsViscosity: 1.0,
                    worldCopyJump: false,
                    minZoom: minZoomLvl
                }).setView([20.0, 10.0], minZoomLvl);
                
                L.control.zoom({ position: 'bottomleft' }).addTo(this.map);

                // CartoDB Dark Matter Layer (noWrap: true)
                L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                    maxZoom: 18,
                    minZoom: minZoomLvl,
                    noWrap: true,
                    attribution: '&copy; CARTO'
                }).addTo(this.map);

                // Jalankan Deteksi Lokasi Pengguna
                this.detectUserLocation();

                // Gambar Rute Maritim
                this.ruteServer.forEach(rute => {
                    L.polyline([rute.asal, rute.tujuan], {
                        color: '#38485d', weight: 1.2, opacity: 0.25, dashArray: '4, 8'
                    }).addTo(this.map);
                });

                // Gambar Node / Marker Berdenyut Kustom untuk Negara
                this.dataPetaServer.forEach(point => {
                    if (point.lintang !== null && point.bujur !== null) {
                        let levelRisiko = point.level_risiko || 'rendah';
                        
                        let pulsingIcon = L.divIcon({
                            html: `<div style="background-color: ${point.warna}; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 8px ${point.warna};" class="risk-pulse-${levelRisiko.toLowerCase()}"></div>`,
                            className: 'risk-pulse-container',
                            iconSize: [12, 12],
                            iconAnchor: [6, 6]
                        });

                        let marker = L.marker([point.lintang, point.bujur], { icon: pulsingIcon });
                        marker.pointData = point;
                        
                        // Tooltip Hover Sederhana
                        marker.bindTooltip(`<strong>${point.nama}</strong><br>Skor Risiko: ${point.skor_total}`, {
                            className: 'dark-tooltip', direction: 'top'
                        });

                        // Event Klik Marker
                        marker.on('click', () => {
                            this.selectedIso = point.kode_iso;
                            this.fetchDataNegara(point.kode_iso);
                            this.drawConnectionLine([point.lintang, point.bujur], point.nama);
                            this.map.flyTo([point.lintang, point.bujur], 4, { duration: 1.2 });
                        });

                        if (this.showCountryMarkers) {
                            marker.addTo(this.map);
                        }
                        this.markerList.push(marker);
                    }
                });

                // Gambar Marker Pelabuhan (Jangkar)
                this.dataPelabuhan.forEach(port => {
                    if (port.lintang !== null && port.bujur !== null) {
                        let portIcon = L.divIcon({
                            html: `<div style="background-color: ${port.warna}; border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.5);"><i class="fa-solid fa-anchor" style="color: white; font-size: 10px;"></i></div>`,
                            className: '',
                            iconSize: [22, 22],
                            iconAnchor: [11, 11]
                        });

                        let marker = L.marker([port.lintang, port.bujur], { icon: portIcon });
                        marker.portData = port;

                        marker.bindTooltip(`
                            <div style="font-family:'Inter'; text-align:left;">
                                <strong style="font-family:'Outfit'; font-size:13px; color:#fff;">${port.nama} (${port.kode_locode})</strong>
                                <div style="color:#9ca3af; font-size:10px; margin-bottom:3px;">${port.negara}</div>
                                <div style="font-size:11px;">Kepadatan: <strong style="color:${port.warna}">${port.tingkat_kepadatan}% (${port.label_kepadatan})</strong></div>
                            </div>
                        `, { className: 'dark-tooltip', direction: 'top' });

                        marker.on('click', () => {
                            this.selectedIso = port.kode_iso;
                            this.fetchDataNegara(port.kode_iso);
                            this.drawConnectionLine([port.lintang, port.bujur], port.nama);
                            this.map.flyTo([port.lintang, port.bujur], 6, { duration: 1.2 });
                        });

                        if (this.showPortMarkers) {
                            marker.addTo(this.map);
                        }
                        this.portMarkerList.push(marker);
                    }
                });
            },

            detectUserLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(position => {
                        this.userCoords = [position.coords.latitude, position.coords.longitude];
                        this.renderUserMarker();
                    }, error => {
                        console.warn("Geolocation browser gagal, menggunakan koordinat server fallback:", error);
                        // Fallback ke IP-based (Menggunakan API eksternal sederhana jika browser gagal)
                        fetch('https://ipapi.co/json/')
                            .then(res => res.json())
                            .then(data => {
                                if (data.latitude && data.longitude) {
                                    this.userCoords = [data.latitude, data.longitude];
                                    this.renderUserMarker();
                                } else {
                                    this.userCoords = [-6.2088, 106.8456]; // Fallback Jakarta final
                                    this.renderUserMarker();
                                }
                            })
                            .catch(() => {
                                this.userCoords = [-6.2088, 106.8456]; // Fallback Jakarta final
                                this.renderUserMarker();
                            });
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    });
                } else {
                    this.userCoords = [-6.2088, 106.8456];
                    this.renderUserMarker();
                }
            },

            renderUserMarker() {
                if (!this.userCoords) return;
                let userIcon = L.divIcon({
                    html: '<div class="user-pulse-marker"></div>',
                    className: 'user-pulse-container',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });
                this.userMarker = L.marker(this.userCoords, { icon: userIcon }).addTo(this.map);
                this.userMarker.bindTooltip('<strong>Lokasi Anda (Akses Sistem)</strong>', { className: 'dark-tooltip', direction: 'top' });
            },

            drawConnectionLine(targetCoords, targetName = '') {
                if (!this.userCoords) return;
                if (this.routeLine) {
                    this.map.removeLayer(this.routeLine);
                }
                
                // Hitung jarak geodetik menggunakan formula Haversine
                const lat1 = this.userCoords[0];
                const lon1 = this.userCoords[1];
                const lat2 = targetCoords[0];
                const lon2 = targetCoords[1];
                this.userDistance = this.hitungJarak(lat1, lon1, lat2, lon2);

                // Polyline interaktif berpendar ke arah negara/pelabuhan
                this.routeLine = L.polyline([this.userCoords, targetCoords], {
                    color: '#ef4444',
                    weight: 2.5,
                    opacity: 0.8,
                    dashArray: '8, 8',
                    className: 'animated-route-line'
                }).addTo(this.map);

                // Zoom ke area rute penghubung
                let bounds = L.latLngBounds([this.userCoords, targetCoords]);
                this.map.fitBounds(bounds, { padding: [60, 60], maxZoom: 6 });
            },

            hitungJarak(lat1, lon1, lat2, lon2) {
                const R = 6371; // km
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLon = (lon2 - lon1) * Math.PI / 180;
                const a = 
                    Math.sin(dLat/2) * Math.sin(dLat/2) +
                    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
                    Math.sin(dLon/2) * Math.sin(dLon/2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                const d = R * c;
                return Math.round(d);
            },

            focusUser() {
                if (this.userCoords) {
                    this.map.flyTo(this.userCoords, 10, { duration: 1.5 });
                } else {
                    alert("Lokasi Anda belum terdeteksi. Izinkan akses lokasi pada browser Anda.");
                }
            },

            resetPeta() {
                this.map.setView([20.0, 10.0], 2);
                if (this.routeLine) {
                    this.map.removeLayer(this.routeLine);
                    this.routeLine = null;
                }
                this.userDistance = null;
                this.selectedIso = '';
                this.selectedPort = '';
                this.dataNegara = null;
            },

            clearRoute() {
                if (this.routeLine) {
                    this.map.removeLayer(this.routeLine);
                    this.routeLine = null;
                }
                this.userDistance = null;
            },

            toggleCountryMarkers() {
                this.markerList.forEach(m => {
                    if (this.showCountryMarkers) {
                        m.addTo(this.map);
                    } else {
                        this.map.removeLayer(m);
                    }
                });
            },

            togglePortMarkers() {
                this.portMarkerList.forEach(m => {
                    if (this.showPortMarkers) {
                        m.addTo(this.map);
                    } else {
                        this.map.removeLayer(m);
                    }
                });
            },

            // Filter pelabuhan berdasarkan negara terpilih
            get portsForSelectedCountry() {
                if (!this.dataNegara) return [];
                return this.dataPelabuhan.filter(p => p.kode_iso === this.dataNegara.negara.kode_iso);
            },

            flyToPort() {
                if (!this.selectedPort) return;
                const coords = this.selectedPort.split(',');
                const lat = parseFloat(coords[0]);
                const lng = parseFloat(coords[1]);
                this.drawConnectionLine([lat, lng]);
                this.map.flyTo([lat, lng], 8, { duration: 1.5 });
            },

            flyToPortCoords(lat, lng, name) {
                this.drawConnectionLine([lat, lng], name);
                this.map.flyTo([lat, lng], 8, { duration: 1.5 });
            },

            totalBobot() {
                return Number(this.simBobotCuaca) + Number(this.simBobotEkonomi) + Number(this.simBobotKurs) + Number(this.simBobotBerita);
            },

            recalculateRisks() {
                const wCuaca = Number(this.simBobotCuaca);
                const wEko = Number(this.simBobotEkonomi);
                const wKurs = Number(this.simBobotKurs);
                const wBerita = Number(this.simBobotBerita);
                const sum = wCuaca + wEko + wKurs + wBerita;
                const wSisa = 100 - sum;
                const wLogistik = wSisa > 0 ? wSisa / 2 : 0;
                const wPolitik = wSisa > 0 ? wSisa / 2 : 0;

                this.markerList.forEach(m => {
                    const pt = m.pointData;
                    if (!pt) return;

                    let newScore = (
                        (pt.skor_cuaca * wCuaca) +
                        (pt.skor_ekonomi * wEko) +
                        (pt.skor_nilai_tukar * wKurs) +
                        (pt.skor_berita * wBerita) +
                        (pt.skor_logistik * wLogistik) +
                        (pt.skor_politik * wPolitik)
                    ) / 100;

                    newScore = Math.min(100, Math.max(0, Math.round(newScore * 100) / 100));
                    
                    let level = 'Rendah';
                    let warna = '#10b981'; // emerald
                    if (newScore >= 70) { level = 'Kritis'; warna = '#ef4444'; }
                    else if (newScore >= 45) { level = 'Tinggi'; warna = '#f97316'; }
                    else if (newScore >= 25) { level = 'Sedang'; warna = '#f59e0b'; }

                    const pulsingIconHtml = `<div style="background-color: ${warna}; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 8px ${warna};" class="risk-pulse-${level.toLowerCase()}"></div>`;
                    
                    m.setIcon(L.divIcon({
                        html: pulsingIconHtml,
                        className: 'risk-pulse-container',
                        iconSize: [12, 12],
                        iconAnchor: [6, 6]
                    }));

                    m.setTooltipContent(`<strong>${pt.nama}</strong><br>Skor Terhitung: ${newScore}`);

                    // Sinkronisasi data di detail panel jika negara ini aktif
                    if (this.dataNegara && this.dataNegara.negara.kode_iso === pt.kode_iso) {
                        this.simSkorTerkini = newScore;
                        this.simLevelTerkini = level;
                    }
                });
            },

            async fetchDataNegara(kodeIso) {
                if (!kodeIso) {
                    this.dataNegara = null;
                    this.simSkorTerkini = null;
                    this.simLevelTerkini = null;
                    return;
                }

                this.isLoading = true;
                try {
                    const response = await fetch(`{{ url('/api/negara') }}/${kodeIso}`);
                    if (!response.ok) throw new Error('Network response was not ok');
                    const data = await response.json();
                    
                    this.dataNegara = data;
                    
                    // Reset status simulasi untuk negara baru
                    this.simSkorTerkini = null;
                    this.simLevelTerkini = null;

                    // Jalankan kalkulasi simulasi bobot di awal jika simulator sedang aktif/diubah
                    if (this.showSimulator) {
                        this.recalculateRisks();
                    }
                } catch (error) {
                    console.error("Gagal mengambil data:", error);
                    alert("Gagal mengambil data SCM negara.");
                } finally {
                    this.isLoading = false;
                    
                    // Render Chart setelah Alpine selesai update DOM dan transisi (delay 200ms)
                    this.$nextTick(() => {
                        setTimeout(() => {
                            if (this.dataNegara && this.dataNegara.forex && this.dataNegara.forex.history) {
                                this.renderForexChart(this.dataNegara.forex.history);
                            }
                        }, 200);
                    });
                }
            },

            renderForexChart(history) {
                const ctx = document.getElementById('forexChart');
                if (!ctx) return;
                
                if (this.forexChartInstance) {
                    this.forexChartInstance.destroy();
                }

                if (!history || history.length === 0) return;

                const labels = history.map(item => new Date(item.tanggal_berlaku).toLocaleDateString('id-ID', {day:'numeric', month:'short'}));
                const dataPoints = history.map(item => item.nilai_tukar);

                this.forexChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Nilai Tukar ke USD',
                            data: dataPoints,
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.05)',
                            borderWidth: 1.5,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                            fill: true,
                            tension: 0.35
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { display: false },
                            y: { 
                                display: true, 
                                position: 'right',
                                grid: { color: 'rgba(255, 255, 255, 0.04)' },
                                ticks: { color: '#909097', font: {size: 9} }
                            }
                        },
                        interaction: { intersect: false, mode: 'index' }
                    }
                });
            },

            // Utilities
            getWarnaRisikoText(level) {
                switch(level?.toLowerCase()) {
                    case 'kritis': return 'text-error';
                    case 'tinggi': return 'text-orange-400';
                    case 'sedang': return 'text-amber-400';
                    default: return 'text-emerald-400';
                }
            },
            getBgRisikoBadge(level) {
                switch(level?.toLowerCase()) {
                    case 'kritis': return 'bg-error-container text-on-error-container border border-error/20';
                    case 'tinggi': return 'bg-orange-500/20 text-orange-400 border border-orange-500/30';
                    case 'sedang': return 'bg-amber-500/20 text-amber-400 border border-amber-500/30';
                    default: return 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30';
                }
            },
            getSentimentColor(sentimen) {
                return sentimen === 'negatif' ? 'text-error' : (sentimen === 'positif' ? 'text-emerald-400' : 'text-on-surface-variant');
            },
            getBgSeverity(kep) {
                return (kep === 'kritis' || kep === 'tinggi') 
                    ? 'bg-error-container text-on-error-container border border-error/20'
                    : 'bg-amber-500/20 text-amber-400 border border-amber-500/30';
            },
            formatSaran(text) {
                return text ? text.replace(/\n/g, '<br>') : '';
            },

            async toggleFavoritDasbor(negaraId) {
                if (!negaraId) return;
                try {
                    const response = await fetch(`{{ url('/favorit') }}/${negaraId}/toggle`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const data = await response.json();
                    if (data.status === 'success') {
                        if (data.action === 'added') {
                            this.favoritIds.push(negaraId);
                        } else {
                            this.favoritIds = this.favoritIds.filter(id => id !== negaraId);
                        }
                        // Update badge sidebar
                        const badge = document.getElementById('sidebar-favorit-count');
                        if (badge) {
                            badge.innerText = data.total;
                            badge.style.display = data.total > 0 ? 'inline-flex' : 'none';
                        }
                    }
                } catch (e) {
                    console.error('Gagal toggle favorit:', e);
                }
            }
        }));
    });
</script>
@endsection
