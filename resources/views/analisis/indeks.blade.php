@extends('layouts.aplikasi')

@section('judul', 'Analisis Geopolitik & Berita')

@section('konten')
<!-- Statistik Ringkas SCM -->
@php
    $totalArtikel = $artikelList->total();
    $belumDianalisis = \App\Models\ArtikelBerita::doesntHave('analisis')->count();
    $menungguReview = \App\Models\AnalisisArtikel::where('status', 'menunggu_review')->count();
    $sudahDisetujui = \App\Models\AnalisisArtikel::where('status', 'disetujui')->count();
@endphp

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-surface-container-low border border-outline-variant rounded-xl p-4 text-center shadow-md">
        <span class="text-3xl font-extrabold text-on-surface font-label-sm block">{{ $totalArtikel }}</span>
        <span class="text-[10px] text-on-surface-variant font-bold uppercase tracking-wider mt-1 block">Total Berita</span>
    </div>
    <div class="bg-surface-container-low border border-outline-variant rounded-xl p-4 text-center shadow-md">
        <span class="text-3xl font-extrabold text-outline font-label-sm block">{{ $belumDianalisis }}</span>
        <span class="text-[10px] text-on-surface-variant font-bold uppercase tracking-wider mt-1 block">Belum Dianalisis</span>
    </div>
    <div class="bg-surface-container-low border border-outline-variant rounded-xl p-4 text-center shadow-md">
        <span class="text-3xl font-extrabold text-amber-500 font-label-sm block">{{ $menungguReview }}</span>
        <span class="text-[10px] text-on-surface-variant font-bold uppercase tracking-wider mt-1 block">Menunggu Review</span>
    </div>
    <div class="bg-surface-container-low border border-outline-variant rounded-xl p-4 text-center shadow-md">
        <span class="text-3xl font-extrabold text-emerald-500 font-label-sm block">{{ $sudahDisetujui }}</span>
        <span class="text-[10px] text-on-surface-variant font-bold uppercase tracking-wider mt-1 block">Disetujui</span>
    </div>
</div>

<!-- Filters & Search Form -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
    
    <!-- Filter Chips -->
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('analisis.indeks') }}" 
           class="px-3 py-1.5 rounded-full text-xs font-bold transition-all border flex items-center gap-1.5
           {{ !request('status_analisis') ? 'bg-primary text-on-primary border-primary shadow' : 'bg-surface-container-high text-on-surface-variant border-outline-variant hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-[14px]">list</span>
            Semua
        </a>
        
        <a href="{{ route('analisis.indeks', ['status_analisis'=>'belum']) }}" 
           class="px-3 py-1.5 rounded-full text-xs font-bold transition-all border flex items-center gap-1.5
           {{ request('status_analisis')==='belum' ? 'bg-primary text-on-primary border-primary shadow' : 'bg-surface-container-high text-on-surface-variant border-outline-variant hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-[14px]">circle</span>
            Belum Dianalisis
        </a>
        
        <a href="{{ route('analisis.indeks', ['status_analisis'=>'menunggu']) }}" 
           class="px-3 py-1.5 rounded-full text-xs font-bold transition-all border flex items-center gap-1.5
           {{ request('status_analisis')==='menunggu' ? 'bg-primary text-on-primary border-primary shadow' : 'bg-surface-container-high text-on-surface-variant border-outline-variant hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-[14px]">hourglass_empty</span>
            Menunggu Review
        </a>

        <a href="{{ route('analisis.indeks', ['status_analisis'=>'disetujui']) }}" 
           class="px-3 py-1.5 rounded-full text-xs font-bold transition-all border flex items-center gap-1.5
           {{ request('status_analisis')==='disetujui' ? 'bg-primary text-on-primary border-primary shadow' : 'bg-surface-container-high text-on-surface-variant border-outline-variant hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-[14px]">check_circle</span>
            Disetujui
        </a>
    </div>

    <!-- Country Dropdown Filter -->
    <form method="GET" class="flex gap-2 w-full md:w-auto">
        <input type="hidden" name="status_analisis" value="{{ request('status_analisis') }}">
        <select name="negara_id" class="bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none placeholder-outline-variant font-semibold cursor-pointer w-full md:w-52">
            <option value="">-- Semua Negara --</option>
            @foreach($semuaNegara as $id => $nama)
                <option value="{{ $id }}" {{ request('negara_id') == $id ? 'selected' : '' }}>{{ $nama }}</option>
            @endforeach
        </select>
        <button type="submit" class="flex items-center justify-center bg-surface-container-high hover:bg-surface-container-highest border border-outline-variant text-on-surface p-2 rounded-lg transition-all">
            <span class="material-symbols-outlined text-[18px]">filter_list</span>
        </button>
    </form>
</div>

<!-- Article Feed Grid -->
<div class="space-y-3">
    @forelse($artikelList as $artikel)
        @php
            $analisisiUser = $artikel->analisis->where('pengguna_id', auth()->id())->first();
            
            $statusColor = 'bg-gray-500';
            $labelStatus = 'Belum Dianalisis';
            
            if ($analisisiUser) {
                $statusColor = $analisisiUser->status === 'menunggu_review' 
                    ? 'bg-amber-500 shadow-[0_0_6px_#f59e0b]' 
                    : ($analisisiUser->status === 'disetujui' ? 'bg-emerald-500' : 'bg-error');
                $labelStatus = $analisisiUser->labelStatus();
            } elseif ($artikel->analisis->count() > 0) {
                $statusTerbaik = $artikel->analisis->sortByDesc(fn($a) => ['disetujui'=>3,'menunggu_review'=>2,'draft'=>1,'ditolak'=>0][$a->status])->first();
                $statusColor = $statusTerbaik->status === 'menunggu_review' 
                    ? 'bg-amber-500 shadow-[0_0_6px_#f59e0b]' 
                    : ($statusTerbaik->status === 'disetujui' ? 'bg-emerald-500' : 'bg-error');
                $labelStatus = $statusTerbaik->labelStatus() . ' (oleh ' . $statusTerbaik->pengguna->name . ')';
            }

            $iconData = match($artikel->keparahan) {
                'kritis' => ['icon' => 'radiation', 'bg' => 'bg-error-container/20 border-error/30 text-error'],
                'tinggi' => ['icon' => 'warning', 'bg' => 'bg-orange-500/20 border-orange-500/30 text-orange-400'],
                'sedang' => ['icon' => 'error', 'bg' => 'bg-amber-500/20 border-amber-500/30 text-amber-400'],
                default  => ['icon' => 'info', 'bg' => 'bg-emerald-500/20 border-emerald-500/30 text-emerald-400'],
            };
        @endphp
        
        <a href="{{ route('analisis.detail', $artikel->id) }}" 
           class="bg-surface-container-low border border-outline-variant hover:border-primary/40 rounded-xl p-4 flex flex-col md:flex-row items-start md:items-center gap-4 transition-all shadow-md">
            
            <!-- Severity Icon Indicator -->
            <div class="w-10 h-10 rounded-lg flex items-center justify-center border {{ $iconData['bg'] }} flex-shrink-0">
                <span class="material-symbols-outlined text-[20px]">{{ $iconData['icon'] }}</span>
            </div>

            <!-- Content Area -->
            <div class="flex-grow min-w-0 space-y-1">
                <h4 class="text-xs font-bold text-on-surface hover:text-primary transition-colors leading-relaxed line-clamp-1">
                    {{ $artikel->judul }}
                </h4>
                
                <div class="flex items-center gap-3 text-[10px] text-on-surface-variant font-semibold flex-wrap">
                    @if($artikel->negara)
                        <div class="flex items-center gap-1">
                            <img src="{{ $artikel->negara->bendera_url }}" class="h-2.5 w-4 object-cover rounded border border-outline-variant">
                            <span>{{ $artikel->negara->nama }}</span>
                        </div>
                    @else
                        <span>Global SCM</span>
                    @endif
                    <span>•</span>
                    <span class="px-2 py-0.5 rounded text-[8px] font-extrabold uppercase
                        @if($artikel->keparahan === 'kritis' || $artikel->keparahan === 'tinggi') bg-error-container text-on-error-container border border-error/20
                        @elseif($artikel->keparahan === 'sedang') bg-amber-500/20 text-amber-400 border border-amber-500/30
                        @else bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                        @endif
                    ">
                        {{ $artikel->keparahan }}
                    </span>
                    <span>•</span>
                    <span>{{ \Carbon\Carbon::parse($artikel->diterbitkan_pada)->diffForHumans() }}</span>
                    @if($artikel->analisis->count() > 0)
                        <span>•</span>
                        <span class="text-secondary flex items-center gap-1">
                            <span class="material-symbols-outlined text-[12px]">forum</span>
                            {{ $artikel->analisis->count() }} Analisis
                        </span>
                    @endif
                </div>
            </div>

            <!-- Status Indicator -->
            <div class="w-full md:w-auto flex justify-between md:flex-col items-center md:items-end flex-shrink-0 border-t border-outline-variant/15 pt-3 md:border-t-0 md:pt-0">
                <div class="flex items-center gap-2 text-xs text-on-surface-variant font-semibold">
                    <span class="w-2.5 h-2.5 rounded-full {{ $statusColor }}"></span>
                    <span class="text-[11px]" x-text="'{{ $labelStatus }}'"></span>
                </div>
                
                @if($bisaAnalisis)
                    <span class="text-[10px] font-extrabold text-primary uppercase mt-1 hidden md:block">
                        {{ $analisisiUser ? 'Edit Analisis' : 'Mulai Analisis' }} &rarr;
                    </span>
                @endif
            </div>

        </a>
    @empty
        <div class="text-center py-12 border border-dashed border-outline-variant rounded-xl text-on-surface-variant">
            <span class="material-symbols-outlined text-4xl text-outline mb-2">article</span>
            <p class="text-xs italic">Tidak ada artikel yang sesuai dengan filter.</p>
        </div>
    @endforelse
</div>

<!-- Pagination -->
<div class="mt-6">
    {{ $artikelList->links() }}
</div>
@endsection
