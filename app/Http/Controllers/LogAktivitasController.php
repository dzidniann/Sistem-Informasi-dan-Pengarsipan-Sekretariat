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

        $query = Activity::with(['causer', 'subject'])->latest('created_at');

        // Only Super Admin can view every user's activity.
        if ($user->role->nama !== 'super-admin') {
            $query->whereMorphedTo('causer', $user);
        }

        if ($request->filled('cari')) {
            $kataKunci = $request->cari;

            $query->where(function ($q) use ($kataKunci) {
                $q->where('description', 'like', "%{$kataKunci}%")
                    ->orWhere('subject_type', 'like', "%{$kataKunci}%")
                    ->orWhereHasMorph('causer', [User::class], function ($sub) use ($kataKunci) {
                        $sub->where('name', 'like', "%{$kataKunci}%");
                    });
            });
        }

        $logs = $query->paginate(50)->withQueryString();

        $layout = $this->layoutSesuaiRole();

        return view('log-aktivitas.index', compact('logs', 'layout'));
    }

    public function show(Activity $log)
    {
        $user = auth()->user();

        // Keep direct detail access consistent with the list's ownership rule.
        if ($user->role->nama !== 'super-admin') {
            if (!$log->causer || !($log->causer instanceof User) || !$log->causer->is($user)) {
                abort(403);
            }
        }

        $layout = $this->layoutSesuaiRole();

        return view('log-aktivitas.show', compact('log', 'layout'));
    }

    private function layoutSesuaiRole(): string
    {
        return match (auth()->user()->role->nama) {
            'super-admin' => 'admin.layout',
            'sekretariat' => 'sekretariat.layout',
            'humas' => 'humas.layout',
            'legal-admin' => 'legal.layout',
            'employee' => 'employee.layout',
            default => 'staf.layout',
        };
    }
}
