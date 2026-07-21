@extends('layouts.aplikasi')
@section('judul', 'Edit Pengguna')

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
        <div class="border-b border-outline-variant/30 pb-4 mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-headline-md text-base font-black text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-primary">manage_accounts</span>
                    Edit Pengguna: {{ $pengguna->name }}
                </h2>
                <p class="text-xs text-on-surface-variant mt-1.5 leading-relaxed">
                    Perbarui profil, kata sandi, peran, atau status aktif pengguna ini.
                </p>
            </div>
            
            <div class="flex items-center gap-3 bg-surface-container-highest/50 px-3 py-2 rounded-lg border border-outline-variant/50">
                <div class="w-10 h-10 rounded-full bg-primary/20 border border-primary/30 flex items-center justify-center font-bold text-sm text-primary uppercase select-none flex-shrink-0 shadow-inner">
                    {{ strtoupper(substr($pengguna->name, 0, 2)) }}
                </div>
                <div class="leading-tight pr-2">
                    <div class="text-[10px] text-outline font-extrabold uppercase tracking-wider">Peran Saat Ini</div>
                    <div class="text-xs font-bold text-on-surface">{{ $pengguna->peran->first()?->name ?? 'Belum ada peran' }}</div>
                </div>
            </div>
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

        <form action="{{ route('admin.pengguna.perbarui', $pengguna->id) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')
            
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                    Nama Lengkap <span class="text-error">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-outline-variant material-symbols-outlined text-[18px] select-none">badge</span>
                    <input type="text" name="name" class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg pl-10 pr-3.5 py-2.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none placeholder-outline-variant transition-all font-semibold" value="{{ old('name', $pengguna->name) }}" required>
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                    Alamat Email <span class="text-error">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-outline-variant material-symbols-outlined text-[18px] select-none">mail</span>
                    <input type="email" name="email" class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg pl-10 pr-3.5 py-2.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none placeholder-outline-variant transition-all font-semibold" value="{{ old('email', $pengguna->email) }}" required>
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-bold text-on-surface-variant uppercase tracking-wider flex items-center justify-between">
                    <span>Kata Sandi Baru</span>
                    <span class="text-outline text-[9px] lowercase normal-case italic font-medium">(Kosongkan jika tidak ingin diubah)</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-outline-variant material-symbols-outlined text-[18px] select-none">lock_reset</span>
                    <input type="password" name="password" class="w-full bg-surface-container-lowest border border-outline-variant rounded-lg pl-10 pr-3.5 py-2.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none placeholder-outline-variant transition-all font-semibold" placeholder="Minimal 8 karakter, huruf besar, huruf kecil, dan angka">
                </div>
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
                            <option value="{{ $peran->id }}"
                                {{ old('peran_id', $pengguna->peran->first()?->id) == $peran->id ? 'selected' : '' }}>
                                {{ $peran->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <div class="relative inline-flex items-center">
                        <input type="checkbox" name="status" value="1" {{ old('status', $pengguna->status) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-9 h-5 bg-surface-container-highest peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-on-surface after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                    </div>
                    <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Akun Aktif</span>
                </label>
            </div>
            
            <!-- Audit Info -->
            <div class="bg-surface-container-highest/20 border border-outline-variant/30 rounded-lg p-3.5 flex flex-col md:flex-row gap-4 justify-between mt-2">
                <div class="flex items-center gap-2 text-[11px] text-outline font-semibold">
                    <span class="material-symbols-outlined text-[14px]">how_to_reg</span>
                    Terdaftar: {{ $pengguna->created_at->format('d M Y, H:i') }}
                </div>
                <div class="flex items-center gap-2 text-[11px] text-outline font-semibold">
                    <span class="material-symbols-outlined text-[14px]">login</span>
                    Login terakhir: {{ $pengguna->last_login_at ? $pengguna->last_login_at->diffForHumans() : 'Belum pernah' }}
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-outline-variant/30 mt-6">
                <button type="submit" class="flex items-center gap-2 bg-primary hover:opacity-90 text-on-primary font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-lg shadow-lg transition-all">
                    <span class="material-symbols-outlined text-[16px]">save</span>
                    Perbarui Data
                </button>
                <a href="{{ route('admin.pengguna.indeks') }}" class="flex items-center justify-center bg-surface-container-high hover:bg-surface-container-highest border border-outline-variant text-on-surface font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-lg transition-all">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
