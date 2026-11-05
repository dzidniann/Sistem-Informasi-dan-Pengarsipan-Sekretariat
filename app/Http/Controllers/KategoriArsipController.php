<?php

namespace App\Http\Controllers;

use App\Models\KategoriArsip;
use App\Models\Bidang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KategoriArsipController extends Controller
{
    public function index()
    {
        $bidangList = Bidang::with([
            'kategoriArsip' => function ($query) {
                $query->whereNull('parent_id')->with('children');
            },
        ])->get();

        $semuaBidang = Bidang::all();

        return view('admin.kategori-arsip.index', compact('bidangList', 'semuaBidang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'bidang_id' => 'required|exists:bidangs,id',
            'parent_id' => [
                'nullable',
                Rule::exists('kategori_arsips', 'id')->where('bidang_id', $request->input('bidang_id')),
            ],
        ]);

        DB::transaction(fn () => KategoriArsip::create($request->only('nama', 'bidang_id', 'parent_id')));

        return back()->with('success', 'Kategori berhasil ditambahkan');
    }

    public function update(Request $request, KategoriArsip $kategoriArsip)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
        ]);

        DB::transaction(fn () => $kategoriArsip->update($request->only('nama')));

        return back()->with('success', 'Kategori berhasil diperbarui');
    }

    public function destroy(KategoriArsip $kategoriArsip)
    {
        if (
            $kategoriArsip->arsip()->exists() ||
            $kategoriArsip->humasCsr()->exists() ||
            $kategoriArsip->children()->exists() ||
            $kategoriArsip->legalCategory?->documents()->exists()
        ) {
            return back()->with(
                'error',
                'Kategori masih memiliki arsip, CSR Humas, dokumen Hukum, atau sub kategori, tidak bisa dihapus',
            );
        }

        DB::transaction(function () use ($kategoriArsip) {
            $kategoriArsip->legalCategory?->delete();
            $kategoriArsip->delete();
        });

        return back()->with('success', 'Kategori berhasil dihapus');
    }
}
