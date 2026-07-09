@extends('layouts.aplikasi')

@section('judul', 'Global Control Center')

@section('gaya_tambahan')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    /* Reset & Alpine Cloak */
    [x-cloak] { display: none !important; }

    /* Layout SPA (Full Height) */
    .spa-container {
        display: flex;
        gap: 24px;
        height: calc(100vh - 120px);
        min-height: 700px;
    }

    .panel-kiri {
        flex: 0 0 65%;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .panel-kanan {
        flex: 1;
        background: var(--warna-kaca);
        backdrop-filter: var(--blur-kaca);
        border: 1px solid var(--warna-charcoal-border);
        border-radius: 12px;
        padding: 24px;
        overflow-y: auto;
        position: relative;
    }

    /* Map HUD & Leaflet */
    .map-container {
        flex: 1;
        border-radius: 12px;
        border: 1px solid var(--warna-charcoal-border);
        position: relative;
        overflow: hidden;
    }

    #peta-sig {
        height: 100%;
        width: 100%;
        background-color: #0f172a;
    }

    .hud-overlay {
        position: absolute;
        top: 20px;
        left: 20px;
        z-index: 1000;
        display: flex;
        gap: 12px;
        pointer-events: none;
    }

    .hud-box {
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,0.1);
        padding: 12px 16px;
        border-radius: 8px;
        pointer-events: auto;
        box-shadow: 0 4px 6px rgba(0,0,0,0.3);
    }

    .negara-select {
        background: rgba(15, 23, 42, 0.9);
        color: white;
        border: 1px solid #374151;
        padding: 10px 16px;
        border-radius: 8px;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        min-width: 250px;
        cursor: pointer;
        pointer-events: auto;
    }
    .negara-select:focus { outline: none; border-color: #ef4444; }

    /* Custom Scrollbar for Panel Kanan */
    .panel-kanan::-webkit-scrollbar { width: 6px; }
    .panel-kanan::-webkit-scrollbar-track { background: transparent; }
    .panel-kanan::-webkit-scrollbar-thumb { background: #374151; border-radius: 10px; }

    /* Insight Card Styles */
    .insight-card {
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
    }
    
    .insight-header {
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: 'Outfit', sans-serif;
        font-weight: 600;
        font-size: 15px;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .badge-dynamic {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .loader {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(4px);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 50;
    }
</style>
@endsection

@section('konten')
<div x-data="dashboardSPA()" x-init="initPeta()" class="spa-container">
    
    <!-- Bagian Kiri: Peta Leaflet HUD -->
    <div class="panel-kiri">
        <div class="map-container">
            <div class="hud-overlay">
                <!-- Dropdown Pencarian Cepat -->
                <select class="negara-select" x-model="selectedIso" @change="fetchDataNegara(selectedIso)">
                    <option value="">-- Pilih Negara untuk Analisis --</option>
                    @foreach($negaraList as $n)
                        <option value="{{ $n->kode_iso }}">{{ $n->bendera }} {{ $n->nama }} ({{ $n->kode_iso }})</option>
                    @endforeach
                </select>
                
                <div class="hud-box">
                    <div style="font-size: 10px; color: #9ca3af; text-transform: uppercase;">Total Node SCM</div>
                    <div style="font-size: 20px; font-weight: 700; font-family: 'Outfit';">{{ $negaraList->count() }}</div>
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
            <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 32px; color: #ef4444;"></i>
        </div>

        <!-- STATE 1: Default Global Overview (Jika belum ada negara terpilih) -->
        <div x-show="!selectedIso && !isLoading" x-transition.opacity>
            <h2 style="font-family: 'Outfit'; font-size: 22px; margin-bottom: 24px; border-bottom: 1px solid #374151; padding-bottom: 12px;">
                <i class="fa-solid fa-earth-americas" style="color: #3b82f6;"></i> Ringkasan SCM Global
            </h2>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div style="background: rgba(220, 38, 38, 0.1); border: 1px solid rgba(220, 38, 38, 0.3); padding: 16px; border-radius: 8px;">
                    <div style="font-size: 12px; color: #f87171;">Risiko Kritis</div>
                    <div style="font-size: 32px; font-weight: 700; color: #ef4444; font-family: 'Outfit';">{{ $statistik['kritis'] }}</div>
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
                        <span x-text="dataNegara?.negara?.bendera"></span> 
                        <span x-text="dataNegara?.negara?.nama"></span>
                    </h2>
                    <div style="font-size: 12px; color: #9ca3af; margin-top: 4px;" x-text="'Kode ISO: ' + dataNegara?.negara?.kode_iso"></div>
                </div>
                
                <div style="text-align: right;" x-show="dataNegara?.risiko">
                    <div style="font-size: 10px; color: #9ca3af; text-transform: uppercase;">Skor Risiko Total</div>
                    <div style="font-size: 28px; font-weight: 800; font-family: 'Outfit';" 
                         :style="getWarnaRisiko(dataNegara?.risiko?.level)" 
                         x-text="dataNegara?.risiko?.skor + '/100'"></div>
                    <div class="badge-dynamic" :style="getBgRisiko(dataNegara?.risiko?.level)" x-text="dataNegara?.risiko?.level"></div>
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
            isLoading: false,
            dataNegara: null,
            map: null,
            forexChartInstance: null,

            // Raw Data dari backend
            dataPetaServer: @json($dataPeta),
            ruteServer: @json($ruteEkspedisi),

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

                // Gambar Rute Maritim
                this.ruteServer.forEach(rute => {
                    L.polyline([rute.asal, rute.tujuan], {
                        color: '#3b82f6', weight: 1.5, opacity: 0.3, dashArray: '5, 10'
                    }).addTo(this.map);
                });

                // Gambar Node / Marker
                this.dataPetaServer.forEach(point => {
                    if (point.lintang && point.bujur) {
                        let marker = L.circleMarker([point.lintang, point.bujur], {
                            radius: 8,
                            fillColor: point.warna,
                            color: '#0f172a',
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 0.9
                        }).addTo(this.map);

                        // Tooltip Hover Sederhana
                        marker.bindTooltip(`<strong>${point.nama}</strong><br>Skor: ${point.skor_total}`, {
                            className: 'dark-tooltip', direction: 'top'
                        });

                        // Event Klik Marker -> Fetch Data
                        marker.on('click', () => {
                            this.selectedIso = point.kode_iso;
                            this.fetchDataNegara(point.kode_iso);
                            // Animasi FlyTo
                            this.map.flyTo([point.lintang, point.bujur], 4, { duration: 1.5 });
                        });
                    }
                });
            },

            async fetchDataNegara(kodeIso) {
                if (!kodeIso) {
                    this.dataNegara = null;
                    return;
                }

                this.isLoading = true;
                
                try {
                    const response = await fetch(`/api/negara/${kodeIso}`);
                    if (!response.ok) throw new Error('Network response was not ok');
                    const data = await response.json();
                    
                    this.dataNegara = data;
                    
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
