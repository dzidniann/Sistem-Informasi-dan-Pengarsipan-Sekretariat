<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bidang;
use App\Models\KategoriArsip;
use App\Models\Arsip;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function admin()
    {
        $totalPengguna = User::count();
        $totalBidang = Bidang::count();
        $totalKategori = KategoriArsip::count();
        $totalArsip = Arsip::count();

        $aktivitasTerbaru = Activity::with(['causer', 'subject'])
            ->latest('created_at')
            ->take(6)
            ->get();

        return view(
            'admin.dashboard',
            compact('totalPengguna', 'totalBidang', 'totalKategori', 'totalArsip', 'aktivitasTerbaru'),
        );
    }

    public function sekretariat()
    {
        $bulanData = [];

        for ($i = 5; $i >= 0; $i--) {
            $bulan = now()->subMonths($i);
            $awal = $bulan->copy()->startOfMonth();
            $akhir = $bulan->copy()->endOfMonth();

            $bulanData[] = [
                'label' => $bulan->translatedFormat('M Y'),
                'total' => Arsip::visibleTo(auth()->user())
                    ->whereBetween('created_at', [$awal, $akhir])
                    ->count(),
            ];
        }

        return view('sekretariat.dashboard', compact('bulanData'));
    }

    public function staf()
    {
        return view('staf.dashboard');
    }

    public function rekapBulanan(Request $request)
    {
        $request->validate([
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'nullable|integer|between:1900,2100',
        ]);
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;

        $awal = \Carbon\Carbon::create($tahun, $bulan, 1)->startOfMonth();

        $akhir = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth();

        $rekap = Arsip::visibleTo($request->user())
            ->whereBetween('created_at', [$awal, $akhir])
            ->with('bidang')
            ->get()
            ->groupBy('bidang_id')
            ->map(function ($items) {
                return [
                    'bidang' => $items->first()->bidang->nama_bidang,
                    'total' => $items->count(),
                ];
            })
            ->values();

        return response()->json([
            'namaBulan' => \Carbon\Carbon::create()->month($bulan)->translatedFormat('F'),

            'tahun' => $tahun,

            'totalKeseluruhan' => $rekap->sum('total'),

            'rekap' => $rekap,
        ]);
    }
}
