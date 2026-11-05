<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consultation;
use App\Models\LegalDocument;

class LegalCalendarController extends Controller
{
    /**
     * Kalender konsultasi dan review dokumen (Legal Admin)
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'nullable|integer|between:1900,2100',
        ]);
        $bulan = (int) ($filters['bulan'] ?? now()->month);
        $tahun = (int) ($filters['tahun'] ?? now()->year);

        // Ambil konsultasi yang memiliki jadwal di bulan/tahun yang diminta
        $schedules = Consultation::with(['user', 'assignedLegal'])
            ->whereNotNull('schedule')
            ->whereMonth('schedule', $bulan)
            ->whereYear('schedule', $tahun)
            ->orderBy('schedule')
            ->get();

        // Upcoming schedules (7 hari ke depan)
        $upcoming = Consultation::with(['user', 'assignedLegal'])
            ->whereNotNull('schedule')
            ->where('schedule', '>=', now())
            ->where('schedule', '<=', now()->addDays(7))
            ->whereIn('status', [Consultation::STATUS_SCHEDULED, Consultation::STATUS_ONGOING])
            ->orderBy('schedule')
            ->get();

        $reviewDocuments = LegalDocument::whereNotNull('review_date')
            ->whereMonth('review_date', $bulan)
            ->whereYear('review_date', $tahun)
            ->orderBy('review_date')
            ->orderBy('id')
            ->get();
        $upcomingReviews = LegalDocument::whereBetween('review_date', [
            today()->toDateString(),
            today()->addDays(7)->toDateString(),
        ])
            ->orderBy('review_date')
            ->orderBy('id')
            ->get();
        $pendingQuery = LegalDocument::where('status', LegalDocument::STATUS_NEED_REVIEW);
        $pendingCount = (clone $pendingQuery)->count();
        $pendingReviews = $pendingQuery
            ->orderByRaw('review_date IS NULL')
            ->orderBy('review_date')
            ->orderBy('id')
            ->limit(20)
            ->get();

        return view(
            'legal.calendar.index',
            compact(
                'schedules',
                'upcoming',
                'reviewDocuments',
                'pendingReviews',
                'pendingCount',
                'upcomingReviews',
                'bulan',
                'tahun',
            ),
        );
    }

    /**
     * Data kalender untuk AJAX (JSON)
     */
    public function events(Request $request)
    {
        $filters = $request->validate([
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'nullable|integer|between:1900,2100',
        ]);
        $bulan = (int) ($filters['bulan'] ?? now()->month);
        $tahun = (int) ($filters['tahun'] ?? now()->year);

        $schedules = Consultation::with(['user'])
            ->whereNotNull('schedule')
            ->whereMonth('schedule', $bulan)
            ->whereYear('schedule', $tahun)
            ->orderBy('schedule')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'pemohon' => $item->user->name,
                    'tanggal' => $item->schedule->format('Y-m-d'),
                    'jam' => $item->schedule->format('H:i'),
                    'status' => $item->status,
                    'type' => 'consultation',
                    'url' => route('legal.consultations.show', $item),
                    'allDay' => false,
                ];
            });

        $documents = LegalDocument::whereNotNull('review_date')
            ->whereMonth('review_date', $bulan)
            ->whereYear('review_date', $tahun)
            ->orderBy('review_date')
            ->orderBy('id')
            ->get()
            ->map(
                fn($document) => [
                    'id' => 'document_' . $document->id,
                    'document_id' => $document->id,
                    'title' => $document->name,
                    'pemohon' => null,
                    'tanggal' => $document->review_date->format('Y-m-d'),
                    'jam' => null,
                    'status' => $document->status,
                    'type' => 'document_review',
                    'url' => route('legal.documents.show', $document),
                    'allDay' => true,
                ],
            );

        return response()->json($schedules->concat($documents)->sortBy('tanggal')->values());
    }
}
