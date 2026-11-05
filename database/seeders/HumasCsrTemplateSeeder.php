<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\HumasCsr;
use App\Models\KategoriArsip;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HumasCsrTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $bidang = Bidang::where('nama_bidang', 'Humas')->first();
        if (! $bidang) {
            return;
        }

        DB::transaction(function () use ($bidang) {
            foreach (HumasCsr::CATEGORIES as $name) {
                $category = KategoriArsip::where('bidang_id', $bidang->id)
                    ->whereRaw('UPPER(nama) = ?', [$name])
                    ->first();
                if ($category) {
                    if ($category->nama !== $name) {
                        $category->update(['nama' => $name]);
                    }
                } else {
                    KategoriArsip::create(['bidang_id' => $bidang->id, 'nama' => $name]);
                }
            }
        });
    }
}
