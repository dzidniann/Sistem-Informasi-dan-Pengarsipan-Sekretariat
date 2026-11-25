<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['super-admin', 'sekretariat', 'staf-bidang'] as $name) {
            \App\Models\Role::firstOrCreate(['nama' => $name]);
        }
    }
}
