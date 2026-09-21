<?php

namespace App\Http\Controllers;

use App\Models\KategoriSurat;
use Illuminate\Http\Request;

class KategoriSuratController extends Controller
{
    public function index()
    {
        $kategoriList = KategoriSurat::orderBy('nama_kategori')->get();
        return view('admin.kategori-surat.index', compact('kategoriList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_kategori' => 'required|string|max:30|unique:master_kategori_surat,kode_kategori',
            'nama_kategori' => 'required|string|max:150',
        ]);

        KategoriSurat::create([
            'kode_kategori' => strtoupper($request->kode_kategori),
            'nama_kategori' => $request->nama_kategori,
            'is_active' => true,
        ]);

        return back()->with('success', 'Kategori surat berhasil ditambahkan');
    }

    public function toggleAktif(KategoriSurat $kategoriSurat)
        {
            $kategoriSurat->update(['is_active' => !$kategoriSurat->is_active]);
            return back()->with('success', 'Status kategori diperbarui');
        }

    public function update(Request $request, KategoriSurat $kategoriSurat)
        {
            $request->validate([
                // Pengecualian validasi unique untuk ID yang sedang diedit agar tidak error "kode sudah dipakai"
                'kode_kategori' => 'required|string|max:30|unique:master_kategori_surat,kode_kategori,' . $kategoriSurat->id_kategori . ',id_kategori',
                'nama_kategori' => 'required|string|max:150',
            ]);

            $kategoriSurat->update([
                'kode_kategori' => strtoupper($request->kode_kategori),
                'nama_kategori' => $request->nama_kategori,
            ]);

            return back()->with('success', 'Data kategori surat berhasil diperbarui');
        }

    public function destroy(KategoriSurat $kategoriSurat)
        {
            // Opsional: Cek apakah kategori ini sudah dipakai di tabel arsip surat
            // Jika sudah dipakai, tolak penghapusan untuk mencegah error database.
            $terpakai = \App\Models\ArsipSurat::where('id_kategori', $kategoriSurat->id_kategori)->exists();
            
            if ($terpakai) {
                return back()->with('warning', 'Kategori ini tidak dapat dihapus karena sudah memiliki riwayat nomor surat. Silakan nonaktifkan saja.');
            }

            $kategoriSurat->delete();
            return back()->with('success', 'Kategori surat berhasil dihapus');
        }
}