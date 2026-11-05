<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('legal_governance_documents', function (Blueprint $table) {
            $table->id();
            $table->string('judul_file');
            $table->date('tanggal')->index();
            $table->string('file_path');
            $table->string('original_name');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_governance_documents');
    }
};
