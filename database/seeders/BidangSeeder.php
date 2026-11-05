<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BidangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('bidangs')->insert([
            [
                'nama_bidang' => 'Manajemen Sistem',
                'akses_semua_bidang' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_bidang' => 'Sekretariat dan Pengarsipan',
                'akses_semua_bidang' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_bidang' => 'Kepegawaian',
                'akses_semua_bidang' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
