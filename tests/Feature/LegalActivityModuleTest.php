<?php

namespace Tests\Feature;

use App\Models\{Bidang, Role, User, LegalActivity, LegalNotulensi, Notulensi};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Storage};
use Tests\TestCase;

class LegalActivityModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Never connect to the application's database or run migrate:fresh.
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'activitylog.database_connection' => 'sqlite',
        ]);
        DB::purge('sqlite');
        foreach (glob(database_path('migrations/*.php')) as $migration) {
            $instance = require_once $migration;
            if (is_object($instance)) {
                $instance->up();
            } else {
                $class = \Illuminate\Support\Str::studly(
                    implode('_', array_slice(explode('_', basename($migration, '.php')), 4)),
                );
                if (class_exists($class)) {
                    (new $class())->up();
                } else {
                    (require $migration)->up();
                }
            }
        }
        Storage::fake('local');
    }

    protected function user(string $role = 'legal-admin'): User
    {
        $bidang = Bidang::firstOrCreate(['nama_bidang' => 'Hukum']);
        $role = Role::firstOrCreate(['nama' => $role]);

        return User::create([
            'name' => 'Petugas Legal',
            'email' => uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'bidang_id' => $bidang->id,
            'is_active' => true,
        ]);
    }

    protected function activity(User $user, array $data = []): LegalActivity
    {
        return LegalActivity::create(
            $data + [
                'tanggal_kegiatan' => '2026-10-05',
                'uraian_kegiatan' => 'Pembahasan Kontrak',
                'created_by' => $user->id,
            ],
        );
    }

    protected function minutes(array $data = []): array
    {
        return $data + [
            'judul_rapat' => 'Pembahasan Kontrak',
            'tanggal_rapat' => '2026-10-05',
            'notulis' => 'Petugas Legal',
            'new_activity' => '1',
        ];
    }

    public function test_legal_can_create_update_and_delete_activity_and_minutes(): void
    {
        $user = $this->user();
        $this->actingAs($user)
            ->post(route('legal.activities.store'), [
                'tanggal_kegiatan' => '2026-10-05',
                'uraian_kegiatan' => 'Review Kontrak',
            ])
            ->assertRedirect();
        $activity = LegalActivity::firstOrFail();
        $this->post(
            route('legal.notulensi.store'),
            $this->minutes(['legal_activity_id' => $activity->id, 'new_activity' => null]),
        )
            ->assertSessionHasNoErrors()
            ->assertRedirect();
        $note = LegalNotulensi::firstOrFail();
        $this->assertSame($activity->id, $note->legal_activity_id);
        $this->assertSame($user->id, $note->created_by);
        $this->assertSame(0, Notulensi::count());
        $this->put(
            route('legal.notulensi.update', $note->id),
            $this->minutes([
                'judul_rapat' => 'Review Final',
                'legal_activity_id' => $activity->id,
                'new_activity' => null,
            ]),
        )->assertSessionHasNoErrors();
        $this->assertSame('Review Final', $note->fresh()->judul_rapat);
        $this->put(route('legal.activities.update', $activity->id), [
            'tanggal_kegiatan' => '2026-10-06',
            'uraian_kegiatan' => 'Kontrak Final',
        ])->assertSessionHasNoErrors();
        $this->delete(route('legal.activities.destroy', $activity->id))->assertSessionHas('error');
        $this->delete(route('legal.notulensi.destroy', $note->id))->assertSessionHas('success');
        $this->delete(route('legal.activities.destroy', $activity->id))->assertSessionHas('success');
        $this->assertSame(0, LegalActivity::count());
        $this->assertDatabaseHas('activity_log', ['subject_type' => LegalNotulensi::class, 'event' => 'created']);
    }

    public function test_automatic_activity_is_reused_and_monthly_report_counts_once(): void
    {
        $user = $this->user();
        $this->actingAs($user)->post(route('legal.notulensi.store'), $this->minutes())->assertSessionHasNoErrors();
        $this->post(route('legal.notulensi.store'), $this->minutes())->assertSessionHasNoErrors();
        $this->assertSame(1, LegalActivity::count());
        $this->assertSame(2, LegalNotulensi::count());
        $this->activity($user, ['tanggal_kegiatan' => '2026-09-30', 'uraian_kegiatan' => 'Bulan Lain']);
        $this->activity($user, [
            'tanggal_kegiatan' => '2026-10-31',
            'uraian_kegiatan' => 'Kegiatan Tanpa Notulensi',
            'created_at' => '2026-01-01',
        ]);
        $this->activity($user, ['tanggal_kegiatan' => '2026-11-01', 'uraian_kegiatan' => 'Bulan Berikutnya']);
        Notulensi::create([
            'judul_rapat' => 'Rahasia Sekretariat',
            'tanggal_rapat' => '2026-10-05',
            'notulis' => 'Sekretariat',
            'file_path' => 'old.pdf',
            'bidang_id' => $user->bidang_id,
            'created_by' => $user->id,
        ]);
        $this->get(route('legal.reports.monthly', ['bulan' => 10, 'tahun' => 2026]))
            ->assertOk()
            ->assertViewHas('activities', fn ($items) => $items->count() === 2)
            ->assertSee('Kegiatan Tanpa Notulensi')
            ->assertDontSee('Bulan Lain')
            ->assertDontSee('Bulan Berikutnya')
            ->assertDontSee('Rahasia Sekretariat');
        $this->get(route('legal.notulensi.index'))->assertOk()->assertDontSee('Rahasia Sekretariat');
        $this->get(route('legal.activities.index', ['bulan' => 9, 'tahun' => 2026, 'cari' => 'Bulan']))
            ->assertOk()
            ->assertSee('Bulan Lain')
            ->assertDontSee('Kegiatan Tanpa Notulensi');
        $this->get(
            route('legal.notulensi.index', ['bulan' => 10, 'tahun' => 2026, 'cari' => 'Petugas']),
        )->assertViewHas('records', fn ($items) => $items->total() === 2);
    }

    public function test_unauthorized_users_and_guests_cannot_access_records_reports_or_files(): void
    {
        $user = $this->user('sekretariat');
        $activity = $this->activity($user);
        $this->get(route('legal.activities.index'))->assertRedirect(route('login'));
        $this->actingAs($user);
        foreach (
            [
                'legal.activities.index',
                'legal.notulensi.index',
                'legal.reports.monthly',
                'legal.reports.monthly.preview',
                'legal.reports.monthly.pdf',
            ] as $route
        ) {
            $this->get(route($route))->assertForbidden();
        }
        $this->get(route('legal.activities.attachment', [$activity->id, 'dokumen_pendukung']))->assertForbidden();
        $this->get(route('legal.notulensi.attachment', [1, 'file_path']))->assertForbidden();
        $this->post(route('legal.activities.store'), [])->assertForbidden();
        $this->delete(route('legal.activities.destroy', $activity->id))->assertForbidden();
    }

    public function test_super_admin_has_access_to_new_module(): void
    {
        $this->actingAs($this->user('super-admin'))->get(route('legal.activities.index'))->assertOk();
        $this->get(route('legal.reports.monthly'))->assertOk();
    }

    public function test_validation_and_private_attachment_delivery(): void
    {
        $user = $this->user();
        $this->actingAs($user)
            ->post(route('legal.activities.store'), [])
            ->assertSessionHasErrors(['tanggal_kegiatan', 'uraian_kegiatan']);
        $this->post(route('legal.notulensi.store'), $this->minutes(['new_activity' => null]))->assertSessionHasErrors(
            'legal_activity_id',
        );
        $this->post(
            route('legal.notulensi.store'),
            $this->minutes(['file_path' => UploadedFile::fake()->create('bad.exe', 1, 'application/x-msdownload')]),
        )->assertSessionHasErrors('file_path');
        $this->post(route('legal.activities.store'), [
            'tanggal_kegiatan' => '2026-10-01',
            'uraian_kegiatan' => 'Test',
            'dokumen_pendukung' => UploadedFile::fake()->create('big.pdf', 10241, 'application/pdf'),
        ])->assertSessionHasErrors('dokumen_pendukung');
        $file = UploadedFile::fake()->createWithContent('minutes.pdf', "%PDF-1.4\nlegal minutes");
        $this->post(route('legal.notulensi.store'), $this->minutes(['file_path' => $file]))->assertSessionHasNoErrors();
        $note = LegalNotulensi::firstOrFail();
        Storage::disk('local')->assertExists($note->file_path);
        $this->assertStringStartsWith('legal-private/', $note->file_path);
        $this->get(route('legal.notulensi.attachment', [$note->id, 'file_path']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
        $this->get(route('legal.notulensi.attachment', [$note->id, 'file_path', 'download' => 1]))->assertDownload(
            'minutes.pdf',
        );
        $this->get(route('legal.notulensi.attachment', [$note->id, 'created_by']))->assertNotFound();
        $note->update(['file_path' => '../public/secret.pdf']);
        $this->get(route('legal.notulensi.attachment', [$note->id, 'file_path']))->assertNotFound();
    }

    public function test_pdf_content_type_filename_empty_state_and_statistics_regression(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        $this->activity($user);
        $query = ['bulan' => 10, 'tahun' => 2026];
        $response = $this->get(route('legal.reports.monthly.pdf', $query))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString(
            'Laporan_Kegiatan_Legal_Oktober_2026.pdf',
            $response->headers->get('Content-Disposition'),
        );
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->get(route('legal.reports.monthly.preview', $query))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
        $this->get(route('legal.reports.monthly', ['bulan' => 2, 'tahun' => 2026]))->assertSee(
            'Tidak ada kegiatan pada periode ini',
        );
        $this->get(route('legal.reports.monthly', ['bulan' => 13]))->assertSessionHasErrors('bulan');
        $this->get(route('legal.reports.index'))
            ->assertOk()
            ->assertSee('Laporan Kegiatan Bulanan')
            ->assertViewHas('activities')
            ->assertDontSee('Total Konsultasi Ditangani');
        foreach (['legal.activities.create', 'legal.notulensi.create'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $activity = LegalActivity::firstOrFail();
        $this->get(route('legal.activities.edit', $activity->id))->assertOk();
        $this->get(route('legal.activities.show', $activity->id))->assertOk();
    }

    public function test_failed_minutes_save_rolls_back_activity_and_cleans_uploaded_file(): void
    {
        $this->actingAs($this->user());
        LegalNotulensi::creating(function ($note) {
            if ($note->judul_rapat === 'Simulasi gagal') {
                throw new \RuntimeException('Simulated database failure');
            }
        });
        $this->post(
            route('legal.notulensi.store'),
            $this->minutes([
                'judul_rapat' => 'Simulasi gagal',
                'file_path' => UploadedFile::fake()->createWithContent('test.pdf', "%PDF-1.4\nTest"),
            ]),
        )->assertSessionHas('error');
        $this->assertSame(0, LegalActivity::count());
        $this->assertSame(0, LegalNotulensi::count());
        $this->assertSame([], Storage::disk('local')->allFiles('legal-private'));
    }

    public function test_attachment_replacement_deletion_and_photo_validation(): void
    {
        $this->actingAs($this->user());
        $data = ['tanggal_kegiatan' => '2026-10-01', 'uraian_kegiatan' => 'Dokumentasi Rapat'];
        $this->post(
            route('legal.activities.store'),
            $data + [
                'dokumen_pendukung' => UploadedFile::fake()->createWithContent('old.pdf', "%PDF-1.4\nold"),
                'foto_dokumentasi' => UploadedFile::fake()->createWithContent(
                    'foto.jpg',
                    file_get_contents(public_path('images/logo-legal-report.jpg')),
                ),
            ],
        )->assertSessionHasNoErrors();
        $activity = LegalActivity::firstOrFail();
        $old = $activity->dokumen_pendukung;
        $photo = $activity->foto_dokumentasi;
        $this->get(route('legal.activities.attachment', [$activity->id, 'foto_dokumentasi']))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg');
        $this->assertStringStartsWith('data:image/jpeg;base64,', \App\Services\LegalPrivateFiles::image($photo));
        $this->put(
            route('legal.activities.update', $activity->id),
            $data + [
                'dokumen_pendukung' => UploadedFile::fake()->createWithContent('new.pdf', "%PDF-1.4\nnew"),
            ],
        )->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($old);
        Storage::disk('local')->assertExists($photo);
        $this->post(
            route('legal.activities.store'),
            $data + ['foto_dokumentasi' => UploadedFile::fake()->create('bad.svg', 10, 'image/svg+xml')],
        )->assertSessionHasErrors('foto_dokumentasi');
        $current = $activity->fresh()->dokumen_pendukung;
        $this->delete(route('legal.activities.destroy', $activity->id))->assertSessionHas('success');
        Storage::disk('local')->assertMissing([$current, $photo]);
    }

    public function test_minutes_has_inline_form_history_and_authorized_modal_preview(): void
    {
        $this->actingAs($this->user());
        $this->get(route('legal.notulensi.index'))
            ->assertOk()
            ->assertSee('Tambah Notulensi')
            ->assertSee('Riwayat Notulensi')
            ->assertSee('name="judul_rapat"', false)
            ->assertSee('name="new_activity" value="1"', false)
            ->assertSee('action="' . route('legal.notulensi.store') . '"', false);
        $this->post(
            route('legal.notulensi.store'),
            $this->minutes([
                'file_path' => UploadedFile::fake()->createWithContent('notulensi.pdf', "%PDF-1.4\nNotulensi"),
            ]),
        )->assertSessionHasNoErrors();
        $note = LegalNotulensi::firstOrFail();
        $url = route('legal.notulensi.attachment', [$note->id, 'file_path']);
        $response = $this->get(route('legal.notulensi.index'))
            ->assertOk()
            ->assertSee('data-notulensi-preview', false)
            ->assertSee($url)
            ->assertDontSee('/storage/legal-private/');
        $this->assertSame(1, substr_count($response->getContent(), 'name="cari"'));
        $this->get(route('legal.notulensi.show', $note->id))
            ->assertOk()
            ->assertSee('Buka / Unduh File Notulensi')
            ->assertSee($url);
        $this->get(route('legal.notulensi.edit', $note->id))
            ->assertOk()
            ->assertSee('Edit Notulensi')
            ->assertSee('Pembahasan Kontrak')
            ->assertSee('notulensi.pdf');
    }

    public function test_monthly_report_and_pdf_include_only_activity_documentation(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        $activity = $this->activity($user, [
            'dokumen_pendukung' => 'legal-private/activity.pdf',
            'dokumen_pendukung_name' => 'Pendukung-kegiatan.pdf',
            'foto_dokumentasi' => 'legal-private/activity.jpg',
            'foto_dokumentasi_name' => 'Foto-kegiatan.jpg',
        ]);
        Storage::disk('local')->put(
            'legal-private/activity.jpg',
            file_get_contents(public_path('images/logo-legal-report.jpg')),
        );
        $category = \App\Models\LegalCategory::create(['name' => 'Kategori Uji']);
        $noteDocument = \App\Models\LegalDocument::create([
            'name' => 'Dokumen-khusus-notulensi',
            'category_id' => $category->id,
            'file_path' => 'note.pdf',
            'created_by' => $user->id,
        ]);
        LegalNotulensi::create([
            'legal_activity_id' => $activity->id,
            'legal_document_id' => $noteDocument->id,
            'judul_rapat' => 'Judul-khusus-notulensi',
            'tanggal_rapat' => '2026-10-05',
            'notulis' => 'Notulis Uji',
            'file_path' => 'legal-private/note.pdf',
            'file_path_name' => 'Berkas-notulensi.pdf',
            'foto_path' => 'legal-private/note.jpg',
            'foto_path_name' => 'Foto-notulensi.jpg',
            'created_by' => $user->id,
        ]);
        $period = ['bulan' => 10, 'tahun' => 2026];
        foreach (['legal.reports.index', 'legal.reports.monthly'] as $route) {
            $this->get(route($route, $period))
                ->assertOk()
                ->assertSee('Pendukung-kegiatan.pdf')
                ->assertSee('Foto-kegiatan.jpg')
                ->assertDontSee('Berkas-notulensi.pdf')
                ->assertDontSee('Dokumen-khusus-notulensi')
                ->assertDontSee('Tambah Notulensi');
        }
        $request = \Illuminate\Http\Request::create('/', 'GET', $period);
        $data = app(\App\Http\Controllers\LegalMonthlyReportController::class)->data($request);
        $this->assertCount(1, $data['activities']);
        $this->assertFalse($data['activities']->first()->relationLoaded('notulensi'));
        $data['activities']->first()->preventsLazyLoading = true;
        $html = view('legal.reports.monthly-pdf', $data)->render();
        $this->assertStringContainsString('Pendukung-kegiatan.pdf', $html);
        $this->assertStringContainsString('data:image/jpeg;base64,', $html);
        foreach (
            ['Berkas-notulensi.pdf', 'Foto-notulensi.jpg', 'Judul-khusus-notulensi', 'Dokumen-khusus-notulensi'] as $excluded
        ) {
            $this->assertStringNotContainsString($excluded, $html);
        }
        $this->get(route('legal.reports.monthly.pdf', $period))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }
}
