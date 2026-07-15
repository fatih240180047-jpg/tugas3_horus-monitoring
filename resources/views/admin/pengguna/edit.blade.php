@extends('layouts.aplikasi')
@section('judul', 'Edit Pengguna')

@section('konten')
<div style="max-width:680px;margin:0 auto;">
    <div style="margin-bottom:24px;">
        <a href="{{ route('admin.pengguna.indeks') }}" style="color:var(--warna-teks-abu);font-size:13px;text-decoration:none;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Pengguna
        </a>
    </div>

    <div class="card-panel">
        <div class="card-panel-title">
            <i class="fa-solid fa-user-pen" style="color:var(--warna-merah-terang)"></i>
            Edit Pengguna: {{ $pengguna->name }}
        </div>

        @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:20px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div>
                <strong>Terdapat kesalahan:</strong>
                <ul style="margin-top:6px;padding-left:16px;">
                    @foreach($errors->all() as $err)
                        <li style="font-size:13px;">{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <form action="{{ route('admin.pengguna.perbarui', $pengguna->id) }}" method="POST">
            @csrf @method('PUT')

            <div class="form-group">
                <label class="form-label">Nama Lengkap <span style="color:var(--warna-merah-terang)">*</span></label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $pengguna->name) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Alamat Email <span style="color:var(--warna-merah-terang)">*</span></label>
                <input type="email" name="email" class="form-input" value="{{ old('email', $pengguna->email) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Kata Sandi Baru <span style="color:var(--warna-teks-abu);font-weight:400;font-size:12px;">(kosongkan jika tidak ingin mengubah)</span></label>
                <input type="password" name="password" class="form-input" placeholder="Minimal 8 karakter, huruf besar, huruf kecil, dan angka">
            </div>
            <div class="form-group">
                <label class="form-label">Peran <span style="color:var(--warna-merah-terang)">*</span></label>
                <select name="peran_id" class="form-input" required>
                    <option value="">-- Pilih Peran --</option>
                    @foreach($semuaPeran as $peran)
                        <option value="{{ $peran->id }}"
                            {{ old('peran_id', $pengguna->peran->first()?->id) == $peran->id ? 'selected' : '' }}>
                            {{ $peran->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="status" value="1"
                        {{ old('status', $pengguna->status) ? 'checked' : '' }}
                        style="width:16px;height:16px;accent-color:var(--warna-merah);">
                    <span class="form-label" style="margin:0;">Akun Aktif</span>
                </label>
            </div>

            <!-- Info tambahan -->
            <div style="background:rgba(13,17,23,0.5);border:1px solid rgba(55,65,81,0.3);border-radius:8px;padding:14px;margin-bottom:20px;font-size:12.5px;color:var(--warna-teks-abu);">
                <i class="fa-solid fa-clock" style="margin-right:6px;"></i>
                Terdaftar: {{ $pengguna->created_at->format('d M Y H:i') }}
                &nbsp;·&nbsp;
                Login terakhir: {{ $pengguna->last_login_at ? $pengguna->last_login_at->diffForHumans() : 'Belum pernah' }}
            </div>

            <div style="display:flex;gap:12px;">
                <button type="submit" class="btn btn-primer">
                    <i class="fa-solid fa-save"></i> Perbarui Data
                </button>
                <a href="{{ route('admin.pengguna.indeks') }}" class="btn btn-sekunder">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
