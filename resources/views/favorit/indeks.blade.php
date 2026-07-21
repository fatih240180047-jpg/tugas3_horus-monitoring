@extends('layouts.aplikasi')

@section('judul', 'Pemantauan Favorit')

@section('gaya_tambahan')
<style>
    .favorit-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 24px;
        margin-top: 20px;
    }

    .kartu-favorit {
        background: var(--warna-kaca);
        backdrop-filter: var(--blur-kaca);
        border: 1px solid rgba(55, 65, 81, 0.4);
        border-radius: 16px;
        padding: 24px;
        position: relative;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .kartu-favorit::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: transparent;
        transition: background 0.3s ease;
    }

    .kartu-favorit.risiko-kritis::before { background: var(--warna-merah-terang); }
    .kartu-favorit.risiko-tinggi::before { background: var(--warna-oranye); }
    .kartu-favorit.risiko-sedang::before { background: var(--warna-kuning); }
    .kartu-favorit.risiko-rendah::before { background: var(--warna-hijau); }

    .kartu-favorit:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
        border-color: rgba(239, 68, 68, 0.25);
    }

    .header-kartu {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .identitas-negara {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .bendera-besar {
        font-size: 32px;
    }

    .nama-negara {
        font-family: 'Outfit', sans-serif;
        font-size: 20px;
        font-weight: 700;
        color: #fff;
    }

    .meta-negara {
        font-size: 12px;
        color: var(--warna-teks-abu);
        margin-top: 2px;
    }

    .star-btn {
        background: none;
        border: none;
        color: #fbbf24;
        font-size: 20px;
        cursor: pointer;
        transition: transform 0.2s ease, color 0.2s ease;
        padding: 4px;
    }

    .star-btn:hover {
        transform: scale(1.2);
    }

    .star-btn.unstarred {
        color: var(--warna-teks-abu);
    }

    .section-detail {
        border-top: 1px solid rgba(55, 65, 81, 0.3);
        padding-top: 14px;
        margin-top: 14px;
    }

    .section-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--warna-teks-abu);
        letter-spacing: 0.8px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
        font-size: 13px;
    }

    .info-label {
        color: var(--warna-teks-abu);
    }

    .info-value {
        font-weight: 600;
        color: #e5e7eb;
    }

    .berita-favorit {
        background: rgba(13, 17, 23, 0.4);
        border: 1px solid rgba(55, 65, 81, 0.3);
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 12.5px;
    }

    .berita-judul {
        font-weight: 500;
        color: #f3f4f6;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 4px;
    }

    .berita-meta {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        color: var(--warna-teks-abu);
    }

    .empty-state {
        text-align: center;
        padding: 80px 24px;
        background: var(--warna-kaca);
        border: 1px dashed rgba(55, 65, 81, 0.5);
        border-radius: 16px;
        max-width: 600px;
        margin: 40px auto;
    }

    .empty-icon {
        font-size: 48px;
        color: var(--warna-teks-abu);
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-title {
        font-family: 'Outfit', sans-serif;
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .empty-desc {
        color: var(--warna-teks-abu);
        font-size: 14px;
        line-height: 1.5;
        margin-bottom: 24px;
    }
</style>
@endsection

@section('konten')
<div class="container-favorit">
    @if($favoritList->isEmpty())
        <div class="empty-state">
            <i class="fa-regular fa-star empty-icon"></i>
            <div class="empty-title">Belum Ada Negara Favorit</div>
            <div class="empty-desc">Tandai negara-negara penting dalam rantai pasok global Anda untuk memantau indikator cuaca, nilai tukar, ekonomi, dan berita secara terfokus di satu halaman.</div>
            <a href="{{ route('dasbor.indeks') }}" class="btn btn-primer">
                <i class="fa-solid fa-earth-americas"></i> Buka Control Center
            </a>
        </div>
    @else
        <div class="favorit-grid">
            @foreach($favoritList as $negara)
                @php
                    $risiko = $negara->penilaianRisiko->first();
                    $cuaca = $negara->catatanCuaca->first();
                    $ekonomi = $negara->indikatorEkonomi->first();
                    $kurs = $negara->nilaiTukar->first();
                    $berita = $negara->artikelBerita->first();

                    $levelRisiko = $risiko ? $risiko->level_risiko : 'N/A';
                    $classRisiko = strtolower($levelRisiko) !== 'n/a' ? 'risiko-' . strtolower($levelRisiko) : '';
                @endphp
                <div class="kartu-favorit {{ $classRisiko }}" id="kartu-{{ $negara->id }}">
                    <div>
                        <!-- Header Kartu -->
                        <div class="header-kartu">
                            <div class="identitas-negara">
                                <img src="{{ $negara->bendera_url }}" style="height: 24px; width: 36px; object-fit: cover; border-radius: 4px; border: 1px solid rgba(255,255,255,0.15);" alt="{{ $negara->nama }} Flag">
                                <div>
                                    <a href="{{ route('negara.tampilkan', $negara->kode_iso) }}" class="nama-negara hover:underline">{{ $negara->nama }}</a>
                                    <div class="meta-negara">{{ $negara->kode_iso }} • Ibu Kota: {{ $negara->ibu_kota ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <button class="star-btn" onclick="toggleFavorit({{ $negara->id }}, this)" title="Hapus dari Favorit">
                                <i class="fa-solid fa-star"></i>
                            </button>
                        </div>

                        <!-- Risiko Status -->
                        <div class="info-row" style="margin-bottom: 12px;">
                            <span class="info-label">Status Risiko SCM</span>
                            <span class="badge badge-{{ strtolower($levelRisiko) }}">{{ $levelRisiko }}</span>
                        </div>

                        <!-- Cuaca Section -->
                        <div class="section-detail">
                            <div class="section-title">
                                <i class="fa-solid fa-cloud-sun"></i> Indikator Cuaca
                            </div>
                            @if($cuaca)
                                <div class="info-row">
                                    <span class="info-label">Temperatur / Kondisi</span>
                                    <span class="info-value">{{ $cuaca->suhu }}°C / {{ ucfirst($cuaca->kondisi_cuaca) }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Curah Hujan</span>
                                    <span class="info-value">{{ $cuaca->curah_hujan }} mm</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Kecepatan Angin</span>
                                    <span class="info-value">{{ $cuaca->kecepatan_angin }} km/h</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Risiko Badai</span>
                                    <span class="info-value text-{{ $cuaca->risiko_badai ? 'red-400' : 'gray-300' }}">
                                        {{ $cuaca->risiko_badai ? '⚠️ Tinggi' : 'Rendah' }}
                                    </span>
                                </div>
                            @else
                                <div class="text-center text-xs py-2 text-gray-500">Data cuaca tidak tersedia</div>
                            @endif
                        </div>

                        <!-- Ekonomi & Nilai Tukar -->
                        <div class="section-detail">
                            <div class="section-title">
                                <i class="fa-solid fa-money-bill-trend-up"></i> Finansial & Makro
                            </div>
                            <div class="info-row">
                                <span class="info-label">Mata Uang</span>
                                <span class="info-value">{{ $negara->mata_uang ?? 'USD' }}</span>
                            </div>
                            @if($kurs)
                                <div class="info-row">
                                    <span class="info-label">Kurs vs USD</span>
                                    <span class="info-value">1 USD = {{ number_format($kurs->nilai_tukar, 2) }} {{ $kurs->kode_mata_uang }}</span>
                                </div>
                            @endif
                            @if($ekonomi)
                                <div class="info-row">
                                    <span class="info-label">Inflasi Tahunan</span>
                                    <span class="info-value">{{ $ekonomi->tingkat_inflasi }}%</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">PDB (Nominal)</span>
                                    <span class="info-value">USD {{ number_format($ekonomi->pdb / 1e9, 2) }} B</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Berita Terbaru -->
                    <div class="section-detail" style="border-top: 1px solid rgba(55, 65, 81, 0.35); padding-top: 14px;">
                        <div class="section-title">
                            <i class="fa-regular fa-newspaper"></i> Intelijen Berita
                        </div>
                        @if($berita)
                            <div class="berita-favorit">
                                <a href="{{ $berita->url_sumber }}" target="_blank" class="berita-judul hover:underline hover:text-red-400">{{ $berita->judul }}</a>
                                <div class="berita-meta">
                                    <span class="badge badge-{{ strtolower($berita->keparahan) }}">{{ $berita->keparahan }}</span>
                                    <span>{{ \Carbon\Carbon::parse($berita->diterbitkan_pada)->diffForHumans() }}</span>
                                </div>
                            </div>
                        @else
                            <div class="text-center text-xs py-2 text-gray-500">Tidak ada berita terbaru</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@section('skrip_tambahan')
<script>
    function toggleFavorit(negaraId, btnElement) {
        if (!confirm('Apakah Anda yakin ingin menghapus negara ini dari favorit?')) {
            return;
        }

        fetch(`/favorit/${negaraId}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Hapus kartu dari grid dengan animasi
                const kartu = document.getElementById(`kartu-${negaraId}`);
                if (kartu) {
                    kartu.style.opacity = '0';
                    kartu.style.transform = 'scale(0.9) translateY(10px)';
                    setTimeout(() => {
                        kartu.remove();
                        // Jika sudah habis, reload page untuk nampilkan empty state
                        if (document.querySelectorAll('.kartu-favorit').length === 0) {
                            window.location.reload();
                        }
                    }, 300);
                }
                
                // Update badge jumlah favorit di sidebar
                const badge = document.getElementById('sidebar-favorit-count');
                if (badge) {
                    if (data.total > 0) {
                        badge.innerText = data.total;
                        badge.style.display = 'inline-flex';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            }
        })
        .catch(err => {
            console.error('Error toggling favorite country:', err);
        });
    }
</script>
@endsection
