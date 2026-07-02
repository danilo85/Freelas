<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class AdminSystemController extends Controller
{
    /**
     * Exibe o painel de administração geral (Usuários e Configurações).
     */
    public function index()
    {
        $settings = SystemSetting::firstOrCreate([]);
        
        // Pega todos os usuários cadastrados exceto o logado
        $users = User::where('id', '!=', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.settings', compact('settings', 'users'));
    }

    /**
     * Salva as configurações globais do sistema.
     */
    public function updateSettings(Request $request)
    {
        $settings = SystemSetting::firstOrCreate([]);
        
        $settings->update([
            'allow_registration' => $request->has('allow_registration'),
            'portfolio_maintenance' => $request->has('portfolio_maintenance'),
        ]);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Configurações globais atualizadas com sucesso!');
    }

    /**
     * Aprova um usuário registrado.
     */
    public function approveUser(User $user)
    {
        $user->update(['is_approved' => true]);

        return redirect()->route('admin.settings.index')
            ->with('success', "O usuário {$user->name} foi aprovado com sucesso!");
    }

    /**
     * Reprova/Bloqueia um usuário registrado.
     */
    public function disapproveUser(User $user)
    {
        $user->update(['is_approved' => false]);

        return redirect()->route('admin.settings.index')
            ->with('success', "O acesso do usuário {$user->name} foi suspenso!");
    }

    /**
     * Altera a função (role) de um usuário.
     */
    public function changeRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:master,comum',
        ]);

        $user->update(['role' => $request->role]);

        return redirect()->route('admin.settings.index')
            ->with('success', "A função de {$user->name} foi alterada para " . ($request->role === 'master' ? 'Master' : 'Comum') . "!");
    }
}
