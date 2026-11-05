<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        abort_unless(config('auth.allow_registration', false), 404);

        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless(config('auth.allow_registration', false), 404);
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $employeeRole = \App\Models\Role::where('nama', 'employee')->first();
        $bidangUmum = \App\Models\Bidang::where('nama_bidang', 'Umum')->first();
        abort_unless($employeeRole && $bidangUmum, 503, 'Pendaftaran belum dikonfigurasi.');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $employeeRole?->id,
            'bidang_id' => $bidangUmum?->id,
            'is_active' => false,
        ]);

        event(new Registered($user));

        return redirect()->route('login')->with('status', 'Akun menunggu aktivasi Admin.');
    }
}
