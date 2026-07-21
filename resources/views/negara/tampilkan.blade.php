@extends('layouts.aplikasi')

@section('judul')
<div class="flex items-center gap-3">
    <img src="{{ $negara->bendera_url }}" class="h-6 w-9 object-cover rounded border border-outline-variant shadow-md" alt="{{ $negara->nama }} Flag">
    <span class="font-headline-md text-base font-extrabold text-on-surface">Intelijen SCM: {{ $negara->nama }}</span>
</div>
@endsection

@section('konten')
<div class="space-y-6">

    <!-- Top Info & Risk Overview Header -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        
        <!-- Risk Badge Bento Card -->
        <div class="lg:col-span-8 bg-surface-container-low border border-outline-variant rounded-xl p-6 flex flex-col md:flex-row items-center gap-6 shadow-xl relative overflow-hidden">
            <!-- Glow background effect based on risk -->
            <div class="absolute top-0 left-0 w-2 h-full 
                @if($risikoTerkini)
                    @if($risikoTerkini->level_risiko === 'Kritis') bg-error
                    @elseif($risikoTerkini->level_risiko === 'Tinggi') bg-orange-500
                    @elseif($risikoTerkini->level_risiko === 'Sedang') bg-amber-500
                    @else bg-emerald-500
                    @endif
                @else bg-outline-variant
                @endif
            "></div>

            <div class="flex-shrink-0 flex flex-col items-center justify-center w-36 h-36 rounded-full border-4 font-headline-lg relative 
                @if($risikoTerkini)
                    @if($risikoTerkini->level_risiko === 'Kritis') border-error/30 text-error
                    @elseif($risikoTerkini->level_risiko === 'Tinggi') border-orange-500/30 text-orange-400
                    @elseif($risikoTerkini->level_risiko === 'Sedang') border-amber-500/30 text-amber-400
                    @else border-emerald-500/30 text-emerald-400
                    @endif
                @else border-outline-variant text-on-surface-variant
                @endif
            ">
                <span class="text-4xl font-extrabold font-label-sm leading-none">{{ $risikoTerkini?->skor_total ?? 'N/A' }}</span>
                <span class="text-[10px] text-on-surface-variant uppercase tracking-wider mt-1.5 font-bold">Skor Risiko</span>
            </div>

            <div class="flex-grow space-y-2 text-center md:text-left">
                <div class="text-[11px] text-on-surface-variant font-extrabold uppercase tracking-wider">Tingkat Risiko Terkini</div>
                <h2 class="text-2xl font-black text-on-surface leading-tight">
                    Tingkat Risiko: 
                    <span class="
                        @if($risikoTerkini)
                            @if($risikoTerkini->level_risiko === 'Kritis') text-error
                            @elseif($risikoTerkini->level_risiko === 'Tinggi') text-orange-400
                            @elseif($risikoTerkini->level_risiko === 'Sedang') text-amber-400
                            @else text-emerald-400
                            @endif
                        @else text-on-surface-variant
                        @endif
                    ">{{ $risikoTerkini?->level_risiko ?? 'Belum Dinilai' }}</span>
                </h2>
                
                <div class="space-y-1.5 py-1">
                    @if($risikoTerkini)
                        @foreach($risikoTerkini->penjelasan as $bukti)
                            <div class="text-xs text-on-surface-variant flex items-center justify-center md:justify-start gap-2">
                                <span class="material-symbols-outlined text-[14px] text-error flex-shrink-0">info</span>
                                <span>{{ $bukti }}</span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-xs text-outline italic">Silakan klik tombol "Sinkronkan &amp; Kalkulasi" di sebelah kanan untuk memuat data intelijen dan menghitung indeks risiko negara ini.</p>
                    @endif
                </div>

                <div class="text-[10px] text-outline pt-1">
                    Pembaruan Terakhir: {{ $risikoTerkini ? date('d M Y H:i', strtotime($risikoTerkini->dihitung_pada)) : 'Belum Pernah' }}
                </div>
            </div>

            <div class="flex-shrink-0 mt-4 md:mt-0">
                @if(Auth::user()->adalahSuperAdmin() || Auth::user()->mempunyaiPeran('admin') || Auth::user()->mempunyaiPeran('analis'))
                    <form action="{{ route('negara.sinkronkan', $negara->kode_iso) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 bg-primary hover:opacity-90 text-on-primary font-bold text-xs uppercase tracking-wider px-4 py-2.5 rounded-lg shadow-lg transition-all">
                            <span class="material-symbols-outlined text-[16px] animate-spin-slow">sync</span>
                            Sinkronkan &amp; Kalkulasi
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Country Metadata Card -->
        <div class="lg:col-span-4 bg-surface-container-low border border-outline-variant rounded-xl p-6 flex flex-col justify-between shadow-xl">
            <h3 class="text-xs font-bold text-on-surface uppercase tracking-wider mb-4 border-b border-outline-variant/30 pb-2">Informasi Profil Hub</h3>
            <div class="space-y-3 text-xs">
                <div class="flex justify-between border-b border-outline-variant/10 pb-2">
                    <span class="text-on-surface-variant">Ibu Kota</span>
                    <strong class="text-on-surface font-semibold">{{ $negara->ibu_kota ?? 'N/A' }}</strong>
                </div>
                <div class="flex justify-between border-b border-outline-variant/10 pb-2">
                    <span class="text-on-surface-variant">Kawasan</span>
                    <strong class="text-on-surface font-semibold">{{ $negara->kawasan ?? 'N/A' }}</strong>
                </div>
                <div class="flex justify-between border-b border-outline-variant/10 pb-2">
                    <span class="text-on-surface-variant">Sub-Kawasan</span>
                    <strong class="text-on-surface font-semibold">{{ $negara->sub_kawasan ?? 'N/A' }}</strong>
                </div>
                <div class="flex justify-between border-b border-outline-variant/10 pb-2">
                    <span class="text-on-surface-variant">Populasi</span>
                    <strong class="text-on-surface font-semibold">{{ $negara->populasi ? number_format($negara->populasi, 0, ',', '.') : 'N/A' }}</strong>
                </div>
                <div class="flex justify-between pb-1">
                    <span class="text-on-surface-variant">Koordinat Utama</span>
                    <strong class="text-on-surface font-semibold">{{ $negara->lintang ?? '0' }}, {{ $negara->bujur ?? '0' }}</strong>
                </div>
            </div>
        </div>

    </div>

    <!-- Parameter Analysis Bento Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Weather intelligence -->
        <div class="bg-surface-container-low border border-outline-variant rounded-xl p-5 shadow-lg flex flex-col gap-4">
            <h3 class="text-sm font-bold text-primary flex items-center gap-2 border-b border-outline-variant/30 pb-2.5">
                <span class="material-symbols-outlined text-[18px]">cloud_sync</span>
                Prakiraan Cuaca 7 Hari
            </h3>

            @if($cuacaTerkini)
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-surface-container-lowest border border-outline-variant/50 p-3.5 rounded-lg">
                        <div class="text-[10px] text-on-surface-variant font-bold uppercase">Suhu Saat Ini</div>
                        <div class="text-2xl font-black text-on-surface mt-1 font-label-sm">
                            {{ $cuacaTerkini->suhu ?? 'N/A' }} °C
                        </div>
                        @if($cuacaTerkini->suhu_min && $cuacaTerkini->suhu_max)
                            <div class="text-[9px] text-outline mt-0.5">Rentang: {{ $cuacaTerkini->suhu_min }}°C - {{ $cuacaTerkini->suhu_max }}°C</div>
                        @endif
                    </div>
                    
                    <div class="bg-surface-container-lowest border border-outline-variant/50 p-3.5 rounded-lg">
                        <div class="text-[10px] text-on-surface-variant font-bold uppercase">Kondisi Cuaca</div>
                        <div class="text-xs font-extrabold text-on-surface mt-2 uppercase tracking-wide truncate">
                            {{ $cuacaTerkini->kondisi_cuaca ?? 'N/A' }}
                        </div>
                    </div>
                </div>

                <div class="space-y-2 text-xs border-t border-outline-variant/20 pt-3">
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Curah Hujan</span>
                        <strong class="text-on-surface font-semibold">{{ $cuacaTerkini->curah_hujan ?? '0' }} mm</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Kecepatan Angin</span>
                        <strong class="text-on-surface font-semibold">{{ $cuacaTerkini->kecepatan_angin ?? '0' }} km/jam</strong>
                    </div>
                </div>

                @if($prakiraan7Hari->count() > 1)
                    <div class="border-t border-outline-variant/20 pt-3">
                        <div class="text-[9px] text-outline font-extrabold uppercase tracking-wider mb-2">Ramalan Cuaca 7 Hari</div>
                        <div class="grid grid-cols-7 gap-1">
                            @foreach($prakiraan7Hari->take(7) as $hari)
                                <div class="bg-surface-container-high/40 border border-outline-variant/30 p-1 rounded text-center">
                                    <div class="text-[8px] text-on-surface-variant uppercase font-medium">{{ date('D', strtotime($hari->tanggal_observasi)) }}</div>
                                    <div class="text-[10px] font-bold text-on-surface mt-1">{{ $hari->suhu_max ?? $hari->suhu ?? '--' }}°</div>
                                    <div class="text-[8px] text-primary">{{ $hari->suhu_min ?? '--' }}°</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($cuacaTerkini->insight_scm)
                    <div class="bg-primary/5 border border-primary/20 rounded-lg p-3 text-xs text-on-surface-variant mt-auto">
                        <div class="font-extrabold text-primary flex items-center gap-1 uppercase tracking-wider text-[10px] mb-1">
                            <span class="material-symbols-outlined text-[13px]">lightbulb</span>
                            Dampak Logistik SCM
                        </div>
                        <p class="leading-relaxed whitespace-pre-line">{{ $cuacaTerkini->insight_scm }}</p>
                    </div>
                @endif
            @else
                <div class="text-xs text-on-surface-variant text-center py-8 border border-dashed border-outline-variant rounded-lg">
                    Belum ada data cuaca. Jalankan sinkronisasi untuk memuat data.
                </div>
            @endif
        </div>

        <!-- Macroeconomics -->
        <div class="bg-surface-container-low border border-outline-variant rounded-xl p-5 shadow-lg flex flex-col gap-4">
            <h3 class="text-sm font-bold text-emerald-400 flex items-center gap-2 border-b border-outline-variant/30 pb-2.5">
                <span class="material-symbols-outlined text-[18px]">trending_up</span>
                Indikator Ekonomi Makro
            </h3>

            <div class="space-y-3.5 my-auto text-xs">
                <div class="flex justify-between border-b border-outline-variant/10 pb-2.5">
                    <span class="text-on-surface-variant">Produk Domestik Bruto (PDB)</span>
                    <strong class="text-on-surface font-extrabold font-label-sm">
                        {{ $ekonomiTerkini && $ekonomiTerkini->pdb ? '$' . number_format($ekonomiTerkini->pdb / 1_000_000_000, 2) . ' Milyar' : 'N/A' }}
                    </strong>
                </div>
                <div class="flex justify-between border-b border-outline-variant/10 pb-2.5">
                    <span class="text-on-surface-variant">Tingkat Inflasi</span>
                    <strong class="font-extrabold font-label-sm {{ ($ekonomiTerkini?->tingkat_inflasi ?? 0) > 5 ? 'text-error' : 'text-on-surface' }}">
                        {{ $ekonomiTerkini ? $ekonomiTerkini->tingkat_inflasi . ' %' : 'N/A' }}
                    </strong>
                </div>
                <div class="flex justify-between border-b border-outline-variant/10 pb-2.5">
                    <span class="text-on-surface-variant">Tingkat Pengangguran</span>
                    <strong class="text-on-surface font-extrabold font-label-sm">
                        {{ $ekonomiTerkini ? $ekonomiTerkini->tingkat_pengangguran . ' %' : 'N/A' }}
                    </strong>
                </div>
                <div class="flex justify-between border-b border-outline-variant/10 pb-2.5">
                    <span class="text-on-surface-variant">Suku Bunga Riil</span>
                    <strong class="text-on-surface font-extrabold font-label-sm">
                        {{ $ekonomiTerkini ? $ekonomiTerkini->tingkat_bunga . ' %' : 'N/A' }}
                    </strong>
                </div>
                <div class="flex justify-between pb-1">
                    <span class="text-on-surface-variant">Neraca Perdagangan</span>
                    <strong class="text-on-surface font-extrabold font-label-sm">
                        {{ $ekonomiTerkini && $ekonomiTerkini->neraca_perdagangan ? '$' . number_format($ekonomiTerkini->neraca_perdagangan / 1_000_000, 2) . ' Juta' : 'N/A' }}
                    </strong>
                </div>
            </div>
            
            <div class="text-[10px] text-outline text-center border-t border-outline-variant/20 pt-2.5">
                Sumber Data Bank Dunia (World Bank API)
            </div>
        </div>

        <!-- Forex / Currency -->
        <div class="bg-surface-container-low border border-outline-variant rounded-xl p-5 shadow-lg flex flex-col gap-4">
            <h3 class="text-sm font-bold text-amber-400 flex items-center gap-2 border-b border-outline-variant/30 pb-2.5">
                <span class="material-symbols-outlined text-[18px]">payments</span>
                Nilai Tukar Forex (Valas)
            </h3>

            <div class="space-y-3 text-xs">
                @forelse($nilaiTukarList->take(3) as $forex)
                    <div class="flex justify-between border-b border-outline-variant/10 pb-2 last:border-b-0 last:pb-0">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[14px] text-amber-400">monetization_on</span>
                            <span class="text-on-surface-variant font-semibold">{{ $forex->kode_mata_uang }} / USD</span>
                        </div>
                        <strong class="text-on-surface font-extrabold font-label-sm">{{ number_format($forex->nilai_tukar, 4) }}</strong>
                    </div>
                @empty
                    <div class="text-xs text-on-surface-variant text-center py-4 italic">Belum ada riwayat forex.</div>
                @endforelse
            </div>

            @if($nilaiTukarTerkini?->insight_scm)
                <div class="bg-amber-400/5 border border-amber-400/20 rounded-lg p-3 text-xs text-on-surface-variant mt-auto">
                    <div class="font-extrabold text-amber-400 flex items-center gap-1 uppercase tracking-wider text-[10px] mb-1">
                        <span class="material-symbols-outlined text-[13px]">trending_up</span>
                        Analisis Sentimen Forex
                    </div>
                    <p class="leading-relaxed">{{ $nilaiTukarTerkini->insight_scm }}</p>
                </div>
            @endif
        </div>

    </div>

    <!-- Active Ports Section (BARU & LEBIH KOMPLEKS) -->
    <div class="bg-surface-container-low border border-outline-variant rounded-xl p-6 shadow-xl">
        <h3 class="text-sm font-bold text-tertiary flex items-center gap-2 border-b border-outline-variant/30 pb-3 mb-4">
            <span class="material-symbols-outlined text-[18px]">anchor</span>
            Pelabuhan Maritim Utama SCM
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-outline-variant text-on-surface-variant uppercase tracking-wider text-[10px] font-extrabold">
                        <th class="pb-3 pr-4">Nama Pelabuhan</th>
                        <th class="pb-3 px-4">LOCODE</th>
                        <th class="pb-3 px-4">Kapasitas TEU</th>
                        <th class="pb-3 px-4">Tingkat Kepadatan</th>
                        <th class="pb-3 px-4">Skor Risiko</th>
                        <th class="pb-3 px-4">Operator</th>
                        <th class="pb-3 pl-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10 text-on-surface font-medium">
                    @forelse($negara->pelabuhans as $p)
                        <tr class="hover:bg-surface-container-highest/20 transition-all">
                            <td class="py-3 pr-4 font-bold text-sm text-on-surface">{{ $p->nama }}</td>
                            <td class="py-3 px-4 text-outline font-mono">{{ $p->kode_locode }}</td>
                            <td class="py-3 px-4 font-semibold">{{ $p->kapasitas_teu ? number_format($p->kapasitas_teu) : 'N/A' }} TEU</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-16 bg-surface-container-lowest border border-outline-variant/30 h-1.5 rounded overflow-hidden">
                                        <div class="h-full rounded" style="width: {{ $p->tingkat_kepadatan }}%; background-color: {{ $p->warnaMarker() }}"></div>
                                    </div>
                                    <span class="font-extrabold" style="color: {{ $p->warnaMarker() }}">{{ $p->tingkat_kepadatan }}%</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 font-bold">{{ $p->skor_risiko }}/100</td>
                            <td class="py-3 px-4 text-on-surface-variant">{{ $p->operator ?? 'N/A' }}</td>
                            <td class="py-3 pl-4 text-right">
                                <a href="{{ route('beranda') }}?port={{ $p->lintang }},{{ $p->bujur }}" class="inline-flex items-center gap-1 text-primary hover:underline text-[11px] font-bold">
                                    <span class="material-symbols-outlined text-[13px]">location_on</span>
                                    Lihat Peta
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-on-surface-variant italic border border-dashed border-outline-variant/50 rounded-lg">
                                Tidak ada pelabuhan maritim utama terdaftar di negara ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Geopolitical News & Sentiments Feed -->
    <div class="bg-surface-container-low border border-outline-variant rounded-xl p-6 shadow-xl">
        <h3 class="text-sm font-bold text-on-surface flex items-center gap-2 border-b border-outline-variant/30 pb-3 mb-4">
            <span class="material-symbols-outlined text-[18px]">satellite_dish</span>
            Intelijen Keamanan &amp; Radar Sentimen
        </h3>

        <div class="space-y-5 divide-y divide-outline-variant/20">
            @forelse($beritaList as $art)
                <div class="pt-4 first:pt-0 flex flex-col gap-2.5">
                    <div class="flex justify-between items-center text-[10px] text-on-surface-variant">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase
                                @if($art->keparahan === 'kritis' || $art->keparahan === 'tinggi') bg-error-container text-on-error-container border border-error/20
                                @elseif($art->keparahan === 'sedang') bg-amber-500/20 text-amber-400 border border-amber-500/30
                                @else bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                @endif
                            ">
                                Severity: {{ $art->keparahan }}
                            </span>
                            <span class="font-extrabold uppercase flex items-center gap-1
                                @if($art->sentimen === 'negatif') text-error
                                @elseif($art->sentimen === 'positif') text-emerald-400
                                @else text-on-surface-variant
                                @endif
                            ">
                                <span class="material-symbols-outlined text-[12px] font-bold">
                                    {{ $art->sentimen === 'negatif' ? 'thumb_down' : ($art->sentimen === 'positif' ? 'thumb_up' : 'info') }}
                                </span>
                                {{ $art->sentimen }}
                            </span>
                            <span>•</span>
                            <span class="font-semibold text-outline">{{ $art->sumber }}</span>
                        </div>
                        <span>{{ date('d M Y, H:i', strtotime($art->diterbitkan_pada)) }}</span>
                    </div>

                    @if($art->url_asli)
                        <a href="{{ $art->url_asli }}" target="_blank" class="text-sm font-bold text-on-surface hover:text-primary transition-all leading-snug flex items-center gap-1.5 w-fit">
                            {{ $art->judul }}
                            <span class="material-symbols-outlined text-xs text-outline">open_in_new</span>
                        </a>
                    @else
                        <h4 class="text-sm font-bold text-on-surface leading-snug">{{ $art->judul }}</h4>
                    @endif

                    @if($art->ringkasan)
                        <p class="text-xs text-on-surface-variant leading-relaxed">{{ Str::limit($art->ringkasan, 280) }}</p>
                    @endif

                    @if($art->dampak_scm)
                        <div class="bg-red-500/5 border-l-2 border-error p-3 rounded-r text-xs text-on-surface-variant mt-1.5">
                            <strong class="text-error font-extrabold uppercase text-[10px] tracking-wider block mb-1">Analisis Dampak Logistik &amp; SCM:</strong>
                            <p class="leading-relaxed">{{ $art->dampak_scm }}</p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-xs text-on-surface-variant text-center py-6 border border-dashed border-outline-variant/60 rounded-lg">
                    Tidak ada artikel intelijen relevan yang disinkronkan untuk negara ini.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Charts / Trend Visualizations -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Forex Historical Chart -->
        <div class="bg-surface-container-low border border-outline-variant rounded-xl p-5 shadow-xl flex flex-col gap-4">
            <h3 class="text-xs font-extrabold text-amber-400 uppercase tracking-widest flex items-center gap-1.5 border-b border-outline-variant/30 pb-2">
                <span class="material-symbols-outlined text-[16px]">chart_area</span>
                Tren Forex Historis (Nilai Tukar ke USD)
            </h3>
            <div class="h-[280px] w-full">
                <canvas id="grafikForex"></canvas>
            </div>
        </div>

        <!-- Risk Score Fluctuation Chart -->
        <div class="bg-surface-container-low border border-outline-variant rounded-xl p-5 shadow-xl flex flex-col gap-4">
            <h3 class="text-xs font-extrabold text-error uppercase tracking-widest flex items-center gap-1.5 border-b border-outline-variant/30 pb-2">
                <span class="material-symbols-outlined text-[16px]">show_chart</span>
                Fluktuasi Nilai Indeks Risiko SCM
            </h3>
            <div class="h-[280px] w-full">
                <canvas id="grafikRisiko"></canvas>
            </div>
        </div>

    </div>

    <!-- 7 Day Weather Temperature Chart -->
    <div class="bg-surface-container-low border border-outline-variant rounded-xl p-5 shadow-xl flex flex-col gap-4">
        <h3 class="text-xs font-extrabold text-primary uppercase tracking-widest flex items-center gap-1.5 border-b border-outline-variant/30 pb-2">
            <span class="material-symbols-outlined text-[16px]">weather_mix</span>
            Grafik Tren Suhu Prakiraan Cuaca 7 Hari Ke Depan
        </h3>
        <div class="h-[280px] w-full">
            <canvas id="grafikCuaca7Hari"></canvas>
        </div>
    </div>

</div>
@endsection

@section('skrip_tambahan')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Global configuration
        Chart.defaults.color = '#bec6e0';
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.borderColor = 'rgba(51, 65, 85, 0.4)';

        // 1. Forex Chart
        const forexData = @json($nilaiTukarList->reverse()->values());
        if (forexData.length > 0) {
            const labels = forexData.map(item => new Date(item.tanggal_berlaku).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'}));
            const values = forexData.map(item => item.nilai_tukar);
            const cCode = forexData[0].kode_mata_uang;

            const ctx = document.getElementById('grafikForex').getContext('2d');
            let gradient = ctx.createLinearGradient(0, 0, 0, 260);
            gradient.addColorStop(0, 'rgba(245, 158, 11, 0.35)');
            gradient.addColorStop(1, 'rgba(245, 158, 11, 0.0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: `Nilai Tukar (${cCode}/USD)`,
                        data: values,
                        borderColor: '#fbbf24',
                        backgroundColor: gradient,
                        borderWidth: 2,
                        pointBackgroundColor: '#081425',
                        pointBorderColor: '#fbbf24',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.35
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { grid: { color: 'rgba(255, 255, 255, 0.04)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 2. Risk History Chart
        const riskData = @json($riwayatRisiko->reverse()->values());
        if (riskData.length > 0) {
            const labels = riskData.map(item => new Date(item.dihitung_pada).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', hour: '2-digit'}));
            const values = riskData.map(item => item.skor_total);

            const ctx = document.getElementById('grafikRisiko').getContext('2d');
            let gradient = ctx.createLinearGradient(0, 0, 0, 260);
            gradient.addColorStop(0, 'rgba(239, 68, 68, 0.35)');
            gradient.addColorStop(1, 'rgba(239, 68, 68, 0.0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Skor Indeks Risiko',
                        data: values,
                        borderColor: '#ef4444',
                        backgroundColor: gradient,
                        borderWidth: 2,
                        pointBackgroundColor: '#081425',
                        pointBorderColor: '#ef4444',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.35
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { min: 0, max: 100, grid: { color: 'rgba(255, 255, 255, 0.04)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 3. Weather Forecast 7 Days Chart
        const weatherData = @json($prakiraan7Hari->reverse()->values());
        if (weatherData.length > 0) {
            const labels = weatherData.map(item => new Date(item.tanggal_observasi).toLocaleDateString('id-ID', {weekday: 'short', day: 'numeric'}));
            const maxTemps = weatherData.map(item => item.suhu_max ?? item.suhu);
            const minTemps = weatherData.map(item => item.suhu_min ?? (item.suhu ? item.suhu - 4 : 15));

            const ctx = document.getElementById('grafikCuaca7Hari').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Suhu Maksimum (°C)',
                            data: maxTemps,
                            borderColor: '#f97316',
                            borderWidth: 2,
                            pointBackgroundColor: '#081425',
                            pointBorderColor: '#f97316',
                            pointRadius: 4,
                            fill: false,
                            tension: 0.3
                        },
                        {
                            label: 'Suhu Minimum (°C)',
                            data: minTemps,
                            borderColor: '#3b82f6',
                            borderWidth: 2,
                            pointBackgroundColor: '#081425',
                            pointBorderColor: '#3b82f6',
                            pointRadius: 4,
                            fill: false,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true, labels: { color: '#bec6e0' } } },
                    scales: {
                        y: { grid: { color: 'rgba(255, 255, 255, 0.04)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    });
</script>
@endsection
