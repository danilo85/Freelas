<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PortfolioItem;
use App\Models\PortfolioCategory;
use Illuminate\Http\Request;

class PublicPortfolioController extends Controller
{
    /**
     * Exibe o site público do portfólio.
     */
    public function index()
    {
        // Encontra o usuário master ou Danilo Miguel, caso contrário o primeiro cadastrado
        $user = User::where('role', 'master')->first()
            ?? User::where('email', 'danilo.a.miguel@hotmail.com')->first()
            ?? User::first();

        if (!$user) {
            return view('welcome', [
                'items' => collect(),
                'categories' => collect(),
                'user' => null
            ]);
        }

        // Carrega os itens do portfólio publicados
        $items = PortfolioItem::with(['category', 'authors', 'images'])
            ->where('user_id', $user->id)
            ->where('status', 'publicado')
            ->orderBy('created_at', 'desc')
            ->get();

        // Carrega as categorias de portfólio
        $categories = PortfolioCategory::where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        return view('welcome', compact('items', 'categories', 'user'));
    }

    /**
     * Exibe os detalhes de um trabalho do portfólio.
     */
    public function show($slug)
    {
        $item = PortfolioItem::with(['category', 'authors', 'images', 'client'])
            ->where('slug', $slug)
            ->where('status', 'publicado')
            ->firstOrFail();

        // Incrementa visualizações
        $item->increment('views');

        // Carrega trabalhos relacionados da mesma categoria
        $relatedItems = PortfolioItem::with(['category'])
            ->where('portfolio_category_id', $item->portfolio_category_id)
            ->where('id', '!=', $item->id)
            ->where('status', 'publicado')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        return view('portfolio_detail', compact('item', 'relatedItems'));
    }

    /**
     * Incrementa visualizações de forma assíncrona (AJAX).
     */
    public function incrementViews($id)
    {
        $item = PortfolioItem::findOrFail($id);
        $item->increment('views');
        return response()->json(['success' => true, 'views' => $item->views]);
    }

    /**
     * Incrementa curtidas de forma assíncrona (AJAX).
     */
    public function incrementLikes($id)
    {
        $item = PortfolioItem::findOrFail($id);
        $item->increment('likes');
        return response()->json(['success' => true, 'likes' => $item->likes]);
    }
}
