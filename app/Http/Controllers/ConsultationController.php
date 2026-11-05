<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\ConsultationMessage;
use App\Models\LegalNotification;
use App\Services\NamedUploads;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    /**
     * Daftar konsultasi â€” Legal Admin: semua, Employee: milik sendiri
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isLegalAdmin = $user->hasRole('legal-admin');

        $query = Consultation::with(['user', 'assignedLegal']);

        if (! $isLegalAdmin) {
            // Employee hanya melihat konsultasi sendiri
            $query->byUser($user->id);
        }

        // Search
        if ($request->filled('cari')) {
            $kataKunci = $request->cari;
            $query->where(function ($q) use ($kataKunci) {
                $q->where('title', 'like', "%{$kataKunci}%")->orWhereHas(
                    'user',
                    fn ($sub) => $sub->where('name', 'like', "%{$kataKunci}%"),
                );
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        $consultations = $query->latest()->paginate(25)->withQueryString();
        $statuses = [
            Consultation::STATUS_WAITING_REVIEW,
            Consultation::STATUS_ACCEPTED,
            Consultation::STATUS_SCHEDULED,
            Consultation::STATUS_ONGOING,
            Consultation::STATUS_FINISHED,
        ];

        return view(
            $isLegalAdmin ? 'legal.consultations.index' : 'employee.consultations.index',
            compact('consultations', 'statuses'),
        );
    }

    /**
     * Form ajukan konsultasi baru (Employee)
     */
    public function create()
    {
        return view('employee.consultations.create');
    }

    /**
     * Simpan konsultasi baru (Employee)
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:8192',
        ]);

        $consultation = NamedUploads::create(
            Consultation::class,
            [
                'user_id' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'status' => Consultation::STATUS_WAITING_REVIEW,
            ],
            $request->hasFile('attachment')
                ? ['attachment' => [$request->file('attachment'), 'consultations', 'protected']]
                : [],
        );

        // Notifikasi ke semua legal-admin
        $legalAdmins = \App\Models\User::whereHas('role', function ($q) {
            $q->where('nama', 'legal-admin');
        })->get();

        foreach ($legalAdmins as $admin) {
            LegalNotification::kirim(
                $admin->id,
                LegalNotification::TYPE_NEW_CONSULTATION,
                "Konsultasi baru dari {$user->name}: {$consultation->title}",
                LegalNotification::PRIORITY_HIGH,
            );
        }

        return redirect()->route('employee.consultations.index')->with('success', 'Konsultasi berhasil diajukan');
    }

    /**
     * Detail konsultasi + diskusi
     */
    public function show(Consultation $consultation)
    {
        $user = auth()->user();
        $isLegalAdmin = $user->hasRole('legal-admin');

        // Employee hanya bisa lihat konsultasi sendiri
        if (! $isLegalAdmin) {
            abort_unless($consultation->user_id === $user->id, 403);
        }

        $consultation->load(['user', 'assignedLegal', 'messages.user']);

        return view(
            $isLegalAdmin ? 'legal.consultations.show' : 'employee.consultations.show',
            compact('consultation'),
        );
    }

    /**
     * Kirim pesan diskusi
     */
    public function sendMessage(Request $request, Consultation $consultation)
    {
        $user = auth()->user();
        $isLegalAdmin = $user->hasRole('legal-admin');

        // Employee hanya bisa kirim pesan di konsultasi sendiri
        if (! $isLegalAdmin) {
            abort_unless($consultation->user_id === $user->id, 403);
        }

        $request->validate([
            'message' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:8192',
        ]);

        NamedUploads::create(
            ConsultationMessage::class,
            [
                'consultation_id' => $consultation->id,
                'user_id' => $user->id,
                'message' => $request->message,
            ],
            $request->hasFile('attachment')
                ? ['attachment' => [$request->file('attachment'), 'consultation-messages', 'protected']]
                : [],
        );

        return back()->with('success', 'Pesan berhasil dikirim');
    }

    /**
     * Update status konsultasi (Legal Admin only)
     */
    public function updateStatus(Request $request, Consultation $consultation)
    {
        $request->validate([
            'status' => 'required|in:WAITING_REVIEW,ACCEPTED,SCHEDULED,ONGOING,FINISHED',
        ]);

        $oldStatus = $consultation->status;
        $consultation->update(['status' => $request->status]);

        // Notifikasi ke pemohon
        LegalNotification::kirim(
            $consultation->user_id,
            LegalNotification::TYPE_NEW_CONSULTATION,
            "Status konsultasi '{$consultation->title}' diubah dari {$oldStatus} ke {$request->status}",
            LegalNotification::PRIORITY_MEDIUM,
        );

        return $this->actionResponse($request, $consultation, "Status berhasil diubah ke {$request->status}");
    }

    /**
     * Assign PIC Legal Admin
     */
    public function assign(Request $request, Consultation $consultation)
    {
        $request->validate([
            'assigned_legal' => 'required|exists:users,id',
        ]);

        $consultation->update([
            'assigned_legal' => $request->assigned_legal,
            'status' => Consultation::STATUS_ACCEPTED,
        ]);

        $assignedUser = \App\Models\User::find($request->assigned_legal);

        // Notifikasi ke pemohon
        LegalNotification::kirim(
            $consultation->user_id,
            LegalNotification::TYPE_NEW_CONSULTATION,
            "Konsultasi '{$consultation->title}' telah diterima dan ditangani oleh {$assignedUser->name}",
            LegalNotification::PRIORITY_MEDIUM,
        );

        return $this->actionResponse($request, $consultation, 'PIC berhasil di-assign');
    }

    /**
     * Set jadwal konsultasi (Legal Admin)
     */
    public function schedule(Request $request, Consultation $consultation)
    {
        $request->validate([
            'schedule' => 'required|date|after:now',
        ]);

        $consultation->update([
            'schedule' => $request->schedule,
            'status' => Consultation::STATUS_SCHEDULED,
        ]);

        // Notifikasi ke pemohon
        LegalNotification::kirim(
            $consultation->user_id,
            LegalNotification::TYPE_CONSULTATION_SCHEDULE,
            "Konsultasi '{$consultation->title}' dijadwalkan pada ".
                \Carbon\Carbon::parse($request->schedule)->translatedFormat('d M Y H:i'),
            LegalNotification::PRIORITY_HIGH,
        );

        return $this->actionResponse($request, $consultation, 'Jadwal konsultasi berhasil diatur');
    }

    private function actionResponse(Request $request, Consultation $consultation, string $message)
    {
        if ($request->expectsJson()) {
            $consultation->refresh();

            return response()->json([
                'message' => $message,
                'consultation' => [
                    'status' => $consultation->status,
                    'assigned_legal' => (string) ($consultation->assigned_legal ?? ''),
                    'schedule' => $consultation->schedule?->format('Y-m-d\TH:i') ?? '',
                ],
            ]);
        }

        return back()->with('success', $message);
    }
}
