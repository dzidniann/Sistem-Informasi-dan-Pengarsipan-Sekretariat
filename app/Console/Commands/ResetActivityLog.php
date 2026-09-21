<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;

class ResetActivityLog extends Command
{
    protected $signature = 'log:reset';

    protected $description = 'Menghapus log aktivitas yang lebih lama dari 2 bulan';

    public function handle()
    {
        $batasWaktu = now()->subMonths(2);

        $jumlahDihapus = Activity::where(
            'created_at',
            '<',
            $batasWaktu
        )->count();

        Activity::where(
            'created_at',
            '<',
            $batasWaktu
        )->delete();

        $this->info(
            "Berhasil menghapus {$jumlahDihapus} log aktivitas yang lebih lama dari 2 bulan."
        );

        return self::SUCCESS;
    }
}