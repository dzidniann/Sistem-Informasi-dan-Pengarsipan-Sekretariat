<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LegalDocument;
use App\Models\Consultation;
use App\Models\LegalNotification;
use App\Models\LegalCategory;

class LegalDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $totalDocuments = LegalDocument::count();
        $totalActive = LegalDocument::byStatus(LegalDocument::STATUS_ACTIVE)->count();
        $totalDraft = LegalDocument::byStatus(LegalDocument::STATUS_DRAFT)->count();
        $totalExpired = LegalDocument::byStatus(LegalDocument::STATUS_EXPIRED)->count();
        $totalExpiringSoon = LegalDocument::byStatus(LegalDocument::STATUS_EXPIRING_SOON)->count();
        $totalNeedReview = LegalDocument::byStatus(LegalDocument::STATUS_NEED_REVIEW)->count();
        $totalPublished = LegalDocument::published()->count();

        $totalConsultations = Consultation::count();
        $totalWaiting = Consultation::byStatus(Consultation::STATUS_WAITING_REVIEW)->count();
        $totalScheduled = Consultation::byStatus(Consultation::STATUS_SCHEDULED)->count();
        $totalOngoing = Consultation::byStatus(Consultation::STATUS_ONGOING)->count();

        $unreadNotifications = LegalNotification::forUser($user->id)->unread()->count();

        $recentDocuments = LegalDocument::with(['category', 'creator'])
            ->latest()
            ->take(5)
            ->get();

        $recentConsultations = Consultation::with(['user', 'assignedLegal'])
            ->latest()
            ->take(5)
            ->get();

        $upcomingSchedules = Consultation::with(['user'])
            ->scheduled()
            ->where('schedule', '>=', now())
            ->orderBy('schedule')
            ->take(5)
            ->get();

        // Data chart: dokumen per bulan (6 bulan terakhir)
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $bulan = now()->subMonths($i);
            $awal = $bulan->copy()->startOfMonth();
            $akhir = $bulan->copy()->endOfMonth();

            $chartData[] = [
                'label' => $bulan->translatedFormat('M Y'),
                'total' => LegalDocument::whereBetween('created_at', [$awal, $akhir])->count(),
            ];
        }

        // Data chart: konsultasi per bulan (6 bulan terakhir)
        $consultationChartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $bulan = now()->subMonths($i);
            $awal = $bulan->copy()->startOfMonth();
            $akhir = $bulan->copy()->endOfMonth();

            $consultationChartData[] = [
                'label' => $bulan->translatedFormat('M Y'),
                'total' => Consultation::whereBetween('created_at', [$awal, $akhir])->count(),
            ];
        }

        return view(
            'legal.dashboard',
            compact(
                'totalDocuments',
                'totalActive',
                'totalDraft',
                'totalExpired',
                'totalExpiringSoon',
                'totalNeedReview',
                'totalPublished',
                'totalConsultations',
                'totalWaiting',
                'totalScheduled',
                'totalOngoing',
                'unreadNotifications',
                'recentDocuments',
                'recentConsultations',
                'upcomingSchedules',
                'chartData',
                'consultationChartData',
            ),
        );
    }
}
