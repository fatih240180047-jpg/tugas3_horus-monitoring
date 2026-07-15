@extends('layouts.aplikasi')

@section('judul', 'Kelola Pengguna')

@section('gaya_tambahan')
<style>
    .tabel-admin {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
    }
    .tabel-admin th {
        background: rgba(17, 24, 39, 0.9);
        padding: 12px 16px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--warna-teks-abu);
        border-bottom: 1px solid rgba(55, 65, 81, 0.4);
    }
    .tabel-admin td {
        padding: 14px 16px;
        border-bottom: 1px solid rgba(55, 65, 81, 0.2);
        vertical-align: middle;
    }
    .tabel-admin tr:hover td { background: rgba(255,255,255,0.02); }
    .tabel-admin tr:last-child td { border-bottom: none; }

    .avatar-kecil {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #991b1b, #7f1d1d);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
        color: #fff;
        flex-shrink: 0;
        border: 2px solid rgba(239,68,68,0.25);
    }

    .filter-bar {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .filter-bar .form-input {
        max-width: 200px;
        padding: 9px 12px;
    }

    .filter-bar .form-input select { background: rgba(13, 17, 23, 0.8); }

    .aksi-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        border: none;
        transition: all 0.2s ease;
    }
    .aksi-edit { background: rgba(59,130,246,0.12); border: 1px solid rgba(59,130,246,0.3); color: #93c5fd; }
    .aksi-edit:hover { background: rgba(59,130,246,0.22); }
    .aksi-hapus { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #f87171; }
    .aksi-hapus:hover { background: rgba(239,68,68,0.2); }
    .aksi-pulihkan { background: rgba(22,163,74,0.1); border: 1px solid rgba(22,163,74,0.3); color: #86efac; }
    .aksi-pulihkan:hover { background: rgba(22,163,74,0.2); }
</style>
@endsection

@section('konten')
<div class="header-konten" style="margin-bottom:20px;">
    <div>
        <div style="font-size:12px;color:var(--warna-teks-abu);margin-bottom:4px;">Administrasi Sistem</div>
        <div style="font-size:22px;font-weight:700;font-family:'Outfit',sans-serif;">Manajemen Pengguna</div>
    </div>
    <a href="{{ route('admin.pengguna.buat') }}" class="btn btn-primer">
        <i class="fa-solid fa-user-plus"></i> Tambah Pengguna
    </a>
</div>

<!-- Filter Bar -->
<form method="GET" action="{{ route('admin.pengguna.indeks') }}" class="filter-bar">
    <input type="text" name="q" class="form-input" placeholder="Cari nama / email..." value="{{ request('q') }}" style="max-width:220px;">
    <select name="peran" class="form-input" style="max-width:200px;">
        <option value="">-- Semua Peran --</option>
        @foreach($semuaPeran as $peran)
            <option value="{{ $peran->slug }}" {{ request('peran') === $peran->slug ? 'selected' : '' }}>{{ $peran->name }}</option>
        @endforeach
    </select>
    <select name="status" class="form-input" style="max-width:160px;">
        <option value="">-- Semua Status --</option>
        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
        <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
        <option value="dihapus" {{ request('status') === 'dihapus' ? 'selected' : '' }}>Dihapus</option>
    </select>
    <button type="submit" class="btn btn-sekunder"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
    <a href="{{ route('admin.pengguna.indeks') }}" class="btn btn-sekunder" style="border:none;opacity:0.6;">Reset</a>
</form>

<div class="card-panel">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <div class="card-panel-title" style="margin:0;padding:0;border:none;">
            <i class="fa-solid fa-users" style="color:var(--warna-merah-terang)"></i>
            Total {{ $pengguna->total() }} pengguna terdaftar
        </div>
    </div>

    <div style="overflow-x:auto;">
        <table class="tabel-admin">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pengguna</th>
                    <th>Peran</th>
                    <th>Status</th>
                    <th>Login Terakhir</th>
                    <th>Bergabung</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengguna as $p)
                <tr style="{{ $p->trashed() ? 'opacity:0.5;' : '' }}">
                    <td style="color:var(--warna-teks-abu)">{{ $loop->iteration + ($pengguna->currentPage()-1)*$pengguna->perPage() }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div class="avatar-kecil">{{ strtoupper(substr($p->name, 0, 2)) }}</div>
                            <div>
                                <div style="font-weight:600;">{{ $p->name }}
                                    @if(auth()->id() === $p->id)
                                        <span style="font-size:10px;color:var(--warna-teks-abu);font-weight:400;"> (Anda)</span>
                                    @endif
                                </div>
                                <div style="font-size:12px;color:var(--warna-teks-abu);">{{ $p->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @foreach($p->peran as $peran)
                            <span class="badge" style="background:rgba(153,27,27,0.15);border:1px solid rgba(153,27,27,0.4);color:#fca5a5;">{{ $peran->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        @if($p->trashed())
                            <span class="badge badge-kritis">Dihapus</span>
                        @elseif($p->status)
                            <span class="badge badge-rendah">Aktif</span>
                        @else
                            <span class="badge" style="background:rgba(107,114,128,0.15);color:#9ca3af;border:1px solid rgba(107,114,128,0.3);">Nonaktif</span>
                        @endif
                    </td>
                    <td style="color:var(--warna-teks-abu);font-size:12px;">
                        {{ $p->last_login_at ? $p->last_login_at->diffForHumans() : 'Belum pernah' }}
                    </td>
                    <td style="color:var(--warna-teks-abu);font-size:12px;">
                        {{ $p->created_at->format('d M Y') }}
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;align-items:center;">
                            @if(!$p->trashed())
                                <a href="{{ route('admin.pengguna.edit', $p->id) }}" class="aksi-btn aksi-edit">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </a>
                                @if(auth()->id() !== $p->id)
                                <form action="{{ route('admin.pengguna.hapus', $p->id) }}" method="POST"
                                      onsubmit="return confirm('Nonaktifkan pengguna {{ $p->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="aksi-btn aksi-hapus">
                                        <i class="fa-solid fa-user-slash"></i> Nonaktifkan
                                    </button>
                                </form>
                                @endif
                            @else
                                <form action="{{ route('admin.pengguna.pulihkan', $p->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="aksi-btn aksi-pulihkan">
                                        <i class="fa-solid fa-rotate-left"></i> Pulihkan
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:40px;color:var(--warna-teks-abu);">
                        <i class="fa-solid fa-users-slash" style="font-size:32px;margin-bottom:12px;display:block;opacity:0.4;"></i>
                        Tidak ada pengguna yang sesuai filter.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="margin-top:16px;">{{ $pengguna->links() }}</div>
</div>
@endsection
