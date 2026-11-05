<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LegalDocument;
use App\Models\LegalCategory;

class LibraryController extends Controller
{
    /**
     * Perpustakaan digital — hanya tampilkan dokumen ACTIVE + published
     */
    public function index(Request $request)
    {
        $query = LegalDocument::published()->with(['category']);

        // Search
        if ($request->filled('cari')) {
            $kataKunci = $request->cari;
            $query->where(function ($q) use ($kataKunci) {
                $q->where('name', 'like', "%{$kataKunci}%")
                    ->orWhere('description', 'like', "%{$kataKunci}%")
                    ->orWhereHas('category', fn ($sub) => $sub->where('name', 'like', "%{$kataKunci}%"));
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $documents = $query->latest('published_at')->paginate(20)->withQueryString();
        $categories = LegalCategory::has('documents', '>', 0)->get();

        return view('library.index', compact('documents', 'categories'));
    }

    /**
     * Detail dokumen publik — tampilkan metadata publik saja
     */
    public function show(LegalDocument $document)
    {
        // Hanya tampilkan jika sudah publish dan ACTIVE
        abort_unless($document->canPublish() && $document->is_published, 404);

        // Load hanya relasi yang diperlukan (tanpa history internal)
        $document->load(['category']);

        return view('library.show', compact('document'));
    }
}
