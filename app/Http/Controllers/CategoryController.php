<?php

namespace App\Http\Controllers;

use App\Models\TransactionCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Exibe a listagem de categorias do usuário + padrões.
     */
    public function index()
    {
        $userId = auth()->id();
        
        // Pega categorias do sistema e do usuário
        $categories = TransactionCategory::forUser($userId)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('finances.categories', compact('categories'));
    }

    /**
     * Armazena uma categoria personalizada no banco.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:receita,despesa,ambos',
            'icon' => 'required|string|max:50', // Pode ser um emoji ou classe de ícone
        ]);

        TransactionCategory::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'icon' => $validated['icon'],
        ]);

        return redirect()->route('finances.categories.index')->with('success', 'Categoria cadastrada com sucesso!');
    }

    /**
     * Atualiza uma categoria personalizada.
     */
    public function update(Request $request, TransactionCategory $category)
    {
        // Garante que só edita se for categoria própria ou padrão do sistema (user_id é null)
        abort_if(!is_null($category->user_id) && $category->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        \Log::info('Category Update Request ID ' . $category->id . ':', $request->all());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:receita,despesa,ambos',
            'icon' => 'required|string|max:50',
        ]);

        $category->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'icon' => $validated['icon'],
        ]);

        return redirect()->route('finances.categories.index')->with('success', 'Categoria atualizada com sucesso!');
    }

    /**
     * Remove uma categoria personalizada.
     */
    public function destroy(TransactionCategory $category)
    {
        // Garante que só remove se for categoria própria ou padrão do sistema
        abort_if(!is_null($category->user_id) && $category->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        $category->delete();

        return redirect()->route('finances.categories.index')->with('success', 'Categoria excluída com sucesso!');
    }
}
