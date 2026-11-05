<?php

namespace Tests\Feature;

use App\Models\{Bidang, KategoriArsip, LegalCategory, LegalDocument, Role, User};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Storage};
use Tests\TestCase;

class LegalCategoryMasterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'activitylog.database_connection' => 'sqlite',
        ]);
        DB::purge('sqlite');
        foreach (glob(database_path('migrations/*.php')) as $migration) {
            if (
                $this->name() ===
                    'test_migration_removes_unused_defaults_preserves_documents_and_connects_existing_masters' &&
                str_contains($migration, '150000_connect_legal_categories')
            ) {
                continue;
            }
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
        Storage::fake('public');
        Storage::fake('protected');
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => 'Petugas',
            'email' => uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role_id' => Role::firstOrCreate(['nama' => $role])->id,
            'bidang_id' => Bidang::firstOrCreate(['nama_bidang' => 'Divisi Hukum'])->id,
            'is_active' => true,
        ]);
    }

    public function test_legal_department_rename_preserves_users_and_category_links(): void
    {
        $user = $this->user('legal-admin');
        $master = KategoriArsip::create(['nama' => 'Kontrak', 'bidang_id' => $user->bidang_id]);
        $categoryId = $master->legalCategory->id;
        (require database_path('migrations/2026_10_20_090000_rename_legal_department.php'))->up();
        $this->assertSame('Sub Bidang Hukum', $user->fresh()->bidang->nama_bidang);
        $this->assertSame($categoryId, LegalCategory::masterHukum()->sole()->id);
        $master->update(['nama' => 'Kontrak Baru']);
        $this->assertSame('Kontrak Baru', $master->legalCategory->fresh()->name);
        $this->actingAs($user)
            ->get(route('legal.documents.create'))
            ->assertOk()
            ->assertSee('Sub Bidang Hukum')
            ->assertSee('Kontrak Baru')
            ->assertDontSee('Divisi Hukum');
    }

    public function test_consultation_actions_return_saved_values_without_redirecting_json_requests(): void
    {
        $legal = $this->user('legal-admin');
        $consultation = \App\Models\Consultation::create([
            'user_id' => $this->user('staf-bidang')->id,
            'title' => 'Simpan tanpa reload',
            'description' => 'Permohonan',
            'status' => 'WAITING_REVIEW',
        ]);
        $this->actingAs($legal);
        $this->patchJson(route('legal.consultations.update-status', $consultation), ['status' => 'ONGOING'])
            ->assertOk()
            ->assertJsonPath('consultation.status', 'ONGOING')
            ->assertJsonPath('consultation.assigned_legal', '')
            ->assertJsonPath('consultation.schedule', '');
        $this->patchJson(route('legal.consultations.assign', $consultation), ['assigned_legal' => $legal->id])
            ->assertOk()
            ->assertJsonPath('consultation.assigned_legal', (string) $legal->id)
            ->assertJsonPath('consultation.status', 'ACCEPTED');
        $schedule = now()->addDays(2)->format('Y-m-d\TH:i');
        $this->patchJson(route('legal.consultations.schedule', $consultation), ['schedule' => $schedule])
            ->assertOk()
            ->assertJsonPath('consultation.schedule', $schedule)
            ->assertJsonPath('consultation.status', 'SCHEDULED')
            ->assertJsonPath('consultation.assigned_legal', (string) $legal->id);
        $this->patchJson(route('legal.consultations.update-status', $consultation), ['status' => 'FINISHED'])
            ->assertOk()
            ->assertJsonPath('consultation.status', 'FINISHED')
            ->assertJsonPath('consultation.schedule', $schedule)
            ->assertJsonPath('consultation.assigned_legal', (string) $legal->id);
        $this->patchJson(route('legal.consultations.schedule', $consultation), [
            'schedule' => now()->subDay()->format('Y-m-d\TH:i'),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('schedule');
        $this->assertSame($schedule, $consultation->fresh()->schedule->format('Y-m-d\TH:i'));
        $this->actingAs($this->user('staf-bidang'))
            ->patchJson(route('legal.consultations.update-status', $consultation), ['status' => 'ONGOING'])
            ->assertForbidden();
    }

    public function test_existing_admin_category_page_supplies_legal_documents(): void
    {
        $admin = $this->user('super-admin');
        $this->actingAs($admin)
            ->get(route('kategori-arsip.index'))
            ->assertOk()
            ->assertDontSee('Kategori Dokumen Hukum');
        $this->post(route('kategori-arsip.store'), [
            'nama' => 'Kontrak Baru',
            'bidang_id' => $admin->bidang_id,
        ])->assertSessionHasNoErrors();
        $master = KategoriArsip::firstOrFail();
        $category = $master->legalCategory;
        $this->assertSame('Kontrak Baru', $category->name);
        $this->put(route('kategori-arsip.update', $master), ['nama' => 'Kontrak Final'])->assertSessionHasNoErrors();
        $this->assertSame('Kontrak Final', $category->fresh()->name);
        $sekretariat = Bidang::create(['nama_bidang' => 'Sekretariat dan Pengarsipan']);
        KategoriArsip::create(['nama' => 'Khusus Sekretariat', 'bidang_id' => $sekretariat->id]);
        LegalCategory::create(['name' => 'Kategori Bawaan Lama']);
        $this->actingAs($this->user('legal-admin'))
            ->get(route('legal.documents.create'))
            ->assertOk()
            ->assertSee('Kontrak Final')
            ->assertDontSee('Khusus Sekretariat')
            ->assertDontSee('Kategori Bawaan Lama');
        $this->post(route('legal.documents.store'), [
            'name' => 'Dokumen Baru',
            'category_id' => $category->id,
            'description' => 'Ringkasan kontrak',
            'department' => 'Kepegawaian',
            'pic' => 'Petugas',
            'effective_date' => '2026-10-01',
            'expiry_date' => '2027-10-01',
            'review_date' => '2027-04-01',
            'file' => UploadedFile::fake()->createWithContent('kontrak.pdf', "%PDF-1.4\nTest"),
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('legal_documents', ['category_id' => $category->id, 'name' => 'Dokumen Baru']);
        $this->actingAs($admin)->delete(route('kategori-arsip.destroy', $master))->assertSessionHas('error');
        $unused = KategoriArsip::create(['nama' => 'Kosong', 'bidang_id' => $admin->bidang_id]);
        $unusedLegal = $unused->legalCategory->id;
        $this->delete(route('kategori-arsip.destroy', $unused))->assertSessionHas('success');
        $this->assertDatabaseMissing('legal_categories', ['id' => $unusedLegal]);
    }

    public function test_upload_document_requires_every_form_field(): void
    {
        $user = $this->user('legal-admin');
        $master = KategoriArsip::create(['nama' => 'Kontrak', 'bidang_id' => $user->bidang_id]);
        $data = [
            'name' => 'Dokumen Lengkap',
            'category_id' => $master->legalCategory->id,
            'description' => 'Ringkasan',
            'department' => 'Kepegawaian',
            'pic' => 'Petugas',
            'effective_date' => '2026-10-01',
            'expiry_date' => '2027-10-01',
            'review_date' => '2027-04-01',
            'file' => UploadedFile::fake()->createWithContent('dokumen.pdf', "%PDF-1.4\nTest"),
        ];
        $this->actingAs($user);
        foreach (array_keys($data) as $field) {
            $incomplete = $data;
            unset($incomplete[$field]);
            $this->postJson(route('legal.documents.store'), $incomplete)
                ->assertUnprocessable()
                ->assertJsonValidationErrors($field);
        }
        $this->postJson(route('legal.documents.store'), array_replace($data, ['expiry_date' => '2026-09-30']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('expiry_date');
        $this->assertDatabaseCount('legal_documents', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('legal-documents'));
        $this->post(route('legal.documents.store'), $data)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('legal.documents.index'));
        $this->assertDatabaseHas('legal_documents', ['name' => 'Dokumen Lengkap', 'pic' => 'Petugas']);
    }

    public function test_only_admin_can_manage_categories_and_legacy_category_is_rejected(): void
    {
        $user = $this->user('legal-admin');
        $this->actingAs($user)
            ->post(route('kategori-arsip.store'), ['nama' => 'Dilarang', 'bidang_id' => $user->bidang_id])
            ->assertForbidden();
        $this->get('/admin/kategori-dokumen-hukum')->assertNotFound();
        $legacy = LegalCategory::create(['name' => 'Kategori Lama']);
        $this->post(route('legal.documents.store'), [
            'name' => 'Dokumen',
            'category_id' => $legacy->id,
            'file' => UploadedFile::fake()->createWithContent('test.pdf', "%PDF-1.4\nTest"),
        ])->assertSessionHasErrors('category_id');
        $this->get(route('legal.documents.create'))->assertSee('Belum ada kategori dokumen Hukum');
        $this->actingAs($this->user('super-admin'))
            ->post(route('kategori-arsip.store'), [
                'nama' => 'Sub',
                'bidang_id' => $user->bidang_id,
                'parent_id' => KategoriArsip::create([
                    'nama' => 'Bidang Lain',
                    'bidang_id' => Bidang::create(['nama_bidang' => 'Lain'])->id,
                ])->id,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_legal_seeder_no_longer_adds_default_categories(): void
    {
        $this->seed(\Database\Seeders\LegalSeeder::class);
        $this->assertSame(0, LegalCategory::count());
    }

    public function test_consultation_portal_keeps_original_navigation_without_dashboard_return(): void
    {
        foreach (
            [
                'sekretariat' => 'dashboard.sekretariat',
                'staf-bidang' => 'dashboard.staf',
                'super-admin' => 'dashboard.admin',
                'employee' => null,
            ] as $role => $dashboard
        ) {
            $user = $this->user($role);
            $note = \App\Models\Consultation::create([
                'user_id' => $user->id,
                'title' => 'Konsultasi Milik Saya',
                'description' => 'Pembahasan',
            ]);
            $this->actingAs($user);
            foreach (
                ['employee.consultations.index', 'employee.consultations.create', 'employee.consultations.show'] as $route
            ) {
                $response = $this->get(route($route, $route === 'employee.consultations.show' ? $note : []))
                    ->assertOk()
                    ->assertSee('<aside', false)
                    ->assertSee('id="appShell"', false);
                $this->assertSame(1, substr_count($response->getContent(), '<!DOCTYPE html>'));
                if ($dashboard) {
                    $response
                        ->assertSee(route($dashboard))
                        ->assertDontSee('Kembali ke Dashboard')
                        ->assertDontSee('PORTAL PEGAWAI');
                } else {
                    $response->assertSee('PORTAL PEGAWAI')->assertDontSee('Kembali ke Dashboard');
                    $active = route(
                        $route === 'employee.consultations.create'
                            ? 'employee.consultations.create'
                            : 'employee.consultations.index',
                    );
                    $this->assertMatchesRegularExpression(
                        '/href="' . preg_quote($active, '/') . '" class="nav-link active"/',
                        $response->getContent(),
                    );
                }
            }
        }
        $other = $this->user('staf-bidang');
        $this->actingAs($other)->get(route('employee.consultations.show', $note))->assertForbidden();
    }

    public function test_closed_is_no_longer_allowed_in_consultations(): void
    {
        $user = $this->user('legal-admin');
        $this->actingAs($user);
        foreach (['FINISHED', 'FINISHED', 'WAITING_REVIEW', 'WAITING_REVIEW', 'WAITING_REVIEW', 'ONGOING'] as $status) {
            \App\Models\Consultation::unguarded(
                fn () => \App\Models\Consultation::create([
                    'user_id' => $user->id,
                    'title' => 'Konsultasi ' . $status,
                    'description' => 'Pembahasan',
                    'status' => $status,
                    'created_at' => '2026-10-01 08:00:00',
                    'updated_at' => $status === 'FINISHED' ? '2026-10-04 08:00:00' : '2026-10-11 08:00:00',
                ]),
            );
        }
        $consultation = \App\Models\Consultation::where('status', 'ONGOING')->firstOrFail();
        $this->get(route('legal.consultations.index'))->assertOk()->assertDontSee('CLOSED');
        $this->get(route('legal.consultations.show', $consultation))->assertOk()->assertDontSee('CLOSED');
        $this->patch(route('legal.consultations.update-status', $consultation), [
            'status' => 'CLOSED',
        ])->assertSessionHasErrors('status');
        $this->assertSame('ONGOING', $consultation->fresh()->status);
        $this->patch(route('legal.consultations.update-status', $consultation), [
            'status' => 'FINISHED',
        ])->assertSessionHasNoErrors();
        $this->assertSame('FINISHED', $consultation->fresh()->status);
    }

    public function test_closed_migration_preserves_consultation_and_original_timestamps(): void
    {
        $user = $this->user('legal-admin');
        $consultation = \App\Models\Consultation::unguarded(
            fn () => \App\Models\Consultation::create([
                'user_id' => $user->id,
                'title' => 'Riwayat lama',
                'description' => 'Tetap tersimpan',
                'status' => 'CLOSED',
                'created_at' => '2026-10-01 08:00:00',
                'updated_at' => '2026-10-04 08:00:00',
            ]),
        );
        (require database_path('migrations/2026_10_15_100000_merge_closed_consultations_into_finished.php'))->up();
        $this->assertDatabaseHas('consultations', [
            'id' => $consultation->id,
            'status' => 'FINISHED',
            'title' => 'Riwayat lama',
            'updated_at' => '2026-10-04 08:00:00',
        ]);
    }

    public function test_library_keeps_role_sidebar_on_catalog_filters_and_detail(): void
    {
        $owner = $this->user('legal-admin');
        $category = LegalCategory::create(['name' => 'Kategori Publik']);
        $document = LegalDocument::create([
            'name' => 'Dokumen Publik',
            'category_id' => $category->id,
            'file_path' => 'published.pdf',
            'created_by' => $owner->id,
            'status' => LegalDocument::STATUS_ACTIVE,
            'is_published' => true,
        ]);
        $dashboards = [
            'legal-admin' => 'legal.dashboard',
            'sekretariat' => 'dashboard.sekretariat',
            'staf-bidang' => 'dashboard.staf',
            'employee' => 'employee.consultations.index',
            'super-admin' => 'dashboard.admin',
        ];
        foreach ($dashboards as $role => $dashboard) {
            $this->actingAs($this->user($role));
            foreach (
                [
                    route('library.index'),
                    route('library.index', ['cari' => 'Dokumen', 'category_id' => $category->id]),
                    route('library.show', $document),
                ] as $url
            ) {
                $response = $this->get($url)
                    ->assertOk()
                    ->assertSee('Dokumen Publik')
                    ->assertSee('id="appShell"', false)
                    ->assertSee('<aside', false)
                    ->assertSee(route($dashboard))
                    ->assertDontSee('Kembali ke Dashboard');
                $this->assertSame(1, substr_count($response->getContent(), '<!DOCTYPE html>'));
                if ($role !== 'super-admin') {
                    $this->assertMatchesRegularExpression(
                        '/href="' . preg_quote(route('library.index'), '/') . '" class="nav-link active"/',
                        $response->getContent(),
                    );
                }
            }
        }
        $document->update(['is_published' => false]);
        $this->get(route('library.show', $document))->assertNotFound();
        $this->get(route('library.index'))->assertDontSee('Dokumen Publik');
    }

    public function test_migration_removes_unused_defaults_preserves_documents_and_connects_existing_masters(): void
    {
        $migration = require database_path(
            'migrations/2026_10_14_150000_connect_legal_categories_to_archive_master.php',
        );
        $user = $this->user('super-admin');
        DB::table('kategori_arsips')->insert(['nama' => 'Master Hukum', 'bidang_id' => $user->bidang_id]);
        $unused = LegalCategory::create(['name' => 'Dokumen Perizinan', 'description' => 'Dokumen izin dan lisensi']);
        $used = LegalCategory::create([
            'name' => 'Surat Keputusan',
            'description' => 'Surat keputusan resmi perusahaan',
        ]);
        $custom = LegalCategory::create(['name' => 'Peraturan Daerah', 'description' => 'Deskripsi dari admin']);
        $document = LegalDocument::create([
            'name' => 'Dokumen Lama',
            'category_id' => $used->id,
            'file_path' => 'existing.pdf',
            'created_by' => $user->id,
        ]);
        $migration->up();
        $this->assertDatabaseMissing('legal_categories', ['id' => $unused->id]);
        $this->assertDatabaseHas('legal_documents', ['id' => $document->id, 'category_id' => $used->id]);
        $this->assertDatabaseHas('legal_categories', ['id' => $custom->id]);
        $this->assertSame(['Master Hukum'], LegalCategory::masterHukum()->pluck('name')->all());
    }
}
