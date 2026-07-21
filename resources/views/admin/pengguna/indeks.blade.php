@extends('layouts.aplikasi')

@section('judul', 'Kelola Pengguna')

@section('konten')
<!-- Header Area -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div>
        <span class="text-[10px] text-outline font-bold uppercase tracking-widest block mb-0.5">Administrasi Sistem</span>
        <h2 class="font-headline-md text-base font-black text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px] text-primary">manage_accounts</span>
            Manajemen Pengguna Platform
        </h2>
    </div>
    
    <a href="{{ route('admin.pengguna.buat') }}" 
       class="flex items-center gap-1.5 bg-primary hover:opacity-90 text-on-primary font-bold text-xs uppercase tracking-wider px-4 py-2.5 rounded-lg shadow-lg transition-all">
        <span class="material-symbols-outlined text-[16px]">person_add</span>
        Tambah Pengguna
    </a>
</div>

<!-- Filters Bar -->
<form method="GET" action="{{ route('admin.pengguna.indeks') }}" class="flex flex-wrap gap-3 mb-6 bg-surface-container-low border border-outline-variant p-4 rounded-xl shadow-md items-center">
    <div class="flex flex-col gap-1.5">
        <input type="text" name="q" placeholder="Cari nama / email..." value="{{ request('q') }}"
               class="bg-surface-container-lowest border border-outline-variant rounded-lg px-3 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none placeholder-outline-variant font-semibold w-52">
    </div>
    
    <div class="flex flex-col gap-1.5">
        <select name="peran" class="bg-surface-container-lowest border border-outline-variant rounded-lg px-3 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none font-semibold cursor-pointer w-48">
            <option value="">-- Semua Peran --</option>
            @foreach($semuaPeran as $peran)
                <option value="{{ $peran->slug }}" {{ request('peran') === $peran->slug ? 'selected' : '' }}>{{ $peran->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex flex-col gap-1.5">
        <select name="status" class="bg-surface-container-lowest border border-outline-variant rounded-lg px-3 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none font-semibold cursor-pointer w-40">
            <option value="">-- Semua Status --</option>
            <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            <option value="dihapus" {{ request('status') === 'dihapus' ? 'selected' : '' }}>Dihapus</option>
        </select>
    </div>

    <div class="flex gap-2">
        <button type="submit" class="flex items-center gap-1.5 bg-surface-container-high hover:bg-surface-container-highest border border-outline-variant text-on-surface font-bold text-xs uppercase tracking-wider px-4 py-2 rounded-lg transition-all shadow">
            <span class="material-symbols-outlined text-[15px]">search</span>
            Filter
        </button>
        <a href="{{ route('admin.pengguna.indeks') }}" 
           class="flex items-center justify-center border border-transparent text-on-surface-variant hover:text-on-surface font-bold text-xs uppercase tracking-wider px-3 py-2 rounded-lg transition-all">
            Reset
        </a>
    </div>
</form>

<!-- Users List Table Card -->
<div class="bg-surface-container-low border border-outline-variant rounded-xl p-6 shadow-xl flex flex-col gap-4">
    <div class="border-b border-outline-variant/30 pb-3 flex justify-between items-center">
        <h3 class="font-headline-md text-xs font-bold text-on-surface-variant flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">group</span>
            Total Terdaftar: <span class="text-on-surface font-extrabold font-mono">{{ $pengguna->total() }}</span> Pengguna
        </h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
            <thead>
                <tr class="border-b border-outline-variant text-on-surface-variant uppercase tracking-wider text-[10px] font-extrabold">
                    <th class="pb-3 pr-4">#</th>
                    <th class="pb-3 px-4">Pengguna</th>
                    <th class="pb-3 px-4">Peran Utama</th>
                    <th class="pb-3 px-4">Status</th>
                    <th class="pb-3 px-4">Login Terakhir</th>
                    <th class="pb-3 px-4">Bergabung</th>
                    <th class="pb-3 pl-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/10 text-on-surface font-medium">
                @forelse($pengguna as $p)
                    <tr class="hover:bg-surface-container-highest/20 transition-all {{ $p->trashed() ? 'opacity-50' : '' }}">
                        <td class="py-3.5 pr-4 text-outline font-bold font-mono">
                            {{ $loop->iteration + ($pengguna->currentPage()-1)*$pengguna->perPage() }}
                        </td>
                        <td class="py-3.5 px-4 font-bold text-sm text-on-surface">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary/20 border border-primary/30 flex items-center justify-center font-bold text-xs text-primary uppercase select-none flex-shrink-0">
                                    {{ strtoupper(substr($p->name, 0, 2)) }}
                                </div>
                                <div class="leading-tight">
                                    <h4 class="text-xs font-bold text-on-surface flex items-center gap-1.5">
                                        <span>{{ $p->name }}</span>
                                        @if(auth()->id() === $p->id)
                                            <span class="bg-primary/10 border border-primary/20 text-primary text-[8px] font-extrabold uppercase px-1 py-0.25 rounded">Anda</span>
                                        @endif
                                    </h4>
                                    <span class="text-[10px] text-outline font-semibold font-mono">{{ $p->email }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($p->peran as $peran)
                                    <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase bg-primary/10 text-primary border border-primary/20">
                                        {{ $peran->name }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($p->trashed())
                                <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase bg-error-container text-on-error-container border border-error/20">
                                    Dihapus
                                </span>
                            @elseif($p->status)
                                <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                    Aktif
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase bg-surface-container-highest text-on-surface-variant border border-outline-variant">
                                    Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-on-surface-variant font-semibold whitespace-nowrap">
                            {{ $p->last_login_at ? $p->last_login_at->diffForHumans() : 'Belum pernah' }}
                        </td>
                        <td class="py-3.5 px-4 text-on-surface-variant font-semibold whitespace-nowrap">
                            {{ $p->created_at->format('d M Y') }}
                        </td>
                        <td class="py-3.5 pl-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if(!$p->trashed())
                                    <a href="{{ route('admin.pengguna.edit', $p->id) }}" 
                                       class="flex items-center gap-1 bg-surface-container-high hover:bg-surface-container-highest border border-outline-variant text-on-surface font-bold text-[10px] uppercase tracking-wider px-2.5 py-1.5 rounded-lg transition-all shadow-sm">
                                        <span class="material-symbols-outlined text-[13px]">edit</span>
                                        Edit
                                    </a>
                                    
                                    @if(auth()->id() !== $p->id)
                                        <form action="{{ route('admin.pengguna.hapus', $p->id) }}" method="POST"
                                              onsubmit="return confirm('Nonaktifkan pengguna {{ $p->name }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" 
                                                    class="flex items-center gap-1 bg-error-container/10 border border-error/20 hover:bg-error-container/20 text-error font-bold text-[10px] uppercase tracking-wider px-2.5 py-1.5 rounded-lg transition-all">
                                                <span class="material-symbols-outlined text-[13px]">block</span>
                                                Nonaktif
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <form action="{{ route('admin.pengguna.pulihkan', $p->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" 
                                                class="flex items-center gap-1 bg-emerald-500/10 border border-emerald-500/20 hover:bg-emerald-500/20 text-emerald-400 font-bold text-[10px] uppercase tracking-wider px-2.5 py-1.5 rounded-lg transition-all">
                                            <span class="material-symbols-outlined text-[13px]">restore</span>
                                            Pulihkan
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-on-surface-variant italic border border-dashed border-outline-variant/50 rounded-lg">
                            Tidak ada pengguna yang sesuai filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $pengguna->links() }}
    </div>
</div>
@endsection
