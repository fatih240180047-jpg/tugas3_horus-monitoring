@extends('layouts.aplikasi')
@section('judul', 'Analisis Artikel Berita')

@section('gaya_tambahan')
<style>
    .artikel-grid {
        display: grid;
        gap: 14px;
    }

    .kartu-artikel {
        background: var(--warna-kaca);
        backdrop-filter: var(--blur-kaca);
        border: 1px solid rgba(55, 65, 81, 0.4);
        border-radius: 12px;
        padding: 18px 22px;
        display: flex;
        align-items: center;
        gap: 18px;
        transition: all 0.2s ease;
        text-decoration: none;
        color: inherit;
    }

    .kartu-artikel:hover {
        border-color: rgba(239, 68, 68, 0.25);
        background: rgba(17, 24, 39, 0.8);
        transform: translateX(3px);
    }

    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .dot-belum { background: rgba(107, 114, 128, 0.6); }
    .dot-draft { background: var(--warna-kuning); }
    .dot-menunggu { background: var(--warna-oranye); box-shadow: 0 0 6px var(--warna-oranye); }
    .dot-disetujui { background: var(--warna-hijau); }
    .dot-ditolak { background: var(--warna-merah-terang); }

    .judul-artikel {
        font-weight: 600;
        font-size: 14px;
        color: #f3f4f6;
        margin-bottom: 4px;
        line-height: 1.4;
    }

    .meta-artikel {
        font-size: 12px;
        color: var(--warna-teks-abu);
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .filter-analisis {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .chip-filter {
        padding: 7px 16px;
        border-radius: 20px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        border: 1px solid rgba(55, 65, 81, 0.5);
        color: var(--warna-teks-abu);
        transition: all 0.2s ease;
    }

    .chip-filter:hover, .chip-filter.aktif {
        background: rgba(153, 27, 27, 0.2);
        border-color: var(--warna-merah);
        color: #fff;
    }

    .ikon-analisis {
        min-width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .statbar {
        display: flex;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .stat-item {
        flex: 1;
        min-width: 130px;
        background: var(--warna-kaca);
        border: 1px solid rgba(55, 65, 81, 0.3);
        border-radius: 10px;
        padding: 14px 18px;
        text-align: center;
    }

    .stat-angka {
        font-family: 'Outfit', sans-serif;
        font-size: 26px;
        font-weight: 700;
        display: block;
    }

    .stat-label {
        font-size: 11px;
        color: var(--warna-teks-abu);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>
@endsection

@section('konten')
<!-- Statistik ringkas -->
@php
    $totalArtikel = $artikelList->total();
    $belumDianalisis = \App\Models\ArtikelBerita::doesntHave('analisis')->count();
    $menungguReview = \App\Models\AnalisisArtikel::where('status', 'menunggu_review')->count();
    $sudahDisetujui = \App\Models\AnalisisArtikel::where('status', 'disetujui')->count();
@endphp

<div class="statbar">
    <div class="stat-item">
        <span class="stat-angka" style="color:#e5e7eb;">{{ $totalArtikel }}</span>
        <span class="stat-label">Total Berita</span>
    </div>
    <div class="stat-item">
        <span class="stat-angka" style="color:#9ca3af;">{{ $belumDianalisis }}</span>
        <span class="stat-label">Belum Dianalisis</span>
    </div>
    <div class="stat-item">
        <span class="stat-angka" style="color:var(--warna-oranye);">{{ $menungguReview }}</span>
        <span class="stat-label">Menunggu Review</span>
    </div>
    <div class="stat-item">
        <span class="stat-angka" style="color:var(--warna-hijau);">{{ $sudahDisetujui }}</span>
        <span class="stat-label">Disetujui</span>
    </div>
</div>

<!-- Filter & Pencarian -->
<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:16px;flex-wrap:wrap;">
    <div class="filter-analisis">
        <a href="{{ route('analisis.indeks') }}" class="chip-filter {{ !request('status_analisis') ? 'aktif' : '' }}">
            <i class="fa-solid fa-list"></i> Semua
        </a>
        <a href="{{ route('analisis.indeks', ['status_analisis'=>'belum']) }}" class="chip-filter {{ request('status_analisis')==='belum' ? 'aktif' : '' }}">
            <i class="fa-regular fa-circle"></i> Belum Dianalisis
        </a>
        <a href="{{ route('analisis.indeks', ['status_analisis'=>'menunggu']) }}" class="chip-filter {{ request('status_analisis')==='menunggu' ? 'aktif' : '' }}">
            <i class="fa-solid fa-hourglass-half"></i> Menunggu Review
        </a>
        <a href="{{ route('analisis.indeks', ['status_analisis'=>'disetujui']) }}" class="chip-filter {{ request('status_analisis')==='disetujui' ? 'aktif' : '' }}">
            <i class="fa-solid fa-circle-check"></i> Disetujui
        </a>
    </div>
    <form method="GET" style="display:flex;gap:8px;">
        <input type="hidden" name="status_analisis" value="{{ request('status_analisis') }}">
        <select name="negara_id" class="form-input" style="max-width:200px;padding:9px 12px;font-size:13px;">
            <option value="">-- Semua Negara --</option>
            @foreach($semuaNegara as $id => $nama)
                <option value="{{ $id }}" {{ request('negara_id') == $id ? 'selected' : '' }}>{{ $nama }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-sekunder" style="padding:9px 14px;"><i class="fa-solid fa-filter"></i></button>
    </form>
</div>

<!-- Daftar Artikel -->
<div class="artikel-grid">
    @forelse($artikelList as $artikel)
        @php
            $analisisiUser = $artikel->analisis->where('pengguna_id', auth()->id())->first();
            $statusDot = 'dot-belum';
            $labelStatus = 'Belum Dianalisis';
            if ($analisisiUser) {
                $statusDot = 'dot-' . ($analisisiUser->status === 'menunggu_review' ? 'menunggu' : $analisisiUser->status);
                $labelStatus = $analisisiUser->labelStatus();
            } elseif ($artikel->analisis->count() > 0) {
                $statusTerbaik = $artikel->analisis->sortByDesc(fn($a) => ['disetujui'=>3,'menunggu_review'=>2,'draft'=>1,'ditolak'=>0][$a->status])->first();
                $statusDot = 'dot-' . ($statusTerbaik->status === 'menunggu_review' ? 'menunggu' : $statusTerbaik->status);
                $labelStatus = $statusTerbaik->labelStatus() . ' (oleh ' . $statusTerbaik->pengguna->name . ')';
            }

            $ikonKeparahan = match($artikel->keparahan) {
                'kritis' => ['icon'=>'fa-radiation','bg'=>'rgba(220,38,38,0.15)','color'=>'#f87171'],
                'tinggi' => ['icon'=>'fa-triangle-exclamation','bg'=>'rgba(249,115,22,0.15)','color'=>'#fdba74'],
                'sedang' => ['icon'=>'fa-circle-exclamation','bg'=>'rgba(234,179,8,0.15)','color'=>'#fef08a'],
                default  => ['icon'=>'fa-circle-info','bg'=>'rgba(22,163,74,0.15)','color'=>'#86efac'],
            };
        @endphp
        <a href="{{ route('analisis.detail', $artikel->id) }}" class="kartu-artikel">
            <div class="ikon-analisis" style="background:{{ $ikonKeparahan['bg'] }};color:{{ $ikonKeparahan['color'] }};">
                <i class="fa-solid {{ $ikonKeparahan['icon'] }}"></i>
            </div>

            <div style="flex:1;min-width:0;">
                <div class="judul-artikel">{{ Str::limit($artikel->judul, 95) }}</div>
                <div class="meta-artikel">
                    <span>{{ $artikel->negara?->nama ?? 'Global' }}</span>
                    <span class="badge badge-{{ strtolower($artikel->keparahan) }}">{{ $artikel->keparahan }}</span>
                    <span>{{ \Carbon\Carbon::parse($artikel->diterbitkan_pada)->diffForHumans() }}</span>
                    @if($artikel->analisis->count() > 0)
                        <span style="color:#93c5fd;"><i class="fa-solid fa-comment-dots"></i> {{ $artikel->analisis->count() }} analisis</span>
                    @endif
                </div>
            </div>

            <div style="text-align:right;flex-shrink:0;">
                <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--warna-teks-abu);">
                    <span class="status-dot {{ $statusDot }}"></span>
                    <span>{{ $labelStatus }}</span>
                </div>
                @if($bisaAnalisis)
                <div style="margin-top:8px;">
                    <span class="badge" style="background:rgba(153,27,27,0.15);border:1px solid rgba(153,27,27,0.3);color:#fca5a5;font-size:11px;">
                        {{ $analisisiUser ? 'Edit Analisis' : 'Mulai Analisis' }} →
                    </span>
                </div>
                @endif
            </div>
        </a>
    @empty
        <div style="text-align:center;padding:60px 20px;color:var(--warna-teks-abu);">
            <i class="fa-regular fa-newspaper" style="font-size:40px;opacity:0.3;display:block;margin-bottom:14px;"></i>
            Tidak ada artikel yang sesuai dengan filter.
        </div>
    @endforelse
</div>

<!-- Pagination -->
<div style="margin-top:24px;">{{ $artikelList->links() }}</div>
@endsection
