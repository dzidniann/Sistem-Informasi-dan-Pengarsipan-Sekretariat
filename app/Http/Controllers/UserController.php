<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Bidang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['role', 'bidang'])->get();
        $roles = Role::all();
        $bidangList = Bidang::all();

        return view('admin.pengguna.index', compact('users', 'roles', 'bidangList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role_id' => 'required|exists:roles,id',
            'bidang_id' => 'required|exists:bidangs,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'bidang_id' => $request->bidang_id,
            'is_active' => true,
        ]);

        return back()->with('success', 'Pengguna berhasil ditambahkan');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'bidang_id' => 'required|exists:bidangs,id',
        ]);

        $user->update($request->only('name', 'email', 'role_id', 'bidang_id'));

        return back()->with('success', 'Pengguna berhasil diperbarui');
    }

    public function toggleAktif(User $user)
    {
        $user->update([
            'is_active' => !$user->is_active,
        ]);

        return back()->with('success', 'Status pengguna diperbarui');
    }

    public function role()
    {
        $roles = Role::withCount('users')->get();

        return view('admin.role.index', compact('roles'));
    }
}
