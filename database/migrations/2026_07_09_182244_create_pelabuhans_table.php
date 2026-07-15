<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelabuhans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('negara_id');
            $table->foreign('negara_id')->references('id')->on('negara')->onDelete('cascade');
            $table->string('nama', 200);
            $table->string('kode_locode', 10)->nullable()->comment('UN/LOCODE pelabuhan');
            $table->decimal('lintang', 10, 7)->nullable();
            $table->decimal('bujur', 11, 7)->nullable();
            $table->enum('jenis', ['kontainer', 'curah', 'minyak', 'penumpang', 'campuran'])->default('campuran');
            $table->unsignedBigInteger('kapasitas_teu')->nullable()->comment('TEU per tahun untuk pelabuhan kontainer');
            $table->unsignedTinyInteger('tingkat_kepadatan')->default(50)->comment('0-100, 100 = padat parah');
            $table->unsignedTinyInteger('skor_risiko')->default(30)->comment('Risiko operasional pelabuhan 0-100');
            $table->string('operator', 150)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelabuhans');
    }
};
