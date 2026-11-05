<?php

namespace App\Http\Controllers;

use App\Models\LegalGovernanceDocument;
use App\Services\LegalPrivateFiles;
use App\Services\NamedUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LegalGovernanceDocumentController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'cari' => 'nullable|string|max:255',
            'tanggal' => 'nullable|date_format:Y-m-d',
        ]);
        $query = LegalGovernanceDocument::with('creator');
        if (isset($filters['cari']) && $filters['cari'] !== '') {
            $query->where('judul_file', 'like', '%' . $filters['cari'] . '%');
        }
        if (!empty($filters['tanggal'])) {
            $query->whereDate('tanggal', $filters['tanggal']);
        }
        $documents = $query->orderByDesc('tanggal')->orderByDesc('id')->paginate(25)->withQueryString();

        return view('legal.governance-documents.index', compact('documents'));
    }

    public function create()
    {
        return view('legal.governance-documents.form', ['document' => null]);
    }

    private function validated(Request $request, bool $creating): array
    {
        return $request->validate([
            'judul_file' => 'required|string|max:255',
            'tanggal' => 'required|date_format:Y-m-d|after_or_equal:1900-01-01|before_or_equal:2100-12-31',
            'file' => ($creating ? 'required' : 'nullable') . '|file|mimes:pdf|extensions:pdf|max:5120',
        ]);
    }

    private function originalName(Request $request): string
    {
        return mb_substr(
            preg_replace(
                '/[^\pL\pN ._()-]/u',
                '_',
                basename(str_replace('\\', '/', $request->file('file')->getClientOriginalName())),
            ),
            0,
            200,
        );
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        unset($data['file']);
        $document = NamedUploads::create(
            LegalGovernanceDocument::class,
            $data + [
                'created_by' => $request->user()->id,
                'original_name' => $this->originalName($request),
            ],
            ['file_path' => [$request->file('file'), 'legal-private/governance-documents', 'local']],
        );

        return redirect()
            ->route('legal.governance-documents.show', $document)
            ->with('success', 'Dokumen tata kelola berhasil diunggah.');
    }

    public function show(LegalGovernanceDocument $governanceDocument)
    {
        return view('legal.governance-documents.show', ['document' => $governanceDocument->load('creator')]);
    }

    public function edit(LegalGovernanceDocument $governanceDocument)
    {
        return view('legal.governance-documents.form', ['document' => $governanceDocument]);
    }

    public function update(Request $request, LegalGovernanceDocument $governanceDocument)
    {
        $data = $this->validated($request, false);
        unset($data['file']);
        $newPath = null;
        $oldPath = null;
        try {
            DB::transaction(function () use ($request, $governanceDocument, $data, &$newPath, &$oldPath) {
                $document = LegalGovernanceDocument::whereKey($governanceDocument->id)->lockForUpdate()->firstOrFail();
                if ($request->hasFile('file')) {
                    $oldPath = $document->file_path;
                    $newPath = NamedUploads::store(
                        $request->file('file'),
                        'legal-private/governance-documents',
                        'local',
                        $document->id,
                        'file_path',
                    );
                    $data['file_path'] = $newPath;
                    $data['original_name'] = $this->originalName($request);
                }
                $document->update($data);
            });
        } catch (\Throwable $error) {
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }
            throw $error;
        }
        if ($oldPath && LegalPrivateFiles::path($oldPath)) {
            Storage::disk('local')->delete($oldPath);
        }

        return redirect()
            ->route('legal.governance-documents.show', $governanceDocument)
            ->with('success', 'Dokumen tata kelola berhasil diperbarui.');
    }

    public function file(Request $request, LegalGovernanceDocument $governanceDocument)
    {
        $path = LegalPrivateFiles::path($governanceDocument->file_path);
        abort_unless($path, 404);

        return response()->download(
            $path,
            $governanceDocument->original_name,
            [
                'Content-Type' => 'application/pdf',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ],
            $request->boolean('download') ? 'attachment' : 'inline',
        );
    }

    public function destroy(LegalGovernanceDocument $governanceDocument)
    {
        $path = DB::transaction(function () use ($governanceDocument) {
            $document = LegalGovernanceDocument::whereKey($governanceDocument->id)->lockForUpdate()->firstOrFail();
            $path = $document->file_path;
            $document->delete();

            return $path;
        });
        if (LegalPrivateFiles::path($path)) {
            Storage::disk('local')->delete($path);
        }

        return redirect()
            ->route('legal.governance-documents.index')
            ->with('success', 'Dokumen tata kelola berhasil dihapus.');
    }
}
