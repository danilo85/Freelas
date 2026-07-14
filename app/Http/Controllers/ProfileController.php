<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile', [
            'user' => auth()->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string',
            'theme_color' => 'required|in:green,blue,purple,indigo,orange',
            'sidebar_color' => 'required|in:dark,zinc,teal,navy,purple',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];
        $user->theme_color = $validated['theme_color'];
        $user->sidebar_color = $validated['sidebar_color'];

        // Upload de Avatar
        if ($request->hasFile('avatar')) {
            // Exclui o avatar anterior se existir
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Salva o novo avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        // Upload de Logo
        if ($request->hasFile('logo')) {
            // Exclui a logo anterior se existir
            if ($user->logo) {
                Storage::disk('public')->delete($user->logo);
            }

            // Salva a nova logo
            $path = $request->file('logo')->store('logos', 'public');
            $user->logo = $path;
        }

        // Senha
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->back()->with('success', 'Perfil atualizado com sucesso!');
    }
}
