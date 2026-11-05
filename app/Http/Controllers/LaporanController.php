<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\Notulensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index()
    {
        return view('sekretariat.laporan.index');
    }

    public function generate(Request $request)
    {
        $data = $this->siapkanData($request->bulan, $request->tahun);

        return view('sekretariat.laporan.preview', $data);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->siapkanData($request->bulan, $request->tahun);
        $pdf = Pdf::loadView('sekretariat.laporan.pdf', $data);

        return $pdf->download("laporan-{$request->bulan}-{$request->tahun}.pdf");
    }

    private function siapkanData($bulan, $tahun)
    {
        $awal = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $akhir = Carbon::create($tahun, $bulan, 1)->endOfMonth();

        $totalArsip = Arsip::visibleTo(auth()->user())
            ->whereBetween('created_at', [$awal, $akhir])
            ->count();

        // rekap per bidang, dengan rincian per kategori di dalamnya
        $rekapPerBidang = Arsip::visibleTo(auth()->user())
            ->whereBetween('created_at', [$awal, $akhir])
            ->with('bidang')
            ->get()
            ->groupBy('bidang_id')
            ->map(function ($items, $bidangId) {
                return [
                    'bidang' => $items->first()->bidang,
                    'total' => $items->count(),
                    'perKategori' => $items
                        ->load('kategori')
                        ->groupBy(fn ($item) => $item->kategori->nama)
                        ->map(fn ($grup) => $grup->count()),
                ];
            });

        $notulensi = Notulensi::whereBetween('tanggal_rapat', [$awal, $akhir])
            ->orderBy('tanggal_rapat')
            ->get();

        // Rekap Nomor Surat yang dirilis pada periode ini
        $nomorSuratPeriode = \App\Models\ArsipSurat::with('kategori')
            ->whereBetween('created_at', [$awal, $akhir])
            ->orderBy('created_at')
            ->get();

        $totalNomorSurat = $nomorSuratPeriode->count();
        $totalNomorNormal = $nomorSuratPeriode->where('is_backdate', false)->count();
        $totalNomorSisipan = $nomorSuratPeriode->where('is_backdate', true)->count();

        $rekapPerKategoriSurat = $nomorSuratPeriode
            ->groupBy(fn ($item) => $item->kategori->nama_kategori)
            ->map(fn ($grup) => $grup->count());

        return [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'namaBulan' => Carbon::create()->month($bulan)->translatedFormat('F'),
            'totalArsip' => $totalArsip,
            'rekapPerBidang' => $rekapPerBidang,
            'notulensi' => $notulensi,
            'nomorSuratPeriode' => $nomorSuratPeriode,
            'totalNomorSurat' => $totalNomorSurat,
            'totalNomorNormal' => $totalNomorNormal,
            'totalNomorSisipan' => $totalNomorSisipan,
            'rekapPerKategoriSurat' => $rekapPerKategoriSurat,
        ];
    }
}
