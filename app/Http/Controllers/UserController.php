<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:master,comum',
            'phone' => 'nullable|string',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'theme_color' => 'green',
        ]);

        return redirect()->route('admin.settings.index')->with('success', 'Usuário criado com sucesso!');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:master,comum',
            'phone' => 'nullable|string',
        ]);

        // Impede que o master logado retire o seu próprio nível master
        if ($user->id === auth()->id() && $validated['role'] !== 'master') {
            return redirect()->route('admin.settings.index')->with('error', 'Você não pode alterar o seu próprio nível de acesso.');
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
        ]);

        return redirect()->route('admin.settings.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.settings.index')->with('error', 'Você não pode excluir sua própria conta.');
        }

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return redirect()->route('admin.settings.index')->with('success', 'Usuário excluído com sucesso!');
    }
}
