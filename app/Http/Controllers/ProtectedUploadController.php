<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\ArsipSurat;
use App\Models\Consultation;
use App\Models\ConsultationMessage;
use App\Models\LegalDocument;
use App\Models\Notulensi;
use App\Services\ProtectedUploads;
use Illuminate\Http\Request;

class ProtectedUploadController extends Controller
{
    public function __invoke(Request $request, string $path)
    {
        abort_unless(ProtectedUploads::validPath($path), 404);
        $user = $request->user();
        $role = $user->role?->nama;
        $legal = in_array($role, ['legal-admin', 'super-admin'], true);
        $admin = $role === 'super-admin';

        $archives = Arsip::where('file_path', $path);
        if (! $admin) {
            $archives->visibleTo($user);
        }
        if ($archives->exists()) {
            return ProtectedUploads::response($path, $request->boolean('download'));
        }
        if (in_array($role, ['sekretariat', 'super-admin'], true)) {
            $minutes = Notulensi::where(fn ($query) => $query->where('file_path', $path)->orWhere('foto_path', $path));
            $letters = ArsipSurat::where('file_path', $path);
            if (! $admin) {
                $minutes->where('bidang_id', $user->bidang_id);
                $letters->whereHas('perilis', fn ($query) => $query->where('bidang_id', $user->bidang_id));
            }
            if ($minutes->exists() || $letters->exists()) {
                return ProtectedUploads::response($path, $request->boolean('download'));
            }
        }

        $documents = LegalDocument::where('file_path', $path);
        if (! $legal) {
            $documents->published();
        }
        if ($documents->exists()) {
            return ProtectedUploads::response($path, $request->boolean('download'));
        }

        $consultations = Consultation::where('attachment', $path);
        $messages = ConsultationMessage::where('attachment', $path);
        if (! $legal) {
            $consultations->where('user_id', $user->id);
            $messages->whereHas('consultation', fn ($query) => $query->where('user_id', $user->id));
        }
        abort_unless($consultations->exists() || $messages->exists(), 404);

        return ProtectedUploads::response($path, $request->boolean('download'));
    }
}
