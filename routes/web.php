<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BidangController;
use App\Http\Controllers\KategoriArsipController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ArsipController;
use App\Http\Controllers\NotulensiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\KategoriSuratController;
use App\Http\Controllers\NomorSuratController;
use App\Http\Controllers\LogAktivitasController;


Route::get('/', function () {
    return view('welcome');
})->name('welcome');


Route::get('/dashboard', function () {
    return view('dashboard');
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});


Route::middleware([
    'auth',
    'aktif',
    'nocache',
    'role:super-admin'
])
    ->prefix('admin')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'admin'])
            ->name('dashboard.admin');

        Route::resource('bidang', BidangController::class)
            ->except([
                'create',
                'edit',
                'show'
            ]);

        Route::resource('kategori-arsip', KategoriArsipController::class)
            ->except([
                'create',
                'edit',
                'show'
            ]);

        Route::resource('pengguna', UserController::class)
            ->except([
                'create',
                'edit',
                'show',
                'destroy'
            ])
            ->parameters([
                'pengguna' => 'user'
            ]);

        Route::patch(
            'pengguna/{user}/toggle',
            [UserController::class, 'toggleAktif']
        )->name('pengguna.toggle');

        Route::get(
            'role',
            [UserController::class, 'role']
        )->name('role.index');

        Route::get(
            'kategori-surat',
            [KategoriSuratController::class, 'index']
        )->name('kategori-surat.index');

        Route::post(
            'kategori-surat',
            [KategoriSuratController::class, 'store']
        )->name('kategori-surat.store');

        Route::patch(
            'kategori-surat/{kategoriSurat}/toggle',
            [KategoriSuratController::class, 'toggleAktif']
        )->name('kategori-surat.toggle');

        Route::put('kategori-surat/{kategoriSurat}', [KategoriSuratController::class, 'update'])->name('kategori-surat.update');
    });


Route::middleware([
    'auth',
    'aktif',
    'nocache',
    'role:sekretariat'
])
    ->group(function () {

        Route::get(
            '/dashboard-sekretariat',
            [DashboardController::class, 'sekretariat']
        )->name('dashboard.sekretariat');
    });


Route::middleware([
    'auth',
    'aktif',
    'nocache',
    'role:staf-bidang'
])
    ->group(function () {

        Route::get(
            '/staf',
            [DashboardController::class, 'staf']
        )->name('dashboard.staf');
    });


Route::middleware([
    'auth',
    'aktif',
    'nocache'
])
    ->group(function () {

        Route::get(
            'arsip',
            [ArsipController::class, 'index']
        )->name('arsip.index');

        Route::post(
            'arsip',
            [ArsipController::class, 'store']
        )->name('arsip.store');

        Route::get(
            'arsip/{arsip}',
            [ArsipController::class, 'show']
        )->name('arsip.show');
    });


Route::middleware([
    'auth',
    'aktif',
    'nocache',
    'role:sekretariat'
])
    ->group(function () {

        Route::resource(
            'notulensi',
            NotulensiController::class
        )->except([
            'create',
            'edit',
            'destroy'
        ]);

        Route::get(
            '/notulensi/{notulensi}/berkas',
            [NotulensiController::class, 'berkas']
        )->name('notulensi.berkas');

        Route::prefix('laporan')->group(function () {
            Route::get(
                '/',
                [LaporanController::class, 'index']
            )->name('laporan.index');

            Route::get(
                '/generate',
                [LaporanController::class, 'generate']
            )->name('laporan.generate');

            Route::get(
                '/pdf',
                [LaporanController::class, 'exportPdf']
            )->name('laporan.pdf');
        });
    });


Route::get(
    'dashboard/rekap-bulanan',
    [DashboardController::class, 'rekapBulanan']
)->name('dashboard.rekap-bulanan');


Route::middleware([
    'auth',
    'aktif',
    'nocache',
    'role:sekretariat'
])
    ->prefix('nomor-surat')
    ->group(function () {

        Route::get(
            '/',
            [NomorSuratController::class, 'index']
        )->name('nomor-surat.index');

        Route::post(
            '/',
            [NomorSuratController::class, 'store']
        )->name('nomor-surat.store');

        Route::get(
            '/kategori-aktif',
            [NomorSuratController::class, 'daftarKategoriAktif']
        )->name('nomor-surat.kategori-aktif');

        Route::get(
            '/nomor-berikutnya',
            [NomorSuratController::class, 'ambilNomorBerikutnya']
        )->name('nomor-surat.nomor-berikutnya');

        Route::get(
            '/riwayat-json',
            [NomorSuratController::class, 'riwayatJson']
        )->name('nomor-surat.riwayat-json');

        Route::get(
            '/{arsipSurat}',
            [NomorSuratController::class, 'show']
        )->name('nomor-surat.show');

        Route::put(
            '/{arsipSurat}',
            [NomorSuratController::class, 'update']
        )->name('nomor-surat.update');
    });


Route::middleware([
    'auth',
    'aktif',
    'nocache'
])
    ->group(function () {

        Route::get(
            '/log-aktivitas',
            [LogAktivitasController::class, 'index']
        )->name('log-aktivitas.index');

        Route::get(
            '/log-aktivitas/{log}',
            [LogAktivitasController::class, 'show']
        )->name('log-aktivitas.show');
    });


require __DIR__.'/auth.php';