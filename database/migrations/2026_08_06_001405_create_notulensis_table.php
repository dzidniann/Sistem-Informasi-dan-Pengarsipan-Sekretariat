<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notulensis', function (Blueprint $table) {
            $table->id();
            $table->string('judul_rapat', 255);
            $table->date('tanggal_rapat');
            $table->string('notulis', 100);
            $table->text('deskripsi_singkat')->nullable();
            $table->string('foto_path')->nullable();
            $table->string('file_path');
            $table->foreignId('bidang_id')->constrained('bidangs');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notulensis');
    }
};
