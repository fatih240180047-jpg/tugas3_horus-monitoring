@extends('layouts.aplikasi')

@section('judul', 'Pengaturan Pembobotan Parameter Risiko')

@section('konten')
<div class="max-w-2xl mx-auto bg-surface-container-low border border-outline-variant rounded-xl p-6 md:p-8 shadow-xl">
    <div class="border-b border-outline-variant/30 pb-4 mb-6">
        <h2 class="font-headline-md text-base font-black text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px] text-primary">tune</span>
            Bobot Penilaian Tertimbang SCM
        </h2>
        <p class="text-xs text-on-surface-variant mt-1.5 leading-relaxed">
            Tentukan bobot pengaruh (%) untuk masing-masing parameter intelijen dalam kalkulasi skor risiko global.
            <strong class="text-secondary block mt-1">Total seluruh bobot komponen harus berjumlah tepat 100%.</strong>
        </p>
    </div>

    @if($errors->has('total_bobot'))
        <div class="bg-error-container text-on-error-container border border-error/20 px-4 py-3 rounded-lg mb-6 flex items-center gap-2 text-xs">
            <span class="material-symbols-outlined text-[16px]">report</span>
            <span>{{ $errors->first('total_bobot') }}</span>
        </div>
    @endif

    <form action="{{ route('risiko.bobot.simpan') }}" method="POST" id="form-bobot" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach($daftarBobot as $bobot)
                <div class="flex flex-col gap-1.5">
                    <label for="{{ str_replace('.', '_', $bobot->kunci) }}" class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                        {{ $bobot->deskripsi }}
                    </label>
                    <div class="relative">
                        <input type="number" 
                               name="{{ str_replace('.', '_', $bobot->kunci) }}" 
                               id="{{ str_replace('.', '_', $bobot->kunci) }}" 
                               class="input-bobot w-full bg-surface-container-lowest border border-outline-variant rounded-lg px-3.5 py-2.5 text-xs text-on-surface focus:ring-1 focus:ring-primary focus:outline-none placeholder-outline-variant transition-all font-semibold" 
                               value="{{ old(str_replace('.', '_', $bobot->kunci), $bobot->nilai) }}" 
                               min="0" 
                               max="100" 
                               required>
                        <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-outline-variant select-none">%</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="bg-surface-container-lowest border border-dashed border-outline-variant p-4 rounded-lg flex justify-between items-center mt-4">
            <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Total Akumulasi Bobot:</span>
            <span id="indikator-total" class="font-headline-lg text-2xl font-black font-label-sm text-amber-400">100%</span>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="flex items-center gap-2 bg-primary hover:opacity-90 text-on-primary font-bold text-xs uppercase tracking-wider px-5 py-3 rounded-lg shadow-lg transition-all" id="btn-submit">
                <span class="material-symbols-outlined text-[16px]">save</span>
                Simpan Parameter
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
                indikatorTotal.className = "font-headline-lg text-2xl font-black font-label-sm text-emerald-400";
                btnSubmit.disabled = false;
                btnSubmit.style.opacity = '1';
                btnSubmit.style.cursor = 'pointer';
            } else {
                indikatorTotal.className = "font-headline-lg text-2xl font-black font-label-sm text-error";
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
