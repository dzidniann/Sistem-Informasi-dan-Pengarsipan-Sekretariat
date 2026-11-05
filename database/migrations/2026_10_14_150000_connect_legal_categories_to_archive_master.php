<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('legal_categories', function (Blueprint $table) {
            $table
                ->foreignId('kategori_arsip_id')
                ->nullable()
                ->unique()
                ->constrained('kategori_arsips')
                ->cascadeOnDelete();
        });

        DB::transaction(function () {
            // Remove only untouched seed entries without documents; retain historical references.
            foreach (
                [
                    'Peraturan Perusahaan' => 'Dokumen peraturan internal perusahaan',
                    'Kontrak & Perjanjian' => 'Dokumen kontrak kerja dan perjanjian bisnis',
                    'Surat Keputusan' => 'Surat keputusan resmi perusahaan',
                    'Peraturan Daerah' => 'Peraturan daerah terkait operasional',
                    'Dokumen Perizinan' => 'Dokumen izin dan lisensi',
                    'Dokumen Hukum Lainnya' => 'Kategori umum dokumen hukum',
                ] as $name => $description
            ) {
                DB::table('legal_categories')
                    ->where('name', $name)
                    ->where('description', $description)
                    ->whereNull('kategori_arsip_id')
                    ->whereNotIn('id', DB::table('legal_documents')->select('category_id'))
                    ->delete();
            }
            $masters = DB::table('kategori_arsips')
                ->whereIn('bidang_id', DB::table('bidangs')->where('nama_bidang', 'Divisi Hukum')->select('id'))
                ->get();
            foreach ($masters as $master) {
                DB::table('legal_categories')->insert([
                    'kategori_arsip_id' => $master->id,
                    'name' => $master->nama,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('legal_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kategori_arsip_id');
        });
        // Deleted unused defaults are deliberately not recreated.
    }
};
