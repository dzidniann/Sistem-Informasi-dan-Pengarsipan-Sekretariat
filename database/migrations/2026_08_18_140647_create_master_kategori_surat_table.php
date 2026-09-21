<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_kategori_surat', function (Blueprint $table) {
            $table->id('id_kategori');
            $table->string('kode_kategori', 30);
            $table->string('nama_kategori', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_kategori_surat');
    }
};