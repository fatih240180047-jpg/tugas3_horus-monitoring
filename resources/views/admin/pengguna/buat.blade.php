@extends('layouts.aplikasi')
@section('judul', 'Tambah Pengguna Baru')

@section('konten')
<div style="max-width:680px;margin:0 auto;">
    <div style="margin-bottom:24px;">
        <a href="{{ route('admin.pengguna.indeks') }}" style="color:var(--warna-teks-abu);font-size:13px;text-decoration:none;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Pengguna
        </a>
    </div>

    <div class="card-panel">
        <div class="card-panel-title">
            <i class="fa-solid fa-user-plus" style="color:var(--warna-merah-terang)"></i>
            Tambah Pengguna Baru
        </div>

        @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:20px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div>
                <strong>Terdapat kesalahan input:</strong>
                <ul style="margin-top:6px;padding-left:16px;">
                    @foreach($errors->all() as $err)
                        <li style="font-size:13px;">{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <form action="{{ route('admin.pengguna.simpan') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Nama Lengkap <span style="color:var(--warna-merah-terang)">*</span></label>
                <input type="text" name="name" class="form-input" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso" required>
            </div>
            <div class="form-group">
                <label class="form-label">Alamat Email <span style="color:var(--warna-merah-terang)">*</span></label>
                <input type="email" name="email" class="form-input" value="{{ old('email') }}" placeholder="budi@perusahaan.com" required>
            </div>
            <div class="form-group">
                <label class="form-label">Kata Sandi <span style="color:var(--warna-merah-terang)">*</span></label>
                <input type="password" name="password" class="form-input" placeholder="Minimal 8 karakter, huruf besar, huruf kecil, dan angka" required>
                <small style="color:var(--warna-teks-abu);font-size:12px;margin-top:4px;display:block;">Minimal 8 karakter, mengandung huruf besar, huruf kecil, dan angka.</small>
            </div>
            <div class="form-group">
                <label class="form-label">Peran <span style="color:var(--warna-merah-terang)">*</span></label>
                <select name="peran_id" class="form-input" required>
                    <option value="">-- Pilih Peran --</option>
                    @foreach($semuaPeran as $peran)
                        <option value="{{ $peran->id }}" {{ old('peran_id') == $peran->id ? 'selected' : '' }}>
                            {{ $peran->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="status" value="1" {{ old('status', '1') ? 'checked' : '' }} style="width:16px;height:16px;accent-color:var(--warna-merah);">
                    <span class="form-label" style="margin:0;">Akun Aktif (langsung dapat login)</span>
                </label>
            </div>

            <div style="display:flex;gap:12px;margin-top:24px;">
                <button type="submit" class="btn btn-primer">
                    <i class="fa-solid fa-save"></i> Simpan Pengguna
                </button>
                <a href="{{ route('admin.pengguna.indeks') }}" class="btn btn-sekunder">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
