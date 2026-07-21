@extends('layouts.aplikasi')
@section('judul', 'Tambah Pengguna Baru')

@section('konten')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Back Navigation -->
    <div>
        <a href="{{ route('admin.pengguna.indeks') }}" class="flex items-center gap-1 text-xs font-bold text-on-surface-variant hover:text-on-surface transition-colors w-fit">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            Kembali ke Daftar Pengguna
        </a>
    </div>

    <div class="bg-surface-container-low border border-outline-variant rounded-xl p-6 md:p-8 shadow-xl">
        <div class="border-b border-outline-variant/30 pb-4 mb-6">
            <h2 class="font-headline-md text-base font-black text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-primary">person_add</span>
                Tambah Pengguna Baru
            </h2>
            <p class="text-xs text-on-surface-variant mt-1.5 leading-relaxed">
                Daftarkan pengguna baru ke dalam platform SCM dan tentukan peran akses mereka.
            </p>
        </div>

        @if($errors->any())
            <div class="bg-error-container/20 border border-error/20 p-4 rounded-lg mb-6 flex gap-3 text-xs">
                <span class="material-symbols-outlined text-[18px] text-error flex-shrink-0 mt-0.5">error</span>
                <div>
                    <strong class="text-error font-extrabold block mb-1">Terdapat kesalahan input:</strong>
                    <ul class="list-disc list-inside text-on-surface-variant space-y-1 ml-1 font-semibold">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.pengguna.simpan') }}" method="POST" class="space-y-5">
            @csrf
            
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                    Nama Lengkap <span class="text-error">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-outline-variant material-symbols-outlined text-[18px] select-none">badge</span>
                    <input type="text" name="name" class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg pl-10 pr-3.5 py-2.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none placeholder-outline-variant transition-all font-semibold" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso" required>
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                    Alamat Email <span class="text-error">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-outline-variant material-symbols-outlined text-[18px] select-none">mail</span>
                    <input type="email" name="email" class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg pl-10 pr-3.5 py-2.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none placeholder-outline-variant transition-all font-semibold" value="{{ old('email') }}" placeholder="budi@perusahaan.com" required>
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                    Kata Sandi <span class="text-error">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-outline-variant material-symbols-outlined text-[18px] select-none">lock</span>
                    <input type="password" name="password" class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg pl-10 pr-3.5 py-2.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none placeholder-outline-variant transition-all font-semibold" placeholder="••••••••" required>
                </div>
                <small class="text-[10px] text-outline font-semibold mt-1">Minimal 8 karakter, mengandung huruf besar, huruf kecil, dan angka.</small>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                    Peran / Hak Akses <span class="text-error">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-outline-variant material-symbols-outlined text-[18px] select-none pointer-events-none">admin_panel_settings</span>
                    <select name="peran_id" class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg pl-10 pr-3.5 py-2.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none cursor-pointer font-semibold" required>
                        <option value="">-- Pilih Peran --</option>
                        @foreach($semuaPeran as $peran)
                            <option value="{{ $peran->id }}" {{ old('peran_id') == $peran->id ? 'selected' : '' }}>
                                {{ $peran->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <div class="relative inline-flex items-center">
                        <input type="checkbox" name="status" value="1" {{ old('status', '1') ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-9 h-5 bg-surface-container-highest peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-on-surface after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                    </div>
                    <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Akun Aktif <span class="text-outline text-[10px] lowercase normal-case">(langsung dapat login)</span></span>
                </label>
            </div>

            <div class="flex gap-3 pt-4 border-t border-outline-variant/30 mt-6">
                <button type="submit" class="flex items-center gap-2 bg-primary hover:opacity-90 text-on-primary font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-lg shadow-lg transition-all">
                    <span class="material-symbols-outlined text-[16px]">save</span>
                    Simpan Pengguna
                </button>
                <a href="{{ route('admin.pengguna.indeks') }}" class="flex items-center justify-center bg-surface-container-high hover:bg-surface-container-highest border border-outline-variant text-on-surface font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-lg transition-all">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
