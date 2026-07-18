<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class GenerateApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'horus:api-token {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Sanctum API Token for a user (Super Admin by default)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'superadmin@horus.local';
        $user = \App\Models\Pengguna::where('email', $email)->first();

        if (!$user) {
            $this->error("Pengguna dengan email {$email} tidak ditemukan.");
            return;
        }

        // Hapus token sebelumnya jika mau bersih (opsional)
        // $user->tokens()->delete();

        $tokenName = 'Token Integrasi Eksternal (' . now()->format('Y-m-d') . ')';
        $token = $user->createToken($tokenName);

        $this->info("✅ Berhasil membuat token otentikasi API untuk {$user->name}!");
        $this->warn("Simpan token ini baik-baik. Token ini tidak akan dimunculkan lagi:");
        $this->line("");
        $this->line($token->plainTextToken);
        $this->line("");
        $this->info("Gunakan token ini pada header Authorization: Bearer <TOKEN>");
    }
}
