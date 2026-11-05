<?php

namespace Tests\Audit;

use App\Models\{Bidang, Role, User, Notulensi};
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

// Opt-in evidence tests: these assert observed defects, NOT that the system is secure.
// Kept outside Feature/Unit so normal regression runs do not encode insecure expectations.
class SystemAuditTest extends TestCase
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
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => 'Audit User',
            'email' => uniqid() . '@example.test',
            'password' => bcrypt('AuditPassword123!'),
            'role_id' => Role::firstOrCreate(['nama' => $role])->id,
            'bidang_id' => Bidang::firstOrCreate(['nama_bidang' => 'Audit'])->id,
            'is_active' => true,
        ]);
    }

    public function test_evidence_registration_falls_back_to_super_admin_when_employee_role_is_missing(): void
    {
        Role::create(['nama' => 'super-admin']);
        Bidang::create(['nama_bidang' => 'Manajemen Sistem']);
        $this->post('/register', [
            'name' => 'Audit Registration',
            'email' => 'audit-registration@example.test',
            'password' => 'AuditPassword123!',
            'password_confirmation' => 'AuditPassword123!',
        ])->assertRedirect();
        $this->assertAuthenticated();
        $this->assertSame('super-admin', auth()->user()->role->nama);
    }

    public function test_evidence_second_role_is_rejected_on_legacy_legal_routes(): void
    {
        $this->actingAs($this->user('super-admin'))->get(route('legal.dashboard'))->assertForbidden();
        $this->get(route('legal.reports.index'))->assertOk();
    }

    public function test_evidence_password_hash_is_stored_in_activity_properties(): void
    {
        $user = $this->user('sekretariat');
        $log = Activity::where('subject_type', User::class)
            ->where('subject_id', $user->id)
            ->where('event', 'created')
            ->firstOrFail();
        $this->assertSame($user->password, $log->properties->get('attributes')['password']);
        $this->assertArrayHasKey('password', $user->toArray());
    }

    public function test_evidence_notulensi_update_route_has_no_controller_method(): void
    {
        $user = $this->user('sekretariat');
        $note = Notulensi::create([
            'judul_rapat' => 'Audit Minutes',
            'tanggal_rapat' => '2026-10-16',
            'notulis' => 'Audit',
            'file_path' => 'notulensi/file/audit.pdf',
            'bidang_id' => $user->bidang_id,
            'created_by' => $user->id,
        ]);
        $this->actingAs($user);
        $this->withoutExceptionHandling();
        $this->expectException(\BadMethodCallException::class);
        $this->put(route('notulensi.update', $note), ['judul_rapat' => 'Updated']);
    }
}
