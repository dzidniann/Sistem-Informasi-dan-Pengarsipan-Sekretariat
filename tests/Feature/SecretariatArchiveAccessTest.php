<?php

namespace Tests\Feature;

use App\Models\{Arsip, Bidang, KategoriArsip, Role, User};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Storage};
use Tests\TestCase;

class SecretariatArchiveAccessTest extends TestCase
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
        Storage::fake('public');
        Storage::fake('protected');
    }

    private function user(Bidang $bidang, string $role = 'sekretariat'): User
    {
        return User::create([
            'name' => 'Petugas ' . uniqid(),
            'email' => uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role_id' => Role::firstOrCreate(['nama' => $role])->id,
            'bidang_id' => $bidang->id,
            'is_active' => true,
        ]);
    }

    private function fixtures(): array
    {
        // Existing databases may still have the old cross-field flag enabled.
        $own = Bidang::create(['nama_bidang' => 'Sekretariat dan Pengarsipan', 'akses_semua_bidang' => true]);
        $other = Bidang::create(['nama_bidang' => 'Kepegawaian Rahasia', 'akses_semua_bidang' => false]);
        $user = $this->user($own);
        $peer = $this->user($own);
        $staff = $this->user($other, 'staf-bidang');
        $ownCategory = KategoriArsip::create(['nama' => 'Kategori Sekretariat', 'bidang_id' => $own->id]);
        $otherCategory = KategoriArsip::create(['nama' => 'Kategori Rahasia', 'bidang_id' => $other->id]);
        $archive = Arsip::create([
            'judul_file' => 'Dokumen Sekretariat',
            'tanggal' => today(),
            'file_path' => 'arsip/own.pdf',
            'kategori_id' => $ownCategory->id,
            'bidang_id' => $own->id,
            'uploaded_by' => $peer->id,
        ]);
        $hidden = Arsip::create([
            'judul_file' => 'Dokumen Luar Rahasia',
            'tanggal' => today(),
            'file_path' => 'arsip/other.pdf',
            'kategori_id' => $otherCategory->id,
            'bidang_id' => $other->id,
            'uploaded_by' => $staff->id,
        ]);

        return compact('own', 'other', 'user', 'staff', 'archive', 'hidden', 'ownCategory', 'otherCategory');
    }

    public function test_secretariat_list_search_pagination_and_detail_only_allow_own_field(): void
    {
        $data = $this->fixtures();
        $this->actingAs($data['user']);
        $this->assertFalse($data['user']->bisaAksesSemuaBidang());
        foreach (['', '?cakupan=semua', '?cakupan=sendiri', '?cari=Dokumen', '?page=1&cakupan=semua'] as $suffix) {
            $this->get(route('arsip.index') . $suffix)
                ->assertOk()
                ->assertViewHas(
                    'arsip',
                    fn ($records) => $records->total() === 1 && $records->first()->is($data['archive']),
                )
                ->assertSee('Dokumen Sekretariat')
                ->assertDontSee('Dokumen Luar Rahasia')
                ->assertDontSee('Kategori Rahasia')
                ->assertDontSee('Arsip Semua Bidang');
        }
        $this->get(route('arsip.index', ['cari' => 'Rahasia']))
            ->assertOk()
            ->assertViewHas('arsip', fn ($records) => $records->total() === 0);
        $this->get(route('arsip.show', $data['archive']))->assertOk();
        $this->get(route('arsip.show', $data['hidden']))->assertForbidden();
    }

    public function test_secretariat_upload_cannot_use_another_fields_category(): void
    {
        $data = $this->fixtures();
        $this->actingAs($data['user'])
            ->post(route('arsip.store'), [
                'judul_file' => 'Upload ditolak',
                'tanggal' => today()->toDateString(),
                'kategori_id' => $data['otherCategory']->id,
                'file' => UploadedFile::fake()->createWithContent('test.pdf', "%PDF-1.4\nTest"),
            ])
            ->assertForbidden();
        $this->assertDatabaseMissing('arsips', ['judul_file' => 'Upload ditolak']);
        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->post(route('arsip.store'), [
            'judul_file' => 'Upload sendiri',
            'tanggal' => today()->toDateString(),
            'kategori_id' => $data['ownCategory']->id,
            'file' => UploadedFile::fake()->createWithContent('test.pdf', "%PDF-1.4\nTest"),
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('arsips', ['judul_file' => 'Upload sendiri', 'bidang_id' => $data['own']->id]);
    }

    public function test_dashboard_summary_and_both_report_outputs_only_use_own_archives(): void
    {
        $data = $this->fixtures();
        $this->actingAs($data['user']);
        $this->get(route('dashboard.sekretariat'))
            ->assertOk()
            ->assertViewHas('bulanData', fn ($months) => collect($months)->sum('total') === 1);
        $params = ['bulan' => now()->month, 'tahun' => now()->year];
        $this->getJson(route('dashboard.rekap-bulanan', $params))
            ->assertOk()
            ->assertJsonPath('totalKeseluruhan', 1)
            ->assertJsonCount(1, 'rekap')
            ->assertJsonPath('rekap.0.bidang', $data['own']->nama_bidang);
        $this->get(route('laporan.generate', $params))
            ->assertOk()
            ->assertViewHas('totalArsip', 1)
            ->assertViewHas(
                'rekapPerBidang',
                fn ($records) => $records->count() === 1 && $records->first()['bidang']->is($data['own']),
            )
            ->assertDontSee($data['other']->nama_bidang)
            ->assertDontSee($data['otherCategory']->nama);
        $pdf = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf->shouldReceive('download')->once()->andReturn(response('PDF test'));
        Pdf::shouldReceive('loadView')
            ->once()
            ->withArgs(function ($view, $report) use ($data) {
                $this->assertSame('sekretariat.laporan.pdf', $view);
                $this->assertSame(1, $report['totalArsip']);
                $this->assertCount(1, $report['rekapPerBidang']);
                $this->assertTrue($report['rekapPerBidang']->first()['bidang']->is($data['own']));

                return true;
            })
            ->andReturn($pdf);
        $this->get(route('laporan.pdf', $params))->assertOk();
    }

    public function test_summary_requires_login_and_other_roles_keep_existing_archive_scope(): void
    {
        $this->getJson(route('dashboard.rekap-bulanan'))->assertUnauthorized();
        $data = $this->fixtures();
        $this->actingAs($data['staff'])
            ->getJson(route('dashboard.rekap-bulanan'))
            ->assertOk()
            ->assertJsonPath('totalKeseluruhan', 1)
            ->assertJsonPath('rekap.0.bidang', $data['other']->nama_bidang);
        $this->getJson(route('dashboard.rekap-bulanan', ['bulan' => 13]))->assertUnprocessable();
        $data['other']->update(['akses_semua_bidang' => true]);
        $staff = $data['staff']->fresh();
        $this->assertTrue($staff->bisaAksesSemuaBidang());
        $this->actingAs($staff)
            ->get(route('arsip.index'))
            ->assertOk()
            ->assertViewHas('arsip', fn ($records) => $records->total() === 2);
    }
}
