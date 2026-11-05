<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('humas_csrs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidang_id')->constrained('bidangs')->restrictOnDelete();
            $table->foreignId('kategori_id')->constrained('kategori_arsips')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('pemohon');
            $table->string('nomor_surat')->nullable();
            $table->string('nama_kegiatan');
            $table->date('tanggal_proses')->index();
            $table->text('keluar')->nullable();
            $table->string('nomor_voucher')->nullable();
            $table->string('status', 30);
            $table->string('luaran', 30);
            $table->decimal('nominal', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->index(['bidang_id', 'tanggal_proses']);
        });
        Schema::create('humas_csr_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('humas_csr_id')->constrained('humas_csrs')->cascadeOnDelete();
            $table->string('jenis', 20);
            $table->string('path');
            $table->string('original_name');
            $table->string('mime', 100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('humas_csr_attachments');
        Schema::dropIfExists('humas_csrs');
    }
};
