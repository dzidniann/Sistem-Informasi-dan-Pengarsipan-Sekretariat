<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\Role;
use Illuminate\Database\Seeder;

class HumasSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['nama' => 'humas']);
        Bidang::firstOrCreate(['nama_bidang' => 'Humas'], ['akses_semua_bidang' => false]);
        $this->call(HumasCsrTemplateSeeder::class);
    }
}
