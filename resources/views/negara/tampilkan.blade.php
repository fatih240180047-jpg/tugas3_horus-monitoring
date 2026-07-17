@extends('layouts.aplikasi')

@section('judul')
    Intelijen Negara: {{ $negara->nama }}
@endsection

@section('gaya_tambahan')
<style>
    .grid-intelijen {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .info-list {
        list-style: none;
    }

    .info-list li {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid var(--warna-charcoal-border);
        font-size: 14px;
    }

    .info-list li:last-child {
        border-bottom: none;
    }

    .info-label {
        color: var(--warna-teks-abu);
        font-weight: 500;
    }

    .info-value {
        font-weight: 600;
        color: var(--warna-teks-putih);
    }

    .panel-risiko-utama {
        display: flex;
        align-items: center;
        gap: 32px;
        background-color: var(--warna-kaca);
        border: 1px solid var(--warna-charcoal-border);
        padding: 32px;
        border-radius: 12px;
        margin-bottom: 32px;
    }

    .lingkaran-skor {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        border: 8px solid var(--warna-charcoal-border);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        font-family: 'Outfit', sans-serif;
    }

    .lingkaran-skor .skor {
        font-size: 40px;
        font-weight: 800;
    }

    .lingkaran-skor .skor-label {
        font-size: 11px;
        color: var(--warna-teks-abu);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .risiko-deskripsi {
        flex-grow: 1;
    }

    .risiko-deskripsi h2 {
        font-family: 'Outfit', sans-serif;
        font-size: 24px;
        margin-bottom: 8px;
    }

    .risiko-deskripsi p {
        color: var(--warna-teks-abu);
        font-size: 14px;
        line-height: 1.6;
    }

    .tabel-data {
        width: 100%;
        border-collapse: collapse;
    }

    .tabel-data th, .tabel-data td {
        padding: 12px 16px;
        text-align: left;
        border-bottom: 1px solid var(--warna-charcoal-border);
        font-size: 14px;
    }

    .tabel-data th {
        color: var(--warna-teks-abu);
        font-weight: 600;
    }

    .tabel-data tr:hover {
        background-color: rgba(255, 255, 255, 0.01);
    }
</style>
@endsection

@section('konten')

<!-- Panel Skor Risiko Utama -->
<div class="panel-risiko-utama" style="border-left: 8px solid 
    @if($risikoTerkini)
        @if($risikoTerkini->level_risiko === 'Kritis') #dc2626
        @elseif($risikoTerkini->level_risiko === 'Tinggi') #f97316
        @elseif($risikoTerkini->level_risiko === 'Sedang') #eab308
        @else #16a34a
        @endif
    @else var(--warna-charcoal-border)
    @endif
;">
    <div class="lingkaran-skor" style="border-color: 
        @if($risikoTerkini)
            @if($risikoTerkini->level_risiko === 'Kritis') #dc2626
            @elseif($risikoTerkini->level_risiko === 'Tinggi') #f97316
            @elseif($risikoTerkini->level_risiko === 'Sedang') #eab308
            @else #16a34a
            @endif
        @else var(--warna-charcoal-border)
        @endif
    ;">
        <span class="skor">{{ $risikoTerkini?->skor_total ?? 'N/A' }}</span>
        <span class="skor-label">Skor Risiko</span>
    </div>
    <div class="risiko-deskripsi">
        <h2>Tingkat Risiko: {{ $risikoTerkini?->level_risiko ?? 'Belum Dinilai' }}</h2>
        <div style="margin-bottom: 12px;">
            @if($risikoTerkini)
                @foreach($risikoTerkini->penjelasan as $bukti)
                    <p style="margin-bottom: 4px; font-size: 13px;"><i class="fa-solid fa-circle-info" style="color: var(--warna-merah-terang); margin-right: 6px;"></i> {{ $bukti }}</p>
                @endforeach
            @else
                <p>Silakan klik tombol "Sinkronkan & Kalkulasi" untuk memuat data intelijen dan menghitung indeks risiko negara ini.</p>
            @endif
        </div>
        <p style="font-size: 12px;">Pembaruan Terakhir: {{ $risikoTerkini ? date('d M Y H:i', strtotime($risikoTerkini->dihitung_pada)) : 'Belum Pernah' }}</p>
    </div>
    <div>
        @if(Auth::user()->adalahSuperAdmin() || Auth::user()->mempunyaiPeran('admin') || Auth::user()->mempunyaiPeran('analis'))
            <form action="{{ route('negara.sinkronkan', $negara->kode_iso) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primer">
                    <i class="fa-solid fa-arrows-rotate"></i> Sinkronkan & Kalkulasi
                </button>
            </form>
        @endif
    </div>
</div>

<!-- Grid Analisis Parameter Intelijen -->
<div class="grid-intelijen">
    
    <!-- Parameter 1: Cuaca 7 Hari + Insight SCM -->
    <div class="card-panel">
        <h2 class="card-panel-title">
            <i class="fa-solid fa-cloud-sun" style="color: #60a5fa;"></i> Intelijen Cuaca & Prakiraan 7 Hari
            <span style="font-size: 11px; color: var(--warna-teks-abu); margin-left: 8px; font-weight: 400;">via Open-Meteo</span>
        </h2>

        {{-- Cuaca Hari Ini --}}
        @if($cuacaTerkini)
        <ul class="info-list" style="margin-bottom: 16px;">
            <li>
                <span class="info-label">Suhu Saat Ini</span>
                <span class="info-value">{{ $cuacaTerkini->suhu ?? 'N/A' }} °C
                    @if($cuacaTerkini->suhu_min && $cuacaTerkini->suhu_max)
                    <span style="font-size: 11px; color: var(--warna-teks-abu);">({{ $cuacaTerkini->suhu_min }}° – {{ $cuacaTerkini->suhu_max }}°)</span>
                    @endif
                </span>
            </li>
            <li>
                <span class="info-label">Kondisi Cuaca</span>
                <span class="info-value">{{ ucfirst($cuacaTerkini->kondisi_cuaca ?? 'N/A') }}</span>
            </li>
            <li>
                <span class="info-label">Curah Hujan</span>
                <span class="info-value">{{ $cuacaTerkini->curah_hujan ?? 'N/A' }} mm</span>
            </li>
            <li>
                <span class="info-label">Kecepatan Angin</span>
                <span class="info-value">{{ $cuacaTerkini->kecepatan_angin ?? 'N/A' }} km/jam</span>
            </li>
        </ul>

        {{-- Prakiraan 7 Hari - Mini Cards --}}
        @if($prakiraan7Hari->count() > 1)
        <div style="font-size: 11px; color: var(--warna-teks-abu); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Prakiraan 7 Hari ke Depan</div>
        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; margin-bottom: 16px;">
            @foreach($prakiraan7Hari->take(7) as $hari)
            <div style="background: rgba(255,255,255,0.04); border: 1px solid var(--warna-charcoal-border); border-radius: 8px; padding: 8px 4px; text-align: center;">
                <div style="font-size: 10px; color: var(--warna-teks-abu);">{{ date('D', strtotime($hari->tanggal_observasi)) }}</div>
                <div style="font-size: 11px; font-weight: 700; color: var(--warna-teks-putih); margin: 4px 0;">{{ $hari->suhu_max ?? $hari->suhu ?? '--' }}°</div>
                <div style="font-size: 9px; color: #60a5fa;">{{ $hari->suhu_min ?? '--' }}°</div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Insight SCM Cuaca --}}
        @if($cuacaTerkini->insight_scm)
        <div style="background: rgba(96, 165, 250, 0.08); border: 1px solid rgba(96, 165, 250, 0.3); border-radius: 8px; padding: 12px;">
            <div style="font-size: 11px; color: #60a5fa; font-weight: 600; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">
                <i class="fa-solid fa-lightbulb"></i> Analisis Dampak SCM
            </div>
            <div style="font-size: 12px; color: var(--warna-teks-abu); line-height: 1.6; white-space: pre-line;">{{ $cuacaTerkini->insight_scm }}</div>
        </div>
        @endif
        @else
        <p style="color: var(--warna-teks-abu); font-size: 13px; text-align: center; padding: 24px;">Belum ada data cuaca. Klik "Sinkronkan & Kalkulasi" untuk mengambil data cuaca real-time.</p>
        @endif
    </div>

    <!-- Parameter 2: Makroekonomi -->
    <div class="card-panel">
        <h2 class="card-panel-title">
            <i class="fa-solid fa-chart-line" style="color: #34d399;"></i> Indikator Ekonomi
            <span style="font-size: 11px; color: var(--warna-teks-abu); margin-left: 8px; font-weight: 400;">via World Bank</span>
        </h2>
        <ul class="info-list">
            <li>
                <span class="info-label">Produk Domestik Bruto (PDB)</span>
                <span class="info-value">
                    {{ $ekonomiTerkini && $ekonomiTerkini->pdb ? '$' . number_format($ekonomiTerkini->pdb / 1_000_000_000, 2) . ' Milyar' : 'N/A' }}
                </span>
            </li>
            <li>
                <span class="info-label">Tingkat Inflasi</span>
                <span class="info-value" style="color: {{ ($ekonomiTerkini?->tingkat_inflasi ?? 0) > 5 ? '#f87171' : 'var(--warna-teks-putih)' }};">
                    {{ $ekonomiTerkini ? $ekonomiTerkini->tingkat_inflasi . ' %' : 'N/A' }}
                </span>
            </li>
            <li>
                <span class="info-label">Tingkat Pengangguran</span>
                <span class="info-value">{{ $ekonomiTerkini ? $ekonomiTerkini->tingkat_pengangguran . ' %' : 'N/A' }}</span>
            </li>
            <li>
                <span class="info-label">Tingkat Bunga Riil</span>
                <span class="info-value">{{ $ekonomiTerkini ? $ekonomiTerkini->tingkat_bunga . ' %' : 'N/A' }}</span>
            </li>
            <li>
                <span class="info-label">Neraca Perdagangan</span>
                <span class="info-value">
                    {{ $ekonomiTerkini && $ekonomiTerkini->neraca_perdagangan ? '$' . number_format($ekonomiTerkini->neraca_perdagangan / 1_000_000, 2) . ' Juta' : 'N/A' }}
                </span>
            </li>
        </ul>
    </div>

    <!-- Parameter 3: Keuangan / Forex + Insight SCM -->
    <div class="card-panel">
        <h2 class="card-panel-title">
            <i class="fa-solid fa-coins" style="color: #fbbf24;"></i> Nilai Tukar Valuta Asing
            <span style="font-size: 11px; color: var(--warna-teks-abu); margin-left: 8px; font-weight: 400;">via open.er-api.com</span>
        </h2>
        <ul class="info-list">
            @forelse($nilaiTukarList as $forex)
                <li>
                    <span class="info-label">{{ $forex->kode_mata_uang }} / USD</span>
                    <span class="info-value" style="font-family: 'Outfit';">{{ number_format($forex->nilai_tukar, 4) }}</span>
                </li>
            @empty
                <li style="text-align: center; color: var(--warna-teks-abu);">Belum ada riwayat forex.</li>
            @endforelse
        </ul>

        {{-- Insight SCM Nilai Tukar --}}
        @if($nilaiTukarTerkini?->insight_scm)
        <div style="background: rgba(251, 191, 36, 0.08); border: 1px solid rgba(251, 191, 36, 0.3); border-radius: 8px; padding: 12px; margin-top: 12px;">
            <div style="font-size: 11px; color: #fbbf24; font-weight: 600; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">
                <i class="fa-solid fa-lightbulb"></i> Analisis Fluktuasi SCM
            </div>
            <div style="font-size: 12px; color: var(--warna-teks-abu); line-height: 1.6;">{{ $nilaiTukarTerkini->insight_scm }}</div>
        </div>
        @endif
    </div>

</div>

<!-- Feed Berita Relevan Negara + Dampak SCM -->
<div class="card-panel">
    <h2 class="card-panel-title">
        <i class="fa-solid fa-newspaper"></i> Umpan Berita & Sentimen Geopolitik
        <span style="font-size: 11px; color: var(--warna-teks-abu); margin-left: 8px; font-weight: 400;">via GNews API / NewsAPI</span>
    </h2>
    @forelse($beritaList as $art)
    <div style="border-bottom: 1px solid var(--warna-charcoal-border); padding: 20px 0;">
        {{-- Header berita --}}
        <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 10px;">
            <div style="flex-grow: 1;">
                <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 6px; flex-wrap: wrap;">
                    <span class="badge badge-{{ $art->keparahan === 'kritis' || $art->keparahan === 'tinggi' ? 'kritis' : ($art->keparahan === 'sedang' ? 'sedang' : 'rendah') }}">
                        {{ $art->keparahan }}
                    </span>
                    <span style="font-size: 11px; color: {{ $art->sentimen === 'negatif' ? '#f87171' : ($art->sentimen === 'positif' ? '#4ade80' : 'var(--warna-teks-abu)') }}; font-weight: 600;">
                        <i class="fa-solid {{ $art->sentimen === 'negatif' ? 'fa-thumbs-down' : ($art->sentimen === 'positif' ? 'fa-thumbs-up' : 'fa-hand') }}"></i>
                        {{ ucfirst($art->sentimen) }}
                    </span>
                    <span style="font-size: 11px; color: var(--warna-teks-abu);">{{ $art->sumber ?? 'Unknown Source' }}</span>
                    <span style="font-size: 11px; color: var(--warna-teks-abu);">{{ date('d M Y H:i', strtotime($art->diterbitkan_pada)) }}</span>
                </div>

                @if($art->url_asli)
                <a href="{{ $art->url_asli }}" target="_blank" rel="noopener"
                   style="font-size: 15px; font-weight: 600; color: var(--warna-teks-putih); text-decoration: none; line-height: 1.4; transition: color 0.2s;">
                    {{ $art->judul }}
                    <i class="fa-solid fa-external-link" style="font-size: 11px; margin-left: 4px; opacity: 0.5;"></i>
                </a>
                @else
                <span style="font-size: 15px; font-weight: 600; color: var(--warna-teks-putih);">{{ $art->judul }}</span>
                @endif
            </div>
        </div>

        {{-- Ringkasan --}}
        @if($art->ringkasan)
        <p style="font-size: 13px; color: var(--warna-teks-abu); line-height: 1.6; margin-bottom: 10px;">
            {{ Str::limit($art->ringkasan, 250) }}
        </p>
        @endif

        {{-- Dampak SCM --}}
        @if($art->dampak_scm)
        <div style="background: rgba(239, 68, 68, 0.07); border-left: 3px solid #991b1b; border-radius: 0 6px 6px 0; padding: 10px 14px;">
            <div style="font-size: 10px; color: var(--warna-merah-terang); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                <i class="fa-solid fa-shield-halved"></i> Dampak SCM
            </div>
            <div style="font-size: 12px; color: var(--warna-teks-abu); line-height: 1.5; white-space: pre-line;">{{ $art->dampak_scm }}</div>
        </div>
        @endif
    </div>
    @empty
    <p style="padding: 24px; color: var(--warna-teks-abu); text-align: center;">
        Belum ada berita yang disinkronkan untuk negara ini. Klik "Sinkronkan & Kalkulasi" untuk mengambil berita terkini.
    </p>
    @endforelse
</div>

<!-- Area Bagan / Grafik Visual (Chart.js) -->
<div class="layout-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-top: 32px;">
    <!-- Grafik Tren Nilai Tukar -->
    <div class="card-panel">
        <h2 class="card-panel-title">
            <i class="fa-solid fa-chart-area" style="color: #fbbf24;"></i> Tren Nilai Tukar Historis
        </h2>
        <div style="position: relative; height: 300px; width: 100%;">
            <canvas id="grafikForex"></canvas>
        </div>
    </div>

    <!-- Grafik Historis Skor Risiko -->
    <div class="card-panel">
        <h2 class="card-panel-title">
            <i class="fa-solid fa-chart-line" style="color: #f87171;"></i> Fluktuasi Skor Risiko
        </h2>
        <div style="position: relative; height: 300px; width: 100%;">
            <canvas id="grafikRisiko"></canvas>
        </div>
    </div>
</div>

@endsection

@section('skrip_tambahan')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Konfigurasi Tema Gelap (Dark Mode) untuk Chart.js
        Chart.defaults.color = '#9ca3af';
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.borderColor = 'rgba(55, 65, 81, 0.5)';

        // ----------------------------------------------------
        // 1. Grafik Tren Nilai Tukar (Forex)
        // ----------------------------------------------------
        const forexDataRaw = @json($nilaiTukarList->reverse()->values());
        
        if(forexDataRaw.length > 0) {
            const forexLabels = forexDataRaw.map(item => {
                const date = new Date(item.tanggal_berlaku);
                return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
            });
            const forexValues = forexDataRaw.map(item => item.nilai_tukar);
            const mataUang = forexDataRaw[0].kode_mata_uang;

            const ctxForex = document.getElementById('grafikForex').getContext('2d');
            
            // Gradient fill
            let gradientForex = ctxForex.createLinearGradient(0, 0, 0, 300);
            gradientForex.addColorStop(0, 'rgba(251, 191, 36, 0.5)'); // amber-400
            gradientForex.addColorStop(1, 'rgba(251, 191, 36, 0.0)');

            new Chart(ctxForex, {
                type: 'line',
                data: {
                    labels: forexLabels,
                    datasets: [{
                        label: `Nilai Tukar (\${mataUang} / USD)`,
                        data: forexValues,
                        borderColor: '#fbbf24',
                        backgroundColor: gradientForex,
                        borderWidth: 2,
                        pointBackgroundColor: '#111827',
                        pointBorderColor: '#fbbf24',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4 // curve
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        y: { beginAtZero: false },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // ----------------------------------------------------
        // 2. Grafik Historis Skor Risiko
        // ----------------------------------------------------
        const risikoDataRaw = @json($riwayatRisiko->reverse()->values());
        
        if(risikoDataRaw.length > 0) {
            const risikoLabels = risikoDataRaw.map(item => {
                const date = new Date(item.dihitung_pada);
                return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
            });
            const risikoValues = risikoDataRaw.map(item => item.skor_total);

            const ctxRisiko = document.getElementById('grafikRisiko').getContext('2d');
            
            let gradientRisiko = ctxRisiko.createLinearGradient(0, 0, 0, 300);
            gradientRisiko.addColorStop(0, 'rgba(239, 68, 68, 0.5)'); // red-500
            gradientRisiko.addColorStop(1, 'rgba(239, 68, 68, 0.0)');

            new Chart(ctxRisiko, {
                type: 'line',
                data: {
                    labels: risikoLabels,
                    datasets: [{
                        label: 'Skor Risiko SCM',
                        data: risikoValues,
                        borderColor: '#ef4444',
                        backgroundColor: gradientRisiko,
                        borderWidth: 2,
                        pointBackgroundColor: '#111827',
                        pointBorderColor: '#ef4444',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        y: { min: 0, max: 100 },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    });
</script>
@endsection
