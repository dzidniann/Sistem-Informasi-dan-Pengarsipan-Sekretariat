<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\KategoriArsip;
use Illuminate\Http\Request;

class ArsipController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Arsip::with(['kategori', 'bidang']);

        $cakupanSendiri = $request->cakupan === 'sendiri';

        if (!$user->bisaAksesSemuaBidang() || $cakupanSendiri) {
            $query->where('bidang_id', $user->bidang_id);
        }

       if ($request->filled('cari')) {
            $kataKunci = $request->cari;
            $query->where(function ($q) use ($kataKunci) {
                $q->where('judul_file', 'like', "%{$kataKunci}%")
                ->orWhereHas('kategori', fn($sub) => $sub->where('nama', 'like', "%{$kataKunci}%"))
                ->orWhereHas('bidang', fn($sub) => $sub->where('nama_bidang', 'like', "%{$kataKunci}%"))
                ->orWhereHas('uploader', fn($sub) => $sub->where('name', 'like', "%{$kataKunci}%"));
            });
        }

        $arsip = $query->latest()->paginate(25)->withQueryString();

        // dropdown kategori upload sekarang selalu terbatas ke bidang sendiri,
        // baik untuk sekretariat maupun staf bidang
        $kategoriList = KategoriArsip::where('bidang_id', $user->bidang_id)->get();

        return view('arsip.index', compact('arsip', 'kategoriList', 'cakupanSendiri'));
    }

   public function store(Request $request)
{
    $user = auth()->user();

    $request->validate([
        'judul_file' => 'required|string|max:255',
        'tanggal' => 'required|date',
        'kategori_id' => 'required|exists:kategori_arsips,id',
        'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
    ]);

    $kategori = KategoriArsip::findOrFail($request->kategori_id);

    abort_unless(
        $user->bisaAksesSemuaBidang() || $user->bidang_id === $kategori->bidang_id,
        403
    );

    $path = $request->file('file')->store(
        'arsip/' . $kategori->id,
        'public'
    );

    $arsip = Arsip::create([
        'judul_file' => $request->judul_file,
        'tanggal' => $request->tanggal,
        'file_path' => $path,
        'kategori_id' => $kategori->id,
        'bidang_id' => $kategori->bidang_id,
        'uploaded_by' => $user->id,
    ]);

    return back()->with('success', 'Berkas berhasil diunggah');
}

public function show(Arsip $arsip)
{
    $user = auth()->user();

    abort_unless(
        $user->bisaAksesSemuaBidang() || $user->bidang_id === $arsip->bidang_id,
        403
    );

    return view('arsip.show', compact('arsip'));
}
}