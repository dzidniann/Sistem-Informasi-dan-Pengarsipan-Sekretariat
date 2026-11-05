<?php

namespace App\Http\Controllers;

use App\Models\HumasCsr;
use App\Models\HumasCsrAttachment;
use App\Models\KategoriArsip;
use App\Services\HumasPrivateFiles;
use App\Services\NamedUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HumasCsrController extends Controller
{
    public function categories()
    {
        return KategoriArsip::where('bidang_id', auth()->user()->bidang_id)
            ->whereIn('nama', HumasCsr::CATEGORIES)
            ->get()
            ->unique('nama')
            ->sortBy(fn ($category) => array_search($category->nama, HumasCsr::CATEGORIES, true))
            ->values();
    }

    public function filters(Request $request): array
    {
        return $request->validate([
            'cari' => 'nullable|string|max:255',
            'tahun' => 'nullable|integer|between:1900,2100',
            'bulan' => 'nullable|integer|between:1,12',
            'status' => ['nullable', Rule::in(array_keys(HumasCsr::STATUSES))],
            'luaran' => ['nullable', Rule::in(array_keys(HumasCsr::LUARAN))],
            'kategori_id' => [
                'nullable',
                Rule::exists('kategori_arsips', 'id')->where('bidang_id', $request->user()->bidang_id)->whereIn('nama', HumasCsr::CATEGORIES),
            ],
        ]);
    }

    public function dashboard()
    {
        $query = HumasCsr::visibleTo(auth()->user())->whereYear('tanggal_proses', now()->year);

        return view('humas.dashboard', [
            'total' => (clone $query)->count(),
            'dibantu' => (clone $query)->where('status', 'DIBANTU')->count(),
            'nominal' => (clone $query)->sum('nominal'),
            'recent' => (clone $query)->with('kategori')->latest('tanggal_proses')->latest('id')->take(5)->get(),
        ]);
    }

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $records = HumasCsr::visibleTo($request->user())
            ->filtered($filters)
            ->with(['kategori', 'creator'])
            ->latest('tanggal_proses')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('humas.csr.index', ['records' => $records, 'categories' => $this->categories()]);
    }

    public function create()
    {
        return view('humas.csr.form', ['csr' => null, 'categories' => $this->categories()]);
    }

    public function edit(HumasCsr $csr)
    {
        $this->authorizeRecord($csr);

        return view('humas.csr.form', ['csr' => $csr->load('attachments'), 'categories' => $this->categories()]);
    }

    public function show(HumasCsr $csr)
    {
        $this->authorizeRecord($csr);

        return view('humas.csr.show', ['csr' => $csr->load(['kategori', 'creator', 'attachments'])]);
    }

    protected function authorizeRecord(HumasCsr $csr): void
    {
        abort_unless((int) $csr->bidang_id === (int) auth()->user()->bidang_id, 403);
    }

    public function store(Request $request)
    {
        return $this->save($request);
    }

    public function update(Request $request, HumasCsr $csr)
    {
        $this->authorizeRecord($csr);

        return $this->save($request, $csr);
    }

    private function save(Request $request, ?HumasCsr $csr = null)
    {
        $data = $request->validate([
            'pemohon' => 'required|string|max:255',
            'nama_kegiatan' => 'required|string|max:255',
            'nomor_surat' => 'nullable|string|max:255',
            'tanggal_proses' => 'required|date_format:Y-m-d|after_or_equal:1900-01-01|before_or_equal:2100-12-31',
            'keluar' => 'nullable|string|max:5000',
            'nomor_voucher' => 'nullable|string|max:255',
            'kategori_id' => [
                'required',
                Rule::exists('kategori_arsips', 'id')->where('bidang_id', $request->user()->bidang_id)->whereIn('nama', HumasCsr::CATEGORIES),
            ],
            'status' => ['required', Rule::in(array_keys(HumasCsr::STATUSES))],
            'luaran' => ['required', Rule::in(array_keys(HumasCsr::LUARAN))],
            'nominal' => 'nullable|required_if:luaran,DANA|numeric|decimal:0,2|min:0|max:999999999999.99',
            'keterangan' => 'nullable|string|max:10000',
            'lampiran_surat' => ($csr ? 'nullable' : 'required').'|file|mimes:pdf,doc,docx|max:8192',
            'bukti_bantuan' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:8192',
            'foto' => 'nullable|array|max:5',
            'foto.*' => 'required|image|mimes:jpg,jpeg,png|max:4096|dimensions:max_width=6000,max_height=6000',
            'hapus_lampiran' => 'nullable|array|max:50',
            'hapus_lampiran.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('humas_csr_attachments', 'id')->where('humas_csr_id', $csr?->id ?? 0),
            ],
        ]);
        if ($data['luaran'] === 'DANA' && (float) $data['nominal'] <= 0) {
            throw ValidationException::withMessages(['nominal' => 'Nominal bantuan dana harus lebih dari nol.']);
        }
        $uploads = [];
        foreach (['lampiran_surat' => 'surat', 'bukti_bantuan' => 'bukti'] as $field => $jenis) {
            if ($request->hasFile($field)) {
                $uploads[] = [$request->file($field), $jenis];
            }
        }
        foreach ($request->file('foto', []) as $photo) {
            $uploads[] = [$photo, 'foto'];
        }
        if (array_sum(array_map(fn ($upload) => $upload[0]->getSize(), $uploads)) > 14 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'lampiran_surat' => 'Total lampiran dalam satu penyimpanan maksimal 14 MB.',
            ]);
        }
        $remove = $data['hapus_lampiran'] ?? [];
        unset($data['lampiran_surat'], $data['bukti_bantuan'], $data['foto'], $data['hapus_lampiran']);
        $data['nominal'] = $data['nominal'] ?? 0;
        $newPaths = [];
        $oldPaths = [];
        try {
            $csr = DB::transaction(function () use ($csr, $data, $uploads, $remove, &$newPaths, &$oldPaths, $request) {
                if ($csr) {
                    $csr = HumasCsr::whereKey($csr->id)->lockForUpdate()->firstOrFail();
                    $this->authorizeRecord($csr);
                    if ($csr->attachments()->count() - count($remove) + count($uploads) < 1) {
                        throw ValidationException::withMessages([
                            'hapus_lampiran' => 'Pertahankan minimal satu lampiran arsip.',
                        ]);
                    }
                    $csr->update($data);
                    foreach ($csr->attachments()->whereIn('id', $remove)->get() as $attachment) {
                        $oldPaths[] = $attachment->path;
                        $attachment->delete();
                    }
                } else {
                    $csr = HumasCsr::create(
                        $data + ['bidang_id' => $request->user()->bidang_id, 'created_by' => $request->user()->id],
                    );
                }
                foreach ($uploads as [$file, $jenis]) {
                    $name = mb_substr(
                        preg_replace('/[^\pL\pN ._()-]/u', '_', basename($file->getClientOriginalName())),
                        0,
                        200,
                    );
                    $attachment = NamedUploads::create(
                        HumasCsrAttachment::class,
                        [
                            'jenis' => $jenis,
                            'humas_csr_id' => $csr->id,
                            'original_name' => $name,
                            'mime' => $file->getMimeType(),
                        ],
                        ['path' => [$file, 'humas-private/'.$csr->id, 'local']],
                    );
                    $newPaths[] = $attachment->path;
                }

                return $csr;
            });
        } catch (\Throwable $error) {
            Storage::disk('local')->delete($newPaths);
            if ($error instanceof ValidationException) {
                throw $error;
            }
            report($error);

            return back()
                ->withInput($request->except(['lampiran_surat', 'bukti_bantuan', 'foto']))
                ->with('error', 'Arsip gagal disimpan. Silakan coba kembali.');
        }
        foreach ($oldPaths as $path) {
            if (HumasPrivateFiles::path($path)) {
                Storage::disk('local')->delete($path);
            }
        }

        return redirect()->route('humas.csr.show', $csr)->with('success', 'Arsip CSR berhasil disimpan.');
    }

    public function destroy(HumasCsr $csr)
    {
        $this->authorizeRecord($csr);
        $paths = DB::transaction(function () use ($csr) {
            $record = HumasCsr::whereKey($csr->id)->lockForUpdate()->firstOrFail();
            $this->authorizeRecord($record);
            $paths = $record->attachments()->pluck('path')->all();
            $record->delete();

            return $paths;
        });
        foreach ($paths as $path) {
            if (HumasPrivateFiles::path($path)) {
                Storage::disk('local')->delete($path);
            }
        }

        return redirect()->route('humas.csr.index')->with('success', 'Arsip CSR berhasil dihapus.');
    }

    public function attachment(Request $request, HumasCsr $csr, HumasCsrAttachment $attachment)
    {
        $this->authorizeRecord($csr);
        abort_unless((int) $attachment->humas_csr_id === (int) $csr->id, 404);
        $path = HumasPrivateFiles::path($attachment->path);
        abort_unless($path, 404);
        $mime = mime_content_type($path);
        if (
            $request->boolean('preview') &&
            ! $request->boolean('download') &&
            ! in_array($mime, ['application/pdf', 'image/jpeg', 'image/png'], true)
        ) {
            $paragraphs =
                strtolower(pathinfo($attachment->original_name, PATHINFO_EXTENSION)) === 'docx'
                    ? app(\App\Services\HumasWordPreview::class)->paragraphs($path)
                    : null;

            return response()
                ->view('humas.csr.attachment-preview', compact('csr', 'attachment', 'paragraphs'))
                ->header('Cache-Control', 'private, no-store')
                ->header('X-Content-Type-Options', 'nosniff');
        }
        $inline =
            ! $request->boolean('download') && in_array($mime, ['application/pdf', 'image/jpeg', 'image/png'], true);

        return response()->download(
            $path,
            $attachment->original_name,
            ['Content-Type' => $mime, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store'],
            $inline ? 'inline' : 'attachment',
        );
    }
}
