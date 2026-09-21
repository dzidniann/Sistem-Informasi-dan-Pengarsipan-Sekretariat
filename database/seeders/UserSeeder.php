<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Bidang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Role::where('nama', 'super-admin')->first();
        $sekretariat = Role::where('nama', 'sekretariat')->first();
        $stafBidang = Role::where('nama', 'staf-bidang')->first();

        $bidangManajemen = Bidang::where('nama_bidang', 'Manajemen Sistem')->first();
        $bidangSekretariat = Bidang::where('nama_bidang', 'Sekretariat dan Pengarsipan')->first();
        $bidangKepegawaian = Bidang::where('nama_bidang', 'Kepegawaian')->first();

        User::create([
            'name' => 'Admin Utama',
            'email' => 'admin@instansi.go.id',
            'password' => Hash::make('password123'),
            'role_id' => $superAdmin->id,
            'bidang_id' => $bidangManajemen->id,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Rina Sekretariat',
            'email' => 'rina@instansi.go.id',
            'password' => Hash::make('password123'),
            'role_id' => $sekretariat->id,
            'bidang_id' => $bidangSekretariat->id,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Budi Staf Kepegawaian',
            'email' => 'budi@instansi.go.id',
            'password' => Hash::make('password123'),
            'role_id' => $stafBidang->id,
            'bidang_id' => $bidangKepegawaian->id,
            'is_active' => true,
        ]);
    }
}