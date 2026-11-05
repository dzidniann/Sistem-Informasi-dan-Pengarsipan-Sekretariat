<?php

namespace App\Http\Controllers;

use App\Services\NamedUploads;

use App\Models\{LegalActivity, LegalNotulensi, LegalDocument, User};
use App\Services\LegalPrivateFiles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Validation\Rule;

class LegalActivityController extends Controller
{
    protected bool $minutes = false;

    protected function model(): string
    {
        return $this->minutes ? LegalNotulensi::class : LegalActivity::class;
    }

    protected function prefix(): string
    {
        return $this->minutes ? 'legal.notulensi' : 'legal.activities';
    }

    protected function fields(): array
    {
        return $this->minutes ? ['file_path', 'foto_path'] : ['dokumen_pendukung', 'foto_dokumentasi'];
    }

    public function index(Request $request)
    {
        $request->validate([
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'nullable|integer|between:1900,2100',
            'cari' => 'nullable|string|max:255',
        ]);
        $date = $this->minutes ? 'tanggal_rapat' : 'tanggal_kegiatan';
        $title = $this->minutes ? 'judul_rapat' : 'uraian_kegiatan';
        $query = $this->model()::with('document');
        if ($request->filled('cari')) {
            $query->where(function ($q) use ($request, $date, $title) {
                $q->where($title, 'like', '%' . $request->cari . '%')->orWhere(
                    $date,
                    'like',
                    '%' . $request->cari . '%',
                );
                if ($this->minutes) {
                    $q->orWhere('notulis', 'like', '%' . $request->cari . '%');
                }
            });
        }
        if ($request->filled('bulan')) {
            $query->whereMonth($date, $request->bulan);
        }
        if ($request->filled('tahun')) {
            $query->whereYear($date, $request->tahun);
        }
        $data = [
            'records' => $query->orderByDesc($date)->orderByDesc('id')->paginate(15)->withQueryString(),
            'minutes' => $this->minutes,
            'prefix' => $this->prefix(),
        ];
        if (!$this->minutes) {
            $data = array_merge($this->form()->getData(), $data);
        }

        return view($this->minutes ? 'legal.records.index' : 'legal.activities.index', $data);
    }

    protected function form($record = null)
    {
        return view($this->minutes ? 'legal.records.form' : 'legal.activities.form', [
            'record' => $record,
            'minutes' => $this->minutes,
            'prefix' => $this->prefix(),
            'documents' => LegalDocument::orderBy('name')->get(['id', 'name']),
            'activities' => $this->minutes
                ? LegalActivity::orderByDesc('tanggal_kegiatan')->get(['id', 'uraian_kegiatan', 'tanggal_kegiatan'])
                : collect(),
        ]);
    }

    public function create()
    {
        return $this->index(request());
    }

    public function edit($id)
    {
        return $this->form($this->model()::findOrFail($id));
    }

    public function show($id)
    {
        $record = $this->model()
            ::with(['document', 'creator', $this->minutes ? 'activity' : 'notulensi.document'])
            ->findOrFail($id);

        return view($this->minutes ? 'legal.records.show' : 'legal.activities.show', [
            'record' => $record,
            'minutes' => $this->minutes,
            'prefix' => $this->prefix(),
            'fields' => $this->fields(),
        ]);
    }

    protected function validated(Request $request): array
    {
        $rules = ['legal_document_id' => 'nullable|integer|exists:legal_documents,id'];
        if ($this->minutes) {
            $rules += [
                'judul_rapat' => 'required|string|max:255',
                'tanggal_rapat' => 'required|date_format:Y-m-d',
                'notulis' => 'required|string|max:100',
                'deskripsi_singkat' => 'nullable|string|max:20000',
                'legal_activity_id' => 'nullable|required_without:new_activity|integer|exists:legal_activities,id',
                'new_activity' => ['nullable', Rule::in(['1'])],
            ];
        } else {
            $rules += [
                'tanggal_kegiatan' => 'required|date_format:Y-m-d',
                'uraian_kegiatan' => 'required|string|max:255',
                'keterangan' => 'nullable|string|max:20000',
            ];
        }
        [$file, $photo] = $this->fields();
        $rules[$file] = 'nullable|file|mimes:pdf,doc,docx|max:8192';
        $rules[$photo] = 'nullable|image|mimes:jpg,jpeg,png|max:4096|dimensions:max_width=6000,max_height=6000';

        return $request->validate($rules);
    }

    public function store(Request $request)
    {
        return $this->save($request);
    }

    public function update(Request $request, $id)
    {
        return $this->save($request, $this->model()::findOrFail($id));
    }

    protected function save(Request $request, $record = null)
    {
        $data = $this->validated($request);
        $new = [];
        $old = [];
        $uploads = [];
        try {
            foreach ($this->fields() as $field) {
                unset($data[$field]);
                if ($request->hasFile($field)) {
                    $uploads[$field] = $request->file($field);
                    if ($record?->$field) {
                        $old[] = $record->$field;
                    }
                    $data[$field] = $record?->$field ?? '';
                    $data[$field . '_name'] = mb_substr(
                        preg_replace(
                            '/[^\pL\pN ._()-]/u',
                            '_',
                            basename($request->file($field)->getClientOriginalName()),
                        ),
                        0,
                        200,
                    );
                }
            }
            $record = DB::transaction(function () use ($data, $record, $uploads, &$new) {
                // Serialize automatic matching per author, including simultaneous submissions.
                if ($this->minutes) {
                    User::whereKey(auth()->id())->lockForUpdate()->firstOrFail();
                    if (!empty($data['new_activity'])) {
                        $attributes = [
                            'created_by' => auth()->id(),
                            'tanggal_kegiatan' => $data['tanggal_rapat'],
                            'uraian_kegiatan' => $data['judul_rapat'],
                            'legal_document_id' => $data['legal_document_id'] ?? null,
                        ];
                        $activity = LegalActivity::where('created_by', auth()->id())
                            ->whereDate('tanggal_kegiatan', $data['tanggal_rapat'])
                            ->where('uraian_kegiatan', $data['judul_rapat'])
                            ->where('legal_document_id', $data['legal_document_id'] ?? null)
                            ->first();
                        $activity ??= LegalActivity::create(
                            $attributes + ['keterangan' => $data['deskripsi_singkat'] ?? null],
                        );
                        $data['legal_activity_id'] = $activity->id;
                    } else {
                        LegalActivity::whereKey($data['legal_activity_id'])->lockForUpdate()->firstOrFail();
                    }
                    unset($data['new_activity']);
                }
                if ($record) {
                    $record->update($data);
                } else {
                    $record = $this->model()::create($data + ['created_by' => auth()->id()]);
                }
                foreach ($uploads as $field => $file) {
                    $path = NamedUploads::store(
                        $file,
                        'legal-private/' . ($this->minutes ? 'minutes' : 'activities'),
                        'local',
                        $record->id,
                        $field,
                    );
                    $new[] = $path;
                    $record->$field = $path;
                }
                if ($uploads) {
                    $record->saveQuietly();
                }

                return $record;
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($new);
            report($e);

            return back()
                ->withInput($request->except($this->fields()))
                ->with('error', 'Data gagal disimpan. Silakan coba kembali.');
        }
        Storage::disk('local')->delete($old);

        return redirect()
            ->route($this->prefix() . '.show', $record->id)
            ->with('success', 'Data berhasil disimpan.');
    }

    public function destroy($id)
    {
        $record = $this->model()::findOrFail($id);
        $deleted = DB::transaction(function () use ($record) {
            $record = $this->model()::whereKey($record->id)->lockForUpdate()->firstOrFail();
            if (!$this->minutes && $record->notulensi()->exists()) {
                return false;
            }
            $record->delete();

            return true;
        });
        if (!$deleted) {
            return back()->with(
                'error',
                'Kegiatan masih memiliki notulensi. Hapus atau pindahkan notulensi terlebih dahulu.',
            );
        }
        foreach ($this->fields() as $field) {
            if (LegalPrivateFiles::path($record->$field)) {
                Storage::disk('local')->delete($record->$field);
            }
        }

        return redirect()
            ->route($this->prefix() . '.index')
            ->with('success', 'Data berhasil dihapus.');
    }

    public function attachment(Request $request, $id, $field)
    {
        abort_unless(in_array($field, $this->fields(), true), 404);
        $record = $this->model()::findOrFail($id);
        $path = LegalPrivateFiles::path($record->$field);
        abort_unless($path, 404);
        $name = $record->{$field . '_name'} ?: basename($path);
        $mime = mime_content_type($path);
        $inline =
            !$request->boolean('download') && in_array($mime, ['application/pdf', 'image/jpeg', 'image/png'], true);

        return response()->download(
            $path,
            $name,
            ['Content-Type' => $mime, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store'],
            $inline ? 'inline' : 'attachment',
        );
    }
}
