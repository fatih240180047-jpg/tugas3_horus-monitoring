@extends('layouts.aplikasi')
@section('judul', 'Country Comparison Engine')
@section('gaya_tambahan')
<style>
[x-cloak]{display:none!important}
.komparasi-grid{display:grid;grid-template-columns:1fr 60px 1fr;gap:0;min-height:calc(100vh - 200px)}
.panel-negara{background:var(--warna-kaca);border:1px solid var(--warna-charcoal-border);border-radius:12px;padding:24px;overflow-y:auto}
.vs-divider{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:16px;padding:0 8px}
.vs-badge{background:var(--warna-merah);color:#fff;font-family:'Outfit';font-weight:800;font-size:18px;padding:10px 14px;border-radius:50%;width:50px;height:50px;display:flex;align-items:center;justify-content:center}
.select-negara{width:100%;background:rgba(0,0,0,0.3);color:#fff;border:1px solid var(--warna-charcoal-border);padding:12px 16px;border-radius:8px;font-size:14px;font-family:'Inter'}
.select-negara:focus{outline:none;border-color:#ef4444}
.metrik-baris{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.05);font-size:13px}
.metrik-baris:last-child{border-bottom:none}
.modul-judul{font-family:'Outfit';font-size:15px;font-weight:600;padding:12px 0 8px;border-bottom:1px solid rgba(255,255,255,0.1);margin-bottom:12px;display:flex;align-items:center;gap:8px}
.chart-wrap{height:220px;position:relative;margin:12px 0}
.win-badge{font-size:10px;padding:2px 6px;border-radius:3px;font-weight:700}
.win{background:rgba(34,197,94,0.2);color:#22c55e;border:1px solid #22c55e}
.lose{background:rgba(239,68,68,0.2);color:#ef4444;border:1px solid #ef4444}
.tie{background:rgba(156,163,175,0.2);color:#9ca3af;border:1px solid #9ca3af}
</style>
@endsection

@section('konten')
<div x-data="komparasiEngine()" x-init="init()">

    <!-- Header & Selector -->
    <div style="display:flex;gap:16px;align-items:center;margin-bottom:24px;flex-wrap:wrap">
        <div style="flex:1;min-width:220px">
            <label style="font-size:11px;color:#9ca3af;text-transform:uppercase;display:block;margin-bottom:6px">Negara A</label>
            <select class="select-negara" x-model="isoA" @change="mulaiKomparasi()">
                <option value="">-- Pilih Negara A --</option>
                @foreach($negaraList as $n)
                    <option value="{{ $n->kode_iso }}">{{ $n->bendera }} {{ $n->nama }}</option>
                @endforeach
            </select>
        </div>
        <div style="text-align:center;padding-top:20px">
            <div class="vs-badge">VS</div>
        </div>
        <div style="flex:1;min-width:220px">
            <label style="font-size:11px;color:#9ca3af;text-transform:uppercase;display:block;margin-bottom:6px">Negara B</label>
            <select class="select-negara" x-model="isoB" @change="mulaiKomparasi()">
                <option value="">-- Pilih Negara B --</option>
                @foreach($negaraList as $n)
                    <option value="{{ $n->kode_iso }}">{{ $n->bendera }} {{ $n->nama }}</option>
                @endforeach
            </select>
        </div>
        <div style="padding-top:20px">
            <button @click="mulaiKomparasi()" class="btn btn-primer" :disabled="isLoading">
                <i class="fa-solid fa-scale-balanced"></i> Bandingkan
            </button>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="isLoading" style="text-align:center;padding:60px;color:#9ca3af">
        <i class="fa-solid fa-circle-notch fa-spin" style="font-size:36px;color:#ef4444"></i>
        <div style="margin-top:12px">Menganalisis data intelijen SCM…</div>
    </div>

    <!-- Placeholder -->
    <div x-show="!hasil && !isLoading" style="text-align:center;padding:80px 20px;color:#6b7280">
        <i class="fa-solid fa-scale-balanced" style="font-size:48px;margin-bottom:16px;display:block;opacity:0.3"></i>
        <div style="font-size:16px;font-family:'Outfit'">Pilih dua negara untuk memulai perbandingan</div>
        <div style="font-size:13px;margin-top:8px">Sistem akan membandingkan Risiko SCM, Cuaca, Ekonomi, Forex & Pelabuhan secara real-time.</div>
    </div>

    <!-- Hasil Komparasi -->
    <div x-show="hasil && !isLoading" x-cloak>

        <!-- Radar Chart Global -->
        <div style="background:var(--warna-kaca);border:1px solid var(--warna-charcoal-border);border-radius:12px;padding:24px;margin-bottom:24px">
            <div class="modul-judul"><i class="fa-solid fa-spider"></i> Radar Perbandingan Risiko SCM Multi-Dimensi</div>
            <div style="max-width:500px;margin:0 auto;height:300px"><canvas id="radarChart"></canvas></div>
        </div>

        <!-- Split Panel -->
        <div class="komparasi-grid" style="gap:20px;grid-template-columns:1fr 1fr">

            <!-- Negara A -->
            <div class="panel-negara" style="border-top:3px solid #3b82f6">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
                    <div>
                        <div style="font-size:32px" x-text="hasil?.negara_a?.profil?.bendera"></div>
                        <div style="font-family:'Outfit';font-size:20px;font-weight:700" x-text="hasil?.negara_a?.profil?.nama"></div>
                        <div style="font-size:11px;color:#9ca3af" x-text="hasil?.negara_a?.profil?.kawasan"></div>
                    </div>
                    <div x-show="hasil?.negara_a?.risiko" style="text-align:right">
                        <div style="font-size:28px;font-weight:800;font-family:'Outfit'" :style="getWarnaRisiko(hasil?.negara_a?.risiko?.level)" x-text="hasil?.negara_a?.risiko?.skor + '/100'"></div>
                        <div class="badge-dynamic" :style="getBgRisiko(hasil?.negara_a?.risiko?.level)" x-text="hasil?.negara_a?.risiko?.level"></div>
                    </div>
                </div>
                <div x-html="renderDetailNegara(hasil?.negara_a, 'a')"></div>
            </div>

            <!-- Negara B -->
            <div class="panel-negara" style="border-top:3px solid #ef4444">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
                    <div>
                        <div style="font-size:32px" x-text="hasil?.negara_b?.profil?.bendera"></div>
                        <div style="font-family:'Outfit';font-size:20px;font-weight:700" x-text="hasil?.negara_b?.profil?.nama"></div>
                        <div style="font-size:11px;color:#9ca3af" x-text="hasil?.negara_b?.profil?.kawasan"></div>
                    </div>
                    <div x-show="hasil?.negara_b?.risiko" style="text-align:right">
                        <div style="font-size:28px;font-weight:800;font-family:'Outfit'" :style="getWarnaRisiko(hasil?.negara_b?.risiko?.level)" x-text="hasil?.negara_b?.risiko?.skor + '/100'"></div>
                        <div class="badge-dynamic" :style="getBgRisiko(hasil?.negara_b?.risiko?.level)" x-text="hasil?.negara_b?.risiko?.level"></div>
                    </div>
                </div>
                <div x-html="renderDetailNegara(hasil?.negara_b, 'b')"></div>
            </div>
        </div>

        <!-- Tombol Laporan Penuh -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:16px">
            <a :href="'/negara/' + isoA" class="btn btn-sekunder" style="justify-content:center"><i class="fa-solid fa-magnifying-glass-chart"></i> Laporan Penuh: <span x-text="isoA"></span></a>
            <a :href="'/negara/' + isoB" class="btn btn-sekunder" style="justify-content:center"><i class="fa-solid fa-magnifying-glass-chart"></i> Laporan Penuh: <span x-text="isoB"></span></a>
        </div>
    </div>
</div>
@endsection

@section('skrip_tambahan')
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
            if (this.isoA === this.isoB) { alert('Pilih dua negara yang berbeda!'); return; }
            this.isLoading = true;
            this.hasil = null;
            try {
                const res = await fetch('/api/komparasi', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}'},
                    body: JSON.stringify({negara_a: this.isoA, negara_b: this.isoB})
                });
                this.hasil = await res.json();
                this.$nextTick(() => this.renderRadar());
            } catch(e) { alert('Gagal mengambil data.'); }
            finally { this.isLoading = false; }
        },

        renderRadar() {
            const ctx = document.getElementById('radarChart');
            if (!ctx) return;
            if (this.radarChartInstance) this.radarChartInstance.destroy();
            const a = this.hasil?.negara_a, b = this.hasil?.negara_b;
            this.radarChartInstance = new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: ['Risiko Total', 'Stabilitas Cuaca', 'Kesehatan Ekonomi', 'Stabilitas Forex', 'Berita Positif', 'Kapasitas Pelabuhan'],
                    datasets: [
                        { label: a?.profil?.nama, data: this.hitungSkorRadar(a), borderColor:'#3b82f6', backgroundColor:'rgba(59,130,246,0.15)', pointBackgroundColor:'#3b82f6', borderWidth:2 },
                        { label: b?.profil?.nama, data: this.hitungSkorRadar(b), borderColor:'#ef4444', backgroundColor:'rgba(239,68,68,0.15)', pointBackgroundColor:'#ef4444', borderWidth:2 }
                    ]
                },
                options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{labels:{color:'#e5e7eb'}}},
                    scales:{ r:{ min:0, max:100, ticks:{color:'#9ca3af',stepSize:20,backdropColor:'transparent'}, grid:{color:'rgba(255,255,255,0.1)'}, pointLabels:{color:'#e5e7eb',font:{size:11}} } }
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
                if (seri) return `<span class="win-badge tie">SERI</span>`;
                return menang ? `<span class="win-badge win">✓ LEBIH BAIK</span>` : `<span class="win-badge lose">✗ LEBIH LEMAH</span>`;
            };
            let html = '';
            // Cuaca
            html += `<div class="modul-judul" style="color:#60a5fa"><i class="fa-solid fa-cloud-sun"></i> Cuaca</div>`;
            html += `<div class="metrik-baris"><span style="color:#9ca3af">Suhu Terkini</span><span>${n.cuaca?.suhu ?? 'N/A'}°C ${winTag(n.cuaca?.suhu, lain?.cuaca?.suhu, false)}</span></div>`;
            html += `<div class="metrik-baris"><span style="color:#9ca3af">Kecepatan Angin</span><span>${n.cuaca?.angin ?? 'N/A'} km/h ${winTag(n.cuaca?.angin, lain?.cuaca?.angin, false)}</span></div>`;
            // Ekonomi
            html += `<div class="modul-judul" style="color:#34d399;margin-top:16px"><i class="fa-solid fa-chart-line"></i> Ekonomi</div>`;
            html += `<div class="metrik-baris"><span style="color:#9ca3af">Inflasi</span><span>${n.ekonomi?.inflasi ?? 'N/A'}% ${winTag(n.ekonomi?.inflasi, lain?.ekonomi?.inflasi, false)}</span></div>`;
            html += `<div class="metrik-baris"><span style="color:#9ca3af">PDB</span><span>$${n.ekonomi?.pdb_miliar ?? 'N/A'} M ${winTag(n.ekonomi?.pdb_miliar, lain?.ekonomi?.pdb_miliar, true)}</span></div>`;
            html += `<div class="metrik-baris"><span style="color:#9ca3af">Pengangguran</span><span>${n.ekonomi?.pengangguran ?? 'N/A'}% ${winTag(n.ekonomi?.pengangguran, lain?.ekonomi?.pengangguran, false)}</span></div>`;
            // Forex
            html += `<div class="modul-judul" style="color:#fbbf24;margin-top:16px"><i class="fa-solid fa-coins"></i> Forex (${n.forex?.mata_uang}/USD)</div>`;
            html += `<div class="metrik-baris"><span style="color:#9ca3af">Nilai Tukar</span><span>${Number(n.forex?.terkini ?? 0).toFixed(4)}</span></div>`;
            // Berita
            html += `<div class="modul-judul" style="color:#e5e7eb;margin-top:16px"><i class="fa-solid fa-newspaper"></i> Radar Berita</div>`;
            html += `<div class="metrik-baris"><span style="color:#9ca3af">Berita Kritis</span><span style="color:#ef4444">${n.berita?.kritis ?? 0} artikel ${winTag(n.berita?.kritis, lain?.berita?.kritis, false)}</span></div>`;
            html += `<div class="metrik-baris"><span style="color:#9ca3af">Sentimen Positif</span><span style="color:#22c55e">${n.berita?.positif ?? 0} artikel ${winTag(n.berita?.positif, lain?.berita?.positif, true)}</span></div>`;
            // Pelabuhan
            html += `<div class="modul-judul" style="color:#a78bfa;margin-top:16px"><i class="fa-solid fa-anchor"></i> Pelabuhan (${n.pelabuhan?.total ?? 0} pelabuhan)</div>`;
            html += `<div class="metrik-baris"><span style="color:#9ca3af">Rerata Kepadatan</span><span>${n.pelabuhan?.rerata_kepadatan ?? 'N/A'}% ${winTag(n.pelabuhan?.rerata_kepadatan, lain?.pelabuhan?.rerata_kepadatan, false)}</span></div>`;
            html += `<div class="metrik-baris"><span style="color:#9ca3af">Pelabuhan Kritis</span><span style="color:#ef4444">${n.pelabuhan?.pelabuhan_kritis ?? 0} ${winTag(n.pelabuhan?.pelabuhan_kritis, lain?.pelabuhan?.pelabuhan_kritis, false)}</span></div>`;
            // Daftar berita clickable
            if (n.berita?.daftar?.length) {
                html += `<div class="modul-judul" style="margin-top:16px"><i class="fa-solid fa-satellite-dish"></i> Berita Terkini</div>`;
                n.berita.daftar.slice(0,3).forEach(b => {
                    const url = b.url_asli ? `href="${b.url_asli}" target="_blank"` : '';
                    html += `<div style="padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.05)"><a ${url} style="font-size:12px;font-weight:600;color:#f9fafb;text-decoration:none;display:block;line-height:1.4">${b.judul} ${b.url_asli ? '<i class="fa-solid fa-arrow-up-right-from-square" style="font-size:9px;opacity:0.6"></i>' : ''}</a><span style="font-size:10px;color:#9ca3af">${b.sentimen} • ${b.keparahan}</span></div>`;
                });
            }
            return html;
        },

        getWarnaRisiko(level) {
            return {Kritis:'color:#ef4444',Tinggi:'color:#f97316',Sedang:'color:#eab308'}[level] ?? 'color:#22c55e';
        },
        getBgRisiko(level) {
            const map = {Kritis:'background:rgba(220,38,38,0.2);color:#ef4444;border:1px solid #dc2626', Tinggi:'background:rgba(249,115,22,0.2);color:#f97316;border:1px solid #f97316', Sedang:'background:rgba(234,179,8,0.2);color:#eab308;border:1px solid #eab308'};
            return map[level] ?? 'background:rgba(34,197,94,0.2);color:#22c55e;border:1px solid #22c55e';
        }
    }));
});
</script>
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection
