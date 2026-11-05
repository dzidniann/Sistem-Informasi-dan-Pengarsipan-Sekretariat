<?php

namespace App\Http\Controllers;

use App\Models\ArsipSurat;
use App\Models\KategoriSurat;
use App\Services\NamedUploads;
use App\Services\NomorSuratService;
use Illuminate\Http\Request;

class NomorSuratController extends Controller
{
    public function index(Request $request)
    {
        $query = ArsipSurat::with('kategori', 'perilis')->latest('created_at');

        if ($request->filled('cari')) {
            $kataKunci = $request->cari;
            $query->where(function ($q) use ($kataKunci) {
                $q->where('full_nomor', 'like', "%{$kataKunci}%")
                    ->orWhere('perihal', 'like', "%{$kataKunci}%")
                    ->orWhere('tujuan', 'like', "%{$kataKunci}%")
                    ->orWhereHas('kategori', fn ($sub) => $sub->where('nama_kategori', 'like', "%{$kataKunci}%"))
                    ->orWhereHas('perilis', fn ($sub) => $sub->where('name', 'like', "%{$kataKunci}%"));
            });
        }

        $riwayat = $query->paginate(50)->withQueryString();

        return view('nomor-surat.index', compact('riwayat'));
    }

    public function daftarKategoriAktif()
    {
        $kategori = KategoriSurat::where('is_active', true)->orderBy('nama_kategori')->get();

        return response()->json($kategori);
    }

    public function ambilNomorBerikutnya(Request $request, NomorSuratService $service)
    {
        $request->validate([
            'id_kategori' => 'required|exists:master_kategori_surat,id_kategori',
        ]);

        $kategori = KategoriSurat::findOrFail($request->id_kategori);
        $tahun = $request->tahun ?? now()->year;
        $nomorSelanjutnya = $service->nomorSelanjutnya($kategori->id_kategori, $tahun);

        return response()->json([
            'id_kategori' => $kategori->id_kategori,
            'kode_kategori' => $kategori->kode_kategori,
            'nama_kategori' => $kategori->nama_kategori,
            'nomor_selanjutnya' => $nomorSelanjutnya,
            'bulan_romawi' => \App\Helpers\RomawiHelper::dariBulan(now()->month),
            'tahun' => $tahun,
        ]);
    }

    public function riwayatJson(Request $request)
    {
        $query = ArsipSurat::with('kategori', 'perilis')->latest('created_at');

        if ($request->filled('id_kategori')) {
            $query->where('id_kategori', $request->id_kategori);
        }

        if ($request->filled('cari')) {
            $kataKunci = $request->cari;

            $query->where(function ($q) use ($kataKunci) {
                $q->where('full_nomor', 'like', "%{$kataKunci}%")
                    ->orWhere('perihal', 'like', "%{$kataKunci}%")
                    ->orWhere('tujuan', 'like', "%{$kataKunci}%")
                    ->orWhereHas('kategori', fn ($sub) => $sub->where('nama_kategori', 'like', "%{$kataKunci}%"))
                    ->orWhereHas('perilis', fn ($sub) => $sub->where('name', 'like', "%{$kataKunci}%"));
            });
        }

        $data = $query
            ->take(50)
            ->get()
            ->map(function ($r) {
                return [
                    'id_surat' => $r->id_surat,
                    'full_nomor' => $r->full_nomor,
                    'perihal' => $r->perihal,
                    'tujuan' => $r->tujuan,
                    'catatan' => $r->catatan,
                    'kategori' => $r->kategori->nama_kategori ?? '-',
                    'is_backdate' => $r->is_backdate,
                    'perilis' => $r->perilis->name ?? '-',
                    'waktu' => $r->created_at ? $r->created_at->format('d M Y, H:i') : '-',
                    'file_path' => $r->file_path,
                ];
            });

        return response()->json($data);
    }

    public function store(Request $request, NomorSuratService $service)
    {
        $request->validate([
            'id_kategori' => 'required|exists:master_kategori_surat,id_kategori',
            'nomor_urut' => 'required|numeric|min:1|max:9999',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
            'tanggal_surat' => 'nullable|date',
            'perihal' => 'nullable|string|max:255',
            'tujuan' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:8192',
        ]);

        $nomorUrutBersih = (int) $request->nomor_urut;

        $hasil = $service->rilis(
            $request->id_kategori,
            $nomorUrutBersih,
            auth()->id(),
            (int) $request->bulan,
            (int) $request->tahun,
            [
                'tanggal_surat' => $request->tanggal_surat,
                'perihal' => $request->perihal,
                'tujuan' => $request->tujuan,
                'catatan' => $request->catatan,
            ],
            $request->hasFile('file') ? ['file_path' => [$request->file('file'), 'nomor-surat', 'protected']] : [],
        );

        if ($hasil->is_backdate) {
            return redirect()
                ->route('nomor-surat.index')
                ->with(
                    'warning',
                    "Nomor {$nomorUrutBersih} sudah pernah dirilis untuk kategori ini. Nomor ditandai sisipan: {$hasil->full_nomor}",
                );
        }

        return redirect()
            ->route('nomor-surat.index')
            ->with('success', "Nomor surat berhasil dirilis: {$hasil->full_nomor}");
    }

    public function show(ArsipSurat $arsipSurat)
    {
        return view('nomor-surat.show', ['surat' => $arsipSurat]);
    }

    public function update(Request $request, ArsipSurat $arsipSurat)
    {
        $request->validate([
            'tanggal_surat' => 'nullable|date',
            'perihal' => 'nullable|string|max:255',
            'tujuan' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:8192',
        ]);

        $data = $request->only(['tanggal_surat', 'perihal', 'tujuan', 'catatan']);

        if ($request->hasFile('file')) {
            $data['file_path'] = NamedUploads::store(
                $request->file('file'),
                'nomor-surat',
                'protected',
                (int) $arsipSurat->getKey(),
                'file_path',
            );
        }

        $arsipSurat->update($data);

        return redirect()
            ->route('nomor-surat.show', $arsipSurat)
            ->with('success', 'Detail surat berhasil diperbarui: '.$arsipSurat->full_nomor);
    }
}
