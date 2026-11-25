<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            DB::table('roles')->insertOrIgnore([
                'nama' => 'staf-bidang',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $staffId = DB::table('roles')->where('nama', 'staf-bidang')->value('id');
            $oldIds = DB::table('roles')->where('nama', 'employee')->pluck('id');
            DB::table('users')->whereIn('role_id', $oldIds)->update([
                'role_id' => $staffId,
                'updated_at' => now(),
            ]);
            DB::table('roles')->whereIn('id', $oldIds)->delete();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Penggabungan role tidak dapat dipisahkan otomatis. Gunakan backup untuk memulihkan penugasan role sebelumnya.');
    }
};
