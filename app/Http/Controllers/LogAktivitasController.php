<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class LogAktivitasController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Activity::with(['causer', 'subject'])
            ->latest('created_at');

        // Admin boleh melihat semua log.
        // Selain admin, hanya melihat log user dengan role yang sama.
        if ($user->role->nama !== 'admin') {
            $roleId = $user->role_id;

            $query->whereHasMorph(
                'causer',
                [User::class],
                function ($q) use ($roleId) {
                    $q->where('role_id', $roleId);
                }
            );
        }

        if ($request->filled('cari')) {
            $kataKunci = $request->cari;

            $query->where(function ($q) use ($kataKunci) {
                $q->where('description', 'like', "%{$kataKunci}%")
                    ->orWhere('subject_type', 'like', "%{$kataKunci}%")
                    ->orWhereHasMorph(
                        'causer',
                        [User::class],
                        function ($sub) use ($kataKunci) {
                            $sub->where('name', 'like', "%{$kataKunci}%");
                        }
                    );
            });
        }

        $logs = $query
            ->paginate(50)
            ->withQueryString();

        $layout = $this->layoutSesuaiRole();

        return view(
            'log-aktivitas.index',
            compact('logs', 'layout')
        );
    }

    public function show(Activity $log)
    {
        $user = auth()->user();

        // Admin boleh melihat detail semua log.
        // Selain admin, hanya boleh membuka log dari role yang sama.
        if ($user->role->nama !== 'super-admin') {
            if (
                !$log->causer ||
                !$log->causer instanceof User ||
                $log->causer->role_id !== $user->role_id
            ) {
                abort(403);
            }
        }

        $layout = $this->layoutSesuaiRole();

        return view(
            'log-aktivitas.show',
            compact('log', 'layout')
        );
    }

    private function layoutSesuaiRole(): string
    {
        return match (auth()->user()->role->nama) {
            'super-admin' => 'admin.layout',
            'sekretariat' => 'sekretariat.layout',
            default => 'staf.layout',
        };
    }
}