<?php

namespace App\Http\Controllers;

use App\Models\KategoriArsip;
use App\Models\Bidang;
use Illuminate\Http\Request;

class KategoriArsipController extends Controller
{
    public function index()
    {
        $bidangList = Bidang::with(['kategoriArsip' => function ($query) {
            $query->whereNull('parent_id')->with('children');
        }])->get();

        $semuaBidang = Bidang::all();

        return view('admin.kategori-arsip.index', compact('bidangList', 'semuaBidang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'bidang_id' => 'required|exists:bidangs,id',
            'parent_id' => 'nullable|exists:kategori_arsips,id',
        ]);

        KategoriArsip::create(
            $request->only('nama', 'bidang_id', 'parent_id')
        );

        return back()->with('success', 'Kategori berhasil ditambahkan');
    }

    public function update(Request $request, KategoriArsip $kategoriArsip)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
        ]);

        $kategoriArsip->update(
            $request->only('nama')
        );

        return back()->with('success', 'Kategori berhasil diperbarui');
    }

    public function destroy(KategoriArsip $kategoriArsip)
    {
        if ($kategoriArsip->arsip()->exists() || $kategoriArsip->children()->exists()) {
            return back()->with(
                'error',
                'Kategori masih memiliki arsip atau sub kategori, tidak bisa dihapus'
            );
        }

        $kategoriArsip->delete();

        return back()->with('success', 'Kategori berhasil dihapus');
    }
}