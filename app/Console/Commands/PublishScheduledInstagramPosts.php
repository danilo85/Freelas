<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InstagramPost;
use App\Http\Controllers\InstagramController;
use Illuminate\Support\Facades\Log;

class PublishScheduledInstagramPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instagram:publish-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica e publica automaticamente postagens agendadas do Instagram cuja data/hora já passou.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $duePosts = InstagramPost::where('status', 'agendado')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($duePosts->isEmpty()) {
            $this->info('Nenhuma postagem agendada pendente.');
            return 0;
        }

        $this->info("Encontradas {$duePosts->count()} postagens agendadas para publicar.");
        $controller = app(InstagramController::class);

        foreach ($duePosts as $post) {
            try {
                $this->info("Publicando post ID {$post->id} (Tipo: {$post->media_type})...");
                
                if ($post->media_type === 'CAROUSEL') {
                    $controller->publishCarouselPostToInstagram($post);
                } elseif ($post->media_type === 'STORY') {
                    $controller->publishStoryPostToInstagram($post);
                } else {
                    $controller->publishPostToInstagram($post);
                }

                $post->refresh();
                if ($post->status === 'publicado') {
                    $this->info("✅ Post ID {$post->id} publicado com sucesso!");
                } else {
                    $this->error("❌ Falha no post ID {$post->id}: {$post->error_message}");
                }
            } catch (\Exception $e) {
                Log::error("Erro ao publicar post agendado ID {$post->id}: " . $e->getMessage());
                $post->update([
                    'status' => 'erro',
                    'error_message' => $e->getMessage()
                ]);
                $this->error("Erro no post ID {$post->id}: " . $e->getMessage());
            }
        }

        return 0;
    }
}
