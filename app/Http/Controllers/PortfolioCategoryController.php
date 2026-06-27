<?php

namespace App\Http\Controllers;

use App\Models\PortfolioCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PortfolioCategoryController extends Controller
{
    /**
     * Exibe a listagem de categorias.
     */
    public function index()
    {
        $categories = auth()->user()->portfolioCategories()->orderBy('name')->get();
        return view('portfolio_categories.index', compact('categories'));
    }

    /**
     * Armazena uma nova categoria de portfólio.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        auth()->user()->portfolioCategories()->create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return redirect()->route('portfolio-categories.index')->with('success', 'Categoria cadastrada com sucesso!');
    }

    /**
     * Atualiza os dados de uma categoria de portfólio.
     */
    public function update(Request $request, PortfolioCategory $portfolioCategory)
    {
        abort_if($portfolioCategory->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $portfolioCategory->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return redirect()->route('portfolio-categories.index')->with('success', 'Categoria atualizada com sucesso!');
    }

    /**
     * Exclui uma categoria de portfólio.
     */
    public function destroy(PortfolioCategory $portfolioCategory)
    {
        abort_if($portfolioCategory->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        $portfolioCategory->delete();

        return redirect()->route('portfolio-categories.index')->with('success', 'Categoria excluída com sucesso!');
    }
}
