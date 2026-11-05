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
use App\Http\Controllers\LegalDashboardController;
use App\Http\Controllers\LegalDocumentController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\LegalCalendarController;

Route::get('/', function () {
    return redirect()->route('login');
})->name('welcome');

Route::get('/dashboard', function () {
    return view('dashboard');
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'aktif', 'nocache', 'role:super-admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'admin'])->name('dashboard.admin');

        Route::resource('bidang', BidangController::class)->except(['create', 'edit', 'show']);

        Route::resource('kategori-arsip', KategoriArsipController::class)->except(['create', 'edit', 'show']);

        Route::resource('pengguna', UserController::class)
            ->except(['create', 'edit', 'show', 'destroy'])
            ->parameters([
                'pengguna' => 'user',
            ]);

        Route::patch('pengguna/{user}/toggle', [UserController::class, 'toggleAktif'])->name('pengguna.toggle');

        Route::get('role', [UserController::class, 'role'])->name('role.index');

        Route::get('kategori-surat', [KategoriSuratController::class, 'index'])->name('kategori-surat.index');

        Route::post('kategori-surat', [KategoriSuratController::class, 'store'])->name('kategori-surat.store');

        Route::patch('kategori-surat/{kategoriSurat}/toggle', [KategoriSuratController::class, 'toggleAktif'])->name(
            'kategori-surat.toggle',
        );

        Route::put('kategori-surat/{kategoriSurat}', [KategoriSuratController::class, 'update'])->name(
            'kategori-surat.update',
        );
    });

Route::middleware(['auth', 'aktif', 'nocache', 'role:sekretariat'])->group(function () {
    Route::get('/dashboard-sekretariat', [DashboardController::class, 'sekretariat'])->name('dashboard.sekretariat');
});

Route::middleware(['auth', 'aktif', 'nocache', 'role:staf-bidang'])->group(function () {
    Route::get('/staf', [DashboardController::class, 'staf'])->name('dashboard.staf');
});

Route::middleware(['auth', 'aktif', 'nocache'])->group(function () {
    Route::get('arsip', [ArsipController::class, 'index'])->name('arsip.index');

    Route::post('arsip', [ArsipController::class, 'store'])->name('arsip.store');

    Route::get('arsip/{arsip}', [ArsipController::class, 'show'])->name('arsip.show');
});

Route::middleware(['auth', 'aktif', 'nocache', 'role:sekretariat'])->group(function () {
    Route::resource('notulensi', NotulensiController::class)->except(['create', 'edit', 'destroy']);

    Route::get('/notulensi/{notulensi}/berkas', [NotulensiController::class, 'berkas'])->name('notulensi.berkas');

    Route::prefix('laporan')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('laporan.index');

        Route::get('/generate', [LaporanController::class, 'generate'])->name('laporan.generate');

        Route::get('/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.pdf');
    });
});

Route::get('dashboard/rekap-bulanan', [DashboardController::class, 'rekapBulanan'])
    ->middleware(['auth', 'aktif', 'nocache'])
    ->name('dashboard.rekap-bulanan');

Route::middleware(['auth', 'aktif', 'nocache', 'role:sekretariat'])
    ->prefix('nomor-surat')
    ->group(function () {
        Route::get('/', [NomorSuratController::class, 'index'])->name('nomor-surat.index');

        Route::post('/', [NomorSuratController::class, 'store'])->name('nomor-surat.store');

        Route::get('/kategori-aktif', [NomorSuratController::class, 'daftarKategoriAktif'])->name(
            'nomor-surat.kategori-aktif',
        );

        Route::get('/nomor-berikutnya', [NomorSuratController::class, 'ambilNomorBerikutnya'])->name(
            'nomor-surat.nomor-berikutnya',
        );

        Route::get('/riwayat-json', [NomorSuratController::class, 'riwayatJson'])->name('nomor-surat.riwayat-json');

        Route::get('/{arsipSurat}', [NomorSuratController::class, 'show'])->name('nomor-surat.show');

        Route::put('/{arsipSurat}', [NomorSuratController::class, 'update'])->name('nomor-surat.update');
    });

Route::middleware(['auth', 'aktif', 'nocache'])->group(function () {
    Route::get('/log-aktivitas', [LogAktivitasController::class, 'index'])->name('log-aktivitas.index');

    Route::get('/log-aktivitas/{log}', [LogAktivitasController::class, 'show'])->name('log-aktivitas.show');
});

// ==================== LEGAL ADMIN MODULE ====================
Route::middleware(['auth', 'aktif', 'nocache', 'role:legal-admin,super-admin'])
    ->prefix('legal')
    ->name('legal.')
    ->group(function () {
        Route::get('/dashboard', [LegalDashboardController::class, 'index'])->name('dashboard');

        // Documents
        Route::resource('documents', LegalDocumentController::class);
        Route::match(['put', 'patch'], 'documents/{document}/status', [
            LegalDocumentController::class,
            'updateStatus',
        ])->name('documents.update-status');
        Route::post('documents/{document}/publish', [LegalDocumentController::class, 'publish'])->name(
            'documents.publish',
        );
        Route::post('documents/{document}/unpublish', [LegalDocumentController::class, 'unpublish'])->name(
            'documents.unpublish',
        );

        // Consultations (Legal view)
        Route::get('consultations', [ConsultationController::class, 'index'])->name('consultations.index');
        Route::get('consultations/{consultation}', [ConsultationController::class, 'show'])->name('consultations.show');
        Route::post('consultations/{consultation}/message', [ConsultationController::class, 'sendMessage'])->name(
            'consultations.message',
        );
        Route::match(['put', 'patch'], 'consultations/{consultation}/status', [
            ConsultationController::class,
            'updateStatus',
        ])->name('consultations.update-status');
        Route::match(['put', 'patch'], 'consultations/{consultation}/assign', [
            ConsultationController::class,
            'assign',
        ])->name('consultations.assign');
        Route::match(['put', 'patch'], 'consultations/{consultation}/schedule', [
            ConsultationController::class,
            'schedule',
        ])->name('consultations.schedule');

        // Calendar
        Route::get('calendar', [LegalCalendarController::class, 'index'])->name('calendar.index');
        Route::get('calendar/events', [LegalCalendarController::class, 'events'])->name('calendar.events');

        // Reports
        Route::withoutMiddleware('role:legal-admin,super-admin')
            ->middleware(\App\Http\Middleware\LegalModuleAccess::class)
            ->group(function () {
                Route::get('governance-documents/{governanceDocument}/file', [
                    \App\Http\Controllers\LegalGovernanceDocumentController::class, 'file',
                ])->name('governance-documents.file');
                Route::resource('governance-documents', \App\Http\Controllers\LegalGovernanceDocumentController::class)
                    ->parameters(['governance-documents' => 'governanceDocument']);
                Route::resource('activities', \App\Http\Controllers\LegalActivityController::class);
                Route::resource('notulensi', \App\Http\Controllers\LegalNotulensiController::class);
                Route::get('activities/{id}/attachments/{field}', [
                    \App\Http\Controllers\LegalActivityController::class,
                    'attachment',
                ])->name('activities.attachment');
                Route::get('notulensi/{id}/attachments/{field}', [
                    \App\Http\Controllers\LegalNotulensiController::class,
                    'attachment',
                ])->name('notulensi.attachment');
                Route::get('reports/monthly', [
                    \App\Http\Controllers\LegalMonthlyReportController::class,
                    'index',
                ])->name('reports.monthly');
                Route::get('reports', [\App\Http\Controllers\LegalMonthlyReportController::class, 'index'])->name(
                    'reports.index',
                );
                Route::get('reports/monthly/preview', [
                    \App\Http\Controllers\LegalMonthlyReportController::class,
                    'preview',
                ])->name('reports.monthly.preview');
                Route::get('reports/monthly/pdf', [
                    \App\Http\Controllers\LegalMonthlyReportController::class,
                    'download',
                ])->name('reports.monthly.pdf');
            });
    });

// ==================== EMPLOYEE CONSULTATION MODULE ====================
Route::middleware(['auth', 'aktif', 'nocache'])
    ->prefix('employee')
    ->name('employee.')
    ->group(function () {
        Route::get('consultations', [ConsultationController::class, 'index'])->name('consultations.index');
        Route::get('consultations/create', [ConsultationController::class, 'create'])->name('consultations.create');
        Route::post('consultations', [ConsultationController::class, 'store'])->name('consultations.store');
        Route::get('consultations/{consultation}', [ConsultationController::class, 'show'])->name('consultations.show');
        Route::post('consultations/{consultation}/message', [ConsultationController::class, 'sendMessage'])->name(
            'consultations.message',
        );
    });

// ==================== DIGITAL LIBRARY MODULE ====================
Route::middleware(['auth', 'aktif', 'nocache'])
    ->prefix('library')
    ->name('library.')
    ->group(function () {
        Route::get('/', [LibraryController::class, 'index'])->name('index');
        Route::get('/{document}', [LibraryController::class, 'show'])->name('show');
    });

// Humas CSR uses its own role and always scopes records to the user's bidang.
Route::middleware(['auth', 'aktif', 'nocache', 'role:humas'])
    ->prefix('humas')
    ->name('humas.')
    ->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\HumasCsrController::class, 'dashboard'])->name('dashboard');
        Route::get('reports', [\App\Http\Controllers\HumasReportController::class, 'index'])->name('reports.index');
        Route::get('reports/excel', [\App\Http\Controllers\HumasReportController::class, 'excel'])->name(
            'reports.excel',
        );
        Route::get('reports/pdf', [\App\Http\Controllers\HumasReportController::class, 'pdf'])->name('reports.pdf');
        Route::get('csr/{csr}/attachments/{attachment}', [
            \App\Http\Controllers\HumasCsrController::class,
            'attachment',
        ])->name('csr.attachment');
        Route::resource('csr', \App\Http\Controllers\HumasCsrController::class);
    });

require __DIR__ . '/auth.php';
