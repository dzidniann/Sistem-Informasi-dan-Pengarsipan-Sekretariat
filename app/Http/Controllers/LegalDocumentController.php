<?php

namespace App\Http\Controllers;

use App\Models\LegalCategory;
use App\Models\LegalDocument;
use App\Models\LegalDocumentHistory;
use App\Services\NamedUploads;
use Illuminate\Http\Request;

class LegalDocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = LegalDocument::with(['category', 'creator']);

        // Search
        if ($request->filled('cari')) {
            $kataKunci = $request->cari;
            $query->where(function ($q) use ($kataKunci) {
                $q->where('name', 'like', "%{$kataKunci}%")
                    ->orWhere('department', 'like', "%{$kataKunci}%")
                    ->orWhere('pic', 'like', "%{$kataKunci}%")
                    ->orWhereHas('category', fn ($sub) => $sub->where('name', 'like', "%{$kataKunci}%"));
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['name', 'effective_date', 'expiry_date', 'review_date', 'status', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $documents = $query->paginate(25)->withQueryString();
        $categories = LegalCategory::masterHukum()->orderBy('name')->get();
        $statuses = [
            LegalDocument::STATUS_DRAFT,
            LegalDocument::STATUS_NEED_REVIEW,
            LegalDocument::STATUS_ACTIVE,
            LegalDocument::STATUS_EXPIRING_SOON,
            LegalDocument::STATUS_EXPIRED,
        ];

        return view('legal.documents.index', compact('documents', 'categories', 'statuses'));
    }

    public function create()
    {
        $categories = LegalCategory::masterHukum()->orderBy('name')->get();

        return view('legal.documents.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => [
                'required',
                \Illuminate\Validation\Rule::in(LegalCategory::masterHukum()->pluck('id')->all()),
            ],
            'description' => 'required|string',
            'department' => 'required|string|max:100',
            'pic' => 'required|string|max:100',
            'effective_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:effective_date',
            'review_date' => 'required|date',
            'file' => 'required|file|mimes:pdf,doc,docx|max:8192',
        ]);

        $document = NamedUploads::create(
            LegalDocument::class,
            [
                'name' => $request->name,
                'category_id' => $request->category_id,
                'description' => $request->description,
                'department' => $request->department,
                'pic' => $request->pic,
                'effective_date' => $request->effective_date,
                'expiry_date' => $request->expiry_date,
                'review_date' => $request->review_date,
                'status' => LegalDocument::STATUS_DRAFT,
                'version' => '1.0',
                'created_by' => $user->id,
            ],
            ['file_path' => [$request->file('file'), 'legal-documents', 'protected']],
        );

        // Catat history
        LegalDocumentHistory::create([
            'document_id' => $document->id,
            'version' => '1.0',
            'action' => 'Dokumen dibuat',
            'description' => 'Dokumen baru diunggah oleh '.$user->name,
            'created_by' => $user->id,
        ]);

        return redirect()->route('legal.documents.index')->with('success', 'Dokumen berhasil diunggah');
    }

    public function show(LegalDocument $document)
    {
        $document->load(['category', 'creator', 'histories.creator']);

        return view('legal.documents.show', compact('document'));
    }

    public function edit(LegalDocument $document)
    {
        $categories = LegalCategory::masterHukum()->orderBy('name')->get();

        return view('legal.documents.edit', compact('document', 'categories'));
    }

    public function update(Request $request, LegalDocument $document)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => [
                'required',
                \Illuminate\Validation\Rule::in(LegalCategory::masterHukum()->pluck('id')->all()),
            ],
            'description' => 'nullable|string',
            'department' => 'nullable|string|max:100',
            'pic' => 'nullable|string|max:100',
            'effective_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:effective_date',
            'review_date' => 'nullable|date',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:8192',
        ]);

        $oldVersion = $document->version;

        // Jika file baru di-upload, increment version
        if ($request->hasFile('file')) {
            $path = NamedUploads::store(
                $request->file('file'),
                'legal-documents',
                'protected',
                $document->id,
                'file_path',
            );
            $document->file_path = $path;

            // Increment version: 1.0 -> 1.1
            $parts = explode('.', $oldVersion);
            $parts[1] = (int) $parts[1] + 1;
            $document->version = implode('.', $parts);
        }

        $document->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'department' => $request->department,
            'pic' => $request->pic,
            'effective_date' => $request->effective_date,
            'expiry_date' => $request->expiry_date,
            'review_date' => $request->review_date,
            'file_path' => $document->file_path,
            'version' => $document->version,
        ]);

        // Catat history
        $actionText = $request->hasFile('file') ? 'File dokumen diperbarui' : 'Metadata dokumen diperbarui';

        LegalDocumentHistory::create([
            'document_id' => $document->id,
            'version' => $document->version,
            'action' => $actionText,
            'description' => $actionText.' oleh '.$user->name,
            'created_by' => $user->id,
        ]);

        return redirect()->route('legal.documents.show', $document)->with('success', 'Dokumen berhasil diperbarui');
    }

    /**
     * Update status dokumen (workflow)
     */
    public function updateStatus(Request $request, LegalDocument $document)
    {
        $user = auth()->user();

        $request->validate([
            'status' => 'required|in:DRAFT,NEED_REVIEW,ACTIVE',
        ]);

        $oldStatus = $document->status;
        $document->update(['status' => $request->status]);

        LegalDocumentHistory::create([
            'document_id' => $document->id,
            'version' => $document->version,
            'action' => 'Status diubah',
            'description' => "Status diubah dari {$oldStatus} ke {$document->status} oleh {$user->name}",
            'created_by' => $user->id,
        ]);

        return back()->with('success', "Status berhasil diubah ke {$document->status}");
    }

    /**
     * Publish dokumen ke perpustakaan
     */
    public function publish(LegalDocument $document)
    {
        $user = auth()->user();

        if (! $document->canPublish()) {
            return back()->with(
                'error',
                'Dokumen hanya bisa dipublish jika berstatus ACTIVE atau EXPIRING_SOON dan belum kedaluwarsa',
            );
        }

        $document->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        LegalDocumentHistory::create([
            'document_id' => $document->id,
            'version' => $document->version,
            'action' => 'Dokumen dipublish',
            'description' => 'Dokumen dipublish ke perpustakaan oleh '.$user->name,
            'created_by' => $user->id,
        ]);

        return back()->with('success', 'Dokumen berhasil dipublish ke perpustakaan');
    }

    /**
     * Unpublish dokumen dari perpustakaan
     */
    public function unpublish(LegalDocument $document)
    {
        $user = auth()->user();

        $document->update([
            'is_published' => false,
            'published_at' => null,
        ]);

        LegalDocumentHistory::create([
            'document_id' => $document->id,
            'version' => $document->version,
            'action' => 'Dokumen di-unpublish',
            'description' => 'Dokumen dihapus dari perpustakaan oleh '.$user->name,
            'created_by' => $user->id,
        ]);

        return back()->with('success', 'Dokumen berhasil dihapus dari perpustakaan');
    }

    /**
     * Destroy / hapus dokumen
     */
    public function destroy(LegalDocument $document)
    {
        $document->delete();

        return redirect()->route('legal.documents.index')->with('success', 'Dokumen berhasil dihapus');
    }
}
