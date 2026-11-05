<?php

namespace Tests\Feature;

use App\Models\{Arsip, ArsipSurat, Bidang, Consultation, ConsultationMessage, KategoriArsip, KategoriSurat, LegalCategory, LegalDocument, Notulensi, Role, User};
use App\Services\ProtectedUploads;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Storage};
use Tests\TestCase;

class ProtectedUploadAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'activitylog.database_connection' => 'sqlite']);
        DB::purge('sqlite');
        foreach (glob(database_path('migrations/*.php')) as $migration) {
            $instance = require_once $migration;
            if (is_object($instance)) {
                $instance->up();
            } else {
                $class = \Illuminate\Support\Str::studly(implode('_', array_slice(explode('_', basename($migration, '.php')), 4)));
                if (class_exists($class)) {
                    (new $class())->up();
                } else {
                    (require $migration)->up();
                }
            }
        }
        foreach (['public', 'protected', 'local'] as $disk) {
            Storage::fake($disk);
        }
    }

    private function user(string $role = 'staf-bidang', ?Bidang $bidang = null): User
    {
        return User::create([
            'name' => 'Petugas', 'email' => uniqid().'@example.test', 'password' => bcrypt('password'),
            'role_id' => Role::firstOrCreate(['nama' => $role])->id,
            'bidang_id' => ($bidang ?? Bidang::create(['nama_bidang' => 'Bidang '.uniqid()]))->id,
            'is_active' => true,
        ]);
    }

    private function file(string $path, string $disk = 'protected'): string
    {
        Storage::disk($disk)->put($path, "%PDF-1.4\nDokumen privat");
        return '/storage/'.$path;
    }

    public function test_registration_is_closed_even_when_only_super_admin_role_exists(): void
    {
        $this->user('super-admin');
        $this->get('/register')->assertNotFound();
        $this->post('/register', ['name' => 'Tamu', 'email' => 'guest@example.test', 'password' => 'password', 'password_confirmation' => 'password'])->assertNotFound();
        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_archive_download_requires_login_and_correct_bidang(): void
    {
        $owner = $this->user();
        $peer = $this->user('staf-bidang', $owner->bidang);
        $outsider = $this->user();
        $category = KategoriArsip::create(['nama' => 'Arsip', 'bidang_id' => $owner->bidang_id]);
        Arsip::create(['judul_file' => 'Rahasia', 'tanggal' => today(), 'file_path' => 'arsip/a.pdf', 'kategori_id' => $category->id, 'bidang_id' => $owner->bidang_id, 'uploaded_by' => $owner->id]);
        $url = $this->file('arsip/a.pdf');
        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs($owner)->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->actingAs($peer)->get($url.'?download=1')->assertOk();
        $this->actingAs($outsider)->get($url)->assertNotFound();
        $this->actingAs($this->user('super-admin'))->get($url)->assertOk();
    }

    public function test_registration_controller_never_falls_back_to_super_admin(): void
    {
        $this->user('super-admin');
        config(['auth.allow_registration' => true]);
        $request = \Illuminate\Http\Request::create('/register', 'POST', ['name' => 'Tamu', 'email' => 'guest@example.test', 'password' => 'password', 'password_confirmation' => 'password']);
        try {
            app(\App\Http\Controllers\Auth\RegisteredUserController::class)->store($request);
            $this->fail('Missing employee role must fail closed.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {
            $this->assertSame(503, $error->getStatusCode());
        }
        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_explicit_registration_cannot_activate_itself_or_choose_admin_role(): void
    {
        $admin = $this->user('super-admin');
        $employee = Role::create(['nama' => 'employee']);
        $umum = Bidang::create(['nama_bidang' => 'Umum']);
        config(['auth.allow_registration' => true]);
        $request = \Illuminate\Http\Request::create('/register', 'POST', ['name' => 'Tamu', 'email' => 'guest@example.test', 'password' => 'password', 'password_confirmation' => 'password', 'role_id' => $admin->role_id, 'bidang_id' => $admin->bidang_id, 'is_active' => true]);
        app(\App\Http\Controllers\Auth\RegisteredUserController::class)->store($request);
        $created = User::where('email', 'guest@example.test')->firstOrFail();
        $this->assertSame($employee->id, $created->role_id);
        $this->assertSame($umum->id, $created->bidang_id);
        $this->assertFalse((bool) $created->is_active);
        $this->assertGuest();
    }

    public function test_new_archive_upload_is_private_and_retains_its_file_url(): void
    {
        $user = $this->user();
        $category = KategoriArsip::create(['nama' => 'Arsip', 'bidang_id' => $user->bidang_id]);
        $this->actingAs($user)->post(route('arsip.store'), ['judul_file' => 'Privat', 'tanggal' => today()->toDateString(), 'kategori_id' => $category->id, 'file' => UploadedFile::fake()->createWithContent('Berkas.pdf', "%PDF-1.4\nPRIVATE")])->assertSessionHasNoErrors();
        $archive = Arsip::sole();
        Storage::disk('protected')->assertExists($archive->file_path);
        Storage::disk('public')->assertMissing($archive->file_path);
        $this->get('/storage/'.$archive->file_path)->assertOk();
    }

    public function test_library_files_follow_publication_expiry_and_legal_access(): void
    {
        $legal = $this->user('legal-admin', Bidang::create(['nama_bidang' => 'Sub Bidang Hukum']));
        KategoriArsip::create(['nama' => 'Peraturan', 'bidang_id' => $legal->bidang_id]);
        $category = LegalCategory::firstOrFail();
        $published = LegalDocument::create(['name' => 'Peraturan', 'category_id' => $category->id, 'file_path' => 'legal-documents/a.pdf', 'status' => 'ACTIVE', 'expiry_date' => today()->addYear(), 'is_published' => true, 'created_by' => $legal->id]);
        $private = $published->replicate();
        $private->file_path = 'legal-documents/b.pdf';
        $private->is_published = false;
        $private->save();
        $publicUrl = $this->file($published->file_path);
        $privateUrl = $this->file($private->file_path);
        $reader = $this->user('employee');
        $this->actingAs($reader)->get($publicUrl)->assertOk();
        $this->get($privateUrl)->assertNotFound();
        DB::table('legal_documents')->where('id', $published->id)->update(['expiry_date' => today()->subDay()->toDateString(), 'status' => 'ACTIVE']);
        $this->get($publicUrl)->assertNotFound();
        $this->actingAs($legal)->get($publicUrl)->assertOk();
        $this->get($privateUrl)->assertOk();
    }

    public function test_consultation_and_message_files_are_not_shared_with_other_employees(): void
    {
        $owner = $this->user('employee');
        $outsider = $this->user('employee', $owner->bidang);
        $legal = $this->user('legal-admin');
        $consultation = Consultation::create(['user_id' => $owner->id, 'title' => 'Konsultasi', 'description' => 'Privat', 'attachment' => 'consultations/a.pdf', 'status' => 'WAITING_REVIEW']);
        ConsultationMessage::create(['consultation_id' => $consultation->id, 'user_id' => $legal->id, 'message' => 'Balasan', 'attachment' => 'consultation-messages/a.pdf']);
        foreach (['consultations/a.pdf', 'consultation-messages/a.pdf'] as $path) {
            $url = $this->file($path);
            $this->actingAs($owner)->get($url)->assertOk();
            $this->actingAs($outsider)->get($url)->assertNotFound();
            $this->actingAs($legal)->get($url)->assertOk();
        }
    }

    public function test_secretariat_minutes_photos_and_letters_require_role_and_bidang(): void
    {
        $secretary = $this->user('sekretariat');
        $outsider = $this->user('sekretariat');
        $staff = $this->user('staf-bidang', $secretary->bidang);
        Notulensi::create(['judul_rapat' => 'Rapat', 'tanggal_rapat' => today(), 'notulis' => 'Notulis', 'file_path' => 'notulensi/file/a.pdf', 'foto_path' => 'notulensi/foto/a.png', 'bidang_id' => $secretary->bidang_id, 'created_by' => $secretary->id]);
        $category = KategoriSurat::create(['kode_kategori' => 'QA', 'nama_kategori' => 'Surat', 'is_active' => true]);
        ArsipSurat::create(['id_kategori' => $category->id_kategori, 'nomor_urut' => 1, 'bulan_romawi' => 'XI', 'tahun' => 2026, 'full_nomor' => '001/QA/XI/2026', 'dirilis_oleh' => $secretary->id, 'file_path' => 'nomor-surat/a.pdf']);
        foreach (['notulensi/file/a.pdf', 'notulensi/foto/a.png', 'nomor-surat/a.pdf'] as $path) {
            $url = $this->file($path);
            $this->actingAs($secretary)->get($url)->assertOk();
            $this->actingAs($outsider)->get($url)->assertNotFound();
            $this->actingAs($staff)->get($url)->assertNotFound();
        }
    }

    public function test_orphan_paths_traversal_and_disabled_accounts_cannot_read_files(): void
    {
        $user = $this->user();
        $this->actingAs($user)->get($this->file('orphan.pdf'))->assertNotFound();
        foreach (['../.env', '/etc/passwd', 'a/../b.pdf', 'a\\b.pdf', 'a/%2e%2e/b.pdf', '.env'] as $path) {
            $this->assertFalse(ProtectedUploads::validPath($path));
        }
        $user->update(['is_active' => false]);
        $this->get('/storage/orphan.pdf')->assertRedirect('login');
    }

    public function test_migration_verifies_backup_and_preserves_database_paths(): void
    {
        $this->file('arsip/old.pdf', 'public');
        $this->artisan('files:protect-public', ['--dry-run' => true])->assertSuccessful();
        Storage::disk('public')->assertExists('arsip/old.pdf');
        $this->artisan('files:protect-public')->assertSuccessful();
        Storage::disk('public')->assertMissing('arsip/old.pdf');
        Storage::disk('protected')->assertExists('arsip/old.pdf');
        $backups = Storage::disk('local')->allFiles('private-backups');
        $this->assertCount(2, $backups);
        $backup = collect($backups)->first(fn ($path) => str_ends_with($path, '.pdf'));
        $this->assertSame(Storage::disk('protected')->get('arsip/old.pdf'), Storage::disk('local')->get($backup));
        $this->artisan('files:protect-public')->assertSuccessful();
    }

    public function test_conflicting_private_destination_is_not_overwritten(): void
    {
        Storage::disk('public')->put('arsip/a.pdf', 'original');
        Storage::disk('protected')->put('arsip/a.pdf', 'different');
        $this->artisan('files:protect-public')->assertFailed();
        $this->assertSame('original', Storage::disk('public')->get('arsip/a.pdf'));
        $this->assertSame('different', Storage::disk('protected')->get('arsip/a.pdf'));
    }

    public function test_passwords_are_excluded_from_new_logs_and_user_serialization(): void
    {
        $user = $this->user();
        $this->assertArrayNotHasKey('password', $user->toArray());
        $this->assertArrayNotHasKey('remember_token', $user->toArray());
        foreach (\Spatie\Activitylog\Models\Activity::where('subject_type', User::class)->get() as $activity) {
            $this->assertArrayNotHasKey('password', $activity->properties['attributes'] ?? []);
        }
    }
}
