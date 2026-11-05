<?php

namespace App\Http\Controllers;

use App\Models\Notulensi;
use App\Services\NamedUploads;
use Illuminate\Http\Request;

class NotulensiController extends Controller
{
    public function index(Request $request)
    {
        $query = Notulensi::with('pembuat')->where('bidang_id', auth()->user()->bidang_id);

        if ($request->filled('cari')) {
            $kataKunci = $request->cari;

            $query->where(function ($q) use ($kataKunci) {
                $q->where('judul_rapat', 'like', "%{$kataKunci}%")
                    ->orWhere('notulis', 'like', "%{$kataKunci}%")
                    ->orWhere('deskripsi_singkat', 'like', "%{$kataKunci}%");
            });
        }

        $notulensi = $query->latest('tanggal_rapat')->paginate(25)->withQueryString();

        return view('sekretariat.notulensi.index', compact('notulensi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul_rapat' => 'required|string|max:255',
            'tanggal_rapat' => 'required|date',
            'notulis' => 'required|string|max:100',
            'deskripsi_singkat' => 'nullable|string',
            'foto' => 'nullable|image|max:4096',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:8192',
        ]);

        $files = ['file_path' => [$request->file('file'), 'notulensi/file', 'protected']];
        if ($request->hasFile('foto')) {
            $files['foto_path'] = [$request->file('foto'), 'notulensi/foto', 'protected'];
        }

        NamedUploads::create(
            Notulensi::class,
            [
                'judul_rapat' => $request->judul_rapat,
                'tanggal_rapat' => $request->tanggal_rapat,
                'notulis' => $request->notulis,
                'deskripsi_singkat' => $request->deskripsi_singkat,
                'bidang_id' => auth()->user()->bidang_id,
                'created_by' => auth()->id(),
            ],
            $files,
        );

        return back()->with('success', 'Notulensi berhasil disimpan');
    }

    public function show(Notulensi $notulensi)
    {
        abort_unless(auth()->user()->bidang_id === $notulensi->bidang_id, 404);

        return view('sekretariat.notulensi.show', compact('notulensi'));
    }

    /*
    |--------------------------------------------------------------------------
    | Preview Berkas
    |--------------------------------------------------------------------------
    */

    public function berkas(Notulensi $notulensi)
    {
        abort_unless(auth()->user()->bidang_id === $notulensi->bidang_id, 404);

        return \App\Services\ProtectedUploads::response($notulensi->file_path ?? '', request()->boolean('download'));
    }
}
