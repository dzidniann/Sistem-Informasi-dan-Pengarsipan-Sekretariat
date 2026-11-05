<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        DB::table('bidangs')
            ->where('nama_bidang', 'Divisi Hukum')
            ->update(['nama_bidang' => 'Sub Bidang Hukum']);
    }

    public function down(): void
    {
        DB::table('bidangs')
            ->where('nama_bidang', 'Sub Bidang Hukum')
            ->update(['nama_bidang' => 'Divisi Hukum']);
    }
};
