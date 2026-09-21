<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CekAkunAktif
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && !auth()->user()->is_active) {
            auth()->logout();
            return redirect('login')->with('error', 'Akun anda dinonaktifkan, hubungi admin untuk minta akses ya bub');
        }
        return $next($request);
    }
}