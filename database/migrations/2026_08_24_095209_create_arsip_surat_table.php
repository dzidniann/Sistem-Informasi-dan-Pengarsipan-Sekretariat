<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arsip_surat', function (Blueprint $table) {
            $table->id('id_surat');
            $table->foreignId('id_kategori')
                ->constrained('master_kategori_surat', 'id_kategori');
            $table->unsignedInteger('nomor_urut');
            $table->string('bulan_romawi', 5);
            $table->year('tahun');
            $table->boolean('is_backdate')->default(false);
            $table->string('full_nomor', 100);
            $table->date('tanggal_surat')->nullable();
            $table->string('perihal', 255)->nullable();
            $table->string('tujuan', 255)->nullable();
            $table->text('catatan')->nullable();
            $table->string('file_path')->nullable();
            $table->foreignId('dirilis_oleh')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['id_kategori', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip_surat');
    }
};