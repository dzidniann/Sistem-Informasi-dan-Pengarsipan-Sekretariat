<?php

namespace App\Http\Controllers;

use App\Models\LegalActivity;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LegalMonthlyReportController extends Controller
{
    public function data(Request $request): array
    {
        $request->validate([
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'nullable|integer|between:1900,2100',
        ]);
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);
        $start = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $activities = LegalActivity::with('document')
            ->where('tanggal_kegiatan', '>=', $start->toDateString())
            ->where('tanggal_kegiatan', '<', $start->copy()->addMonth()->toDateString())
            ->orderBy('tanggal_kegiatan')
            ->orderBy('id')
            ->get();

        return compact('bulan', 'tahun', 'activities') + ['namaBulan' => $start->locale('id')->translatedFormat('F')];
    }

    public function index(Request $request)
    {
        return view('legal.reports.monthly', $this->data($request));
    }

    protected function pdf(Request $request)
    {
        $data = $this->data($request);
        $pdf = Pdf::loadView('legal.reports.monthly-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions(['isRemoteEnabled' => false, 'isPhpEnabled' => false]);
        $pdf->render();
        $pdf->getDomPDF()->getCanvas()->page_text(700, 565, 'Halaman {PAGE_NUM} / {PAGE_COUNT}', null, 9);

        return [$pdf, 'Laporan_Kegiatan_Legal_' . $data['namaBulan'] . '_' . $data['tahun'] . '.pdf'];
    }

    public function preview(Request $request)
    {
        [$pdf, $name] = $this->pdf($request);

        return $pdf->stream($name);
    }

    public function download(Request $request)
    {
        [$pdf, $name] = $this->pdf($request);

        return $pdf->download($name);
    }
}
