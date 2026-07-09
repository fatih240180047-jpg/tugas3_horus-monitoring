@extends('layouts.aplikasi')

@section('judul', 'Dasbor Pemantauan Risiko')

@section('gaya_tambahan')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #peta-sig {
        height: 480px;
        width: 100%;
        border-radius: 12px;
        border: 1px solid var(--warna-charcoal-border);
        margin-bottom: 32px;
        z-index: 10;
    }
    
    .tabel-negara {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
    }

    .tabel-negara th, .tabel-negara td {
        padding: 14px 16px;
        text-align: left;
        border-bottom: 1px solid var(--warna-charcoal-border);
    }

    .tabel-negara th {
        color: var(--warna-teks-abu);
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .tabel-negara tr:hover {
        background-color: rgba(31, 41, 55, 0.4);
    }

    .berita-item {
        padding: 16px;
        border-bottom: 1px solid var(--warna-charcoal-border);
        transition: background-color 0.3s ease;
    }

    .berita-item:last-child {
        border-bottom: none;
    }

    .berita-item:hover {
        background-color: rgba(255, 255, 255, 0.02);
    }

    .berita-meta {
        display: flex;
        gap: 12px;
        font-size: 11px;
        color: var(--warna-teks-abu);
        margin-bottom: 6px;
    }

    .berita-judul {
        font-size: 14px;
        font-weight: 600;
        color: var(--warna-teks-putih);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .berita-judul:hover {
        color: var(--warna-merah-terang);
    }

    .layout-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 32px;
    }

    @media (max-width: 1024px) {
        .layout-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('konten')

<!-- Kartu Ringkasan Metrik -->
<div class="grid-kartu">
    <div class="kartu-metrik" style="border-left: 4px solid var(--warna-merah-terang);">
        <div class="metrik-detail">
            <h3>Risiko Kritis</h3>
            <span class="nilai" style="color: #f87171;">{{ $statistik['kritis'] }}</span>
        </div>
        <span class="metrik-icon" style="color: #f87171;"><i class="fa-solid fa-triangle-exclamation"></i></span>
    </div>
    
    <div class="kartu-metrik" style="border-left: 4px solid var(--warna-oranye);">
        <div class="metrik-detail">
            <h3>Risiko Tinggi</h3>
            <span class="nilai" style="color: #fdba74;">{{ $statistik['tinggi'] }}</span>
        </div>
        <span class="metrik-icon" style="color: #fdba74;"><i class="fa-solid fa-circle-exclamation"></i></span>
    </div>

    <div class="kartu-metrik" style="border-left: 4px solid var(--warna-kuning);">
        <div class="metrik-detail">
            <h3>Risiko Sedang</h3>
            <span class="nilai" style="color: #fef08a;">{{ $statistik['sedang'] }}</span>
        </div>
        <span class="metrik-icon" style="color: #fef08a;"><i class="fa-solid fa-circle-minus"></i></span>
    </div>

    <div class="kartu-metrik" style="border-left: 4px solid var(--warna-hijau);">
        <div class="metrik-detail">
            <h3>Risiko Rendah</h3>
            <span class="nilai" style="color: #86efac;">{{ $statistik['rendah'] }}</span>
        </div>
        <span class="metrik-icon" style="color: #86efac;"><i class="fa-solid fa-circle-check"></i></span>
    </div>
</div>

<div class="layout-grid">
    <!-- Kolom Kiri: Peta SIG & Tabel -->
    <div>
        <div class="card-panel" style="padding: 12px;">
            <div id="peta-sig"></div>
        </div>

        <div class="card-panel">
            <h2 class="card-panel-title">
                <i class="fa-solid fa-earth-americas"></i> Radar Negara Terpantau
            </h2>
            <div style="overflow-x: auto;">
                <table class="tabel-negara">
                    <thead>
                        <tr>
                            <th>Negara</th>
                            <th>Mata Uang</th>
                            <th>Skor Risiko</th>
                            <th>Level</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($risikoTerkini as $item)
                            <tr>
                                <td style="font-weight: 600;">
                                    <span style="font-size: 16px; margin-right: 8px;">{{ $item->negara->bendera ?? '🌐' }}</span>
                                    {{ $item->negara->nama }} ({{ $item->negara->kode_iso }})
                                </td>
                                <td>{{ \App\Services\Implementasi\LayananNilaiTukar::dapatkanMataUangNegara($item->negara->kode_iso) }}</td>
                                <td style="font-family: 'Outfit', sans-serif; font-weight: 700;">{{ $item->skor_total }} / 100</td>
                                <td>
                                    <span class="badge badge-{{ strtolower($item->level_risiko) }}">
                                        {{ $item->level_risiko }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('negara.tampilkan', $item->negara->kode_iso) }}" class="btn btn-sekunder" style="padding: 6px 12px; font-size: 12px;">
                                        <i class="fa-solid fa-magnifying-glass-chart"></i> Analisis
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--warna-teks-abu);">Belum ada data risiko terkalkulasi. Silakan lakukan sinkronisasi data negara.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Feed Berita Global & Rekomendasi Terkini -->
    <div>
        <div class="card-panel" style="padding-bottom: 8px;">
            <h2 class="card-panel-title">
                <i class="fa-solid fa-newspaper"></i> Intelijen Berita Terbaru
            </h2>
            <div style="display: flex; flex-direction: column;">
                @forelse($beritaTerbaru as $berita)
                    <div class="berita-item">
                        <div class="berita-meta">
                            <span><i class="fa-solid fa-globe"></i> {{ $berita->negara->nama }}</span>
                            <span>•</span>
                            <span>{{ date('d M H:i', strtotime($berita->diterbitkan_pada)) }}</span>
                        </div>
                        <a href="#" class="berita-judul">{{ $berita->judul }}</a>
                        <div style="margin-top: 6px; display: flex; gap: 8px; align-items: center;">
                            <span class="badge badge-{{ $berita->keparahan === 'kritis' || $berita->keparahan === 'tinggi' ? 'kritis' : ($berita->keparahan === 'sedang' ? 'sedang' : 'rendah') }}" style="font-size: 9px; padding: 2px 6px;">
                                {{ $berita->keparahan }}
                            </span>
                            <span style="font-size: 11px; color: {{ $berita->sentimen === 'negatif' ? '#f87171' : ($berita->sentimen === 'positif' ? '#4ade80' : 'var(--warna-teks-abu)') }}; font-weight: 500;">
                                <i class="fa-solid {{ $berita->sentimen === 'negatif' ? 'fa-thumbs-down' : ($berita->sentimen === 'positif' ? 'fa-thumbs-up' : 'fa-hand') }}"></i> {{ ucfirst($berita->sentimen) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p style="padding: 16px; color: var(--warna-teks-abu); text-align: center;">Belum ada artikel berita disinkronkan.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@section('skrip_tambahan')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Centered around coordinate (10.0, 40.0) global view
        var map = L.map('peta-sig').setView([15.0, 45.0], 2);

        // Tile layer using OpenStreetMap config - Dark Theme
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 18,
            minZoom: 2,
            attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
        }).addTo(map);

        var dataPeta = @json($dataPeta);
        var ruteEkspedisi = @json($ruteEkspedisi);

        // 1. Gambar Marker & Popup SCM Terpadu
        dataPeta.forEach(function (point) {
            if (point.lintang && point.bujur) {
                var marker = L.circleMarker([point.lintang, point.bujur], {
                    radius: 10,
                    fillColor: point.warna,
                    color: '#ffffff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.8
                }).addTo(map);

                marker.bindPopup(`
                    <div style="color: #111827; font-family: 'Inter', sans-serif; min-width: 220px;">
                        <h4 style="margin: 0 0 6px 0; font-family: 'Outfit'; font-size: 16px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px;">
                            \${point.nama} (\${point.kode_iso})
                        </h4>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin: 8px 0; font-size: 12px;">
                            <div style="background: #f3f4f6; padding: 6px; border-radius: 6px;">
                                <span style="display:block; color: #6b7280; font-size: 10px;">Skor Risiko</span>
                                <strong style="color: \${point.warna};">\${point.skor_total}</strong>
                            </div>
                            <div style="background: #f3f4f6; padding: 6px; border-radius: 6px;">
                                <span style="display:block; color: #6b7280; font-size: 10px;">Status</span>
                                <strong style="color: \${point.warna};">\${point.level_risiko}</strong>
                            </div>
                        </div>

                        <ul style="list-style: none; padding: 0; margin: 8px 0; font-size: 11px;">
                            <li style="margin-bottom: 4px;">🌡️ Cuaca: <strong>\${point.scm.cuaca}</strong></li>
                            <li style="margin-bottom: 4px;">📈 Inflasi: <strong>\${point.scm.inflasi}</strong></li>
                            <li style="margin-bottom: 4px;">📰 Berita: <strong>\${point.scm.berita}</strong></li>
                        </ul>

                        <div style="margin-top: 12px; text-align: center;">
                            <a href="/negara/\${point.kode_iso}" style="display: inline-block; background-color: #991b1b; color: #ffffff; padding: 6px 12px; border-radius: 4px; font-weight: 600; text-decoration: none; font-size: 11px; width: 100%;">Analisis Detil &rarr;</a>
                        </div>
                    </div>
                `);
            }
        });

        // 2. Gambar Rute Ekspedisi (Polylines)
        ruteEkspedisi.forEach(function (rute) {
            var latlngs = [
                rute.asal,
                rute.tujuan
            ];
            
            var polyline = L.polyline(latlngs, {
                color: '#3b82f6', // Blue color for sea routes
                weight: 2,
                opacity: 0.4,
                dashArray: '10, 10', // Dashed line to simulate route path
                lineJoin: 'round'
            }).addTo(map);

            // Menambahkan panah sederhana atau tooltip
            polyline.bindTooltip(`Rute SCM: \${rute.kode_asal} &rarr; \${rute.kode_tujuan}`, {
                sticky: true,
                className: 'rute-tooltip'
            });
        });
    });
</script>
@endsection
