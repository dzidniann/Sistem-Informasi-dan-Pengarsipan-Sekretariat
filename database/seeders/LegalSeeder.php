<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Bidang;
use App\Models\User;

class LegalSeeder extends Seeder
{
    /**
     * Seed data untuk modul Legal Management System.
     *
     * Menambahkan:
     * - 2 role baru: legal-admin, staf-bidang
     * - 1 bidang baru: Sub Bidang Hukum
     * Kategori dokumen diisi admin melalui Kategori Arsip bidang Sub Bidang Hukum.
     * - User sampel legal-admin dan staf-bidang
     */
    public function run(): void
    {
        // ===== Tambah Role Baru =====
        $existingRoles = Role::pluck('nama')->toArray();

        if (!in_array('legal-admin', $existingRoles)) {
            DB::table('roles')->insert([
                'nama' => 'legal-admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!in_array('staf-bidang', $existingRoles)) {
            DB::table('roles')->insert([
                'nama' => 'staf-bidang',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ===== Tambah Bidang Baru =====
        $existingBidang = Bidang::whereIn('nama_bidang', ['Sub Bidang Hukum', 'Divisi Hukum'])->first();

        if (!$existingBidang) {
            DB::table('bidangs')->insert([
                'nama_bidang' => 'Sub Bidang Hukum',
                'akses_semua_bidang' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Bidang Umum untuk staf-bidang default
        $existingUmum = Bidang::where('nama_bidang', 'Umum')->first();

        if (!$existingUmum) {
            DB::table('bidangs')->insert([
                'nama_bidang' => 'Umum',
                'akses_semua_bidang' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ===== User Sampel =====
        $legalAdmin = Role::where('nama', 'legal-admin')->first();
        $staffRole = Role::where('nama', 'staf-bidang')->first();
        $bidangHukum = Bidang::whereIn('nama_bidang', ['Sub Bidang Hukum', 'Divisi Hukum'])->first();
        $bidangUmum = Bidang::where('nama_bidang', 'Umum')->first();

        if ($legalAdmin && $bidangHukum) {
            $existingLegalUser = User::where('email', 'legal@instansi.go.id')->first();
            if (!$existingLegalUser) {
                User::create([
                    'name' => 'Admin Hukum',
                    'email' => 'legal@instansi.go.id',
                    'password' => Hash::make('password123'),
                    'role_id' => $legalAdmin->id,
                    'bidang_id' => $bidangHukum->id,
                    'is_active' => true,
                ]);
            }
        }

        if ($staffRole && $bidangUmum) {
            $existingStaff = User::where('email', 'pegawai@instansi.go.id')->first();
            if (!$existingStaff) {
                User::create([
                    'name' => 'Pegawai Umum',
                    'email' => 'pegawai@instansi.go.id',
                    'password' => Hash::make('password123'),
                    'role_id' => $staffRole->id,
                    'bidang_id' => $bidangUmum->id,
                    'is_active' => true,
                ]);
            }
        }
    }
}
