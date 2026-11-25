<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StaffPortalAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (in_array($request->user()?->role?->nama, ['staf-bidang', 'employee'], true)) {
            abort_unless($request->routeIs(
                'employee.consultations.*',
                'library.*',
                'uploads.show',
                'welcome',
                'login',
                'logout',
                'dashboard',
                'dashboard.staf',
                'profile.*',
                'password.*',
                'verification.*',
            ), 403);
        }

        return $next($request);
    }
}
