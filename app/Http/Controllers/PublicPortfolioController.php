<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PortfolioItem;
use App\Models\PortfolioCategory;
use App\Models\PortfolioSetting;
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
                'user' => null,
                'settings' => $this->getSettings(null)
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

        $settings = $this->getSettings($user);

        return view('welcome', compact('items', 'categories', 'user', 'settings'));
    }

    /**
     * Exibe os detalhes de um trabalho do portfólio.
     */
    public function show($slug)
    {
        $item = PortfolioItem::with(['category', 'authors', 'images', 'client', 'user'])
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

        $settings = $this->getSettings($item->user);

        return view('portfolio_detail', compact('item', 'relatedItems', 'settings'));
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

    /**
     * Obtém as configurações ou retorna as padrões do Danilo Miguel.
     */
    protected function getSettings($user)
    {
        $settings = $user ? $user->portfolioSetting : null;
        
        if (!$settings) {
            $settings = new PortfolioSetting([
                'site_title' => 'Danilo Miguel | Designer e Ilustrador',
                'site_subtitle' => 'Transformando ideias em experiências visuais marcantes',
                'site_description' => "Cada traço, cor e forma é pensado de maneira estratégica para contar histórias envolventes em livros infantis, materiais pedagógicos e jogos educativos.",
                'about_title' => 'Prazer, sou Danilo Miguel',
                'about_text' => "Com anos de experiência focados em design editorial e ilustração, crio soluções sob medida que integram beleza artística e inteligência estrutural. Desenvolvo livros de literatura infantil, materiais didáticos de estimulação cognitiva, jogos personalizados de tabuleiro ou cartas e identidades visuais corporativas.\n\nMeu trabalho visa transformar ideias abstratas e materiais textuais densos em composições leves, dinâmicas e altamente interativas. Acompanho autores e editoras desde o conceito original até a entrega do arquivo final preparado para as gráficas.",
                'skills' => 'Ilustração Infantil, Diagramação Editorial, Design de Jogos Pedagógicos, Identidade Visual, Criação de Personagens',
                'contact_email' => 'danilo.a.miguel@hotmail.com',
                'contact_phone' => '(14) 99143-6268',
                'behance_url' => 'behance.net/danilomiguel',
                'faq_items' => [
                    ['question' => 'Que tipo de materiais você desenvolve?', 'answer' => 'Desenvolvo livros infantis, materiais didáticos/pedagógicos de estimulação cognitiva, jogos personalizados de tabuleiro ou cartas, diagramação de catálogos, capas de livros, logotipos corporativos e peças gráficas gerais.'],
                    ['question' => 'Qual é o prazo de entrega dos projetos?', 'answer' => 'O prazo varia conforme a complexidade de cada demanda. Projetos simples e pontuais levam em média de 10 a 20 dias úteis. Projetos editoriais maiores com alto volume de ilustrações autorais podem requerer prazos mais amplos, definidos em orçamento.'],
                    ['question' => 'Como posso solicitar um orçamento?', 'answer' => 'Basta clicar no botão do WhatsApp disponível em nosso site e enviar uma mensagem com os detalhes básicos do seu projeto. Retorno o contato no mesmo dia para alinhar mais informações.'],
                    ['question' => 'Você atende clientes de fora do seu estado?', 'answer' => 'Sim! Atendo clientes e editoras de todo o Brasil e do exterior de forma 100% remota. O processo é simples: compartilhamento de referências e arquivos por e-mail/nuvem e reuniões por WhatsApp ou videoconferência.'],
                    ['question' => 'Em quais formatos você entrega os arquivos finais?', 'answer' => 'Entrego os arquivos finais fechados prontos para impressão (geralmente PDF em padrão X1a ou similar) e, caso acordado no contrato, posso fornecer os arquivos editáveis fontes (Adobe InDesign, Illustrator ou Photoshop).']
                ]
            ]);
        }

        return $settings;
    }
}
