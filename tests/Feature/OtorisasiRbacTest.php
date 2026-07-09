<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Pengguna;
use Database\Seeders\PeranDanIzinSeeder;

class OtorisasiRbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Jalankan seeder RBAC
        $this->seed(PeranDanIzinSeeder::class);
    }

    public function test_pengguna_tamu_tidak_bisa_mengakses_dasbor(): void
    {
        $response = $this->get('/dasbor');
        $response->assertRedirect('/masuk');
    }

    public function test_super_admin_bisa_mengakses_pengaturan_bobot_risiko(): void
    {
        $superAdmin = Pengguna::where('email', 'superadmin@horus.local')->first();
        
        $response = $this->actingAs($superAdmin)->get('/risiko/bobot');
        
        $response->assertStatus(200);
        $response->assertSee('Bobot Penilaian Tertimbang');
    }

    public function test_analis_risiko_tidak_bisa_mengakses_pengaturan_bobot(): void
    {
        $analis = Pengguna::where('email', 'analis@horus.local')->first();
        
        $response = $this->actingAs($analis)->get('/risiko/bobot');
        
        $response->assertStatus(403);
    }
}
