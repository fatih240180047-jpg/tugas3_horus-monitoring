@extends('layouts.aplikasi')
@section('judul', 'Detail Analisis Artikel')

@section('gaya_tambahan')
<style>
    .artikel-header {
        background: var(--warna-kaca);
        backdrop-filter: var(--blur-kaca);
        border: 1px solid rgba(55,65,81,0.4);
        border-radius: 14px;
        padding: 28px;
        margin-bottom: 24px;
    }

    .form-analisis {
        background: var(--warna-kaca);
        backdrop-filter: var(--blur-kaca);
        border: 1px solid rgba(55,65,81,0.4);
        border-radius: 14px;
        padding: 28px;
        margin-bottom: 24px;
    }

    .analisis-card {
        background: rgba(13,17,23,0.6);
        border: 1px solid rgba(55,65,81,0.35);
        border-radius: 10px;
        padding: 18px 22px;
        margin-bottom: 14px;
    }

    .analisis-card.disetujui {
        border-color: rgba(22,163,74,0.35);
        background: rgba(22,163,74,0.05);
    }

    .analisis-card.menunggu_review {
        border-color: rgba(249,115,22,0.35);
    }

    .analisis-card.ditolak {
        border-color: rgba(239,68,68,0.25);
        opacity: 0.7;
    }

    .reviewer-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        flex-wrap: wrap;
        gap: 8px;
    }

    .penulis-analisis {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        font-size: 14px;
    }

    .avatar-mini {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: linear-gradient(135deg, #991b1b, #7f1d1d);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        color: #fff;
    }

    .textarea-analisis {
        width: 100%;
        min-height: 140px;
        resize: vertical;
        background: rgba(13,17,23,0.8);
        border: 1px solid rgba(55,65,81,0.6);
        border-radius: 8px;
        color: var(--warna-teks-putih);
        font-size: 14px;
        padding: 12px 14px;
        line-height: 1.6;
        font-family: 'Inter', sans-serif;
        transition: border-color 0.2s ease;
    }

    .textarea-analisis:focus {
        outline: none;
        border-color: var(--warna-merah-terang);
    }

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media (max-width: 640px) { .grid-2 { grid-template-columns: 1fr; } }

    .btn-approve {
        background: linear-gradient(135deg, #166534, #16a34a);
        color: #fff;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 18px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn-approve:hover { box-shadow: 0 4px 14px rgba(22,163,74,0.35); }

    .btn-reject {
        background: transparent;
        border: 1px solid rgba(239,68,68,0.4);
        color: #f87171;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 18px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-reject:hover { background: rgba(239,68,68,0.1); }

    .tolak-form { display: none; margin-top: 12px; }
    .tolak-form.show { display: block; }
</style>
@endsection

@section('konten')
<div style="margin-bottom:20px;">
    <a href="{{ route('analisis.indeks') }}" style="color:var(--warna-teks-abu);font-size:13px;text-decoration:none;">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Analisis
    </a>
</div>

<!-- Header Artikel -->
<div class="artikel-header">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:18px;">
        <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <span class="badge badge-{{ strtolower($artikel->keparahan) }}">{{ $artikel->keparahan }}</span>
                <span class="badge" style="background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.3);color:#a5b4fc;">{{ ucfirst($artikel->sentimen) }}</span>
                <span style="font-size:12px;color:var(--warna-teks-abu);">{{ $artikel->negara?->bendera }} {{ $artikel->negara?->nama }}</span>
            </div>
            <h2 style="font-family:'Outfit',sans-serif;font-size:20px;font-weight:700;line-height:1.4;margin-bottom:10px;">{{ $artikel->judul }}</h2>
            <p style="color:var(--warna-teks-abu);font-size:13.5px;line-height:1.6;">{{ $artikel->ringkasan }}</p>
        </div>
        @if($artikel->url_sumber)
        <a href="{{ $artikel->url_sumber }}" target="_blank" class="btn btn-sekunder" style="flex-shrink:0;font-size:13px;">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> Buka Sumber
        </a>
        @endif
    </div>
    <div style="display:flex;gap:16px;flex-wrap:wrap;font-size:12px;color:var(--warna-teks-abu);border-top:1px solid rgba(55,65,81,0.3);padding-top:14px;">
        <span><i class="fa-solid fa-newspaper" style="margin-right:5px;"></i>{{ $artikel->sumber }}</span>
        <span><i class="fa-regular fa-calendar" style="margin-right:5px;"></i>{{ \Carbon\Carbon::parse($artikel->diterbitkan_pada)->format('d M Y, H:i') }}</span>
        <span><i class="fa-solid fa-comment-dots" style="margin-right:5px;"></i>{{ $semuaAnalisis->count() }} analisis terkumpul</span>
    </div>
</div>

<!-- Form Analisis (untuk Analis & Pengadaan) -->
@if($bisaAnalisis)
<div class="form-analisis">
    <div class="card-panel-title" style="margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(55,65,81,0.3);">
        <i class="fa-solid fa-pen-nib" style="color:var(--warna-merah-terang);"></i>
        {{ $analisisSaya ? 'Perbarui Analisis Anda' : 'Tulis Analisis Intelijen' }}
        @if($analisisSaya)
            <span class="badge {{ $analisisSaya->warnaBadgeStatus() }}" style="margin-left:8px;">{{ $analisisSaya->labelStatus() }}</span>
        @endif
    </div>

    @if($analisisSaya && in_array($analisisSaya->status, ['disetujui']))
        <div class="alert alert-sukses">
            <i class="fa-solid fa-circle-check"></i>
            Analisis Anda telah disetujui oleh {{ $analisisSaya->penyetuju?->name }}. Tidak dapat diedit lagi.
        </div>
    @else
    <form action="{{ route('analisis.simpan', $artikel->id) }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">Komentar Analisis Intelijen <span style="color:var(--warna-merah-terang)">*</span></label>
            <textarea name="komentar_analis" class="textarea-analisis" placeholder="Tulis analisis mendalam terhadap artikel ini dari perspektif risiko SCM (minimal 50 karakter)...">{{ old('komentar_analis', $analisisSaya?->komentar_analis) }}</textarea>
            @error('komentar_analis')<div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Rekomendasi Tindakan Mitigasi</label>
            <textarea name="rekomendasi_tindakan" class="textarea-analisis" style="min-height:90px;" placeholder="Langkah-langkah konkret yang disarankan kepada tim pengadaan...">{{ old('rekomendasi_tindakan', $analisisSaya?->rekomendasi_tindakan) }}</textarea>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Tingkat Kepercayaan</label>
                <select name="tingkat_kepercayaan" class="form-input">
                    @foreach(['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi'] as $val => $label)
                        <option value="{{ $val }}" {{ old('tingkat_kepercayaan', $analisisSaya?->tingkat_kepercayaan) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Dampak terhadap SCM</label>
                <select name="dampak_scm" class="form-input">
                    @foreach(['tidak_berdampak' => 'Tidak Berdampak', 'minor' => 'Minor', 'signifikan' => 'Signifikan', 'kritis' => 'Kritis'] as $val => $label)
                        <option value="{{ $val }}" {{ old('dampak_scm', $analisisSaya?->dampak_scm) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($analisisSaya && $analisisSaya->catatan_reviewer)
        <div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.3);border-radius:8px;padding:14px;margin-bottom:16px;">
            <div style="font-size:12px;font-weight:700;color:#f87171;margin-bottom:6px;"><i class="fa-solid fa-message-exclamation"></i> Catatan Reviewer:</div>
            <div style="font-size:13.5px;color:#fca5a5;">{{ $analisisSaya->catatan_reviewer }}</div>
        </div>
        @endif

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" name="kirim_review" value="0" class="btn btn-sekunder">
                <i class="fa-regular fa-floppy-disk"></i> Simpan Draft
            </button>
            <button type="submit" name="kirim_review" value="1" class="btn btn-primer">
                <i class="fa-solid fa-paper-plane"></i> Kirim untuk Review
            </button>
        </div>
    </form>
    @endif
</div>
@endif

<!-- Semua Analisis yang Masuk -->
@if($semuaAnalisis->count() > 0)
<div class="card-panel">
    <div class="card-panel-title">
        <i class="fa-solid fa-comments" style="color:var(--warna-merah-terang);"></i>
        Semua Analisis ({{ $semuaAnalisis->count() }})
    </div>

    @foreach($semuaAnalisis->sortByDesc('created_at') as $analisis)
    <div class="analisis-card {{ $analisis->status }}">
        <div class="reviewer-header">
            <div class="penulis-analisis">
                <div class="avatar-mini">{{ strtoupper(substr($analisis->pengguna->name, 0, 2)) }}</div>
                <div>
                    <div>{{ $analisis->pengguna->name }}</div>
                    <div style="font-size:11px;font-weight:400;color:var(--warna-teks-abu);">
                        {{ $analisis->pengguna->peranUtama() }} · {{ $analisis->updated_at->diffForHumans() }}
                    </div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <span class="badge {{ $analisis->warnaBadgeStatus() }}">{{ $analisis->labelStatus() }}</span>
                <span class="badge" style="background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.3);color:#a5b4fc;">
                    Dampak: {{ $analisis->labelDampakScm() }}
                </span>
            </div>
        </div>

        <p style="font-size:13.5px;line-height:1.6;color:#e5e7eb;margin-bottom:10px;">{{ $analisis->komentar_analis }}</p>

        @if($analisis->rekomendasi_tindakan)
        <div style="background:rgba(99,102,241,0.05);border-left:3px solid rgba(99,102,241,0.4);padding:10px 14px;border-radius:0 6px 6px 0;margin-bottom:10px;">
            <div style="font-size:11px;font-weight:700;color:#a5b4fc;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">Rekomendasi Mitigasi</div>
            <div style="font-size:13px;color:#e5e7eb;line-height:1.5;">{{ $analisis->rekomendasi_tindakan }}</div>
        </div>
        @endif

        @if($analisis->status === 'disetujui')
        <div style="font-size:12px;color:#86efac;margin-top:8px;">
            <i class="fa-solid fa-circle-check"></i>
            Disetujui oleh {{ $analisis->penyetuju?->name }} pada {{ $analisis->disetujui_pada?->format('d M Y H:i') }}
        </div>
        @endif

        @if($analisis->catatan_reviewer && $analisis->status === 'ditolak')
        <div style="background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.25);border-radius:6px;padding:10px 14px;margin-top:8px;">
            <span style="font-size:11px;color:#f87171;font-weight:700;">Alasan Penolakan: </span>
            <span style="font-size:13px;color:#fca5a5;">{{ $analisis->catatan_reviewer }}</span>
        </div>
        @endif

        <!-- Tombol Approve/Reject untuk Reviewer -->
        @if($bisaReview && $analisis->status === 'menunggu_review')
        <div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap;align-items:flex-start;">
            <form action="{{ route('analisis.setujui', $analisis->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn-approve" onclick="return confirm('Setujui analisis ini?')">
                    <i class="fa-solid fa-circle-check"></i> Setujui
                </button>
            </form>

            <div>
                <button type="button" class="btn-reject" onclick="document.getElementById('tolak-{{ $analisis->id }}').classList.toggle('show')">
                    <i class="fa-solid fa-circle-xmark"></i> Tolak
                </button>
                <div id="tolak-{{ $analisis->id }}" class="tolak-form">
                    <form action="{{ route('analisis.tolak', $analisis->id) }}" method="POST">
                        @csrf
                        <textarea name="catatan_reviewer" class="textarea-analisis" style="min-height:70px;" placeholder="Tulis alasan penolakan untuk analis..." required></textarea>
                        <button type="submit" class="btn-reject" style="margin-top:8px;">Kirim Penolakan</button>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endforeach
</div>
@endif
@endsection
