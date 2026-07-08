@extends('layouts.aplikasi')

@section('judul', 'Tindakan Mitigasi Risiko')

@section('konten')

<div class="card-panel">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; border-bottom: 1px solid var(--warna-charcoal-border); padding-bottom: 16px;">
        <h2 class="card-panel-title" style="margin-bottom: 0; border-bottom: none;">
            <i class="fa-solid fa-hand-holding-hand"></i> Daftar Rekomendasi Mitigasi
        </h2>
        
        <!-- Filter Status -->
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('risiko.rekomendasi.indeks', ['status' => 'Tertunda']) }}" class="btn {{ $status === 'Tertunda' ? 'btn-primer' : 'btn-sekunder' }}" style="padding: 6px 12px; font-size: 13px;">
                Tertunda
            </a>
            <a href="{{ route('risiko.rekomendasi.indeks', ['status' => 'Diselesaikan']) }}" class="btn {{ $status === 'Diselesaikan' ? 'btn-primer' : 'btn-sekunder' }}" style="padding: 6px 12px; font-size: 13px;">
                Diselesaikan
            </a>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table class="tabel-data">
            <thead>
                <tr>
                    <th>Negara</th>
                    <th>Rekomendasi Mitigasi</th>
                    <th>Prioritas</th>
                    <th>Status</th>
                    <th>Tanggal Dibuat</th>
                    <th>Tindakan / Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekomendasi as $item)
                    <tr>
                        <td style="font-weight: 600; white-space: nowrap;">
                            <span style="font-size: 16px; margin-right: 8px;">{{ $item->penilaianRisiko->negara->bendera ?? '🌐' }}</span>
                            {{ $item->penilaianRisiko->negara->nama }}
                        </td>
                        <td>{{ $item->rekomendasi }}</td>
                        <td>
                            <span class="badge badge-{{ strtolower($item->prioritas === 'Kritis' ? 'kritis' : ($item->prioritas === 'Tinggi' ? 'tinggi' : ($item->prioritas === 'Sedang' ? 'sedang' : 'rendah'))) }}">
                                {{ $item->prioritas }}
                            </span>
                        </td>
                        <td>
                            <span class="badge" style="background-color: {{ $item->status === 'Diselesaikan' ? 'rgba(22, 163, 74, 0.2)' : 'rgba(234, 179, 8, 0.2)' }}; border: 1px solid {{ $item->status === 'Diselesaikan' ? '#16a34a' : '#eab308' }}; color: {{ $item->status === 'Diselesaikan' ? '#86efac' : '#fef08a' }}">
                                {{ $item->status }}
                            </span>
                        </td>
                        <td style="white-space: nowrap;">{{ date('d M Y H:i', strtotime($item->created_at)) }}</td>
                        <td>
                            @if($item->status === 'Tertunda')
                                @if(Auth::user()->adalahSuperAdmin() || Auth::user()->mempunyaiPeran('pengadaan') || Auth::user()->mempunyaiPeran('admin'))
                                    <!-- Form Penyelesaian Rekomendasi -->
                                    <form action="{{ route('risiko.rekomendasi.tangani', $item->id) }}" method="POST" style="display: flex; gap: 8px; align-items: center;">
                                        @csrf
                                        <input type="text" name="tindakan_diambil" placeholder="Tindakan yang diambil..." class="form-input" style="padding: 6px 12px; font-size: 13px; max-width: 200px;" required>
                                        <button type="submit" class="btn btn-primer" style="padding: 6px 12px; font-size: 12px;">
                                            <i class="fa-solid fa-check"></i> Selesaikan
                                        </button>
                                    </form>
                                @else
                                    <span style="color: var(--warna-teks-abu); font-style: italic; font-size: 13px;">Hanya Manajer Pengadaan</span>
                                @endif
                            @else
                                <div style="font-size: 12px; color: var(--warna-teks-abu);">
                                    <strong>Diselesaikan oleh:</strong> {{ $item->diselesaikanOleh?->nama ?? 'Sistem' }} <br>
                                    <strong>Pada:</strong> {{ date('d M Y H:i', strtotime($item->diselesaikan_pada)) }} <br>
                                    <strong>Tindakan:</strong> {{ $item->tindakan_diambil }}
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--warna-teks-abu); padding: 24px;">Belum ada tindakan mitigasi risiko.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 24px;">
        {{ $rekomendasi->appends(['status' => $status])->links() }}
    </div>
</div>

@endsection
