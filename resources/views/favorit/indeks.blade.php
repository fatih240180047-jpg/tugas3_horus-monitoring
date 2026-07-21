@extends('layouts.aplikasi')

@section('judul', 'Pemantauan Watchlist Favorit')

@section('konten')
<div class="container-favorit">
    @if($favoritList->isEmpty())
        <div class="max-w-md mx-auto text-center py-16 px-6 bg-surface-container-low border border-dashed border-outline-variant rounded-2xl shadow-xl mt-12 flex flex-col items-center gap-4">
            <span class="material-symbols-outlined text-5xl text-outline-variant animate-pulse">star</span>
            <h3 class="font-headline-md text-base font-bold text-on-surface">Belum Ada Negara Favorit</h3>
            <p class="text-xs text-on-surface-variant leading-relaxed">
                Tandai negara-negara penting dalam rantai pasok global Anda untuk memantau indikator cuaca, nilai tukar, ekonomi, dan berita secara terfokus di satu halaman.
            </p>
            <a href="{{ route('dasbor.indeks') }}" class="flex items-center gap-2 bg-primary hover:opacity-90 text-on-primary font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-lg shadow-lg transition-all mt-2">
                <span class="material-symbols-outlined text-[16px]">public</span>
                Buka Control Center Peta
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($favoritList as $negara)
                @php
                    $risiko = $negara->penilaianRisiko->first();
                    $cuaca = $negara->catatanCuaca->first();
                    $ekonomi = $negara->indikatorEkonomi->first();
                    $kurs = $negara->nilaiTukar->first();
                    $berita = $negara->artikelBerita->first();

                    $levelRisiko = $risiko ? $risiko->level_risiko : 'N/A';
                    
                    $accentColor = match(strtolower($levelRisiko)) {
                        'kritis' => 'border-t-error',
                        'tinggi' => 'border-t-orange-500',
                        'sedang' => 'border-t-amber-500',
                        'rendah' => 'border-t-emerald-500',
                        default  => 'border-t-outline-variant',
                    };
                @endphp
                
                <div class="bg-surface-container-low border border-outline-variant {{ $accentColor }} border-t-4 rounded-xl p-5 shadow-lg flex flex-col justify-between transition-all hover:-translate-y-1 hover:shadow-2xl" 
                     id="kartu-{{ $negara->id }}">
                    
                    <div class="space-y-4">
                        <!-- Card Header -->
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <img src="{{ $negara->bendera_url }}" class="h-5 w-7.5 object-cover rounded border border-outline-variant shadow-sm" alt="{{ $negara->nama }} Flag">
                                <div class="leading-tight">
                                    <a href="{{ route('negara.tampilkan', $negara->kode_iso) }}" class="text-sm font-bold text-on-surface hover:underline">{{ $negara->nama }}</a>
                                    <div class="text-[10px] text-on-surface-variant font-semibold mt-0.5">{{ $negara->kode_iso }} • Ibu Kota: {{ $negara->ibu_kota ?? 'N/A' }}</div>
                                </div>
                            </div>
                            
                            <button class="text-amber-400 hover:scale-115 active:scale-95 transition-all p-1" 
                                    onclick="toggleFavorit({{ $negara->id }}, this)" title="Hapus dari Favorit">
                                <span class="material-symbols-outlined text-[20px] fill-1">star</span>
                            </button>
                        </div>

                        <!-- Risk Rating -->
                        <div class="flex justify-between items-center text-xs border-b border-outline-variant/20 pb-3">
                            <span class="text-on-surface-variant font-bold uppercase tracking-wider text-[10px]">Indeks Risiko SCM</span>
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase
                                @if($levelRisiko === 'Kritis') bg-error-container text-on-error-container border border-error/20
                                @elseif($levelRisiko === 'Tinggi') bg-orange-500/20 text-orange-400 border border-orange-500/30
                                @elseif($levelRisiko === 'Sedang') bg-amber-500/20 text-amber-400 border border-amber-500/30
                                @else bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                @endif
                            ">
                                {{ $levelRisiko }}
                            </span>
                        </div>

                        <!-- Weather Parameters -->
                        <div class="space-y-2 border-b border-outline-variant/20 pb-3.5">
                            <div class="text-[9px] text-outline font-extrabold uppercase tracking-widest flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">cloud</span>
                                Kondisi Cuaca SCM
                            </div>
                            
                            @if($cuaca)
                                <div class="flex justify-between text-xs font-semibold">
                                    <span class="text-on-surface-variant">Suhu &amp; Kondisi</span>
                                    <span class="text-on-surface">{{ $cuaca->suhu }}°C / {{ ucfirst($cuaca->kondisi_cuaca) }}</span>
                                </div>
                                <div class="flex justify-between text-xs font-semibold">
                                    <span class="text-on-surface-variant">Curah Hujan</span>
                                    <span class="text-on-surface">{{ $cuaca->curah_hujan }} mm</span>
                                </div>
                                <div class="flex justify-between text-xs font-semibold">
                                    <span class="text-on-surface-variant">Risiko Anomali</span>
                                    <span class="font-extrabold {{ $cuaca->risiko_badai ? 'text-error' : 'text-emerald-400' }}">
                                        {{ $cuaca->risiko_badai ? 'Tinggi' : 'Normal / Aman' }}
                                    </span>
                                </div>
                            @else
                                <div class="text-center text-[10px] text-outline py-2 italic">Data cuaca tidak tersedia</div>
                            @endif
                        </div>

                        <!-- Financial & Macro Indicators -->
                        <div class="space-y-2 pb-1">
                            <div class="text-[9px] text-outline font-extrabold uppercase tracking-widest flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">payments</span>
                                Finansial &amp; Makroekonomi
                            </div>
                            
                            <div class="flex justify-between text-xs font-semibold">
                                <span class="text-on-surface-variant">Mata Uang Resmi</span>
                                <span class="text-on-surface font-mono">{{ $negara->mata_uang ?? 'USD' }}</span>
                            </div>
                            @if($kurs)
                                <div class="flex justify-between text-xs font-semibold">
                                    <span class="text-on-surface-variant">Nilai Tukar Forex</span>
                                    <span class="text-on-surface font-mono">1 USD = {{ number_format($kurs->nilai_tukar, 2) }} {{ $kurs->kode_mata_uang }}</span>
                                </div>
                            @endif
                            @if($ekonomi)
                                <div class="flex justify-between text-xs font-semibold">
                                    <span class="text-on-surface-variant">Laju Inflasi</span>
                                    <span class="text-on-surface">{{ $ekonomi->tingkat_inflasi }}%</span>
                                </div>
                                <div class="flex justify-between text-xs font-semibold">
                                    <span class="text-on-surface-variant">Produk Domestik Bruto</span>
                                    <span class="text-on-surface">USD {{ number_format($ekonomi->pdb / 1e9, 2) }} B</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- News Radar -->
                    <div class="border-t border-outline-variant/20 pt-3.5 mt-3.5 space-y-2">
                        <div class="text-[9px] text-outline font-extrabold uppercase tracking-widest flex items-center gap-1">
                            <span class="material-symbols-outlined text-[13px]">newspaper</span>
                            Berita Radar Intelijen
                        </div>
                        
                        @if($berita)
                            <div class="bg-surface-container-lowest border border-outline-variant/40 p-2.5 rounded-lg flex flex-col gap-1.5">
                                <a href="{{ $berita->url_sumber }}" target="_blank" 
                                   class="text-[11px] font-bold text-on-surface hover:text-primary transition-all leading-normal line-clamp-2">
                                    {{ $berita->judul }}
                                </a>
                                <div class="flex justify-between items-center text-[9px] text-outline font-semibold">
                                    <span class="px-1.5 py-0.5 rounded uppercase
                                        @if($berita->keparahan === 'kritis' || $berita->keparahan === 'tinggi') bg-error-container text-on-error-container border border-error/20
                                        @else bg-amber-500/20 text-amber-400 border border-amber-500/30
                                        @endif
                                    ">
                                        {{ $berita->keparahan }}
                                    </span>
                                    <span>{{ \Carbon\Carbon::parse($berita->diterbitkan_pada)->diffForHumans() }}</span>
                                </div>
                            </div>
                        @else
                            <div class="text-center text-[10px] text-outline py-2 italic">Tidak ada berita geopolitik terbaru</div>
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
        if (!confirm('Apakah Anda yakin ingin menghapus negara ini dari pemantauan favorit?')) {
            return;
        }

        fetch(`{{ url('/favorit') }}/${negaraId}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const kartu = document.getElementById(`kartu-${negaraId}`);
                if (kartu) {
                    kartu.style.opacity = '0';
                    kartu.style.transform = 'scale(0.9) translateY(10px)';
                    setTimeout(() => {
                        kartu.remove();
                        if (document.querySelectorAll('[id^="kartu-"]').length === 0) {
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
