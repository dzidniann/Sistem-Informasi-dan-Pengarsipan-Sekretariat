<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('legal_activities', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_kegiatan')->index();
            $table->string('uraian_kegiatan');
            $table->foreignId('legal_document_id')->nullable()->constrained()->nullOnDelete();
            $table->text('keterangan')->nullable();
            $table->string('dokumen_pendukung')->nullable();
            $table->string('dokumen_pendukung_name')->nullable();
            $table->string('foto_dokumentasi')->nullable();
            $table->string('foto_dokumentasi_name')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
        Schema::create('legal_notulensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_activity_id')->constrained()->restrictOnDelete();
            $table->foreignId('legal_document_id')->nullable()->constrained()->nullOnDelete();
            $table->string('judul_rapat');
            $table->date('tanggal_rapat')->index();
            $table->string('notulis', 100);
            $table->text('deskripsi_singkat')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_path_name')->nullable();
            $table->string('foto_path')->nullable();
            $table->string('foto_path_name')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_notulensis');
        Schema::dropIfExists('legal_activities');
    }
};
