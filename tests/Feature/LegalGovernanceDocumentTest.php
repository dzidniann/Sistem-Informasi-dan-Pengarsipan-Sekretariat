<?php

namespace Tests\Feature;

use App\Models\{Bidang, LegalGovernanceDocument, Role, User};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Storage};
use Tests\TestCase;

class LegalGovernanceDocumentTest extends TestCase
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
        Storage::fake('local');
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


    private function payload(): array
    {
        return [
            'judul_file' => 'Pedoman Tata Kelola',
            'tanggal' => '2026-10-30',
            'file' => UploadedFile::fake()->createWithContent('Pedoman.pdf', "%PDF-1.4\nTATA KELOLA"),
        ];
    }

    public function test_upload_private_preview_download_edit_and_delete(): void
    {
        $this->actingAs($this->user('legal-admin'));
        $this->get(route('legal.governance-documents.create'))->assertOk()->assertSee('Maksimal 5 MB.');
        $this->post(route('legal.governance-documents.store'), $this->payload())->assertSessionHasNoErrors();
        $document = LegalGovernanceDocument::sole();
        $this->assertSame($document->id . '_Pedoman.pdf', basename($document->file_path));
        Storage::disk('local')->assertExists($document->file_path);
        Storage::disk('public')->assertMissing($document->file_path);
        $this->get(route('legal.governance-documents.index', ['cari' => 'Pedoman']))->assertOk()->assertSee('Pedoman Tata Kelola');
        $this->get(route('legal.governance-documents.index', ['cari' => 'Tidak Ada']))->assertOk()->assertDontSee('Pedoman Tata Kelola');
        $this->get(route('legal.governance-documents.show', $document))->assertOk()->assertSee('<iframe', false);
        $preview = $this->get(route('legal.governance-documents.file', $document))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline;', $preview->headers->get('Content-Disposition'));
        $download = $this->get(route('legal.governance-documents.file', [$document, 'download' => 1]))->assertOk();
        $this->assertStringContainsString('attachment;', $download->headers->get('Content-Disposition'));
        $oldPath = $document->file_path;
        $this->put(route('legal.governance-documents.update', $document), ['judul_file' => 'Judul Diperbarui', 'tanggal' => '2026-10-31'])->assertSessionHasNoErrors();
        $this->assertSame($oldPath, $document->fresh()->file_path);
        $data = $this->payload(); $data['file'] = UploadedFile::fake()->createWithContent('Pedoman.pdf', "%PDF-1.4\nREVISI");
        $this->put(route('legal.governance-documents.update', $document), $data)->assertSessionHasNoErrors();
        $newPath = $document->fresh()->file_path;
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($newPath);
        $this->delete(route('legal.governance-documents.destroy', $document))->assertRedirect(route('legal.governance-documents.index'));
        Storage::disk('local')->assertMissing($newPath);
        $this->assertDatabaseCount('legal_governance_documents', 0);
    }

    public function test_pdf_only_five_mb_limit_and_required_fields(): void
    {
        $this->actingAs($this->user('legal-admin'));
        foreach (['judul_file', 'tanggal', 'file'] as $field) {
            $data = $this->payload(); unset($data[$field]);
            $this->postJson(route('legal.governance-documents.store'), $data)->assertUnprocessable()->assertJsonValidationErrors($field);
        }
        foreach ([
            UploadedFile::fake()->create('not-word.docx', 20, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            UploadedFile::fake()->image('gambar.jpg'),
            UploadedFile::fake()->create('large.pdf', 5121, 'application/pdf'),
            UploadedFile::fake()->createWithContent('misnamed.doc', "%PDF-1.4\nFILE"),
        ] as $file) {
            $this->postJson(route('legal.governance-documents.store'), array_replace($this->payload(), ['file' => $file]))
                ->assertUnprocessable()->assertJsonValidationErrors('file');
        }
        $this->postJson(route('legal.governance-documents.store'), array_replace($this->payload(), ['tanggal' => '2026-02-30']))
            ->assertUnprocessable()->assertJsonValidationErrors('tanggal');
        $this->assertDatabaseCount('legal_governance_documents', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
        $allowed = $this->payload();
        $allowed['file'] = UploadedFile::fake()->createWithContent('limit.pdf', "%PDF-1.4\nLIMIT")->size(5120);
        $this->post(route('legal.governance-documents.store'), $allowed)->assertSessionHasNoErrors();
        $document = LegalGovernanceDocument::sole();
        $originalPath = $document->file_path;
        $invalid = $this->payload();
        $invalid['file'] = UploadedFile::fake()->create('too-large.pdf', 5121, 'application/pdf');
        $this->putJson(route('legal.governance-documents.update', $document), $invalid)
            ->assertUnprocessable()->assertJsonValidationErrors('file');
        $this->assertSame($originalPath, $document->fresh()->file_path);

    }

    public function test_role_access_and_private_path_checks(): void
    {
        $this->actingAs($this->user('legal-admin'))->post(route('legal.governance-documents.store'), $this->payload())->assertSessionHasNoErrors();
        $document = LegalGovernanceDocument::sole();
        foreach (['humas', 'staf-bidang', 'sekretariat', 'employee'] as $role) {
            $this->actingAs($this->user($role));
            $this->get(route('legal.governance-documents.index'))->assertForbidden();
            $this->get(route('legal.governance-documents.show', $document))->assertForbidden();
            $this->get(route('legal.governance-documents.file', $document))->assertForbidden();
            $this->put(route('legal.governance-documents.update', $document), $this->payload())->assertForbidden();
            $this->delete(route('legal.governance-documents.destroy', $document))->assertForbidden();
        }
        $this->actingAs($this->user('super-admin'))->get(route('legal.governance-documents.index'))->assertOk();
        $document->update(['file_path' => '../outside.pdf']);
        $this->get(route('legal.governance-documents.file', $document))->assertNotFound();
        auth()->logout();
        $this->getJson(route('legal.governance-documents.file', $document))->assertUnauthorized();
    }

    public function test_failed_replacement_keeps_old_file_and_metadata(): void
    {
        $this->actingAs($this->user('legal-admin'))->post(route('legal.governance-documents.store'), $this->payload());
        $document = LegalGovernanceDocument::sole(); $oldPath = $document->file_path;
        LegalGovernanceDocument::updating(function () { throw new \RuntimeException('Simulated failure'); });
        try {
            $this->put(route('legal.governance-documents.update', $document), array_replace($this->payload(), ['judul_file' => 'Gagal']))->assertServerError();
            $this->assertSame('Pedoman Tata Kelola', $document->fresh()->judul_file);
            $this->assertSame($oldPath, $document->fresh()->file_path);
            $this->assertSame([$oldPath], Storage::disk('local')->allFiles());
        } finally {
            LegalGovernanceDocument::flushEventListeners();
        }
    }
}
