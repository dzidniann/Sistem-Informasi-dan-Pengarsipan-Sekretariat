<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LegalModuleAccess
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(in_array($request->user()?->role?->nama, ['legal-admin', 'super-admin'], true), 403);

        return $next($request);
    }
}
