@extends('layouts.aplikasi')

@section('judul', 'Pengaturan Pembobotan Parameter Risiko')

@section('konten')

<div class="card-panel" style="max-width: 800px; margin: 0 auto;">
    <h2 class="card-panel-title">
        <i class="fa-solid fa-sliders"></i> Bobot Penilaian Tertimbang
    </h2>
    <p style="color: var(--warna-teks-abu); font-size: 14px; margin-bottom: 24px; line-height: 1.6;">
        Tentukan bobot pengaruh (%) untuk masing-masing parameter intelijen dalam kalkulasi skor risiko global.
        <strong>Total seluruh bobot komponen harus berjumlah tepat 100%.</strong>
    </p>

    @if($errors->has('total_bobot'))
        <div class="alert alert-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ $errors->first('total_bobot') }}</span>
        </div>
    @endif

    <form action="{{ route('risiko.bobot.simpan') }}" method="POST" id="form-bobot">
        @csrf
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
            @foreach($daftarBobot as $bobot)
                <div class="form-group">
                    <label for="{{ str_replace('.', '_', $bobot->kunci) }}" class="form-label">
                        {{ $bobot->deskripsi }}
                    </label>
                    <div style="position: relative;">
                        <input type="number" 
                               name="{{ str_replace('.', '_', $bobot->kunci) }}" 
                               id="{{ str_replace('.', '_', $bobot->kunci) }}" 
                               class="form-input input-bobot" 
                               value="{{ old(str_replace('.', '_', $bobot->kunci), $bobot->nilai) }}" 
                               min="0" 
                               max="100" 
                               required>
                        <span style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: var(--warna-teks-abu); font-weight: bold;">%</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="background-color: rgba(31, 41, 55, 0.4); padding: 18px 24px; border-radius: 8px; border: 1px dashed var(--warna-charcoal-border); margin-bottom: 28px; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-weight: 600; font-size: 15px; color: var(--warna-teks-abu);">Total Akumulasi Bobot:</span>
            <span id="indikator-total" style="font-family: 'Outfit'; font-size: 24px; font-weight: 800; color: var(--warna-emas-terang);">100%</span>
        </div>

        <div style="text-align: right;">
            <button type="submit" class="btn btn-primer" id="btn-submit">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Parameter
            </button>
        </div>
    </form>
</div>

@endsection

@section('skrip_tambahan')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = document.querySelectorAll('.input-bobot');
        const indikatorTotal = document.getElementById('indikator-total');
        const btnSubmit = document.getElementById('btn-submit');

        function hitungTotal() {
            let total = 0;
            inputs.forEach(input => {
                total += parseInt(input.value) || 0;
            });

            indikatorTotal.textContent = total + '%';

            if (total === 100) {
                indikatorTotal.style.color = '#4ade80'; // Green
                btnSubmit.disabled = false;
                btnSubmit.style.opacity = '1';
                btnSubmit.style.cursor = 'pointer';
            } else {
                indikatorTotal.style.color = '#f87171'; // Red
                btnSubmit.disabled = true;
                btnSubmit.style.opacity = '0.5';
                btnSubmit.style.cursor = 'not-allowed';
            }
        }

        inputs.forEach(input => {
            input.addEventListener('input', hitungTotal);
        });

        // Jalankan kalkulasi pertama kali load
        hitungTotal();
    });
</script>
@endsection
