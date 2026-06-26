<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AuthorController extends Controller
{
    /**
     * Exibe a listagem de autores do usuário autenticado com estatísticas.
     */
    public function index()
    {
        $userId = auth()->id();
        
        // Carrega os autores do usuário logado
        $authors = auth()->user()->authors()->orderBy('name')->get();

        // 1. Total de Autores
        $totalAuthorsCount = $authors->count();

        // 2. Autores com Biografia
        $authorsWithBioCount = auth()->user()->authors()
            ->whereNotNull('bio')
            ->where('bio', '!=', '')
            ->count();

        // 3. Novos Autores (cadastrados nos últimos 30 dias)
        $newAuthorsCount = auth()->user()->authors()
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
        
        return view('authors.index', compact(
            'authors',
            'totalAuthorsCount',
            'authorsWithBioCount',
            'newAuthorsCount'
        ));
    }

    /**
     * Exibe o formulário de cadastro de autor.
     */
    public function create()
    {
        return view('authors.create');
    }

    /**
     * Armazena um novo autor associado ao usuário autenticado.
     */
    public function store(Request $request)
    {
        $userId = auth()->id();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:authors,email,NULL,id,user_id,' . $userId,
            'phone' => 'nullable|string',
            'document' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'bio' => 'nullable|string',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        auth()->user()->authors()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'document' => $validated['document'],
            'avatar' => $avatarPath,
            'bio' => $validated['bio'] ?? null,
        ]);

        return redirect()->route('authors.index')->with('success', 'Autor cadastrado com sucesso!');
    }

    /**
     * Exibe os detalhes de um autor (garantindo que pertence ao usuário).
     */
    public function show(Author $author)
    {
        // Verificação de Segurança (tenancy check)
        abort_if($author->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        return view('authors.show', compact('author'));
    }

    /**
     * Exibe o formulário de edição de um autor.
     */
    public function edit(Author $author)
    {
        // Verificação de Segurança (tenancy check)
        abort_if($author->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        return view('authors.edit', compact('author'));
    }

    /**
     * Atualiza os dados de um autor (garantindo que pertence ao usuário).
     */
    public function update(Request $request, Author $author)
    {
        $userId = auth()->id();
        
        // Verificação de Segurança (tenancy check)
        abort_if($author->user_id !== $userId, 403, 'Ação não autorizada.');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:authors,email,' . $author->id . ',id,user_id,' . $userId,
            'phone' => 'nullable|string',
            'document' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'bio' => 'nullable|string',
        ]);

        $author->name = $validated['name'];
        $author->email = $validated['email'];
        $author->phone = $validated['phone'];
        $author->document = $validated['document'];
        $author->bio = $validated['bio'] ?? null;

        if ($request->hasFile('avatar')) {
            // Remove o avatar anterior
            if ($author->avatar) {
                Storage::disk('public')->delete($author->avatar);
            }
            $author->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $author->registration_completed = true;
        $author->save();

        return redirect()->route('authors.index')->with('success', 'Autor atualizado com sucesso!');
    }

    /**
     * Exclui um autor (garantindo que pertence ao usuário).
     */
    public function destroy(Author $author)
    {
        // Verificação de Segurança (tenancy check)
        abort_if($author->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        if ($author->avatar) {
            Storage::disk('public')->delete($author->avatar);
        }

        $author->delete();

        return redirect()->route('authors.index')->with('success', 'Autor excluído com sucesso!');
    }
}
