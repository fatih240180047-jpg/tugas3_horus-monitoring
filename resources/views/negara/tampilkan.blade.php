@extends('layouts.aplikasi')

@section('judul')
    <span style="font-size: 32px; margin-right: 8px;">{{ $negara->bendera ?? '🌐' }}</span> Intelijen Negara: {{ $negara->nama }}
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
    
    <!-- Parameter 1: Cuaca -->
    <div class="card-panel">
        <h2 class="card-panel-title">
            <i class="fa-solid fa-cloud-sun" style="color: #60a5fa;"></i> Intelijen Cuaca
        </h2>
        <ul class="info-list">
            <li>
                <span class="info-label">Suhu Rata-rata</span>
                <span class="info-value">{{ $cuacaTerkini ? $cuacaTerkini->suhu . ' °C' : 'N/A' }}</span>
            </li>
            <li>
                <span class="info-label">Kelembaban</span>
                <span class="info-value">{{ $cuacaTerkini ? $cuacaTerkini->kelembaban . ' %' : 'N/A' }}</span>
            </li>
            <li>
                <span class="info-label">Curah Hujan</span>
                <span class="info-value">{{ $cuacaTerkini ? $cuacaTerkini->curah_hujan . ' mm' : 'N/A' }}</span>
            </li>
            <li>
                <span class="info-label">Kecepatan Angin</span>
                <span class="info-value">{{ $cuacaTerkini ? $cuacaTerkini->kecepatan_angin . ' km/jam' : 'N/A' }}</span>
            </li>
            <li>
                <span class="info-label">Kondisi Cuaca</span>
                <span class="info-value">{{ $cuacaTerkini ? ucfirst($cuacaTerkini->kondisi_cuaca) : 'N/A' }}</span>
            </li>
        </ul>
    </div>

    <!-- Parameter 2: Makroekonomi -->
    <div class="card-panel">
        <h2 class="card-panel-title">
            <i class="fa-solid fa-chart-line" style="color: #34d399;"></i> Indikator Ekonomi
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
                <span class="info-value">{{ $ekonomiTerkini ? $ekonomiTerkini->tingkat_inflasi . ' %' : 'N/A' }}</span>
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

    <!-- Parameter 3: Keuangan / Forex -->
    <div class="card-panel">
        <h2 class="card-panel-title">
            <i class="fa-solid fa-coins" style="color: #fbbf24;"></i> Nilai Tukar Valuta Asing
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
    </div>

</div>

<!-- Feed Berita Relevan Negara -->
<div class="card-panel">
    <h2 class="card-panel-title">
        <i class="fa-solid fa-newspaper"></i> Umpan Berita & Sentimen Lokal
    </h2>
    <div style="overflow-x: auto;">
        <table class="tabel-data">
            <thead>
                <tr>
                    <th>Waktu Rilis</th>
                    <th>Judul Berita</th>
                    <th>Sentimen</th>
                    <th>Tingkat Keparahan</th>
                    <th>Sumber</th>
                </tr>
            </thead>
            <tbody>
                @forelse($beritaList as $art)
                    <tr>
                        <td style="white-space: nowrap;">{{ date('d M Y H:i', strtotime($art->diterbitkan_pada)) }}</td>
                        <td style="font-weight: 500;">{{ $art->judul }}</td>
                        <td style="color: {{ $art->sentimen === 'negatif' ? '#f87171' : ($art->sentimen === 'positif' ? '#4ade80' : 'var(--warna-teks-abu)') }}; font-weight: 600;">
                            {{ ucfirst($art->sentimen) }}
                        </td>
                        <td>
                            <span class="badge badge-{{ $art->keparahan === 'kritis' || $art->keparahan === 'tinggi' ? 'kritis' : ($art->keparahan === 'sedang' ? 'sedang' : 'rendah') }}">
                                {{ $art->keparahan }}
                            </span>
                        </td>
                        <td>{{ $art->sumber ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--warna-teks-abu);">Belum ada berita yang disinkronkan untuk negara ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
