<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\EditorialRevision;
use App\Models\EditorialRevisionCorrection;
use App\Models\EditorialRevisionFile;
use App\Models\EditorialRevisionGlossary;
use App\Models\EditorialRevisionComment;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditorialRevisionController extends Controller
{
    /**
     * Lista todas as revisões editoriais.
     */
    public function index(Request $request)
    {
        $query = EditorialRevision::with(['user', 'revisor', 'client', 'files', 'corrections'])
            ->where(function ($q) {
                // Se for revisor, vê apenas as atribuídas a ele ou criadas por ele
                if (auth()->user()->isRevisor()) {
                    $q->where('revisor_id', auth()->id())
                      ->orWhere('user_id', auth()->id());
                } else {
                    $q->where('user_id', auth()->id());
                }
            })
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $revisions = $query->get();

        // Métricas
        $totalCount = $revisions->count();
        $pendingCount = $revisions->where('status', 'aguardando_revisor')->count();
        $inProgressCount = $revisions->where('status', 'em_revisao')->count();
        $completedCount = $revisions->where('status', 'concluido')->count();

        $isGoogleConnected = !empty(env('GOOGLE_DRIVE_REFRESH_TOKEN'));

        return view('editorial_revisions.index', compact(
            'revisions',
            'totalCount',
            'pendingCount',
            'inProgressCount',
            'completedCount',
            'isGoogleConnected'
        ));
    }

    /**
     * Exibe o formulário de criação de nova revisão editorial.
     */
    public function create()
    {
        $projects = Project::where('user_id', auth()->id())->latest()->get();
        $clients = Client::where('user_id', auth()->id())->latest()->get();
        $revisores = User::where('role', 'revisor')->orWhere('id', auth()->id())->latest()->get();
        $isGoogleConnected = !empty(env('GOOGLE_DRIVE_REFRESH_TOKEN'));

        return view('editorial_revisions.create', compact('projects', 'clients', 'revisores', 'isGoogleConnected'));
    }

    /**
     * Salva uma nova revisão editorial e processa os uploads de arquivos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'files' => 'required|array|min:1',
            'files.*' => 'file|max:1048576', // Até 1GB por arquivo
            'expires_days' => 'nullable|integer|min:1|max:90',
        ]);

        // Se o usuário solicitou criar um novo revisor na hora
        $revisorId = $request->revisor_id;
        if ($request->boolean('create_new_revisor') && $request->filled('new_revisor_name') && $request->filled('new_revisor_email')) {
            $request->validate([
                'new_revisor_email' => 'required|email|unique:users,email',
                'new_revisor_password' => 'required|string|min:6',
            ]);

            $newRevisor = User::create([
                'name' => $request->new_revisor_name,
                'email' => $request->new_revisor_email,
                'password' => Hash::make($request->new_revisor_password),
                'role' => 'revisor',
                'is_approved' => true,
            ]);

            $revisorId = $newRevisor->id;
        }

        // Escolha de disco de armazenamento
        $userChoice = $request->get('storage_disk', 'google');
        $hasGoogle = !empty(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
        $disk = ($userChoice === 'google' && $hasGoogle) ? 'google' : 'public';

        $deadline = $request->filled('deadline_at') 
            ? Carbon::parse($request->deadline_at) 
            : ($request->filled('expires_days') ? Carbon::now()->addDays((int) $request->expires_days) : null);

        $revision = EditorialRevision::create([
            'user_id' => auth()->id(),
            'revisor_id' => $revisorId,
            'client_id' => $request->client_id,
            'project_id' => $request->project_id,
            'title' => $request->title,
            'description' => $request->description,
            'share_token' => Str::random(32),
            'status' => 'aguardando_revisor',
            'deadline_at' => $deadline,
            'password' => $request->filled('password') ? bcrypt($request->password) : null,
            'storage_disk' => $disk,
            'is_active' => true,
        ]);

        // Upload de arquivos
        foreach ($request->file('files') as $file) {
            $mime = $file->getClientMimeType();
            $ext = strtolower($file->getClientOriginalExtension());
            
            $fileType = 'image';
            if (in_array($ext, ['doc', 'docx', 'txt', 'rtf', 'odt'])) {
                $fileType = 'word';
            } elseif ($ext === 'pdf') {
                $fileType = 'pdf';
            }

            $path = $file->store('editorial_revisions', $disk);

            EditorialRevisionFile::create([
                'editorial_revision_id' => $revision->id,
                'filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $mime,
                'file_type' => $fileType,
                'version' => 1,
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Projeto de Revisão Editorial criado com sucesso!',
                'redirect_url' => route('revisoes-editoriais.show', $revision->id),
            ]);
        }

        return redirect()->route('revisoes-editoriais.show', $revision->id)
            ->with('success', 'Projeto de Revisão Editorial criado com sucesso!');
    }

    /**
     * Exibe o workspace principal da revisão editorial.
     */
    public function show(EditorialRevision $editorialRevision)
    {
        $editorialRevision->load([
            'user', 
            'revisor', 
            'client', 
            'project', 
            'files', 
            'corrections.createdBy', 
            'corrections.comments', 
            'glossaries'
        ]);

        // Se o revisor abrir a página, atualiza status para 'em_revisao'
        if (auth()->id() === $editorialRevision->revisor_id && $editorialRevision->status === 'aguardando_revisor') {
            $editorialRevision->update(['status' => 'em_revisao']);
        }

        $correctionsByCategory = $editorialRevision->corrections->groupBy('category');

        return view('editorial_revisions.show', compact('editorialRevision', 'correctionsByCategory'));
    }

    /**
     * Atualiza as configurações e status da revisão.
     */
    public function update(Request $request, EditorialRevision $editorialRevision)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|string',
        ]);

        $editorialRevision->update([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'revisor_id' => $request->revisor_id ?: $editorialRevision->revisor_id,
            'deadline_at' => $request->filled('deadline_at') ? Carbon::parse($request->deadline_at) : $editorialRevision->deadline_at,
        ]);

        return back()->with('success', 'Configurações da Revisão Editorial atualizadas com sucesso.');
    }

    /**
     * Cadastra uma nova correção/apontamento de revisão.
     */
    public function storeCorrection(Request $request, EditorialRevision $editorialRevision)
    {
        $request->validate([
            'category' => 'required|string',
            'original_text' => 'nullable|string',
            'suggested_text' => 'nullable|string',
            'justification' => 'nullable|string',
        ]);

        $correction = EditorialRevisionCorrection::create([
            'editorial_revision_id' => $editorialRevision->id,
            'editorial_revision_file_id' => $request->editorial_revision_file_id,
            'page_number' => $request->page_number,
            'original_text' => $request->original_text,
            'suggested_text' => $request->suggested_text,
            'justification' => $request->justification,
            'category' => $request->category,
            'priority' => $request->get('priority', 'media'),
            'status' => 'pendente',
            'source' => auth()->user()->isRevisor() ? 'revisor' : 'autor',
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Apontamento registrado com sucesso!');
    }

    /**
     * Atualiza o status de uma correção (Aceitar, Ignorar, Resolver).
     */
    public function updateCorrectionStatus(Request $request, EditorialRevisionCorrection $correction)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $correction->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Status da correção atualizado.');
    }

    /**
     * Remove uma correção.
     */
    public function destroyCorrection(EditorialRevisionCorrection $correction)
    {
        $correction->delete();
        return back()->with('success', 'Apontamento removido.');
    }

    /**
     * Salva um termo no Glossário do projeto.
     */
    public function storeGlossary(Request $request, EditorialRevision $editorialRevision)
    {
        $request->validate([
            'correct_term' => 'required|string|max:255',
        ]);

        EditorialRevisionGlossary::create([
            'editorial_revision_id' => $editorialRevision->id,
            'correct_term' => $request->correct_term,
            'incorrect_terms' => $request->incorrect_terms,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Termo adicionado ao Glossário do projeto.');
    }

    /**
     * Remove um termo do Glossário.
     */
    public function destroyGlossary(EditorialRevisionGlossary $glossary)
    {
        $glossary->delete();
        return back()->with('success', 'Termo removido do Glossário.');
    }

    /**
     * Adiciona um comentário em um apontamento.
     */
    public function storeComment(Request $request, EditorialRevisionCorrection $correction)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        EditorialRevisionComment::create([
            'editorial_revision_correction_id' => $correction->id,
            'user_id' => auth()->id(),
            'author_name' => auth()->user()->name,
            'message' => $request->message,
        ]);

        return back()->with('success', 'Comentário enviado.');
    }

    /**
     * Exclui uma revisão editorial e seus arquivos do disco.
     */
    public function destroy(EditorialRevision $editorialRevision)
    {
        foreach ($editorialRevision->files as $file) {
            try {
                if (Storage::disk($editorialRevision->storage_disk)->exists($file->file_path)) {
                    Storage::disk($editorialRevision->storage_disk)->delete($file->file_path);
                }
            } catch (\Throwable $e) {}
        }

        $editorialRevision->delete();

        return redirect()->route('revisoes-editoriais.index')
            ->with('success', 'Projeto de Revisão Editorial excluído com sucesso.');
    }

    /**
     * Método utilitário para formatar bytes em tamanhos legíveis.
     */
    public function formatBytes($bytes, $decimals = 2)
    {
        if ($bytes <= 0) return '0 Bytes';
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = Math.floor(Math.log($bytes) / Math.log($k));
        return parseFloat(($bytes / Math.pow($k, $i)).toFixed($decimals)) + ' ' + $sizes[$i];
    }

    /**
     * Gerenciamento de Revisores Cadastrados.
     */
    public function revisoresIndex()
    {
        $revisores = User::where('role', 'revisor')->withCount(['revisionsAsRevisor'])->latest()->get();
        return view('editorial_revisions.revisores', compact('revisores'));
    }

    public function revisoresStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'revisor',
            'is_approved' => true,
        ]);

        return back()->with('success', 'Revisor cadastrado com sucesso!');
    }

    public function revisoresUpdate(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'Dados do revisor atualizados com sucesso!');
    }

    public function revisoresDestroy(User $user)
    {
        if ($user->role === 'revisor') {
            $user->delete();
            return back()->with('success', 'Revisor excluído do sistema.');
        }

        return back()->with('error', 'Não é possível excluir este usuário.');
    }
}
