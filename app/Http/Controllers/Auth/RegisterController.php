<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string',
        ]);

        // Atribui 'master' para o primeiro usuário registrado, e 'comum' para os demais
        $isFirstUser = User::count() === 0;
        $role = $isFirstUser ? 'master' : 'comum';

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => $role,
            'theme_color' => 'green', // Padrão inicial verde
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Cadastro realizado com sucesso! Bem-vindo ao Gestor de Freelas.');
    }
}
