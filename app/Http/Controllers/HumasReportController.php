<?php

namespace App\Http\Controllers;

use App\Models\HumasCsr;
use App\Services\HumasCsrExcel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HumasReportController extends Controller
{
    private function data(Request $request): array
    {
        $module = new HumasCsrController();
        $filters = $module->filters($request);
        $filters['tahun'] = (int) ($filters['tahun'] ?? now()->year);
        $records = HumasCsr::visibleTo($request->user())
            ->filtered($filters)
            ->with('kategori')
            ->orderBy('tanggal_proses')
            ->orderBy('id')
            ->get();
        $tahun = $filters['tahun'];
        $bulan = !empty($filters['bulan']) ? (int) $filters['bulan'] : null;
        $periode = $bulan
            ? Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y')
            : 'Tahun ' . $tahun;

        return compact('records', 'tahun', 'bulan', 'periode', 'filters') + ['categories' => $module->categories()];
    }

    public function index(Request $request)
    {
        return view('humas.reports.index', $this->data($request));
    }

    public function excel(Request $request, HumasCsrExcel $excel)
    {
        $data = $this->data($request);
        $path = $excel->make($data['records'], $data['tahun'], $data['bulan']);
        $name =
            'Rekap_CSR_Humas_' .
            $data['tahun'] .
            ($data['bulan'] ? '_' . str_pad($data['bulan'], 2, '0', STR_PAD_LEFT) : '') .
            '.xlsx';

        return response()
            ->download($path, $name, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'private, no-store',
            ])
            ->deleteFileAfterSend(true);
    }

    public function pdf(Request $request)
    {
        $data = $this->data($request);
        $pdf = Pdf::loadView('humas.reports.pdf', $data)
            ->setPaper('a3', 'landscape')
            ->setOptions(['isRemoteEnabled' => false, 'isPhpEnabled' => false]);
        $pdf->render();
        $pdf->getDomPDF()->getCanvas()->page_text(1030, 815, 'Halaman {PAGE_NUM} / {PAGE_COUNT}', null, 9);

        return $pdf->download(
            'Rekap_CSR_Humas_' . $data['tahun'] . ($data['bulan'] ? '_' . $data['bulan'] : '') . '.pdf',
        );
    }
}
