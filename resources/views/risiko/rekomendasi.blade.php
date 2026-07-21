@extends('layouts.aplikasi')

@section('judul', 'Tindakan Mitigasi Risiko')

@section('konten')
<div class="bg-surface-container-low border border-outline-variant rounded-xl p-6 shadow-xl flex flex-col gap-6">
    
    <!-- Header with Filters -->
    <div class="border-b border-outline-variant/30 pb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="font-headline-md text-base font-black text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-primary">handshake</span>
                Daftar Rekomendasi Mitigasi SCM
            </h2>
            <p class="text-xs text-on-surface-variant mt-1">Daftar tindakan mitigasi tertunda atau selesai berdasarkan analisis risiko negara</p>
        </div>
        
        <!-- Filter Status Buttons -->
        <div class="flex gap-2">
            <a href="{{ route('risiko.rekomendasi.indeks', ['status' => 'Tertunda']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all border 
               {{ $status === 'Tertunda' ? 'bg-primary text-on-primary border-primary shadow' : 'bg-surface-container-high text-on-surface-variant border-outline-variant hover:text-on-surface' }}">
                Tertunda
            </a>
            <a href="{{ route('risiko.rekomendasi.indeks', ['status' => 'Diselesaikan']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all border 
               {{ $status === 'Diselesaikan' ? 'bg-primary text-on-primary border-primary shadow' : 'bg-surface-container-high text-on-surface-variant border-outline-variant hover:text-on-surface' }}">
                Diselesaikan
            </a>
        </div>
    </div>

    <!-- Recommendations Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
            <thead>
                <tr class="border-b border-outline-variant text-on-surface-variant uppercase tracking-wider text-[10px] font-extrabold">
                    <th class="pb-3 pr-4">Negara Hub</th>
                    <th class="pb-3 px-4">Rekomendasi Mitigasi</th>
                    <th class="pb-3 px-4">Prioritas</th>
                    <th class="pb-3 px-4">Status</th>
                    <th class="pb-3 px-4">Tanggal Dibuat</th>
                    <th class="pb-3 pl-4 text-right">Tindakan / Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/10 text-on-surface font-medium">
                @forelse($rekomendasi as $item)
                    <tr class="hover:bg-surface-container-highest/20 transition-all">
                        <td class="py-4 pr-4 font-bold text-sm text-on-surface">
                            <div class="flex items-center gap-2">
                                <img src="{{ $item->penilaianRisiko->negara->bendera_url }}" class="h-3 w-4.5 object-cover rounded border border-outline-variant" alt="">
                                <span>{{ $item->penilaianRisiko->negara->nama }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-4 leading-relaxed max-w-sm">{{ $item->rekomendasi }}</td>
                        <td class="py-4 px-4">
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase
                                @if($item->prioritas === 'Kritis' || $item->prioritas === 'Tinggi') bg-error-container text-on-error-container border border-error/20
                                @elseif($item->prioritas === 'Sedang') bg-amber-500/20 text-amber-400 border border-amber-500/30
                                @else bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                @endif
                            ">
                                {{ $item->prioritas }}
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase
                                {{ $item->status === 'Diselesaikan' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30' }}">
                                {{ $item->status }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-on-surface-variant font-semibold whitespace-nowrap">
                            {{ date('d M Y, H:i', strtotime($item->created_at)) }}
                        </td>
                        <td class="py-4 pl-4 text-right">
                            @if($item->status === 'Tertunda')
                                @if(Auth::user()->adalahSuperAdmin() || Auth::user()->mempunyaiPeran('pengadaan') || Auth::user()->mempunyaiPeran('admin'))
                                    <!-- Form Penyelesaian Rekomendasi -->
                                    <form action="{{ route('risiko.rekomendasi.tangani', $item->id) }}" method="POST" class="flex items-center justify-end gap-2">
                                        @csrf
                                        <input type="text" name="tindakan_diambil" placeholder="Tindakan yang diambil..." 
                                               class="bg-surface-container-lowest border border-outline-variant rounded-lg px-2.5 py-1.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none placeholder-outline-variant transition-all max-w-[180px] font-semibold" required>
                                        <button type="submit" class="flex items-center gap-1.5 bg-primary hover:opacity-90 text-on-primary font-bold text-xs uppercase tracking-wider px-3 py-1.5 rounded-lg shadow transition-all">
                                            <span class="material-symbols-outlined text-[14px]">done</span>
                                            Selesaikan
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-outline italic">Hanya Manajer Pengadaan</span>
                                @endif
                            @else
                                <div class="text-[11px] text-on-surface-variant text-right leading-relaxed">
                                    <div>Diselesaikan oleh: <strong class="text-on-surface">{{ $item->diselesaikanOleh?->nama ?? 'Sistem' }}</strong></div>
                                    <div class="text-[10px] text-outline">Pada: {{ date('d M Y, H:i', strtotime($item->diselesaikan_pada)) }}</div>
                                    <div class="bg-surface-container-lowest border border-outline-variant/30 px-2 py-1 rounded mt-1 inline-block text-left">
                                        <strong class="text-emerald-400 block text-[9px] uppercase tracking-wider">Tindakan:</strong>
                                        <span class="text-on-surface">{{ $item->tindakan_diambil }}</span>
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-on-surface-variant italic border border-dashed border-outline-variant/50 rounded-lg">
                            Belum ada tindakan mitigasi risiko.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $rekomendasi->appends(['status' => $status])->links() }}
    </div>
</div>
@endsection
