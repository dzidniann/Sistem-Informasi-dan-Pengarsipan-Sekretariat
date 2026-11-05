<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use Illuminate\Http\Request;

class BidangController extends Controller
{
    public function index()
    {
        $bidang = Bidang::withCount(['users', 'kategoriArsip'])->get();

        return view('admin.bidang.index', compact('bidang'));
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'nama_bidang' => 'required|string|max:100|unique:bidangs',
                'akses_semua_bidang' => 'nullable|boolean',
            ],
            [
                'nama_bidang.unique' => 'Nama bidang ini sudah terdaftar, gunakan nama lain.',
            ],
        );

        $bidang = Bidang::create([
            'nama_bidang' => $request->nama_bidang,
            'akses_semua_bidang' => $request->boolean('akses_semua_bidang'),
        ]);

        return back()->with('success', 'Bidang berhasil ditambahkan');
    }

    public function update(Request $request, Bidang $bidang)
    {
        $request->validate([
            'nama_bidang' => 'required|string|max:100|unique:bidangs,nama_bidang,' . $bidang->id,
        ]);

        $dataLama = $bidang->getOriginal();

        $bidang->update([
            'nama_bidang' => $request->nama_bidang,
            'akses_semua_bidang' => $request->boolean('akses_semua_bidang'),
        ]);

        return back()->with('success', 'Bidang berhasil diperbarui');
    }

    public function destroy(Bidang $bidang)
    {
        if ($bidang->users()->exists() || $bidang->kategoriArsip()->exists()) {
            return back()->with('error', 'Bidang masih memiliki data terkait, tidak bisa dihapus');
        }

        $bidang->delete();

        return back()->with('success', 'Bidang berhasil dihapus');
    }
}
