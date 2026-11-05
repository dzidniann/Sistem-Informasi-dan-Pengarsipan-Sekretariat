<?php

namespace Tests\Feature;

use App\Models\{Bidang, KategoriArsip, LegalCategory, LegalDocument, Role, User};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class LegalDocumentExpiryTest extends TestCase
{
    private User $user;

    private LegalCategory $category;

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
        Carbon::setTestNow(Carbon::parse('2026-10-19 12:00:00', 'Asia/Makassar'));
        $bidang = Bidang::create(['nama_bidang' => 'Divisi Hukum']);
        $this->user = User::create([
            'name' => 'Petugas Legal',
            'email' => 'expiry@example.test',
            'password' => bcrypt('password'),
            'role_id' => Role::create(['nama' => 'legal-admin'])->id,
            'bidang_id' => $bidang->id,
            'is_active' => true,
        ]);
        $this->category = KategoriArsip::create(['nama' => 'Peraturan', 'bidang_id' => $bidang->id])->legalCategory;
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function document(array $attributes = []): LegalDocument
    {
        return LegalDocument::create(
            $attributes + [
                'name' => 'Peraturan Uji',
                'category_id' => $this->category->id,
                'created_by' => $this->user->id,
                'file_path' => 'legal-documents/test.pdf',
                'status' => 'ACTIVE',
            ],
        );
    }

    public function test_boundaries_use_calendar_dates_and_preserve_review_workflow(): void
    {
        foreach (
            [31 => 'ACTIVE', 30 => 'EXPIRING_SOON', 1 => 'EXPIRING_SOON', 0 => 'EXPIRING_SOON', -1 => 'EXPIRED'] as $days => $expected
        ) {
            $document = $this->document(['expiry_date' => today()->addDays($days)->toDateString()]);
            $this->assertSame($expected, $document->status);
            $this->assertSame($days < 0, $document->isExpired());
        }
        $this->assertSame('ACTIVE', $this->document()->status);
        foreach (['DRAFT', 'NEED_REVIEW'] as $workflow) {
            $this->assertSame(
                $workflow,
                $this->document(['status' => $workflow, 'expiry_date' => '2026-10-01'])->status,
            );
        }
    }

    public function test_scheduler_reconciles_stale_documents_and_is_idempotent(): void
    {
        $soon = $this->document(['expiry_date' => '2026-11-18']);
        $expired = $this->document(['expiry_date' => '2026-10-18']);
        DB::table('legal_documents')
            ->whereIn('id', [$soon->id, $expired->id])
            ->update(['status' => 'ACTIVE']);
        $draft = $this->document(['status' => 'DRAFT', 'expiry_date' => '2026-10-01']);
        $count = Activity::where('subject_type', LegalDocument::class)->count();
        $this->artisan('legal:refresh-document-statuses')
            ->expectsOutput('Status 2 dokumen berhasil diperbarui.')
            ->assertExitCode(0);
        $this->assertSame('EXPIRING_SOON', $soon->fresh()->status);
        $this->assertSame('EXPIRED', $expired->fresh()->status);
        $this->assertSame('DRAFT', $draft->fresh()->status);
        $this->assertSame($count + 2, Activity::where('subject_type', LegalDocument::class)->count());
        $this->artisan('legal:refresh-document-statuses')
            ->expectsOutput('Status 0 dokumen berhasil diperbarui.')
            ->assertExitCode(0);
        $this->assertSame($count + 2, Activity::where('subject_type', LegalDocument::class)->count());
    }

    public function test_midnight_transition_and_date_extensions_update_status(): void
    {
        $document = $this->document(['expiry_date' => '2026-10-19']);
        Carbon::setTestNow(Carbon::parse('2026-10-20 00:00:00', 'Asia/Makassar'));
        $this->artisan('legal:refresh-document-statuses')->assertExitCode(0);
        $this->assertSame('EXPIRED', $document->fresh()->status);
        $document->refresh()->update(['expiry_date' => '2026-12-31']);
        $this->assertSame('ACTIVE', $document->fresh()->status);
        $document->update(['expiry_date' => '2026-11-01']);
        $this->assertSame('EXPIRING_SOON', $document->status);
        $document->update(['expiry_date' => null]);
        $this->assertSame('ACTIVE', $document->status);
    }

    public function test_manual_approval_uses_actual_expiry_and_rejects_derived_status_input(): void
    {
        $this->actingAs($this->user);
        $document = $this->document(['status' => 'DRAFT', 'expiry_date' => '2026-10-18']);
        $this->patch(route('legal.documents.update-status', $document), ['status' => 'ACTIVE'])->assertSessionHas(
            'success',
            'Status berhasil diubah ke EXPIRED',
        );
        $this->assertSame('EXPIRED', $document->fresh()->status);
        $this->assertStringContainsString('ke EXPIRED', $document->histories()->firstOrFail()->description);
        $this->patch(route('legal.documents.update-status', $document), [
            'status' => 'EXPIRING_SOON',
        ])->assertSessionHasErrors('status');
    }

    public function test_still_valid_expiring_documents_remain_public_but_expired_ones_are_hidden(): void
    {
        $soon = $this->document(['expiry_date' => '2026-10-19', 'is_published' => true]);
        $expired = $this->document([
            'name' => 'Dokumen Kedaluwarsa',
            'expiry_date' => '2026-10-18',
            'is_published' => true,
        ]);
        $this->assertTrue($soon->canPublish());
        $this->assertFalse($expired->canPublish());
        // Simulate a delayed scheduler: publication still checks the actual date.
        DB::table('legal_documents')
            ->where('id', $expired->id)
            ->update(['status' => 'ACTIVE']);
        $this->actingAs($this->user)
            ->get(route('library.index'))
            ->assertOk()
            ->assertSee('Peraturan Uji')
            ->assertDontSee('Dokumen Kedaluwarsa');
        $this->get(route('library.show', $soon))->assertOk();
        $this->get(route('library.show', $expired))->assertNotFound();
        $this->get(route('legal.dashboard'))->assertOk()->assertViewHas('totalPublished', 1);
    }

    public function test_calendar_includes_pending_reviews_without_losing_consultations(): void
    {
        $review = $this->document(['name' => 'Review Oktober', 'status' => 'NEED_REVIEW', 'review_date' => '2026-10-22']);
        $undated = $this->document(['name' => 'Belum Ditentukan', 'status' => 'NEED_REVIEW']);
        $november = $this->document(['name' => 'Review November', 'status' => 'NEED_REVIEW', 'review_date' => '2026-11-02']);
        $active = $this->document(['name' => 'Review Aktif', 'status' => 'ACTIVE', 'review_date' => '2026-10-22']);
        $consultation = \App\Models\Consultation::create([
            'user_id' => $this->user->id, 'title' => 'Konsultasi Mendatang', 'description' => 'Uji jadwal',
            'status' => 'SCHEDULED', 'schedule' => '2026-10-22 10:00:00',
        ]);
        $this->actingAs($this->user)->get(route('legal.calendar.index', ['bulan' => 10, 'tahun' => 2026]))
            ->assertOk()->assertSee('Konsultasi Mendatang')->assertSee('Review Oktober')
            ->assertSee('Tanggal review belum diisi')->assertSee('Review Aktif')
            ->assertViewHas('reviewDocuments', fn ($items) => $items->modelKeys() === [$review->id, $active->id])
            ->assertViewHas('upcomingReviews', fn ($items) => $items->modelKeys() === [$review->id, $active->id])
            ->assertViewHas('pendingCount', 3);
        $events = $this->getJson(route('legal.calendar.events', ['bulan' => 10, 'tahun' => 2026]))
            ->assertOk()->assertJsonCount(3)->json();
        $documentEvent = collect($events)->firstWhere('type', 'document_review');
        $this->assertSame('document_' . $review->id, $documentEvent['id']);
        $this->assertTrue($documentEvent['allDay']);
        $this->assertSame(route('legal.documents.show', $review), $documentEvent['url']);
        $this->assertSame($consultation->id, collect($events)->firstWhere('type', 'consultation')['id']);
        $this->getJson(route('legal.calendar.events', ['bulan' => 11, 'tahun' => 2026]))
            ->assertOk()->assertJsonCount(1)->assertJsonFragment(['document_id' => $november->id]);
        $review->update(['status' => 'DRAFT']);
        $this->getJson(route('legal.calendar.events', ['bulan' => 10, 'tahun' => 2026]))->assertOk()->assertJsonCount(3);
        $review->update(['review_date' => null]);
        $this->getJson(route('legal.calendar.events', ['bulan' => 10, 'tahun' => 2026]))->assertOk()->assertJsonCount(2);
        $this->getJson(route('legal.calendar.events', ['bulan' => 13]))->assertUnprocessable();
    }

    public function test_review_status_changes_on_review_day_and_does_not_repeat_changes(): void
    {
        $active = $this->document(['review_date' => '2026-10-20', 'expiry_date' => '2026-12-31']);
        $draft = $this->document(['status' => 'DRAFT', 'review_date' => '2026-10-20']);
        $this->assertSame('ACTIVE', $active->status);
        $this->assertSame('DRAFT', $draft->status);
        Carbon::setTestNow(Carbon::parse('2026-10-20 00:00:00', 'Asia/Makassar'));
        $this->artisan('legal:refresh-document-statuses')->assertExitCode(0);
        $this->assertSame('NEED_REVIEW', $active->fresh()->status);
        $this->assertSame('NEED_REVIEW', $draft->fresh()->status);
        $logs = Activity::where('subject_type', LegalDocument::class)->count();
        $this->artisan('legal:refresh-document-statuses')->expectsOutput('Status 0 dokumen berhasil diperbarui.')->assertExitCode(0);
        $this->assertSame($logs, Activity::where('subject_type', LegalDocument::class)->count());
        $active->refresh()->update(['review_date' => '2026-11-01', 'status' => 'ACTIVE']);
        $this->assertSame('ACTIVE', $active->status);
        $this->assertSame('NEED_REVIEW', $this->document(['review_date' => '2026-10-19'])->status);
    }

    public function test_expiry_takes_priority_over_review_and_review_beats_expiring_soon(): void
    {
        $this->assertSame('NEED_REVIEW', $this->document(['review_date' => '2026-10-19', 'expiry_date' => '2026-10-25'])->status);
        $expired = $this->document(['review_date' => '2026-10-19', 'expiry_date' => '2026-10-19']);
        $this->assertSame('NEED_REVIEW', $expired->status);
        Carbon::setTestNow(Carbon::parse('2026-10-20 00:00:00', 'Asia/Makassar'));
        $this->artisan('legal:refresh-document-statuses')->assertExitCode(0);
        $this->assertSame('EXPIRED', $expired->fresh()->status);
    }
}
