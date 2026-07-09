<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Negara;
use App\Models\Pengguna;
use App\Services\Implementasi\MesinRisiko;
use Database\Seeders\PeranDanIzinSeeder;
use Database\Seeders\NegaraSeeder;
use Database\Seeders\PengaturanSeeder;

class MesinRisikoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Jalankan seeder yang diperlukan
        $this->seed(PeranDanIzinSeeder::class);
        $this->seed(NegaraSeeder::class);
        $this->seed(PengaturanSeeder::class);
    }

    /**
     * Uji kalkulasi risiko ketika data intelijen belum lengkap.
     */
    public function test_kalkulasi_risiko_tanpa_data_intelijen_akan_menghasilkan_skor_dasar(): void
    {
        $negara = Negara::where('kode_iso', 'IDN')->first();
        
        $mesinRisiko = $this->app->make(MesinRisiko::class);
        $penilaian = $mesinRisiko->hitung($negara);

        // Tanpa data, skor cuaca = 0, ekonomi = 15, nilai tukar = 15, berita = 10, logistik = 15, politik = 15
        // Bobot: Cuaca 20, Ekonomi 25, Nilai Tukar 15, Berita 20, Logistik 10, Politik 10
        // (0*0.2) + (15*0.25) + (15*0.15) + (10*0.2) + (15*0.1) + (15*0.1) = 0 + 3.75 + 2.25 + 2.0 + 1.5 + 1.5 = 11
        
        $this->assertEquals(11.0, $penilaian->skor_total);
        $this->assertEquals('Rendah', $penilaian->level_risiko);
    }

    /**
     * Uji kalkulasi risiko dengan skenario cuaca ekstrem dan krisis ekonomi.
     */
    public function test_kalkulasi_risiko_skenario_kritis(): void
    {
        $negara = Negara::where('kode_iso', 'IDN')->first();

        // Inject data cuaca ekstrem (Curah Hujan > 100, Angin > 60) -> Skor Cuaca = 70
        $negara->catatanCuaca()->create([
            'tanggal_observasi' => date('Y-m-d'),
            'suhu' => 30,
            'kelembaban' => 90,
            'curah_hujan' => 150,
            'kecepatan_angin' => 80,
            'kondisi_cuaca' => 'Badai',
            'sumber_api' => 'Test',
            'dibuat_pada' => now(),
        ]);

        // Inject data ekonomi buruk (Inflasi > 10%, Pengangguran > 12%) -> Skor Ekonomi = 85
        $negara->indikatorEkonomi()->create([
            'tanggal_indikator' => date('Y') . '-01-01',
            'pdb' => 1000000,
            'tingkat_inflasi' => 12.5,
            'tingkat_pengangguran' => 15.0,
            'tingkat_bunga' => 5.0,
            'neraca_perdagangan' => -1000, // defisit (+30)
            'sumber_api' => 'Test',
            'dibuat_pada' => now(),
        ]);

        $mesinRisiko = $this->app->make(MesinRisiko::class);
        $penilaian = $mesinRisiko->hitung($negara);

        // Skor cuaca: 40 (hujan ekstrem) + 30 (angin badai) = 70
        // Skor ekonomi: 15 (base) + 40 (inflasi kritis) + 30 (pengangguran kritis) + 30 (defisit) = 115 -> max 100
        // (70*0.2) + (100*0.25) = 14 + 25 = 39 minimum
        
        $this->assertTrue($penilaian->skor_total > 40.0);
        $this->assertContains($penilaian->level_risiko, ['Sedang', 'Tinggi', 'Kritis']);
    }
}
