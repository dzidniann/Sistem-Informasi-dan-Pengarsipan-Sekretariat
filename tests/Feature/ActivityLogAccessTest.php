<?php

namespace Tests\Feature;

use App\Models\{Bidang, Role, User};
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ActivityLogAccessTest extends TestCase
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

    private function user(string $role = 'staf-bidang', string $bidang = 'Kepegawaian'): User
    {
        return User::create([
            'name' => uniqid('Pengguna '),
            'email' => uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role_id' => Role::firstOrCreate(['nama' => $role])->id,
            'bidang_id' => Bidang::firstOrCreate(['nama_bidang' => $bidang])->id,
            'is_active' => true,
        ]);
    }

    public function test_staff_can_only_list_and_open_own_logs_even_with_same_role_and_department(): void
    {
        $user = $this->user();
        $peer = $this->user();
        $otherDepartment = $this->user('staf-bidang', 'Teknik');
        $own = activity()->causedBy($user)->log('Aktivitas milik sendiri');
        $foreign = activity()->causedBy($peer)->log('Aktivitas rekan satu bidang');
        $other = activity()->causedBy($otherDepartment)->log('Aktivitas bidang lain');
        $system = activity()->log('Aktivitas otomatis sistem');
        $nonUser = activity()->causedBy($user->bidang)->log('Aktivitas model lain');
        $this->actingAs($user)
            ->get(route('log-aktivitas.index'))
            ->assertOk()
            ->assertViewHas('logs', fn ($logs) => $logs->total() === 1 && $logs->contains('id', $own->id))
            ->assertSee('Riwayat aktivitas akun Anda sendiri.')
            ->assertDontSee('Aktivitas rekan satu bidang');
        $this->get(route('log-aktivitas.show', $own))->assertOk();
        foreach ([$foreign, $other, $system, $nonUser] as $denied) {
            $this->get(route('log-aktivitas.show', $denied))->assertForbidden();
        }
        $this->get(route('log-aktivitas.index', ['cari' => 'Aktivitas rekan']))
            ->assertOk()
            ->assertViewHas('logs', fn ($logs) => $logs->total() === 0);
        $this->get(route('log-aktivitas.index', ['cari' => $peer->name]))
            ->assertOk()
            ->assertViewHas('logs', fn ($logs) => $logs->total() === 0);
    }

    public function test_super_admin_lists_and_opens_staff_and_system_logs(): void
    {
        $staff = $this->user();
        $admin = $this->user('super-admin');
        $foreign = activity()->causedBy($staff)->log('Aktivitas staf');
        $system = activity()->log('Aktivitas sistem');
        $this->actingAs($admin)
            ->get(route('log-aktivitas.index'))
            ->assertOk()
            ->assertViewHas(
                'logs',
                fn ($logs) => $logs->contains('id', $foreign->id) && $logs->contains('id', $system->id),
            )
            ->assertSee('Riwayat aktivitas seluruh pengguna.');
        $this->get(route('log-aktivitas.show', $foreign))->assertOk();
        $this->get(route('log-aktivitas.show', $system))->assertOk();
    }

    public function test_other_non_admin_roles_also_cannot_open_peer_logs(): void
    {
        foreach (['sekretariat', 'legal-admin', 'employee'] as $role) {
            auth()->logout();
            $user = $this->user($role);
            $peer = $this->user($role);
            $log = activity()->causedBy($peer)->log('Riwayat pribadi rekan');
            $this->actingAs($user)
                ->get(route('log-aktivitas.index'))
                ->assertOk()
                ->assertViewHas('logs', fn ($logs) => !$logs->contains('id', $log->id));
            $this->get(route('log-aktivitas.show', $log))->assertForbidden();
        }
    }

    public function test_pagination_and_guests_do_not_bypass_access_rules(): void
    {
        $user = $this->user();
        $peer = $this->user();
        for ($i = 0; $i < 51; $i++) {
            activity()
                ->causedBy($user)
                ->log('Aktivitas sendiri ' . $i);
            activity()
                ->causedBy($peer)
                ->log('Aktivitas rekan ' . $i);
        }
        $this->get(route('log-aktivitas.index'))->assertRedirect(route('login'));
        $this->get(route('log-aktivitas.show', 1))->assertRedirect(route('login'));
        $this->actingAs($user)
            ->get(route('log-aktivitas.index', ['page' => 2]))
            ->assertOk()
            ->assertViewHas(
                'logs',
                fn ($logs) => $logs->total() === 51 &&
                    $logs->count() === 1 &&
                    $logs->every(fn ($log) => $log->causer->is($user)),
            );
    }
}
