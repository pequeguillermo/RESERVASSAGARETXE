<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct()
    {
        // Solo superadmin puede acceder a esto (se asume que el route middleware 'auth' ya protege el controlador)
    }

    public function index()
    {
        $users = User::whereIn('role', ['superadmin', 'admin'])->get();
        return Inertia::render('Admin/Users', [
            'users' => $users
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:superadmin,admin',
            'access_code' => 'required|string|unique:users,access_code'
        ]);

        $validated['email'] = 'admin_' . uniqid() . '@sagaretxe.com'; // Dummy email since we don't use it
        $validated['password'] = Hash::make('Sagaretxe2026'); // Dummy password

        User::create($validated);

        return redirect()->back()->with('success', 'Usuario creado correctamente');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:superadmin,admin',
            'access_code' => 'required|string|unique:users,access_code,' . $user->id
        ]);

        $user->update($validated);

        return redirect()->back()->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy(User $user)
    {
        if (User::where('role', 'superadmin')->count() <= 1 && $user->role === 'superadmin') {
            return redirect()->back()->withErrors(['error' => 'No puedes eliminar al último superadministrador']);
        }
        
        $user->delete();
        return redirect()->back()->with('success', 'Usuario eliminado');
    }
}
