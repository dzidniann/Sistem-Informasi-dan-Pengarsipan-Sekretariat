<?php

namespace App\Http\Controllers;

use App\Models\Notulensi;
use Illuminate\Http\Request;

class NotulensiController extends Controller
{
    public function index(Request $request)
    {
        $query = Notulensi::with('pembuat');

        if ($request->filled('cari')) {
            $kataKunci = $request->cari;

            $query->where(function ($q) use ($kataKunci) {
                $q->where('judul_rapat', 'like', "%{$kataKunci}%")
                    ->orWhere('notulis', 'like', "%{$kataKunci}%")
                    ->orWhere('deskripsi_singkat', 'like', "%{$kataKunci}%");
            });
        }

        $notulensi = $query
            ->latest('tanggal_rapat')
            ->paginate(25)
            ->withQueryString();

        return view(
            'sekretariat.notulensi.index',
            compact('notulensi')
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul_rapat' => 'required|string|max:255',
            'tanggal_rapat' => 'required|date',
            'notulis' => 'required|string|max:100',
            'deskripsi_singkat' => 'nullable|string',
            'foto' => 'nullable|image|max:5120',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $fotoPath = $request->hasFile('foto')
            ? $request->file('foto')->store('notulensi/foto', 'public')
            : null;

        $filePath = $request->file('file')->store(
            'notulensi/file',
            'public'
        );

        Notulensi::create([
            'judul_rapat' => $request->judul_rapat,
            'tanggal_rapat' => $request->tanggal_rapat,
            'notulis' => $request->notulis,
            'deskripsi_singkat' => $request->deskripsi_singkat,
            'foto_path' => $fotoPath,
            'file_path' => $filePath,
            'bidang_id' => auth()->user()->bidang_id,
            'created_by' => auth()->id(),
        ]);

        return back()->with(
            'success',
            'Notulensi berhasil disimpan'
        );
    }

    public function show(Notulensi $notulensi)
    {
        return view(
            'sekretariat.notulensi.show',
            compact('notulensi')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Preview Berkas
    |--------------------------------------------------------------------------
    */

    public function berkas(Notulensi $notulensi)
    {
        abort_unless($notulensi->file_path, 404);

        $path = storage_path(
            'app/public/' . $notulensi->file_path
        );

        abort_unless(file_exists($path), 404);

        $mimeType = mime_content_type($path);

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }
}