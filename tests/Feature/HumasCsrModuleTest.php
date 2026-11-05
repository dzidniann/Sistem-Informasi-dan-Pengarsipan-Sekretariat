<?php

namespace Tests\Feature;

use App\Models\{Bidang, HumasCsr, HumasCsrAttachment, KategoriArsip, Role, User};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Storage};
use Tests\TestCase;
use ZipArchive;

class HumasCsrModuleTest extends TestCase
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
        Storage::fake('local');
        $this->seed(\Database\Seeders\HumasSeeder::class);
    }

    private function user(string $role = 'humas', ?Bidang $bidang = null): User
    {
        return User::create([
            'name' => 'Petugas Humas',
            'email' => uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role_id' => Role::firstOrCreate(['nama' => $role])->id,
            'bidang_id' => ($bidang ?? Bidang::where('nama_bidang', 'Humas')->firstOrFail())->id,
            'is_active' => true,
        ]);
    }

    private function category(User $user): KategoriArsip
    {
        return KategoriArsip::create(['nama' => 'CSR/TJSLP', 'bidang_id' => $user->bidang_id]);
    }

    private function payload(KategoriArsip $category): array
    {
        return [
            'pemohon' => 'Komunitas Pengujian',
            'nama_kegiatan' => 'Bantuan Kegiatan',
            'nomor_surat' => '001/CSR/2026',
            'tanggal_proses' => '2026-05-07',
            'keluar' => 'DILAKSANAKAN (DIHADIRI DIRUT)',
            'nomor_voucher' => '000973',
            'kategori_id' => $category->id,
            'status' => 'DIBANTU',
            'luaran' => 'DANA',
            'nominal' => '1000000.25',
            'keterangan' => 'Catatan <aman>',
            'lampiran_surat' => UploadedFile::fake()->createWithContent('surat.pdf', "%PDF-1.4\nCSR"),
        ];
    }

    private function record(User $user, array $extra = []): HumasCsr
    {
        $data = $this->payload($this->category($user));
        unset($data['lampiran_surat']);

        return HumasCsr::create(
            array_replace($data, ['bidang_id' => $user->bidang_id, 'created_by' => $user->id], $extra),
        );
    }

    public function test_role_setup_login_and_shared_portals_use_humas_sidebar(): void
    {
        $beforeUsers = User::count();
        $this->seed(\Database\Seeders\HumasSeeder::class);
        $this->assertSame(1, Role::where('nama', 'humas')->count());
        $this->assertSame(1, Bidang::where('nama_bidang', 'Humas')->count());
        $this->assertSame($beforeUsers, User::count());
        $user = $this->user();
        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])->assertRedirect(
            route('humas.dashboard'),
        );
        foreach (
            [
                'humas.dashboard',
                'humas.csr.index',
                'humas.csr.create',
                'humas.reports.index',
                'library.index',
                'employee.consultations.index',
                'log-aktivitas.index',
            ] as $route
        ) {
            $this->get(route($route))->assertOk()->assertSee('Pengarsipan Humas')->assertSee('Input Arsip CSR');
        }
        $this->get(route('login'))->assertRedirect(route('humas.dashboard'));
    }

    public function test_csr_crud_keeps_free_text_private_attachments_and_logs(): void
    {
        $user = $this->user();
        $category = $this->category($user);
        $this->actingAs($user)->post(route('humas.csr.store'), $this->payload($category))->assertSessionHasNoErrors();
        $csr = HumasCsr::sole();
        $this->assertSame('DILAKSANAKAN (DIHADIRI DIRUT)', $csr->keluar);
        $this->assertSame('DIBANTU', $csr->status);
        $this->assertSame('000973', $csr->nomor_voucher);
        $this->assertSame('1000000.25', $csr->nominal);
        $attachment = $csr->attachments()->sole();
        Storage::disk('local')->assertExists($attachment->path);
        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->get(route('humas.csr.show', $csr))->assertOk()->assertSee('000973');
        $this->get(route('humas.csr.edit', $csr))->assertOk();
        $this->get(route('humas.csr.attachment', [$csr, $attachment]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
        $download = $this->get(route('humas.csr.attachment', [$csr, $attachment, 'download' => 1]));
        $download->assertOk();
        $this->assertStringContainsString('attachment;', $download->headers->get('Content-Disposition'));
        $data = $this->payload($category);
        $data['keluar'] = 'BANTUAN DISERAHKAN';
        $data['hapus_lampiran'] = [$attachment->id];
        $this->put(route('humas.csr.update', $csr), $data)->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($attachment->path);
        $this->assertSame('BANTUAN DISERAHKAN', $csr->fresh()->keluar);
        $newPath = $csr->attachments()->sole()->path;
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => HumasCsr::class,
            'subject_id' => $csr->id,
            'event' => 'updated',
        ]);
        $this->delete(route('humas.csr.destroy', $csr))->assertRedirect(route('humas.csr.index'));
        Storage::disk('local')->assertMissing($newPath);
        $this->assertDatabaseCount('humas_csr_attachments', 0);
    }

    public function test_role_field_and_attachment_access_cannot_be_bypassed(): void
    {
        $owner = $this->user();
        $csr = $this->record($owner);
        $this->actingAs($owner)
            ->post(route('humas.csr.store'), $this->payload($csr->kategori))
            ->assertSessionHasNoErrors();
        $stored = HumasCsr::latest('id')->firstOrFail();
        $attachment = $stored->attachments()->sole();
        $otherBidang = Bidang::create(['nama_bidang' => 'Humas Cabang', 'akses_semua_bidang' => true]);
        $outsider = $this->user('humas', $otherBidang);
        $this->assertFalse($outsider->bisaAksesSemuaBidang());
        $this->actingAs($outsider)
            ->get(route('humas.csr.index', ['cari' => 'Komunitas']))
            ->assertOk()
            ->assertViewHas('records', fn ($records) => $records->total() === 0);
        foreach (['humas.csr.show', 'humas.csr.edit'] as $route) {
            $this->get(route($route, $stored))->assertForbidden();
        }
        $this->put(route('humas.csr.update', $stored), $this->payload($csr->kategori))->assertForbidden();
        $this->delete(route('humas.csr.destroy', $stored))->assertForbidden();
        $this->get(route('humas.csr.attachment', [$stored, $attachment]))->assertForbidden();
        $this->post(route('humas.csr.store'), $this->payload($csr->kategori))->assertSessionHasErrors('kategori_id');
        $this->get(
            route('humas.reports.excel', ['tahun' => 2026, 'kategori_id' => $csr->kategori_id]),
        )->assertSessionHasErrors('kategori_id');
        $this->actingAs($owner)->get(route('humas.csr.attachment', [$csr, $attachment]))->assertNotFound();
        foreach (['staf-bidang', 'sekretariat', 'legal-admin', 'employee', 'super-admin'] as $role) {
            $this->actingAs($this->user($role))->get(route('humas.csr.index'))->assertForbidden();
        }
        auth()->logout();
        $this->getJson(route('humas.csr.attachment', [$stored, $attachment]))->assertUnauthorized();
    }

    public function test_validation_and_category_deletion_preserve_data_integrity(): void
    {
        $user = $this->user();
        $category = $this->category($user);
        $data = $this->payload($category);
        $this->actingAs($user);
        $this->postJson(route('humas.csr.store'), array_replace($data, [
            'lampiran_surat' => UploadedFile::fake()->create('large.pdf', 8193, 'application/pdf'),
        ]))->assertUnprocessable()->assertJsonValidationErrors('lampiran_surat');
        $this->postJson(route('humas.csr.store'), array_replace($data, [
            'foto' => [UploadedFile::fake()->image('large.png')->size(4097)],
        ]))->assertUnprocessable()->assertJsonValidationErrors('foto.0');
        $this->postJson(route('humas.csr.store'), array_replace($data, [
            'lampiran_surat' => UploadedFile::fake()->create('one.pdf', 8192, 'application/pdf'),
            'bukti_bantuan' => UploadedFile::fake()->create('two.pdf', 8192, 'application/pdf'),
        ]))->assertUnprocessable()->assertJsonValidationErrors('lampiran_surat');
        foreach (['nominal', 'lampiran_surat', 'pemohon', 'tanggal_proses'] as $field) {
            $invalid = $data;
            unset($invalid[$field]);
            $this->postJson(route('humas.csr.store'), $invalid)
                ->assertUnprocessable()
                ->assertJsonValidationErrors($field);
        }
        foreach (['0', '-1', '1.123'] as $amount) {
            $this->postJson(route('humas.csr.store'), array_replace($data, ['nominal' => $amount]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('nominal');
        }
        $this->postJson(
            route('humas.csr.store'),
            array_replace($data, [
                'lampiran_surat' => UploadedFile::fake()->createWithContent('bad.html', '<script>bad</script>'),
            ]),
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('lampiran_surat');
        $data['luaran'] = 'TIDAK_ADA';
        unset($data['nominal']);
        $this->post(route('humas.csr.store'), $data)->assertSessionHasNoErrors();
        $csr = HumasCsr::sole();
        $this->assertSame('0.00', $csr->nominal);
        $update = $data;
        unset($update['lampiran_surat']);
        $update['hapus_lampiran'] = [$csr->attachments()->sole()->id];
        $this->put(route('humas.csr.update', $csr), $update)->assertSessionHasErrors('hapus_lampiran');
        $this->actingAs($this->user('super-admin'))
            ->delete(route('kategori-arsip.destroy', $category))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('kategori_arsips', ['id' => $category->id]);
    }

    public function test_failed_save_rolls_back_record_and_removes_new_file(): void
    {
        $user = $this->user();
        $data = $this->payload($this->category($user));
        HumasCsrAttachment::creating(function () {
            throw new \RuntimeException('Simulated CSR save failure');
        });
        $this->actingAs($user)->post(route('humas.csr.store'), $data)->assertSessionHas('error');
        $this->assertDatabaseCount('humas_csrs', 0);
        $this->assertDatabaseCount('humas_csr_attachments', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
        HumasCsrAttachment::flushEventListeners();
    }

    public function test_filters_period_and_excel_match_spreadsheet_structure(): void
    {
        $user = $this->user();
        $may = $this->record($user, ['keluar' => '=HYPERLINK("https://example.test","literal")']);
        $this->record($user, ['tanggal_proses' => '2026-06-01', 'nama_kegiatan' => 'Bulan Juni']);
        $this->record($user, ['tanggal_proses' => '2025-05-07', 'nama_kegiatan' => 'Tahun Lama']);
        $other = $this->user('humas', Bidang::create(['nama_bidang' => 'Bidang Lain']));
        $this->record($other, ['nama_kegiatan' => 'Arsip Bidang Lain']);
        $this->actingAs($user);
        $filters = ['tahun' => 2026, 'bulan' => 5];
        $this->get(route('humas.reports.index', $filters))
            ->assertOk()
            ->assertViewHas('records', fn ($records) => $records->count() === 1);
        $response = $this->get(route('humas.reports.excel', $filters))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $file = $response->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($file));
        $workbook = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
        $this->assertCount(1, $workbook->sheets->sheet);
        $this->assertSame('MEI', (string) $workbook->sheets->sheet['name']);
        $sheet = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
        $this->assertSame('000973', (string) $sheet->sheetData->row[4]->c[6]->is->t);
        $this->assertSame($may->keluar, (string) $sheet->sheetData->row[4]->c[5]->is->t);
        $this->assertSame('inlineStr', (string) $sheet->sheetData->row[4]->c[5]['t']);
        $this->assertSame('1000000.25', (string) $sheet->sheetData->row[4]->c[10]->v);
        $this->assertCount(5, $sheet->sheetData->row);
        $this->assertSame('MEI', (string) $sheet->sheetData->row[1]->c[0]->is->t);
        $this->assertStringContainsString('mergeCell ref="A2:D2"', $zip->getFromName('xl/worksheets/sheet1.xml'));
        $this->assertStringNotContainsString('TOTAL NOMINAL', $zip->getFromName('xl/worksheets/sheet1.xml'));
        $this->assertStringNotContainsString('REKAP CSR HUMAS', $zip->getFromName('xl/worksheets/sheet1.xml'));
        $this->assertSame(
            '"DIBANTU,BELUM DIBANTU,DIBUATKAN,DIKETAHUI,DILAKSANAKAN,DIGANTI,DIPENDING,-"',
            (string) $sheet->dataValidations->dataValidation[0]->formula1,
        );
        $this->assertSame('"DANA,AIR MINERAL,BARANG,-"', (string) $sheet->dataValidations->dataValidation[1]->formula1);
        $this->assertSame('"' . implode(',', HumasCsr::CATEGORIES) . '"', (string) $sheet->dataValidations->dataValidation[2]->formula1);
        $this->assertCount(8, $sheet->conditionalFormatting[0]->cfRule);
        $this->assertCount(4, $sheet->conditionalFormatting[1]->cfRule);
        $this->assertSame('12.63', (string) $sheet->sheetFormatPr['defaultColWidth']);
        $this->assertSame('15.88', (string) $sheet->cols->col[0]['width']);
        $this->assertStringContainsString('Poppins', $zip->getFromName('xl/styles.xml'));
        $this->assertStringContainsString('dd&quot;/&quot;mm&quot;/&quot;yy', $zip->getFromName('xl/styles.xml'));
        $this->assertStringContainsString('[$Rp]#,##0', $zip->getFromName('xl/styles.xml'));
        $this->assertNotFalse($zip->getFromName('xl/theme/theme1.xml'));
        $styleDocument = simplexml_load_string($zip->getFromName('xl/styles.xml'));
        $this->assertCount(87, $styleDocument->cellXfs->xf);
        $this->assertCount(12, $styleDocument->dxfs->dxf);
        $this->assertSame('75', (string) $sheet->sheetData->row[4]->c[7]['s']);
        $this->assertSame('83', (string) $sheet->sheetData->row[4]->c[9]['s']);
        $this->assertStringContainsString('mergeCell ref="B3:D3"', $zip->getFromName('xl/worksheets/sheet1.xml'));
        foreach (range(1, $zip->numFiles) as $i) {
            $name = $zip->getNameIndex($i - 1);
            if (str_ends_with($name, '.xml') || str_ends_with($name, '.rels')) {
                $this->assertNotFalse(simplexml_load_string($zip->getFromName($name)));
            }
        }
        $zip->close();
        @unlink($file);
        $annual = $this->get(route('humas.reports.excel', ['tahun' => 2026]))->assertOk();
        $path = $annual->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive();
        $zip->open($path);
        $this->assertCount(12, simplexml_load_string($zip->getFromName('xl/workbook.xml'))->sheets->sheet);
        $this->assertStringNotContainsString('Arsip Bidang Lain', $zip->getFromName('xl/worksheets/sheet5.xml'));
        $zip->close();
        @unlink($path);
        $january = $may->replicate();
        $january->tanggal_proses = '1900-01-01';
        $february = $may->replicate();
        $february->tanggal_proses = '1900-02-28';
        $path = (new \App\Services\HumasCsrExcel())->make(collect([$january, $february]), 1900);
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path));
        $this->assertSame(
            '1',
            (string) simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'))->sheetData->row[4]->c[4]->v,
        );
        $this->assertSame(
            '59',
            (string) simplexml_load_string($zip->getFromName('xl/worksheets/sheet2.xml'))->sheetData->row[4]->c[4]->v,
        );
        $zip->close();
        @unlink($path);
        $this->get(route('humas.reports.index', ['tahun' => 2026, 'bulan' => 13]))->assertSessionHasErrors('bulan');
    }

    public function test_template_options_categories_and_all_new_choices_can_be_saved(): void
    {
        $this->seed(\Database\Seeders\HumasCsrTemplateSeeder::class);
        $bidang = Bidang::where('nama_bidang', 'Humas')->firstOrFail();
        $this->assertSame(HumasCsr::CATEGORIES, KategoriArsip::where('bidang_id', $bidang->id)->orderBy('id')->pluck('nama')->all());
        $user = $this->user();
        $this->actingAs($user);
        $response = $this->get(route('humas.csr.create'))->assertOk();
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertCount(11, $xpath->query('//select[@id="kategori_id"]/option'));
        foreach (['status' => HumasCsr::STATUSES, 'luaran' => HumasCsr::LUARAN] as $field => $choices) {
            $options = $xpath->query('//select[@id="' . $field . '"]/option');
            $this->assertCount(count($choices), $options);
            $this->assertSame(array_values($choices), array_map(fn ($option) => trim($option->textContent), iterator_to_array($options)));
        }
        foreach (HumasCsr::STATUSES as $status => $label) {
            $data = $this->payload(KategoriArsip::where('bidang_id', $bidang->id)->firstOrFail());
            $data['status'] = $status;
            $this->post(route('humas.csr.store'), $data)->assertSessionHasNoErrors();
            $this->assertSame($label, HumasCsr::latest('id')->first()->status_label);
        }
        foreach (HumasCsr::LUARAN as $luaran => $label) {
            $data = $this->payload(KategoriArsip::where('bidang_id', $bidang->id)->firstOrFail());
            $data['luaran'] = $luaran;
            $this->post(route('humas.csr.store'), $data)->assertSessionHasNoErrors();
            $this->assertSame($label, HumasCsr::latest('id')->first()->luaran_label);
        }
        $data = $this->payload(KategoriArsip::where('bidang_id', $bidang->id)->firstOrFail());
        $data['status'] = 'DIPROSES';
        $data['luaran'] = 'JASA';
        $data['kategori_id'] = KategoriArsip::create(['nama' => 'Kategori Lama', 'bidang_id' => $bidang->id])->id;
        $this->post(route('humas.csr.store'), $data)->assertSessionHasErrors(['status', 'luaran', 'kategori_id']);
    }

    public function test_legacy_values_remain_readable_and_require_explicit_reselection_when_edited(): void
    {
        $user = $this->user();
        $record = $this->record($user, ['status' => 'DIPROSES', 'luaran' => 'JASA']);
        $this->actingAs($user)->get(route('humas.csr.show', $record))->assertOk()->assertSee('DIPROSES')->assertSee('JASA');
        $this->get(route('humas.csr.edit', $record))->assertOk()->assertSee('Pilih status sesuai template')->assertSee('Pilih luaran sesuai template');
        $this->assertSame('DIPROSES', $record->fresh()->status);
        $this->assertSame('JASA', $record->fresh()->luaran);
    }

    public function test_excel_expands_validation_and_exports_display_labels_without_leaking_internal_codes(): void
    {
        $user = $this->user();
        $record = $this->record($user, ['status' => 'BELUM_DIBANTU', 'luaran' => 'AIR_MINERAL']);
        $records = collect(range(1, 140))->map(fn () => $record->replicate()->setRelation('kategori', $record->kategori));
        $path = (new \App\Services\HumasCsrExcel())->make($records, 2026, 5);
        $zip = new ZipArchive();
        $zip->open($path);
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $this->assertStringContainsString('BELUM DIBANTU', $sheet);
        $this->assertStringContainsString('AIR MINERAL', $sheet);
        $this->assertStringNotContainsString('BELUM_DIBANTU', $sheet);
        $this->assertStringNotContainsString('AIR_MINERAL', $sheet);
        $this->assertStringContainsString('sqref="H5:H144"', $sheet);
        $this->assertStringContainsString('sqref="J5:J144"', $sheet);
        $zip->close();
        @unlink($path);
    }

    public function test_pdf_and_empty_period_render_without_touching_other_modules(): void
    {
        $user = $this->user();
        $this->record($user);
        $this->actingAs($user)
            ->get(route('humas.reports.pdf', ['tahun' => 2026, 'bulan' => 5]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
        $response = $this->get(route('humas.reports.pdf', ['tahun' => 2026, 'bulan' => 2]))->assertOk();
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->get(route('humas.reports.index', ['tahun' => 2026, 'bulan' => 2]))
            ->assertOk()
            ->assertSee('Tidak ada arsip pada periode ini.');
    }

    public function test_docx_preview_displays_escaped_text_and_keeps_download_separate(): void
    {
        $user = $this->user();
        $csr = $this->record($user);
        $path = 'humas-private/'.$csr->id.'/proposal.docx';
        Storage::disk('local')->put($path, '');
        $zip = new ZipArchive();
        $zip->open(Storage::disk('local')->path($path), ZipArchive::OVERWRITE);
        $zip->addFromString('word/document.xml', '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>Proposal CSR &lt;script&gt;alert(1)&lt;/script&gt;</w:t></w:r></w:p></w:body></w:document>');
        $zip->close();
        $attachment = $csr->attachments()->create([
            'jenis' => 'surat', 'path' => $path, 'original_name' => 'proposal.docx',
            'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
        $response = $this->actingAs($user)->get(route('humas.csr.attachment', [$csr, $attachment, 'preview' => 1]));
        $response->assertOk()->assertSee('Proposal CSR <script>alert(1)</script>')->assertDontSee('<script>alert(1)</script>', false);
        $this->assertNull($response->headers->get('Content-Disposition'));
        $download = $this->get(route('humas.csr.attachment', [$csr, $attachment, 'download' => 1]));
        $download->assertOk();
        $this->assertStringContainsString('attachment;', $download->headers->get('Content-Disposition'));
        $outsider = $this->user('humas', Bidang::create(['nama_bidang' => 'Bidang Lain']));
        $this->actingAs($outsider)->get(route('humas.csr.attachment', [$csr, $attachment, 'preview' => 1]))->assertForbidden();
        $zip->open(Storage::disk('local')->path($path), ZipArchive::OVERWRITE);
        $zip->addFromString('word/document.xml', '<!DOCTYPE x [<!ENTITY secret SYSTEM "file:///etc/passwd">]><x>&secret;</x>');
        $zip->close();
        $this->assertNull((new \App\Services\HumasWordPreview())->paragraphs(Storage::disk('local')->path($path)));
    }

    public function test_named_uploads_use_real_ids_and_preserve_files_with_identical_names(): void
    {
        $user = $this->user();
        $data = $this->payload($this->category($user));
        $this->actingAs($user)->post(route('humas.csr.store'), $data)->assertSessionHasNoErrors();
        $csr = HumasCsr::sole();
        $first = $csr->attachments()->sole();
        $this->assertSame($first->id . '_surat.pdf', basename($first->path));
        $oldContent = Storage::disk('local')->get($first->path);
        $data['lampiran_surat'] = UploadedFile::fake()->createWithContent('surat.pdf', "%PDF-1.4\nVERSI BARU");
        $this->put(route('humas.csr.update', $csr), $data)->assertSessionHasNoErrors();
        $second = $csr->attachments()->latest('id')->firstOrFail();
        $this->assertSame($second->id . '_surat.pdf', basename($second->path));
        $this->assertNotSame($first->path, $second->path);
        $this->assertSame($oldContent, Storage::disk('local')->get($first->path));
        $this->assertSame("%PDF-1.4\nVERSI BARU", Storage::disk('local')->get($second->path));
        $file = UploadedFile::fake()->createWithContent('Proposal Bantuan.pdf', "%PDF-1.4\nASLI");
        $one = \App\Services\NamedUploads::store($file, 'humas-private', 'local', 125, 'file');
        $two = \App\Services\NamedUploads::store($file, 'humas-private', 'local', 125, 'file');
        $this->assertSame('125_Proposal_Bantuan.pdf', basename($one));
        $this->assertSame(basename($one), basename($two));
        $this->assertNotSame($one, $two);
        $this->assertSame(Storage::disk('local')->get($one), Storage::disk('local')->get($two));
        $unsafe = UploadedFile::fake()->createWithContent('payload.php', "%PDF-1.4\nASLI");
        $safePath = \App\Services\NamedUploads::store($unsafe, 'humas-private', 'local', 126, 'file');
        $this->assertNotSame('php', pathinfo($safePath, PATHINFO_EXTENSION));
        $this->assertStringStartsWith('126_payload.', basename($safePath));
    }
}
