<?php

namespace Tests\Feature;

use App\Models\{Arsip, Bidang, Consultation, ConsultationMessage, KategoriArsip, LegalCategory, LegalDocument, Role, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaffPortalAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('protected');
    }

    public function test_staff_cannot_open_or_upload_archives_even_with_matching_bidang_and_access_flag(): void
    {
        $staff = User::factory()->create();
        $staff->bidang->update(['akses_semua_bidang' => true]);
        $staff = $staff->fresh();
        $category = KategoriArsip::create(['nama' => 'Privat', 'bidang_id' => $staff->bidang_id]);
        $archive = Arsip::create([
            'judul_file' => 'Arsip lama', 'tanggal' => today(), 'file_path' => 'arsip/old.pdf',
            'kategori_id' => $category->id, 'bidang_id' => $staff->bidang_id, 'uploaded_by' => $staff->id,
        ]);
        Storage::disk('protected')->put($archive->file_path, '%PDF-1.4 private');
        $this->actingAs($staff);
        $this->get(route('arsip.index'))->assertForbidden();
        $this->get(route('arsip.show', $archive))->assertForbidden();
        $this->post(route('arsip.store'), [
            'judul_file' => 'Baru', 'tanggal' => today()->toDateString(), 'kategori_id' => $category->id,
            'file' => UploadedFile::fake()->createWithContent('new.pdf', '%PDF-1.4 file'),
        ])->assertForbidden();
        $this->get('/storage/'.$archive->file_path)->assertNotFound();
        $this->get(route('log-aktivitas.index'))->assertForbidden();
        $this->get(route('dashboard.rekap-bulanan'))->assertForbidden();
        $this->get(route('dashboard.staf'))->assertRedirect(route('employee.consultations.index'));
        $this->assertDatabaseCount('arsips', 1);
        $this->assertFalse($staff->bisaAksesSemuaBidang());
    }

    public function test_staff_can_consult_and_read_published_library_documents_with_restricted_navigation(): void
    {
        $staff = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($staff)->post(route('employee.consultations.store'), [
            'title' => 'Pertanyaan staf', 'description' => 'Mohon konsultasi hukum',
        ])->assertSessionHasNoErrors()->assertRedirect(route('employee.consultations.index'));
        $consultation = Consultation::sole();
        $this->get(route('employee.consultations.index'))->assertOk()
            ->assertSee('Pertanyaan staf')->assertDontSee('Upload Berkas')->assertDontSee('Arsip Bidang')->assertDontSee('Log Aktivitas');
        $this->get(route('employee.consultations.create'))->assertOk();
        $this->get(route('employee.consultations.show', $consultation))->assertOk();
        $this->actingAs($other)->get(route('employee.consultations.show', $consultation))->assertForbidden();
        $legal = User::factory()->create(['role_id' => Role::firstOrCreate(['nama' => 'legal-admin'])->id]);
        $category = LegalCategory::create(['name' => 'Peraturan']);
        $document = LegalDocument::create([
            'name' => 'Bacaan pegawai', 'category_id' => $category->id, 'file_path' => 'legal/published.pdf',
            'created_by' => $legal->id, 'status' => 'ACTIVE', 'is_published' => true, 'expiry_date' => today()->addYear(),
        ]);
        Storage::disk('protected')->put($document->file_path, '%PDF-1.4 published');
        $this->actingAs($staff)->get(route('library.index'))->assertOk()->assertSee('Bacaan pegawai');
        $this->get(route('library.show', $document))->assertOk();
        $this->get('/storage/'.$document->file_path)->assertOk();
        $document->update(['is_published' => false]);
        $this->get('/storage/'.$document->file_path)->assertNotFound();
    }

    public function test_employee_migration_preserves_accounts_and_consultation_ownership_and_can_be_retried(): void
    {
        $legacyRole = Role::create(['nama' => 'employee']);
        $old = User::factory()->create(['role_id' => $legacyRole->id, 'is_active' => false]);
        $password = $old->password;
        $consultation = Consultation::create(['user_id' => $old->id, 'title' => 'Riwayat lama', 'description' => 'Tetap disimpan']);
        $message = ConsultationMessage::create(['consultation_id' => $consultation->id, 'user_id' => $old->id, 'message' => 'Pesan lama']);
        $migration = require database_path('migrations/2026_11_25_100000_merge_employee_into_staff_role.php');
        $migration->up();
        $migration->up();
        $moved = $old->fresh();
        $this->assertSame('staf-bidang', $moved->role->nama);
        $this->assertSame($old->bidang_id, $moved->bidang_id);
        $this->assertSame($password, $moved->password);
        $this->assertFalse((bool) $moved->is_active);
        $this->assertSame($old->id, $consultation->fresh()->user_id);
        $this->assertSame($old->id, $message->fresh()->user_id);
        $this->assertDatabaseMissing('roles', ['nama' => 'employee']);
        $this->assertSame(1, Role::where('nama', 'staf-bidang')->count());
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\LegalSeeder::class);
        $this->assertDatabaseMissing('roles', ['nama' => 'employee']);
    }

    public function test_retired_employee_role_is_hidden_and_rejected_by_admin_user_creation(): void
    {
        $legacyRole = Role::create(['nama' => 'employee']);
        $admin = User::factory()->create(['role_id' => Role::firstOrCreate(['nama' => 'super-admin'])->id]);
        $this->actingAs($admin)->get(route('pengguna.index'))->assertOk()
            ->assertViewHas('roles', fn ($roles) => ! $roles->contains('id', $legacyRole->id));
        $this->get(route('role.index'))->assertOk()
            ->assertViewHas('roles', fn ($roles) => ! $roles->contains('id', $legacyRole->id));
        $this->postJson(route('pengguna.store'), [
            'name' => 'Akun baru', 'email' => 'new@example.test', 'password' => 'password123',
            'role_id' => $legacyRole->id, 'bidang_id' => $admin->bidang_id,
        ])->assertUnprocessable()->assertJsonValidationErrors('role_id');
        $this->postJson(route('pengguna.store'), [
            'name' => 'Staf baru', 'email' => 'staff@example.test', 'password' => 'password123',
            'role_id' => Role::where('nama', 'staf-bidang')->value('id'), 'bidang_id' => $admin->bidang_id,
        ])->assertRedirect();
        $created = User::where('email', 'staff@example.test')->firstOrFail();
        $this->assertSame('staf-bidang', $created->role->nama);
        $this->putJson(route('pengguna.update', $created), [
            'name' => $created->name, 'email' => $created->email,
            'role_id' => $legacyRole->id, 'bidang_id' => $admin->bidang_id,
        ])->assertUnprocessable()->assertJsonValidationErrors('role_id');
    }

    public function test_employee_waiting_for_migration_already_has_the_same_portal_restrictions(): void
    {
        $old = User::factory()->create(['role_id' => Role::create(['nama' => 'employee'])->id]);
        $this->actingAs($old)->get(route('arsip.index'))->assertForbidden();
        $this->get(route('log-aktivitas.index'))->assertForbidden();
        $this->get(route('employee.consultations.index'))->assertOk();
        $this->get(route('library.index'))->assertOk();
    }
}
