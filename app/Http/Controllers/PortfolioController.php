<?php

namespace App\Http\Controllers;

use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use App\Models\PortfolioImage;
use App\Models\Client;
use App\Models\Author;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PortfolioController extends Controller
{
    /**
     * Listagem dos trabalhos de portfólio.
     */
    public function index(Request $request)
    {
        $query = auth()->user()->portfolioItems()->with(['category', 'client', 'authors']);

        // Estatísticas para os cards
        $totalCount = auth()->user()->portfolioItems()->count();
        $publishedCount = auth()->user()->portfolioItems()->where('status', 'publicado')->count();
        $draftsCount = auth()->user()->portfolioItems()->where('status', 'rascunho')->count();
        $featuredCount = auth()->user()->portfolioItems()->where('is_featured', true)->count();

        // Filtros
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('technologies', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('portfolio_category_id', $request->input('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $items = $query->orderBy('created_at', 'desc')->get();
        $categories = auth()->user()->portfolioCategories()->orderBy('name')->get();

        return view('portfolio.index', compact(
            'items',
            'categories',
            'totalCount',
            'publishedCount',
            'draftsCount',
            'featuredCount'
        ));
    }

    /**
     * Pipeline de Orçamentos Finalizados.
     */
    public function pipeline()
    {
        // Projetos finalizados
        $projects = Project::whereHas('client', function ($q) {
            $q->where('user_id', auth()->id());
        })
        ->with(['client', 'authors'])
        ->where('status', 'finalizado')
        ->orderBy('updated_at', 'desc')
        ->get();

        // Mapear IDs de projetos que já foram importados para o portfólio
        $importedProjectIds = auth()->user()->portfolioItems()
            ->whereNotNull('project_id')
            ->pluck('project_id')
            ->toArray();

        return view('portfolio.pipeline', compact('projects', 'importedProjectIds'));
    }

    /**
     * Formulário de cadastro de item.
     */
    public function create(Request $request)
    {
        $categories = auth()->user()->portfolioCategories()->orderBy('name')->get();
        $clients = auth()->user()->clients()->orderBy('name')->get();
        $authors = auth()->user()->authors()->orderBy('name')->get();

        // Tecnologias já utilizadas em outros trabalhos para sugestão/autocomplete
        $existingTechnologies = auth()->user()->portfolioItems()
            ->whereNotNull('technologies')
            ->pluck('technologies')
            ->flatMap(function ($techs) {
                return array_map('trim', explode(',', $techs));
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Dados padrão para preenchimento se originado do pipeline
        $projectData = null;
        if ($request->filled('project_id')) {
            $project = Project::whereHas('client', function ($q) {
                $q->where('user_id', auth()->id());
            })->with(['authors'])->findOrFail($request->input('project_id'));
            $projectData = [
                'id' => $project->id,
                'title' => $project->title,
                'description' => $project->description,
                'client_id' => $project->client_id,
                'author_ids' => $project->authors->pluck('id')->toArray(),
            ];
        }

        return view('portfolio.create', compact('categories', 'clients', 'authors', 'projectData', 'existingTechnologies'));
    }

    /**
     * Armazena o item de portfólio no banco de dados.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'portfolio_category_id' => 'required|exists:portfolio_categories,id',
            'description' => 'required|string',
            'thumb' => 'required|image|max:20480', // Máximo 20MB
            'status' => 'required|in:rascunho,publicado',
            'is_featured' => 'nullable|boolean',
            'client_id' => 'nullable|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'redirect_url' => 'nullable|url',
            'technologies' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'authors' => 'nullable|array',
            'authors.*' => 'exists:authors,id',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|max:20480',
            'gallery_orders' => 'nullable|array',
        ]);

        // Trata imagem thumb (capa)
        $thumbPath = null;
        if ($request->hasFile('thumb')) {
            $thumbPath = $this->optimizeAndSaveImage($request->file('thumb'));
        }

        $item = auth()->user()->portfolioItems()->create([
            'portfolio_category_id' => $request->portfolio_category_id,
            'client_id' => $request->client_id,
            'project_id' => $request->project_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . uniqid(),
            'description' => $request->description,
            'technologies' => $request->technologies,
            'redirect_url' => $request->redirect_url,
            'thumb_path' => $thumbPath,
            'status' => $request->status,
            'is_featured' => $request->has('is_featured') ? (bool)$request->is_featured : false,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
        ]);

        // Vincula autores
        if ($request->filled('authors')) {
            $item->authors()->sync($request->input('authors'));
        }

        // Processa imagens da galeria
        if ($request->hasFile('gallery')) {
            $files = $request->file('gallery');
            $orders = $request->input('gallery_orders', []);

            foreach ($files as $index => $file) {
                $path = $this->optimizeAndSaveImage($file);
                $order = isset($orders[$index]) ? intval($orders[$index]) : 0;

                $item->images()->create([
                    'image_path' => $path,
                    'order' => $order,
                ]);
            }
        }

        return redirect()->route('portfolio.index')->with('success', 'Trabalho de portfólio cadastrado com sucesso!');
    }

    /**
     * Exibe o trabalho de portfólio.
     */
    public function show(PortfolioItem $portfolio)
    {
        abort_if($portfolio->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        $portfolio->load(['category', 'client', 'authors', 'images', 'project']);

        return view('portfolio.show', compact('portfolio'));
    }

    /**
     * Formulário de edição.
     */
    public function edit(PortfolioItem $portfolio)
    {
        abort_if($portfolio->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        $portfolio->load(['authors', 'images']);

        $categories = auth()->user()->portfolioCategories()->orderBy('name')->get();
        $clients = auth()->user()->clients()->orderBy('name')->get();
        $authors = auth()->user()->authors()->orderBy('name')->get();

        // Tecnologias já utilizadas em outros trabalhos para sugestão/autocomplete
        $existingTechnologies = auth()->user()->portfolioItems()
            ->whereNotNull('technologies')
            ->pluck('technologies')
            ->flatMap(function ($techs) {
                return array_map('trim', explode(',', $techs));
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        return view('portfolio.edit', compact('portfolio', 'categories', 'clients', 'authors', 'existingTechnologies'));
    }

    /**
     * Atualiza os dados do item.
     */
    public function update(Request $request, PortfolioItem $portfolio)
    {
        abort_if($portfolio->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        $request->validate([
            'title' => 'required|string|max:255',
            'portfolio_category_id' => 'required|exists:portfolio_categories,id',
            'description' => 'required|string',
            'thumb' => 'nullable|image|max:20480',
            'status' => 'required|in:rascunho,publicado',
            'is_featured' => 'nullable|boolean',
            'client_id' => 'nullable|exists:clients,id',
            'redirect_url' => 'nullable|url',
            'technologies' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'authors' => 'nullable|array',
            'authors.*' => 'exists:authors,id',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|max:20480',
            'gallery_orders' => 'nullable|array',
            'existing_gallery_orders' => 'nullable|array',
            'delete_images' => 'nullable|array',
        ]);

        // Atualiza imagem thumb (capa) se enviado
        $thumbPath = $portfolio->thumb_path;
        if ($request->hasFile('thumb')) {
            if ($thumbPath) {
                Storage::disk('public')->delete($thumbPath);
            }
            $thumbPath = $this->optimizeAndSaveImage($request->file('thumb'));
        }

        $portfolio->update([
            'portfolio_category_id' => $request->portfolio_category_id,
            'client_id' => $request->client_id,
            'title' => $request->title,
            'description' => $request->description,
            'technologies' => $request->technologies,
            'redirect_url' => $request->redirect_url,
            'thumb_path' => $thumbPath,
            'status' => $request->status,
            'is_featured' => $request->has('is_featured') ? (bool)$request->is_featured : false,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
        ]);

        // Autores
        $portfolio->authors()->sync($request->input('authors', []));

        // Deleta imagens da galeria selecionadas
        if ($request->filled('delete_images')) {
            $imagesToDelete = $portfolio->images()->whereIn('id', $request->input('delete_images'))->get();
            foreach ($imagesToDelete as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }
        }

        // Atualiza a ordem das imagens existentes
        if ($request->filled('existing_gallery_orders')) {
            foreach ($request->input('existing_gallery_orders') as $imageId => $orderVal) {
                $portfolio->images()->where('id', $imageId)->update([
                    'order' => intval($orderVal)
                ]);
            }
        }

        // Adiciona novas imagens à galeria
        if ($request->hasFile('gallery')) {
            $files = $request->file('gallery');
            $orders = $request->input('gallery_orders', []);

            foreach ($files as $index => $file) {
                $path = $this->optimizeAndSaveImage($file);
                $order = isset($orders[$index]) ? intval($orders[$index]) : 0;

                $portfolio->images()->create([
                    'image_path' => $path,
                    'order' => $order,
                ]);
            }
        }

        return redirect()->route('portfolio.index')->with('success', 'Trabalho de portfólio atualizado com sucesso!');
    }

    /**
     * Exclui o item de portfólio.
     */
    public function destroy(PortfolioItem $portfolio)
    {
        abort_if($portfolio->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        // Deleta Thumb
        if ($portfolio->thumb_path) {
            Storage::disk('public')->delete($portfolio->thumb_path);
        }

        // Deleta galeria de imagens
        foreach ($portfolio->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        // Deleta pivot e item
        $portfolio->authors()->detach();
        $portfolio->delete();

        return redirect()->route('portfolio.index')->with('success', 'Trabalho de portfólio excluído com sucesso!');
    }

    /**
     * Exibe o formulário de configurações do site de portfólio.
     */
    public function settings()
    {
        $settings = auth()->user()->portfolioSetting;

        if (!$settings) {
            $settings = new \App\Models\PortfolioSetting([
                'site_title' => 'Danilo Miguel | Designer e Ilustrador',
                'site_subtitle' => 'Transformando ideias em experiências visuais marcantes',
                'site_description' => "Cada traço, cor e forma é pensado de maneira estratégica para contar histórias envolventes em livros infantis, materiais pedagógicos e jogos educativos.",
                'about_title' => 'Prazer, sou Danilo Miguel',
                'about_text' => "Com anos de experiência focados em design editorial e ilustração, crio soluções sob medida que integram beleza artística e inteligência estrutural. Desenvolvo livros de literatura infantil, materiais didáticos de estimulação cognitiva, jogos personalizados de tabuleiro ou cartas e identidades visuais corporativas.\n\nMeu trabalho visa transformar ideias abstratas e materiais textuais densos em composições leves, dinâmicas e altamente interativas. Acompanho autores e editoras desde o conceito original até a entrega do arquivo final preparado para as gráficas.",
                'skills' => 'Ilustração Infantil, Diagramação Editorial, Design de Jogos Pedagógicos, Identidade Visual, Criação de Personagens',
                'contact_email' => 'danilo.a.miguel@hotmail.com',
                'contact_phone' => '(14) 99143-6268',
                'behance_url' => 'behance.net/danilomiguel',
                'primary_color' => '#3b82f6',
                'secondary_color' => '#1d4ed8',
                'theme_mode' => 'escuro',
                'faq_items' => [
                    ['question' => 'Que tipo de materiais você desenvolve?', 'answer' => 'Desenvolvo livros infantis, materiais didáticos/pedagógicos de estimulação cognitiva, jogos personalizados de tabuleiro ou cartas, diagramação de catálogos, capas de livros, logotipos corporativos e peças gráficas gerais.'],
                    ['question' => 'Qual é o prazo de entrega dos projetos?', 'answer' => 'O prazo varia conforme a complexidade de cada demanda. Projetos simples e pontuais levam em média de 10 a 20 dias úteis. Projetos editoriais maiores com alto volume de ilustrações autorais podem requerer prazos mais amplos, definidos em orçamento.'],
                    ['question' => 'Como posso solicitar um orçamento?', 'answer' => 'Basta clicar no botão do WhatsApp disponível em nosso site e enviar uma mensagem com os detalhes básicos do seu projeto. Retorno o contato no mesmo dia para alinhar mais informações.'],
                    ['question' => 'Você atende clientes de fora do seu estado?', 'answer' => 'Sim! Atendo clientes e editoras de todo o Brasil e do exterior de forma 100% remota. O processo é simples: compartilhamento de referências e arquivos por e-mail/nuvem e reuniões por WhatsApp ou videoconferência.'],
                    ['question' => 'Em quais formatos você entrega os arquivos finais?', 'answer' => 'Entrego os arquivos finais fechados prontos para impressão (geralmente PDF em padrão X1a ou similar) e, caso acordado no contrato, posso fornecer os arquivos editáveis fontes (Adobe InDesign, Illustrator ou Photoshop).']
                ]
            ]);
        }

        return view('portfolio.settings', compact('settings'));
    }

    /**
     * Atualiza as configurações do site de portfólio.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'site_title' => 'required|string|max:255',
            'site_subtitle' => 'required|string|max:255',
            'site_description' => 'required|string',
            'about_title' => 'nullable|string|max:255',
            'about_text' => 'required|string',
            'skills' => 'required|string',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:50',
            'behance_url' => 'nullable|string|max:255',
            'primary_color' => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'theme_mode' => 'required|in:escuro,claro',
            'faq' => 'nullable|array',
            'faq.*.question' => 'required|string|max:255',
            'faq.*.answer' => 'required|string',
        ]);

        $faqItems = [];
        if ($request->filled('faq')) {
            foreach ($request->input('faq') as $faq) {
                if (!empty($faq['question']) && !empty($faq['answer'])) {
                    $faqItems[] = [
                        'question' => $faq['question'],
                        'answer' => $faq['answer']
                    ];
                }
            }
        }

        auth()->user()->portfolioSetting()->updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'site_title' => $request->site_title,
                'site_subtitle' => $request->site_subtitle,
                'site_description' => $request->site_description,
                'about_title' => $request->about_title,
                'about_text' => $request->about_text,
                'skills' => $request->skills,
                'contact_email' => $request->contact_email,
                'contact_phone' => $request->contact_phone,
                'behance_url' => $request->behance_url,
                'primary_color' => $request->primary_color,
                'secondary_color' => $request->secondary_color,
                'theme_mode' => $request->theme_mode,
                'faq_items' => $faqItems
            ]
        );

        return redirect()->route('portfolio.settings')
            ->with('success', 'Configurações do portfólio atualizadas com sucesso!');
    }

    /**
     * Auxiliar: Otimiza imagens usando a biblioteca nativa GD do PHP, 
     * convertendo para WebP de alta performance com compressão 80%.
     */
    private function optimizeAndSaveImage($file, $folder = 'portfolio')
    {
        ini_set('memory_limit', '512M'); // Previne estouro de memória no processamento de imagens de 20MB
        $info = getimagesize($file->getRealPath());
        $mime = $info['mime'] ?? '';

        // Carrega o recurso de imagem com base no Mime Type
        if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
            $image = imagecreatefromjpeg($file->getRealPath());
        } elseif ($mime === 'image/png') {
            $image = imagecreatefrompng($file->getRealPath());
            imagepalettetotruecolor($image);
            imagealphablending($image, false);
            imagesavealpha($image, true);
        } elseif ($mime === 'image/webp') {
            $image = imagecreatefromwebp($file->getRealPath());
        } else {
            // Se for outro tipo de arquivo, apenas salva normalmente
            return $file->store($folder, 'public');
        }

        // Caminho único para o arquivo WebP
        $filename = $folder . '/' . uniqid() . '.webp';

        // Comprime para WebP na memória usando buffer de saída
        ob_start();
        imagewebp($image, null, 80);
        $webpContent = ob_get_clean();
        imagedestroy($image);

        // Salva usando o Storage do Laravel para total compatibilidade com fakes de teste
        Storage::disk('public')->put($filename, $webpContent);

        return $filename;
    }
}
