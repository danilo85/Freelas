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

        $setting = \App\Models\SystemSetting::first();
        if ($setting && !$setting->allow_registration) {
            return redirect()->route('login')->with('error', 'O cadastro de novos usuários está desativado.');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $setting = \App\Models\SystemSetting::first();
        if ($setting && !$setting->allow_registration) {
            return redirect()->route('login')->with('error', 'O cadastro de novos usuários está desativado.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string',
        ]);

        $isFirstUser = User::count() === 0;
        $role = $isFirstUser ? 'master' : 'comum';
        $isApproved = $isFirstUser ? true : false;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => $role,
            'is_approved' => $isApproved,
            'theme_color' => 'green',
        ]);

        Auth::login($user);

        if (!$isApproved) {
            return redirect()->route('waiting-approval');
        }

        return redirect()->route('dashboard')->with('success', 'Cadastro realizado com sucesso! Bem-vindo ao Gestor de Freelas.');
    }
}
