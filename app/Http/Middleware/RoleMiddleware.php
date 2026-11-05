<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        // Support multiple roles separated by comma
        // Contoh: role:legal-admin,employee
        $roles = explode(',', $role);

        if (!in_array(auth()->user()->role->nama, $roles)) {
            abort(403);
        }

        return $next($request);
    }
}
