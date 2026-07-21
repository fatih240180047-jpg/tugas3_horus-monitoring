@extends('layouts.aplikasi')

@section('judul', 'Detail Analisis Intelijen')

@section('konten')
<div class="space-y-6">
    <!-- Back Navigation -->
    <div>
        <a href="{{ route('analisis.indeks') }}" class="flex items-center gap-1 text-xs font-bold text-on-surface-variant hover:text-on-surface transition-colors w-fit">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            Kembali ke Daftar Analisis
        </a>
    </div>

    <!-- Article Info Card -->
    <div class="bg-surface-container-low border border-outline-variant rounded-xl p-6 shadow-xl flex flex-col gap-4">
        <div class="flex flex-col md:flex-row justify-between items-start gap-4">
            <div class="space-y-2">
                <div class="flex items-center gap-2 flex-wrap text-[10px] text-on-surface-variant font-bold">
                    <span class="px-2 py-0.5 rounded uppercase
                        @if($artikel->keparahan === 'kritis' || $artikel->keparahan === 'tinggi') bg-error-container text-on-error-container border border-error/20
                        @elseif($artikel->keparahan === 'sedang') bg-amber-500/20 text-amber-400 border border-amber-500/30
                        @else bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                        @endif
                    ">
                        Keparahan: {{ $artikel->keparahan }}
                    </span>
                    
                    <span class="px-2 py-0.5 rounded uppercase bg-primary/10 text-primary border border-primary/20">
                        Sentimen: {{ $artikel->sentimen }}
                    </span>
                    
                    @if($artikel->negara)
                        <span class="flex items-center gap-1 bg-surface-container-high border border-outline-variant/30 px-2 py-0.5 rounded">
                            <img src="{{ $artikel->negara->bendera_url }}" class="h-2.5 w-4 object-cover rounded border border-outline-variant">
                            {{ $artikel->negara->nama }}
                        </span>
                    @endif
                </div>

                <h2 class="font-headline-md text-base font-black text-on-surface leading-snug">
                    {{ $artikel->judul }}
                </h2>
                
                @if($artikel->ringkasan)
                    <p class="text-xs text-on-surface-variant leading-relaxed">{{ $artikel->ringkasan }}</p>
                @endif
            </div>

            @if($artikel->url_sumber)
                <a href="{{ $artikel->url_sumber }}" target="_blank" 
                   class="flex items-center gap-1.5 bg-surface-container-high hover:bg-surface-container-highest border border-outline-variant text-on-surface font-bold text-xs uppercase tracking-wider px-3.5 py-2 rounded-lg transition-all flex-shrink-0">
                    <span>Buka Sumber</span>
                    <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                </a>
            @endif
        </div>

        <div class="flex items-center gap-4 flex-wrap text-[11px] text-outline font-semibold border-t border-outline-variant/20 pt-4 mt-2">
            <span class="flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">campaign</span>
                Sumber: {{ $artikel->sumber }}
            </span>
            <span>•</span>
            <span class="flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">calendar_month</span>
                Diterbitkan: {{ \Carbon\Carbon::parse($artikel->diterbitkan_pada)->format('d M Y, H:i') }}
            </span>
            <span>•</span>
            <span class="flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">forum</span>
                {{ $semuaAnalisis->count() }} Analisis Terkumpul
            </span>
        </div>
    </div>

    <!-- Form Analisis (untuk Analis & Pengadaan) -->
    @if($bisaAnalisis)
        <div class="bg-surface-container-low border border-outline-variant rounded-xl p-6 shadow-xl flex flex-col gap-4">
            <div class="border-b border-outline-variant/30 pb-3 flex justify-between items-center">
                <h3 class="font-headline-md text-sm font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-primary">edit_note</span>
                    {{ $analisisSaya ? 'Perbarui Analisis SCM Anda' : 'Tulis Analisis Intelijen Baru' }}
                </h3>
                @if($analisisSaya)
                    <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase
                        @if($analisisSaya->status === 'disetujui') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                        @elseif($analisisSaya->status === 'menunggu_review') bg-amber-500/20 text-amber-400 border border-amber-500/30
                        @else bg-surface-container-highest text-on-surface-variant border border-outline-variant
                        @endif
                    ">
                        {{ $analisisSaya->labelStatus() }}
                    </span>
                @endif
            </div>

            @if($analisisSaya && in_array($analisisSaya->status, ['disetujui']))
                <div class="bg-emerald-500/10 border border-emerald-500/20 px-4 py-3 rounded-lg flex items-center gap-2 text-xs text-emerald-400 font-semibold">
                    <span class="material-symbols-outlined text-[16px]">check_circle</span>
                    <span>Analisis Anda telah disetujui oleh {{ $analisisSaya->penyetuju?->name }}. Data terkunci dan tidak dapat diubah kembali.</span>
                </div>
            @else
                <form action="{{ route('analisis.simpan', $artikel->id) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                            Komentar Analisis Intelijen <span class="text-error">*</span>
                        </label>
                        <textarea name="komentar_analis" rows="4" required
                                  placeholder="Tulis analisis mendalam terhadap artikel ini dari perspektif risiko SCM (minimal 50 karakter)..."
                                  class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-3.5 py-2.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none placeholder-outline-variant transition-all font-semibold leading-relaxed">{{ old('komentar_analis', $analisisSaya?->komentar_analis) }}</textarea>
                        @error('komentar_analis')
                            <div class="text-error text-[11px] font-semibold mt-0.5">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                            Rekomendasi Tindakan Mitigasi
                        </label>
                        <textarea name="rekomendasi_tindakan" rows="3"
                                  placeholder="Langkah-langkah konkret yang disarankan kepada tim pengadaan..."
                                  class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-3.5 py-2.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none placeholder-outline-variant transition-all font-semibold leading-relaxed">{{ old('rekomendasi_tindakan', $analisisSaya?->rekomendasi_tindakan) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Tingkat Kepercayaan</label>
                            <select name="tingkat_kepercayaan" class="bg-surface-container-lowest border border-outline-variant rounded-lg px-3 py-2.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none font-semibold cursor-pointer">
                                @foreach(['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('tingkat_kepercayaan', $analisisSaya?->tingkat_kepercayaan) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Dampak terhadap SCM</label>
                            <select name="dampak_scm" class="bg-surface-container-lowest border border-outline-variant rounded-lg px-3 py-2.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none font-semibold cursor-pointer">
                                @foreach(['tidak_berdampak' => 'Tidak Berdampak', 'minor' => 'Minor', 'signifikan' => 'Signifikan', 'kritis' => 'Kritis'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('dampak_scm', $analisisSaya?->dampak_scm) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if($analisisSaya && $analisisSaya->catatan_reviewer)
                        <div class="bg-error-container/20 border border-error/20 p-4 rounded-lg flex gap-2">
                            <span class="material-symbols-outlined text-[16px] text-error flex-shrink-0 mt-0.5">warning</span>
                            <div class="text-xs text-on-surface-variant leading-relaxed">
                                <strong class="text-error font-extrabold block">Catatan Peninjau / Reviewer:</strong>
                                <span>{{ $analisisSaya->catatan_reviewer }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="flex gap-3 pt-2">
                        <button type="submit" name="kirim_review" value="0" 
                                class="flex items-center gap-1.5 bg-surface-container-high hover:bg-surface-container-highest border border-outline-variant text-on-surface font-bold text-xs uppercase tracking-wider px-4 py-2.5 rounded-lg transition-all">
                            <span class="material-symbols-outlined text-[16px]">draft</span>
                            Simpan Draft
                        </button>
                        <button type="submit" name="kirim_review" value="1" 
                                class="flex items-center gap-1.5 bg-primary hover:opacity-90 text-on-primary font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-lg shadow-lg transition-all">
                            <span class="material-symbols-outlined text-[16px]">send</span>
                            Kirim untuk Review
                        </button>
                    </div>
                </form>
            @endif
        </div>
    @endif

    <!-- Semua Analisis yang Masuk -->
    @if($semuaAnalisis->count() > 0)
        <div class="bg-surface-container-low border border-outline-variant rounded-xl p-6 shadow-xl flex flex-col gap-4">
            <h3 class="font-headline-md text-sm font-bold text-on-surface flex items-center gap-2 border-b border-outline-variant/30 pb-3">
                <span class="material-symbols-outlined text-[18px] text-primary">reviews</span>
                Tanggapan &amp; Analisis Tim SCM ({{ $semuaAnalisis->count() }})
            </h3>

            <div class="space-y-4">
                @foreach($semuaAnalisis->sortByDesc('created_at') as $analisis)
                    <div class="border border-outline-variant rounded-xl p-5 flex flex-col gap-3 relative
                        @if($analisis->status === 'disetujui') bg-emerald-500/5 border-emerald-500/30
                        @elseif($analisis->status === 'menunggu_review') bg-amber-500/5 border-amber-500/30
                        @else bg-surface-container-lowest/50 opacity-80
                        @endif
                    ">
                        <!-- User Card Header -->
                        <div class="flex justify-between items-center gap-4 flex-wrap">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary/20 border border-primary/30 flex items-center justify-center font-bold text-xs text-primary uppercase select-none">
                                    {{ strtoupper(substr($analisis->pengguna->name, 0, 2)) }}
                                </div>
                                <div class="leading-tight">
                                    <h4 class="text-xs font-bold text-on-surface">{{ $analisis->pengguna->name }}</h4>
                                    <span class="text-[10px] text-outline font-semibold uppercase tracking-wider">{{ $analisis->pengguna->peranUtama() }} · {{ $analisis->updated_at->diffForHumans() }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 text-[9px] font-extrabold uppercase">
                                <span class="px-2 py-0.5 rounded
                                    @if($analisis->status === 'disetujui') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                    @elseif($analisis->status === 'menunggu_review') bg-amber-500/20 text-amber-400 border border-amber-500/30
                                    @else bg-surface-container-highest text-on-surface-variant border border-outline-variant
                                    @endif
                                ">
                                    {{ $analisis->labelStatus() }}
                                </span>
                                
                                <span class="px-2 py-0.5 rounded bg-primary/10 text-primary border border-primary/20">
                                    Dampak: {{ $analisis->labelDampakScm() }}
                                </span>
                            </div>
                        </div>

                        <!-- Comments -->
                        <p class="text-xs text-on-surface leading-relaxed font-medium bg-surface-container-lowest border border-outline-variant/30 p-3.5 rounded-lg">
                            {{ $analisis->komentar_analis }}
                        </p>

                        <!-- Mitigation Actions -->
                        @if($analisis->rekomendasi_tindakan)
                            <div class="bg-primary/5 border-l-2 border-primary p-3 rounded-r text-xs text-on-surface-variant">
                                <strong class="text-primary font-extrabold uppercase text-[10px] tracking-wider block mb-1">Rekomendasi Tindakan Mitigasi:</strong>
                                <p class="leading-relaxed">{{ $analisis->rekomendasi_tindakan }}</p>
                            </div>
                        @endif

                        <!-- Resolution audit logs -->
                        @if($analisis->status === 'disetujui')
                            <div class="flex items-center gap-1.5 text-[11px] text-emerald-400 font-semibold pt-1">
                                <span class="material-symbols-outlined text-[14px]">verified</span>
                                <span>Disetujui oleh {{ $analisis->penyetuju?->name }} pada {{ $analisis->disetujui_pada?->format('d M Y, H:i') }}</span>
                            </div>
                        @endif

                        @if($analisis->catatan_reviewer && $analisis->status === 'ditolak')
                            <div class="bg-error-container/10 border border-error/20 p-3 rounded-lg flex gap-2">
                                <span class="material-symbols-outlined text-[14px] text-error mt-0.5">report</span>
                                <div class="text-[11px] text-on-surface-variant">
                                    <strong class="text-error font-extrabold block">Alasan Penolakan:</strong>
                                    <span>{{ $analisis->catatan_reviewer }}</span>
                                </div>
                            </div>
                        @endif

                        <!-- Review Actions for supervisors/reviewers -->
                        @if($bisaReview && $analisis->status === 'menunggu_review')
                            <div class="flex flex-col gap-3 pt-3 border-t border-outline-variant/20 mt-2">
                                <div class="flex gap-2">
                                    <form action="{{ route('analisis.setujui', $analisis->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider px-3.5 py-2 rounded-lg shadow-lg transition-all"
                                                onclick="return confirm('Setujui analisis ini?')">
                                            <span class="material-symbols-outlined text-[16px]">check_circle</span>
                                            Setujui Analisis
                                        </button>
                                    </form>

                                    <button type="button" class="flex items-center gap-1.5 bg-surface-container-high hover:bg-surface-container-highest border border-error/40 text-error font-bold text-xs uppercase tracking-wider px-3.5 py-2 rounded-lg transition-all"
                                            onclick="document.getElementById('tolak-{{ $analisis->id }}').classList.toggle('hidden')">
                                        <span class="material-symbols-outlined text-[16px]">cancel</span>
                                        Tolak
                                    </button>
                                </div>

                                <!-- Collapsible Reject Reason Box -->
                                <div id="tolak-{{ $analisis->id }}" class="hidden">
                                    <form action="{{ route('analisis.tolak', $analisis->id) }}" method="POST" class="space-y-2 max-w-lg">
                                        @csrf
                                        <div class="flex flex-col gap-1.5">
                                            <textarea name="catatan_reviewer" rows="2" required placeholder="Tulis alasan penolakan untuk analis..." 
                                                      class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-3 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none placeholder-outline-variant transition-all font-semibold"></textarea>
                                        </div>
                                        <button type="submit" class="flex items-center gap-1.5 bg-error text-on-error hover:opacity-90 font-bold text-xs uppercase tracking-wider px-3 py-1.5 rounded-lg transition-all">
                                            Kirim Alasan Penolakan
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
