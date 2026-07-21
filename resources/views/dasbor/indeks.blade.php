@extends('layouts.aplikasi')

@section('judul', 'Global Control Center')

@section('gaya_tambahan')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    [x-cloak] { display: none !important; }

    /* ===== SPA LAYOUT ===== */
    .spa-container {
        display: flex;
        gap: 20px;
        height: calc(100vh - 116px);
        min-height: 640px;
    }
    .panel-kiri {
        flex: 0 0 64%;
        display: flex;
        flex-direction: column;
        gap: 0;
    }
    .panel-kanan {
        flex: 1;
        background: rgba(10, 15, 26, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 14px;
        padding: 20px;
        overflow-y: auto;
        position: relative;
        box-shadow: 0 0 40px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.06);
    }
    .panel-kanan::-webkit-scrollbar { width: 4px; }
    .panel-kanan::-webkit-scrollbar-track { background: transparent; }
    .panel-kanan::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 4px; }

    /* ===== MAP CONTAINER ===== */
    .map-container {
        flex: 1;
        border-radius: 14px;
        border: 1px solid rgba(255,255,255,0.08);
        position: relative;
        overflow: hidden;
        box-shadow: 0 0 60px rgba(0,0,0,0.5), 0 0 0 1px rgba(64,150,255,0.05);
    }
    .map-container::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 14px;
        border: 1px solid rgba(64,150,255,0.08);
        pointer-events: none;
        z-index: 1001;
    }
    #peta-sig {
        height: 100%;
        width: 100%;
        background-color: #060b12;
    }

    /* ===== HUD OVERLAY ===== */
    .hud-overlay {
        position: absolute;
        top: 14px;
        left: 14px;
        z-index: 1000;
        display: flex;
        gap: 10px;
        align-items: center;
        pointer-events: none;
    }
    .negara-select {
        background: rgba(8, 12, 20, 0.9);
        backdrop-filter: blur(12px);
        color: #e0e8f0;
        border: 1px solid rgba(255,255,255,0.1);
        padding: 9px 14px;
        border-radius: 9px;
        font-family: 'Inter', sans-serif;
        font-size: 13px;
        min-width: 240px;
        cursor: pointer;
        pointer-events: auto;
        transition: border-color 0.2s ease;
        box-shadow: 0 4px 16px rgba(0,0,0,0.4);
    }
    .negara-select:focus { outline: none; border-color: rgba(255,77,79,0.5); }
    .negara-select option { background: #0d1420; }

    .hud-stat {
        background: rgba(8, 12, 20, 0.88);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.08);
        padding: 8px 14px;
        border-radius: 9px;
        pointer-events: auto;
        box-shadow: 0 4px 16px rgba(0,0,0,0.35);
    }

    /* ===== LOADING OVERLAY ===== */
    .loader {
        position: absolute;
        inset: 0;
        background: rgba(8, 12, 20, 0.75);
        backdrop-filter: blur(6px);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 14px;
        z-index: 50;
        border-radius: 14px;
    }
    .loader-ring {
        width: 42px; height: 42px;
        border: 3px solid rgba(224,49,49,0.15);
        border-top-color: #ff4d4f;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ===== SIMULATOR PANEL ===== */
    .sim-panel {
        background: rgba(224,49,49,0.04);
        border: 1px solid rgba(224,49,49,0.15);
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 18px;
        transition: border-color 0.2s ease;
    }
    .sim-panel:hover { border-color: rgba(224,49,49,0.25); }
    .sim-header {
        font-family: 'Outfit', sans-serif;
        font-size: 12.5px;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        color: #e0e8f0;
        letter-spacing: 0.2px;
    }
    .sim-row {
        display: grid;
        grid-template-columns: 1fr auto;
        align-items: center;
        gap: 10px;
        font-size: 11px;
    }
    .sim-label { color: var(--c-text-3); }
    .sim-label strong { color: var(--c-text-2); font-family: 'JetBrains Mono', monospace; }
    input[type=range] {
        -webkit-appearance: none;
        appearance: none;
        height: 4px;
        border-radius: 2px;
        background: rgba(255,255,255,0.08);
        width: 100%;
        cursor: pointer;
    }
    input[type=range]::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 13px; height: 13px;
        border-radius: 50%;
        background: #ff4d4f;
        border: 2px solid #fff;
        box-shadow: 0 0 8px rgba(255,77,79,0.5);
        cursor: pointer;
    }

    /* ===== INSIGHT CARDS ===== */
    .insight-card {
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 14px;
        transition: border-color 0.2s ease;
    }
    .insight-card:hover { border-color: rgba(255,255,255,0.1); }
    .insight-header {
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: 'Outfit', sans-serif;
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        letter-spacing: -0.1px;
    }

    /* ===== BADGE DYNAMIC (Risk Level) ===== */
    .badge-dynamic {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }

    /* ===== SECTION DIVIDER ===== */
    .section-divider {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 18px 0 14px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--c-text-3);
    }
    .section-divider::before, .section-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: rgba(255,255,255,0.05);
    }

    /* ===== DARK TOOLTIP (Leaflet) ===== */
    .dark-tooltip {
        background: rgba(8,12,20,0.95) !important;
        border: 1px solid rgba(255,255,255,0.12) !important;
        color: #e0e8f0 !important;
        font-family: 'Inter', sans-serif !important;
        font-size: 12px !important;
        border-radius: 8px !important;
        box-shadow: 0 8px 24px rgba(0,0,0,0.5) !important;
        padding: 8px 12px !important;
    }
    .dark-tooltip::before { display: none !important; }

    /* ===== GEOLOCATION & ROUTE ANIMATIONS ===== */
    .user-pulse-container { position: relative; }
    .user-pulse-marker {
        width: 13px; height: 13px;
        background: #4096ff;
        border: 2px solid rgba(255,255,255,0.9);
        border-radius: 50%;
        box-shadow: 0 0 14px rgba(64,150,255,0.7);
    }
    .user-pulse-container::after {
        content: '';
        width: 28px; height: 28px;
        border: 2px solid rgba(64,150,255,0.6);
        border-radius: 50%;
        position: absolute;
        top: -7.5px; left: -7.5px;
        animation: user-pulse-anim 2s infinite ease-out;
        opacity: 0;
        pointer-events: none;
    }
    @keyframes user-pulse-anim {
        0% { transform: scale(0.3); opacity: 0.7; }
        100% { transform: scale(1.3); opacity: 0; }
    }

    .animated-route-line {
        stroke-dasharray: 8;
        animation: dash-animation 25s linear infinite;
    }
    @keyframes dash-animation { to { stroke-dashoffset: -1000; } }

    .risk-pulse-container { position: relative; }
    .risk-pulse-kritis { animation: pulse-kritis 1.4s infinite; }
    .risk-pulse-tinggi  { animation: pulse-tinggi  1.6s infinite; }
    .risk-pulse-sedang  { animation: pulse-sedang  1.8s infinite; }
    .risk-pulse-rendah  { animation: pulse-rendah  2.2s infinite; }

    @keyframes pulse-kritis {
        0%  { box-shadow: 0 0 0 0 rgba(255,77,79,0.8); }
        70% { box-shadow: 0 0 0 8px rgba(255,77,79,0); }
        100%{ box-shadow: 0 0 0 0 rgba(255,77,79,0); }
    }
    @keyframes pulse-tinggi {
        0%  { box-shadow: 0 0 0 0 rgba(255,122,69,0.8); }
        70% { box-shadow: 0 0 0 8px rgba(255,122,69,0); }
        100%{ box-shadow: 0 0 0 0 rgba(255,122,69,0); }
    }
    @keyframes pulse-sedang {
        0%  { box-shadow: 0 0 0 0 rgba(255,197,61,0.8); }
        70% { box-shadow: 0 0 0 8px rgba(255,197,61,0); }
        100%{ box-shadow: 0 0 0 0 rgba(255,197,61,0); }
    }
    @keyframes pulse-rendah {
        0%  { box-shadow: 0 0 0 0 rgba(82,196,26,0.7); }
        70% { box-shadow: 0 0 0 8px rgba(82,196,26,0); }
        100%{ box-shadow: 0 0 0 0 rgba(82,196,26,0); }
    }
</style>
@endsection

@section('konten')
<div x-data="dashboardSPA()" x-init="initPeta()" class="spa-container">
    
    <!-- Bagian Kiri: Peta Leaflet HUD -->
    <div class="panel-kiri">
        <div class="map-container">
            <div class="hud-overlay">
                <!-- Dropdown Pencarian Cepat Negara -->
                <select class="negara-select" x-model="selectedIso" @change="fetchDataNegara(selectedIso)">
                    <option value="">-- Cari Analisis Negara --</option>
                    @foreach($negaraList as $n)
                        <option value="{{ $n->kode_iso }}">{{ $n->nama }} ({{ $n->kode_iso }})</option>
                    @endforeach
                </select>

                <!-- Dropdown Pencarian Pelabuhan -->
                <select class="negara-select" x-model="selectedPort" @change="flyToPort()">
                    <option value="">-- Cari Pelabuhan SCM --</option>
                    @foreach(collect($dataPelabuhan)->sortBy('nama') as $p)
                        <option value="{{ $p['lintang'] }},{{ $p['bujur'] }}">{{ $p['nama'] }} ({{ $p['kode_locode'] }})</option>
                    @endforeach
                </select>
                
                <div class="hud-stat">
                    <div style="font-size: 9px; color: var(--c-text-3); text-transform: uppercase; letter-spacing: 1px;">Total Node SCM</div>
                    <div style="font-size: 19px; font-weight: 700; font-family: 'Outfit'; color: var(--c-text-1);">{{ $negaraList->count() }}</div>
                </div>
            </div>
            
            <!-- Leaflet Target -->
            <div id="peta-sig"></div>
        </div>
    </div>

    <!-- Bagian Kanan: Panel Kendali / Detail SCM -->
    <div class="panel-kanan">
        
        <!-- Loading Overlay -->
        <div x-show="isLoading" class="loader" x-transition>
            <div class="loader-ring"></div>
            <div style="font-size: 11px; color: var(--c-text-3); letter-spacing: 0.5px;">Memuat data intelijen…</div>
        </div>

        <!-- Simulator Bobot Risiko Collapsible Panel -->
        <div class="sim-panel">
            <div class="sim-header" @click="showSimulator = !showSimulator">
                <span><i class="fa-solid fa-sliders" style="color: #ff4d4f; margin-right: 7px;"></i>Simulator Bobot Risiko</span>
                <i class="fa-solid" :class="showSimulator ? 'fa-chevron-up' : 'fa-chevron-down'" style="font-size: 9px; color: var(--c-text-3);"></i>
            </div>
            <div x-show="showSimulator" style="display: flex; flex-direction: column; gap: 9px; margin-top: 12px;" x-transition>
                <div class="sim-row">
                    <div class="sim-label">Cuaca <strong x-text="simBobotCuaca + '%'"></strong></div>
                    <input type="range" min="0" max="100" x-model="simBobotCuaca" @input="recalculateRisks()" style="width: 110px;">
                </div>
                <div class="sim-row">
                    <div class="sim-label">Ekonomi / Inflasi <strong x-text="simBobotEkonomi + '%'"></strong></div>
                    <input type="range" min="0" max="100" x-model="simBobotEkonomi" @input="recalculateRisks()" style="width: 110px;">
                </div>
                <div class="sim-row">
                    <div class="sim-label">Nilai Tukar Valas <strong x-text="simBobotKurs + '%'"></strong></div>
                    <input type="range" min="0" max="100" x-model="simBobotKurs" @input="recalculateRisks()" style="width: 110px;">
                </div>
                <div class="sim-row">
                    <div class="sim-label">Sentimen Berita <strong x-text="simBobotBerita + '%'"></strong></div>
                    <input type="range" min="0" max="100" x-model="simBobotBerita" @input="recalculateRisks()" style="width: 110px;">
                </div>
                <div style="font-size: 9px; color: var(--c-text-3); border-top: 1px solid rgba(255,255,255,0.05); padding-top: 6px; margin-top: 2px;">
                    Total: <span style="color: var(--c-text-2); font-weight: 700; font-family: 'JetBrains Mono', monospace;" x-text="Number(simBobotCuaca)+Number(simBobotEkonomi)+Number(simBobotKurs)+Number(simBobotBerita) + '%'"></span> &mdash; Sisa untuk logistik &amp; politik
                </div>
            </div>
        </div>

        <!-- STATE 1: Default Global Overview (Jika belum ada negara terpilih) -->
        <div x-show="!selectedIso && !isLoading" x-transition.opacity>
            <div style="margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.06);">
                <div style="font-family: 'Outfit'; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-earth-americas" style="color: var(--c-blue);"></i> Ringkasan SCM Global
                </div>
                <div style="font-size: 11px; color: var(--c-text-3); margin-top: 3px;">Klik marker negara di peta untuk memulai analisis</div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                <div style="background: rgba(255,77,79,0.08); border: 1px solid rgba(255,77,79,0.2); padding: 16px 14px; border-radius: 10px; position: relative; overflow: hidden;">
                    <div style="font-size: 10px; color: var(--c-kritis); text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px;">Risiko Kritis</div>
                    <div style="font-size: 30px; font-weight: 800; color: var(--c-kritis); font-family: 'Outfit';">{{ $statistik['kritis'] }}</div>
                </div>
                <div style="background: rgba(234, 179, 8, 0.1); border: 1px solid rgba(234, 179, 8, 0.3); padding: 16px; border-radius: 8px;">
                    <div style="font-size: 12px; color: #fde047;">Risiko Sedang/Tinggi</div>
                    <div style="font-size: 32px; font-weight: 700; color: #eab308; font-family: 'Outfit';">{{ $statistik['tinggi'] + $statistik['sedang'] }}</div>
                </div>
            </div>

            <h3 style="font-family: 'Outfit'; font-size: 16px; margin-bottom: 12px;"><i class="fa-solid fa-newspaper"></i> Umpan Berita Kritis Terbaru</h3>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @forelse($beritaTerbaru as $berita)
                <div style="background: rgba(255,255,255,0.03); padding: 12px; border-radius: 8px; font-size: 13px;">
                    <div style="color: #9ca3af; font-size: 11px; margin-bottom: 4px;">{{ $berita->negara->nama }} • {{ date('d M H:i', strtotime($berita->diterbitkan_pada)) }}</div>
                    @if($berita->url_asli)
                        <a href="{{ $berita->url_asli }}" target="_blank" style="font-weight: 600; margin-bottom: 6px; display: block; color: var(--warna-teks-putih); text-decoration: none;">
                            {{ $berita->judul }} <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 10px; margin-left: 4px; color: #9ca3af;"></i>
                        </a>
                    @else
                        <div style="font-weight: 600; margin-bottom: 6px;">{{ $berita->judul }}</div>
                    @endif
                    <span class="badge-dynamic" style="background: rgba(220,38,38,0.2); color:#f87171; border: 1px solid #dc2626;">{{ $berita->keparahan }}</span>
                </div>
                @empty
                <div style="color: #9ca3af; font-size: 13px; text-align: center; padding: 20px;">Belum ada anomali berita kritis terdeteksi.</div>
                @endforelse
            </div>
            
            <div style="margin-top: 24px; text-align: center; font-size: 12px; color: #6b7280;">
                <i class="fa-solid fa-arrow-pointer"></i> Klik titik negara di peta untuk mulai analisis mendalam.
            </div>
        </div>

        <!-- STATE 2: Detail Negara Terpilih -->
        <div x-show="selectedIso && dataNegara && !isLoading" x-transition.opacity x-cloak>
            
            <!-- Header Negara -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; border-bottom: 1px solid #374151; padding-bottom: 16px;">
                <div>
                    <h2 style="font-family: 'Outfit'; font-size: 28px; font-weight: 700; display: flex; align-items: center; gap: 12px;">
                        <img :src="'https://flagcdn.com/w40/' + dataNegara?.negara?.bendera + '.png'" style="height: 20px; width: 30px; object-fit: cover; border-radius: 3px; border: 1px solid rgba(255,255,255,0.15);" :alt="dataNegara?.negara?.nama + ' Flag'">
                        <span x-text="dataNegara?.negara?.nama"></span>
                    </h2>
                    <div style="font-size: 12px; color: #9ca3af; margin-top: 4px; display:flex;align-items:center;gap:12px;">
                        <span x-text="'Kode ISO: ' + dataNegara?.negara?.kode_iso"></span>
                        <!-- Tombol Favorit -->
                        <button
                            @click="toggleFavoritDasbor(dataNegara?.negara?.id, dataNegara?.negara?.kode_iso)"
                            :title="favoritIds.includes(dataNegara?.negara?.id) ? 'Hapus dari Favorit' : 'Tambah ke Favorit'"
                            style="background:none;border:none;cursor:pointer;padding:4px 8px;border-radius:6px;transition:all 0.2s;display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;"
                            :style="favoritIds.includes(dataNegara?.negara?.id) ? 'background:rgba(153,27,27,0.2);color:#fbbf24;border:1px solid rgba(153,27,27,0.4);' : 'background:rgba(55,65,81,0.3);color:#9ca3af;border:1px solid rgba(55,65,81,0.4);'"
                        >
                            <i class="fa-solid fa-star" :style="favoritIds.includes(dataNegara?.negara?.id) ? 'color:#fbbf24' : 'color:#6b7280'"></i>
                            <span x-text="favoritIds.includes(dataNegara?.negara?.id) ? 'Difavoritkan' : '+ Favorit'"></span>
                        </button>
                    </div>
                </div>
                
                <div style="text-align: right;" x-show="dataNegara?.risiko">
                    <div style="font-size: 10px; color: #9ca3af; text-transform: uppercase;">Skor Risiko Total</div>
                    <div style="font-size: 28px; font-weight: 800; font-family: 'Outfit';" 
                         :style="getWarnaRisiko(simLevelTerkini || dataNegara?.risiko?.level)" 
                         x-text="(simSkorTerkini !== null ? simSkorTerkini : dataNegara?.risiko?.skor) + '/100'"></div>
                    <div class="badge-dynamic" :style="getBgRisiko(simLevelTerkini || dataNegara?.risiko?.level)" x-text="simLevelTerkini || dataNegara?.risiko?.level"></div>
                </div>
            </div>

            <!-- Modul Cuaca -->
            <div class="insight-card">
                <div class="insight-header" style="color: #60a5fa;"><i class="fa-solid fa-cloud-sun"></i> Kondisi & Prakiraan Cuaca SCM</div>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 16px;">
                    <div>
                        <div style="font-size: 24px; font-weight: 700; font-family: 'Outfit';" x-text="(dataNegara?.cuaca?.terkini?.suhu || '--') + ' °C'"></div>
                        <div style="font-size: 13px; color: #9ca3af;" x-text="(dataNegara?.cuaca?.terkini?.kondisi_cuaca || 'N/A').toUpperCase()"></div>
                    </div>
                    <div style="text-align: right; font-size: 12px; color: #9ca3af; line-height: 1.6;">
                        <div>Curah Hujan: <strong style="color:#fff;" x-text="(dataNegara?.cuaca?.terkini?.curah_hujan || '0') + ' mm'"></strong></div>
                        <div>Angin: <strong style="color:#fff;" x-text="(dataNegara?.cuaca?.terkini?.kecepatan_angin || '0') + ' km/h'"></strong></div>
                    </div>
                </div>
                
                <!-- 7 Day Mini Chart -->
                <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; margin-bottom: 12px;" x-show="dataNegara?.cuaca?.prakiraan?.length > 0">
                    <template x-for="hari in dataNegara?.cuaca?.prakiraan.slice(0,7)">
                        <div style="background: rgba(0,0,0,0.2); padding: 6px 2px; text-align: center; border-radius: 4px;">
                            <div style="font-size: 9px; color: #9ca3af;" x-text="new Date(hari.tanggal_observasi).toLocaleDateString('id-ID', {weekday:'short'})"></div>
                            <div style="font-size: 11px; font-weight: 700; margin: 4px 0;" x-text="(hari.suhu_max || hari.suhu || '--') + '°'"></div>
                            <div style="font-size: 9px; color: #60a5fa;" x-text="(hari.suhu_min || '--') + '°'"></div>
                        </div>
                    </template>
                </div>

                <!-- Weather Insight -->
                <div x-show="dataNegara?.cuaca?.insight" style="background: rgba(96, 165, 250, 0.1); border-left: 3px solid #60a5fa; padding: 10px; font-size: 12px; color: #d1d5db; border-radius: 0 4px 4px 0;">
                    <i class="fa-solid fa-lightbulb" style="color:#60a5fa;"></i> <span x-html="formatSaran(dataNegara?.cuaca?.insight)"></span>
                </div>
            </div>

            <!-- Modul Valas / Forex -->
            <div class="insight-card">
                <div class="insight-header" style="color: #fbbf24;"><i class="fa-solid fa-coins"></i> Fluktuasi Nilai Tukar (<span x-text="dataNegara?.forex?.mata_uang"></span>/USD)</div>
                <div style="font-size: 24px; font-family: 'Outfit'; font-weight: 700; margin-bottom: 12px;" x-text="Number(dataNegara?.forex?.terkini?.nilai_tukar || 0).toFixed(4)"></div>
                
                <!-- Chart.js Container -->
                <div style="height: 120px; width: 100%; margin-bottom: 12px;">
                    <canvas id="forexChart"></canvas>
                </div>

                <!-- Forex Insight -->
                <div x-show="dataNegara?.forex?.insight" style="background: rgba(251, 191, 36, 0.1); border-left: 3px solid #fbbf24; padding: 10px; font-size: 12px; color: #d1d5db; border-radius: 0 4px 4px 0;">
                    <i class="fa-solid fa-lightbulb" style="color:#fbbf24;"></i> <span x-html="formatSaran(dataNegara?.forex?.insight)"></span>
                </div>
            </div>

            <!-- Modul Berita -->
            <div class="insight-card">
                <div class="insight-header" style="color: #e5e7eb;"><i class="fa-solid fa-satellite-dish"></i> Radar Berita Geopolitik SCM</div>
                
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <template x-for="berita in dataNegara?.berita">
                        <div style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 12px;">
                            <div style="display: flex; gap: 8px; margin-bottom: 6px; align-items: center;">
                                <span class="badge-dynamic" :style="getBgKeparahan(berita.keparahan)" x-text="berita.keparahan"></span>
                                <span style="font-size: 11px; font-weight: 600;" :style="getWarnaSentimen(berita.sentimen)" x-text="berita.sentimen.toUpperCase()"></span>
                                <span style="font-size: 10px; color: #9ca3af;" x-text="berita.sumber + ' • ' + new Date(berita.diterbitkan_pada).toLocaleDateString()"></span>
                            </div>
                            <!-- Judul Berita -->
                            <template x-if="berita.url_asli">
                                <a :href="berita.url_asli" target="_blank" style="font-size: 14px; font-weight: 600; line-height: 1.4; margin-bottom: 8px; display: block; color: var(--warna-teks-putih); text-decoration: none;">
                                    <span x-text="berita.judul"></span>
                                    <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 10px; margin-left: 4px; color: #9ca3af;"></i>
                                </a>
                            </template>
                            <template x-if="!berita.url_asli">
                                <div style="font-size: 14px; font-weight: 600; line-height: 1.4; margin-bottom: 8px;" x-text="berita.judul"></div>
                            </template>
                            
                            <!-- News SCM Insight -->
                            <div x-show="berita.dampak_scm" style="background: rgba(239, 68, 68, 0.08); border-left: 2px solid #ef4444; padding: 8px 12px; font-size: 11px; color: #d1d5db;">
                                <strong style="color: #ef4444; display:block; margin-bottom:2px;">DAMPAK SCM:</strong>
                                <span x-html="formatSaran(berita.dampak_scm)"></span>
                            </div>
                        </div>
                    </template>
                    <div x-show="!dataNegara?.berita?.length" style="font-size: 12px; color: #9ca3af; text-align: center;">Tidak ada pantauan berita krusial saat ini.</div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 24px;">
                <a :href="'/negara/' + dataNegara?.negara?.kode_iso" class="btn btn-primer" style="width: 100%; justify-content: center;">
                    Buka Laporan Penuh <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
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

            markerList: [],
            userCoords: null,
            userMarker: null,
            routeLine: null,

            initPeta() {
                // Inisiasi Peta Leaflet (Tema Gelap ala Control Center)
                this.map = L.map('peta-sig', { zoomControl: false }).setView([20.0, 10.0], 2);
                L.control.zoom({ position: 'bottomright' }).addTo(this.map);

                // CartoDB Dark Matter
                L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                    maxZoom: 18,
                    minZoom: 2,
                    attribution: '&copy; CARTO'
                }).addTo(this.map);

                // Jalankan Deteksi Lokasi Pengguna
                this.detectUserLocation();

                // Gambar Rute Maritim
                this.ruteServer.forEach(rute => {
                    L.polyline([rute.asal, rute.tujuan], {
                        color: '#3b82f6', weight: 1.5, opacity: 0.25, dashArray: '5, 10'
                    }).addTo(this.map);
                });

                // Gambar Node / Marker Berdenyut Kustom
                this.dataPetaServer.forEach(point => {
                    if (point.lintang && point.bujur) {
                        let levelRisiko = point.level_risiko || 'rendah';
                        
                        let pulsingIcon = L.divIcon({
                            html: `<div style="background-color: ${point.warna}; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 8px ${point.warna};" class="risk-pulse-${levelRisiko.toLowerCase()}"></div>`,
                            className: 'risk-pulse-container',
                            iconSize: [12, 12],
                            iconAnchor: [6, 6]
                        });

                        let marker = L.marker([point.lintang, point.bujur], { icon: pulsingIcon }).addTo(this.map);
                        marker.pointData = point; // Simpan data untuk simulator
                        this.markerList.push(marker);

                        // Tooltip Hover Sederhana
                        marker.bindTooltip(`<strong>${point.nama}</strong><br>Skor Risiko: ${point.skor_total}`, {
                            className: 'dark-tooltip', direction: 'top'
                        });

                        // Event Klik Marker -> Fetch Data & Buat Rute Hubungan ke User
                        marker.on('click', () => {
                            this.selectedIso = point.kode_iso;
                            this.fetchDataNegara(point.kode_iso);
                            this.drawConnectionLine([point.lintang, point.bujur]);
                            this.map.flyTo([point.lintang, point.bujur], 4, { duration: 1.2 });
                        });
                    }
                });

                // Gambar Marker Pelabuhan (Jangkar)
                this.dataPelabuhan.forEach(port => {
                    if (port.lintang && port.bujur) {
                        let portIcon = L.divIcon({
                            html: `<div style="background-color: ${port.warna}; border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.5);"><i class="fa-solid fa-anchor" style="color: white; font-size: 10px;"></i></div>`,
                            className: '',
                            iconSize: [22, 22],
                            iconAnchor: [11, 11]
                        });

                        let marker = L.marker([port.lintang, port.bujur], { icon: portIcon }).addTo(this.map);

                        marker.bindTooltip(`
                            <div style="font-family:'Inter'; text-align:left;">
                                <strong style="font-family:'Outfit'; font-size:14px; color:#fff;">${port.nama} (${port.kode_locode})</strong>
                                <div style="color:#9ca3af; font-size:11px; margin-bottom:4px;">${port.negara}</div>
                                <div style="font-size:12px;">Jenis: <span style="color:#60a5fa">${port.jenis.toUpperCase()}</span></div>
                                <div style="font-size:12px;">Kapasitas: <strong>${port.kapasitas_teu} TEU</strong></div>
                                <div style="font-size:12px;">Kepadatan: <strong style="color:${port.warna}">${port.tingkat_kepadatan}% (${port.label_kepadatan})</strong></div>
                            </div>
                        `, { className: 'dark-tooltip', direction: 'top' });

                        marker.on('click', () => {
                            this.selectedIso = port.kode_iso;
                            this.fetchDataNegara(port.kode_iso);
                            this.drawConnectionLine([port.lintang, port.bujur]);
                            this.map.flyTo([port.lintang, port.bujur], 6, { duration: 1.2 });
                        });
                    }
                });
            },

            detectUserLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(position => {
                        this.userCoords = [position.coords.latitude, position.coords.longitude];
                        this.renderUserMarker();
                    }, error => {
                        console.warn("Geolocation tidak diizinkan, menggunakan koordinat server (Jakarta):", error);
                        this.userCoords = [-6.2088, 106.8456]; // Fallback Jakarta
                        this.renderUserMarker();
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

            drawConnectionLine(targetCoords) {
                if (!this.userCoords) return;
                if (this.routeLine) {
                    this.map.removeLayer(this.routeLine);
                }
                
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

            recalculateRisks() {
                const wCuaca = Number(this.simBobotCuaca);
                const wEko = Number(this.simBobotEkonomi);
                const wKurs = Number(this.simBobotKurs);
                const wBerita = Number(this.simBobotBerita);
                const wSisa = 100 - (wCuaca + wEko + wKurs + wBerita);
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
                    let warna = '#16a34a';
                    if (newScore >= 70) { level = 'Kritis'; warna = '#dc2626'; }
                    else if (newScore >= 45) { level = 'Tinggi'; warna = '#f97316'; }
                    else if (newScore >= 25) { level = 'Sedang'; warna = '#eab308'; }

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

            flyToPort() {
                if(!this.selectedPort) return;
                const coords = this.selectedPort.split(',');
                this.map.flyTo([coords[0], coords[1]], 8, { duration: 1.5 });
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
                    
                    // Render Chart setelah Alpine selesai update DOM
                    this.$nextTick(() => {
                        this.renderForexChart(data.forex.history);
                    });
                } catch (error) {
                    console.error("Gagal mengambil data:", error);
                    alert("Gagal mengambil data SCM negara.");
                } finally {
                    this.isLoading = false;
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
                            borderColor: '#fbbf24',
                            backgroundColor: 'rgba(251, 191, 36, 0.1)',
                            borderWidth: 2,
                            pointRadius: 1,
                            pointHoverRadius: 4,
                            fill: true,
                            tension: 0.4
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
                                grid: { color: 'rgba(255,255,255,0.05)' },
                                ticks: { color: '#9ca3af', font: {size: 10} }
                            }
                        },
                        interaction: { intersect: false, mode: 'index' }
                    }
                });
            },

            // Utility Tampilan
            getWarnaRisiko(level) {
                switch(level?.toLowerCase()) {
                    case 'kritis': return 'color: #ef4444;';
                    case 'tinggi': return 'color: #f97316;';
                    case 'sedang': return 'color: #eab308;';
                    default: return 'color: #22c55e;';
                }
            },
            getBgRisiko(level) {
                switch(level?.toLowerCase()) {
                    case 'kritis': return 'background: rgba(220,38,38,0.2); color:#ef4444; border: 1px solid #dc2626;';
                    case 'tinggi': return 'background: rgba(249,115,22,0.2); color:#f97316; border: 1px solid #f97316;';
                    case 'sedang': return 'background: rgba(234,179,8,0.2); color:#eab308; border: 1px solid #eab308;';
                    default: return 'background: rgba(34,197,94,0.2); color:#22c55e; border: 1px solid #22c55e;';
                }
            },
            getWarnaSentimen(sentimen) {
                return sentimen === 'negatif' ? 'color: #ef4444;' : (sentimen === 'positif' ? 'color: #22c55e;' : 'color: #9ca3af;');
            },
            getBgKeparahan(kep) {
                return (kep === 'kritis' || kep === 'tinggi') 
                    ? 'background: rgba(220,38,38,0.2); color:#ef4444; border: 1px solid #dc2626;'
                    : 'background: rgba(234,179,8,0.2); color:#eab308; border: 1px solid #eab308;';
            },
            formatSaran(text) {
                // Konversi newline ke <br> untuk html output
                return text ? text.replace(/\n/g, '<br>') : '';
            },

            async toggleFavoritDasbor(negaraId, kodeIso) {
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
<style>
    .dark-tooltip {
        background: rgba(15, 23, 42, 0.9);
        border: 1px solid #374151;
        color: white;
        font-family: 'Inter', sans-serif;
    }
    .leaflet-tooltip-top:before { border-top-color: #374151; }
</style>
@endsection
