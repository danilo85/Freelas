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
