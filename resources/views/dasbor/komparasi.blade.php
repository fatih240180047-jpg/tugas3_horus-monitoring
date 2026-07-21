@extends('layouts.aplikasi')
@section('judul', 'Country Comparison Engine')
@section('gaya_tambahan')
<style>
[x-cloak] { display: none !important; }
</style>
@endsection

@section('konten')
<div x-data="komparasiEngine()" x-init="init()" class="space-y-6">

    <!-- Header & Selector HUD -->
    <div class="bg-surface-container-low border border-outline-variant rounded-xl p-6 shadow-xl">
        
        <div class="border-b border-outline-variant/30 pb-4 mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-headline-md text-base font-black text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-primary">compare_arrows</span>
                    Country Comparison Engine
                </h2>
                <p class="text-xs text-on-surface-variant mt-1.5 leading-relaxed">
                    Bandingkan parameter risiko, cuaca, ekonomi, dan forex antara dua negara secara head-to-head.
                </p>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-6 items-center w-full">
            <!-- Negara A -->
            <div class="w-full lg:flex-1">
                <label class="text-[10px] text-outline font-extrabold uppercase tracking-widest block mb-2">Negara Hub A</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-outline-variant material-symbols-outlined text-[18px] select-none pointer-events-none">location_on</span>
                    <select x-model="isoA" @change="mulaiKomparasi()" class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg pl-10 pr-3.5 py-3 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none cursor-pointer font-semibold appearance-none">
                        <option value="">-- Pilih Negara A --</option>
                        @foreach($negaraList as $n)
                            <option value="{{ $n->kode_iso }}">{{ $n->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <!-- VS Badge -->
            <div class="flex-shrink-0 flex items-center justify-center -my-2 lg:my-0 lg:pt-6">
                <div class="w-12 h-12 rounded-full bg-primary text-on-primary flex items-center justify-center font-headline-md text-sm font-black shadow-[0_0_15px_rgba(185,28,28,0.5)] border-2 border-surface-container-low z-10 select-none">VS</div>
            </div>

            <!-- Negara B -->
            <div class="w-full lg:flex-1">
                <label class="text-[10px] text-outline font-extrabold uppercase tracking-widest block mb-2">Negara Hub B</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-outline-variant material-symbols-outlined text-[18px] select-none pointer-events-none">location_on</span>
                    <select x-model="isoB" @change="mulaiKomparasi()" class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg pl-10 pr-3.5 py-3 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none cursor-pointer font-semibold appearance-none">
                        <option value="">-- Pilih Negara B --</option>
                        @foreach($negaraList as $n)
                            <option value="{{ $n->kode_iso }}">{{ $n->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="w-full lg:w-auto lg:pt-6">
                <button @click="mulaiKomparasi()" class="w-full flex items-center justify-center gap-1.5 bg-surface-container-high hover:bg-surface-container-highest border border-outline-variant text-on-surface font-bold text-xs uppercase tracking-wider px-6 py-3 rounded-lg shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed" :disabled="isLoading">
                    <span class="material-symbols-outlined text-[16px]" x-show="!isLoading">youtube_searched_for</span>
                    <span class="material-symbols-outlined text-[16px] animate-spin" x-show="isLoading" x-cloak>sync</span>
                    Bandingkan
                </button>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="isLoading" class="text-center py-16 px-6 bg-surface-container-low border border-dashed border-outline-variant rounded-xl" x-cloak>
        <span class="material-symbols-outlined text-4xl text-primary animate-spin mb-3 block">sync</span>
        <div class="text-sm font-bold text-on-surface">Menganalisis data intelijen SCM…</div>
        <div class="text-xs text-on-surface-variant mt-1">Harap tunggu, mesin komparasi sedang bekerja.</div>
    </div>

    <!-- Placeholder State -->
    <div x-show="!hasil && !isLoading" class="text-center py-16 px-6 bg-surface-container-low border border-dashed border-outline-variant rounded-xl" x-cloak>
        <span class="material-symbols-outlined text-5xl text-outline mb-4 block opacity-50">balance</span>
        <div class="font-headline-md text-xl font-bold text-on-surface">Pilih dua negara untuk memulai perbandingan</div>
        <div class="text-xs text-on-surface-variant mt-2 max-w-md mx-auto">Sistem akan membandingkan Indeks Risiko SCM, Data Cuaca, Parameter Makroekonomi, Forex & Kapasitas Pelabuhan secara real-time.</div>
    </div>

    <!-- Hasil Komparasi Grid -->
    <div x-show="hasil && !isLoading" class="space-y-6" x-cloak>

        <!-- Radar Chart Global -->
        <div class="bg-surface-container-low border border-outline-variant rounded-xl p-6 shadow-xl">
            <h3 class="font-headline-md text-sm font-bold text-on-surface flex items-center gap-2 border-b border-outline-variant/30 pb-3 mb-4">
                <span class="material-symbols-outlined text-[18px] text-secondary">radar</span>
                Radar Perbandingan Risiko SCM Multi-Dimensi
            </h3>
            <div class="w-full max-w-2xl mx-auto h-[320px] relative">
                <canvas id="radarChart"></canvas>
            </div>
        </div>

        <!-- Split Panel perbandingan -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Panel Negara A -->
            <div class="bg-surface-container-low border border-outline-variant rounded-xl p-6 shadow-xl border-t-4 border-t-blue-500 transition-all hover:shadow-2xl">
                <!-- Header A -->
                <div class="flex justify-between items-start mb-6">
                    <div class="flex items-center gap-3">
                        <img :src="hasil?.negara_a?.profil?.bendera ? 'https://flagcdn.com/w40/' + hasil.negara_a.profil.bendera + '.png' : ''" class="h-6 w-9 object-cover rounded shadow-sm border border-outline-variant" alt="">
                        <div class="leading-tight">
                            <h4 class="font-headline-md text-lg font-bold text-on-surface" x-text="hasil?.negara_a?.profil?.nama"></h4>
                            <span class="text-[10px] text-on-surface-variant font-semibold uppercase tracking-wider mt-0.5 block" x-text="hasil?.negara_a?.profil?.kawasan"></span>
                        </div>
                    </div>
                    <div x-show="hasil?.negara_a?.risiko" class="text-right">
                        <div class="font-headline-md text-2xl font-black font-label-sm" :class="getTextWarnaRisikoClass(hasil?.negara_a?.risiko?.level)" x-text="hasil?.negara_a?.risiko?.skor + '/100'"></div>
                        <div class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase mt-1 inline-block" :class="getBadgeRisikoClass(hasil?.negara_a?.risiko?.level)" x-text="hasil?.negara_a?.risiko?.level"></div>
                    </div>
                </div>
                
                <!-- Content A -->
                <div x-html="renderDetailNegara(hasil?.negara_a, 'a')" class="space-y-4"></div>
                
                <a :href="'/negara/' + isoA" class="mt-6 flex items-center justify-center gap-1.5 w-full bg-surface-container-high hover:bg-surface-container-highest border border-outline-variant text-on-surface font-bold text-xs uppercase tracking-wider px-4 py-2.5 rounded-lg transition-all shadow">
                    <span class="material-symbols-outlined text-[16px]">travel_explore</span>
                    Laporan SCM Penuh
                </a>
            </div>

            <!-- Panel Negara B -->
            <div class="bg-surface-container-low border border-outline-variant rounded-xl p-6 shadow-xl border-t-4 border-t-red-500 transition-all hover:shadow-2xl">
                <!-- Header B -->
                <div class="flex justify-between items-start mb-6">
                    <div class="flex items-center gap-3">
                        <img :src="hasil?.negara_b?.profil?.bendera ? 'https://flagcdn.com/w40/' + hasil.negara_b.profil.bendera + '.png' : ''" class="h-6 w-9 object-cover rounded shadow-sm border border-outline-variant" alt="">
                        <div class="leading-tight">
                            <h4 class="font-headline-md text-lg font-bold text-on-surface" x-text="hasil?.negara_b?.profil?.nama"></h4>
                            <span class="text-[10px] text-on-surface-variant font-semibold uppercase tracking-wider mt-0.5 block" x-text="hasil?.negara_b?.profil?.kawasan"></span>
                        </div>
                    </div>
                    <div x-show="hasil?.negara_b?.risiko" class="text-right">
                        <div class="font-headline-md text-2xl font-black font-label-sm" :class="getTextWarnaRisikoClass(hasil?.negara_b?.risiko?.level)" x-text="hasil?.negara_b?.risiko?.skor + '/100'"></div>
                        <div class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase mt-1 inline-block" :class="getBadgeRisikoClass(hasil?.negara_b?.risiko?.level)" x-text="hasil?.negara_b?.risiko?.level"></div>
                    </div>
                </div>
                
                <!-- Content B -->
                <div x-html="renderDetailNegara(hasil?.negara_b, 'b')" class="space-y-4"></div>
                
                <a :href="'/negara/' + isoB" class="mt-6 flex items-center justify-center gap-1.5 w-full bg-surface-container-high hover:bg-surface-container-highest border border-outline-variant text-on-surface font-bold text-xs uppercase tracking-wider px-4 py-2.5 rounded-lg transition-all shadow">
                    <span class="material-symbols-outlined text-[16px]">travel_explore</span>
                    Laporan SCM Penuh
                </a>
            </div>
            
        </div>
    </div>
</div>
@endsection

@section('skrip_tambahan')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('komparasiEngine', () => ({
        isoA: '', isoB: '',
        isLoading: false,
        hasil: null,
        radarChartInstance: null,

        init() {},

        async mulaiKomparasi() {
            if (!this.isoA || !this.isoB) return;
            if (this.isoA === this.isoB) { alert('Harap pilih dua negara yang berbeda!'); return; }
            this.isLoading = true;
            this.hasil = null;
            try {
                const res = await fetch('{{ url('/api/komparasi') }}', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}'},
                    body: JSON.stringify({negara_a: this.isoA, negara_b: this.isoB})
                });
                this.hasil = await res.json();
                this.$nextTick(() => this.renderRadar());
            } catch(e) { 
                alert('Gagal mengambil data intelijen dari server.'); 
            } finally { 
                this.isLoading = false; 
            }
        },

        renderRadar() {
            const ctx = document.getElementById('radarChart');
            if (!ctx) return;
            if (this.radarChartInstance) this.radarChartInstance.destroy();
            const a = this.hasil?.negara_a, b = this.hasil?.negara_b;
            this.radarChartInstance = new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: ['Risiko Total SCM', 'Stabilitas Cuaca', 'Kesehatan Ekonomi', 'Stabilitas Forex', 'Sentimen Positif', 'Kapasitas Pelabuhan'],
                    datasets: [
                        { label: a?.profil?.nama, data: this.hitungSkorRadar(a), borderColor:'#3b82f6', backgroundColor:'rgba(59,130,246,0.15)', pointBackgroundColor:'#3b82f6', borderWidth:2 },
                        { label: b?.profil?.nama, data: this.hitungSkorRadar(b), borderColor:'#ef4444', backgroundColor:'rgba(239,68,68,0.15)', pointBackgroundColor:'#ef4444', borderWidth:2 }
                    ]
                },
                options: { 
                    responsive:true, maintainAspectRatio:false, 
                    plugins:{
                        legend:{labels:{color:'#f9fafb', font:{family:'Outfit', size:12, weight:'bold'}}}
                    },
                    scales:{ 
                        r:{ 
                            min:0, max:100, 
                            ticks:{color:'#9ca3af',stepSize:20,backdropColor:'transparent'}, 
                            grid:{color:'rgba(255,255,255,0.1)'}, 
                            pointLabels:{color:'#f9fafb',font:{family:'Outfit', size:11, weight:'bold'}} 
                        } 
                    }
                }
            });
        },

        hitungSkorRadar(n) {
            if (!n) return [0,0,0,0,0,0];
            const risikoInvers = n.risiko ? (100 - (n.risiko.skor ?? 50)) : 50;
            const stabCuaca   = n.cuaca?.angin ? Math.max(0, 100 - n.cuaca.angin) : 60;
            const kesEkonomi  = n.ekonomi?.inflasi ? Math.max(0, 100 - (n.ekonomi.inflasi * 5)) : 50;
            const stabForex   = n.forex?.terkini ? Math.min(100, 100 - Math.min(50, n.forex.terkini / 100)) : 50;
            const pctPos      = n.berita?.total > 0 ? (n.berita.positif / n.berita.total) * 100 : 50;
            const kapPelabuhan= n.pelabuhan?.rerata_kepadatan ? (100 - n.pelabuhan.rerata_kepadatan) : 50;
            return [risikoInvers, stabCuaca, kesEkonomi, stabForex, pctPos, kapPelabuhan].map(v => Math.round(Math.max(0, Math.min(100, v))));
        },

        renderDetailNegara(n, sisi) {
            if (!n) return '';
            const lain = sisi === 'a' ? this.hasil?.negara_b : this.hasil?.negara_a;
            
            const winTag = (valSaya, valLain, lebihBaikTinggi = true) => {
                if (valSaya == null || valLain == null) return '';
                const menang = lebihBaikTinggi ? valSaya > valLain : valSaya < valLain;
                const seri   = valSaya === valLain;
                
                if (seri) return `<span class="px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-surface-container-highest text-on-surface-variant border border-outline-variant">SERI</span>`;
                return menang ? `<span class="px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">✓ UNGGUL</span>` : `<span class="px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-error-container text-on-error-container border border-error/20">✗ LEMAH</span>`;
            };

            const blockTitle = (icon, text, colorClass) => `
                <h4 class="text-[10px] ${colorClass} font-extrabold uppercase tracking-widest flex items-center gap-1.5 border-b border-outline-variant/30 pb-2 mb-2">
                    <span class="material-symbols-outlined text-[14px]">${icon}</span> ${text}
                </h4>
            `;

            const dataRow = (label, value, tag = '') => `
                <div class="flex justify-between items-center text-xs py-1.5 border-b border-outline-variant/10 last:border-b-0">
                    <span class="text-on-surface-variant font-semibold">${label}</span>
                    <span class="text-on-surface font-bold flex items-center gap-2">${value} ${tag}</span>
                </div>
            `;

            let html = '';
            
            // Cuaca
            html += `<div class="bg-surface-container-lowest border border-outline-variant/50 rounded-lg p-4">`;
            html += blockTitle('cloud', 'Stabilitas Cuaca', 'text-blue-400');
            html += dataRow('Suhu Terkini', `${n.cuaca?.suhu ?? 'N/A'}°C`, winTag(n.cuaca?.suhu, lain?.cuaca?.suhu, false));
            html += dataRow('Kecepatan Angin', `${n.cuaca?.angin ?? 'N/A'} km/h`, winTag(n.cuaca?.angin, lain?.cuaca?.angin, false));
            html += `</div>`;
            
            // Ekonomi
            html += `<div class="bg-surface-container-lowest border border-outline-variant/50 rounded-lg p-4">`;
            html += blockTitle('trending_up', 'Makroekonomi', 'text-emerald-400');
            html += dataRow('Laju Inflasi', `${n.ekonomi?.inflasi ?? 'N/A'}%`, winTag(n.ekonomi?.inflasi, lain?.ekonomi?.inflasi, false));
            html += dataRow('PDB (Miliar)', `$${n.ekonomi?.pdb_miliar ?? 'N/A'} B`, winTag(n.ekonomi?.pdb_miliar, lain?.ekonomi?.pdb_miliar, true));
            html += dataRow('Tingkat Pengangguran', `${n.ekonomi?.pengangguran ?? 'N/A'}%`, winTag(n.ekonomi?.pengangguran, lain?.ekonomi?.pengangguran, false));
            html += `</div>`;
            
            // Forex
            html += `<div class="bg-surface-container-lowest border border-outline-variant/50 rounded-lg p-4">`;
            html += blockTitle('payments', `Forex (${n.forex?.mata_uang}/USD)`, 'text-amber-400');
            html += dataRow('Nilai Tukar Terkini', `${Number(n.forex?.terkini ?? 0).toFixed(4)}`);
            html += `</div>`;
            
            // Berita
            html += `<div class="bg-surface-container-lowest border border-outline-variant/50 rounded-lg p-4">`;
            html += blockTitle('radar', 'Radar Sentimen', 'text-on-surface');
            html += dataRow('Berita Berisiko', `<span class="text-error">${n.berita?.kritis ?? 0}</span>`, winTag(n.berita?.kritis, lain?.berita?.kritis, false));
            html += dataRow('Sentimen Positif', `<span class="text-emerald-400">${n.berita?.positif ?? 0}</span>`, winTag(n.berita?.positif, lain?.berita?.positif, true));
            html += `</div>`;

            // Pelabuhan
            html += `<div class="bg-surface-container-lowest border border-outline-variant/50 rounded-lg p-4">`;
            html += blockTitle('directions_boat', `Infrastruktur Maritim (${n.pelabuhan?.total ?? 0})`, 'text-purple-400');
            html += dataRow('Rerata Kepadatan', `${n.pelabuhan?.rerata_kepadatan ?? 'N/A'}%`, winTag(n.pelabuhan?.rerata_kepadatan, lain?.pelabuhan?.rerata_kepadatan, false));
            html += dataRow('Pelabuhan Padat', `<span class="text-error">${n.pelabuhan?.pelabuhan_kritis ?? 0}</span>`, winTag(n.pelabuhan?.pelabuhan_kritis, lain?.pelabuhan?.pelabuhan_kritis, false));
            html += `</div>`;

            return html;
        },

        getTextWarnaRisikoClass(level) {
            return {Kritis:'text-error',Tinggi:'text-orange-500',Sedang:'text-amber-500'}[level] ?? 'text-emerald-500';
        },
        getBadgeRisikoClass(level) {
            const map = {
                Kritis:'bg-error-container text-on-error-container border border-error/20', 
                Tinggi:'bg-orange-500/20 text-orange-400 border border-orange-500/30', 
                Sedang:'bg-amber-500/20 text-amber-400 border border-amber-500/30'
            };
            return map[level] ?? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30';
        }
    }));
});
</script>
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection
