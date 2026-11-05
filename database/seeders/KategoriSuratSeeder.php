<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSuratSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('master_kategori_surat')->insert([
            [
                'kode_kategori' => 'UM.01.01',
                'nama_kategori' => 'Urusan Umum',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_kategori' => 'SK',
                'nama_kategori' => 'Surat Keputusan',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_kategori' => 'SU',
                'nama_kategori' => 'Surat Undangan',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // tambahkan 11 kategori lainnya sesuai daftar instansi kamu
        ]);
    }
}
