<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ProjectRevision;
use App\Models\Author;
use App\Models\Project;
use App\Models\RevisionRound;
use Illuminate\Support\Str;

class ProjectRevisionController extends Controller
{
    public function index(Request $request)
    {
        $query = ProjectRevision::where('user_id', auth()->id())
            ->with(['author', 'project', 'rounds.files.annotations']);

        // Search & Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('subtitle', 'like', "%{$search}%")
                  ->orWhereHas('author', function ($authorQ) use ($search) {
                      $authorQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $revisions = $query->latest()->get();
        $authors = Author::where('user_id', auth()->id())->orderBy('name', 'asc')->get();

        // Calculate summary cards metrics
        $totalProjects = $revisions->count();
        $activeRoundsCount = 0;
        $pendingAdjustmentsCount = 0;

        foreach ($revisions as $rev) {
            foreach ($rev->rounds as $round) {
                if ($round->status === 'pendente' || $round->status === 'em_ajuste') {
                    $activeRoundsCount++;
                }
                foreach ($round->files as $file) {
                    $pendingAdjustmentsCount += $file->annotations->where('status', 'aberto')->count();
                }
            }
        }

        return view('revisions.index', compact(
            'revisions',
            'authors',
            'totalProjects',
            'activeRoundsCount',
            'pendingAdjustmentsCount'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'author_id' => 'required|exists:authors,id',
            'project_id' => 'nullable|exists:projects,id',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
        ]);

        $revision = ProjectRevision::create([
            'user_id' => auth()->id(),
            'author_id' => $request->author_id,
            'project_id' => $request->project_id,
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'share_token' => Str::random(40),
            'status' => 'ativo',
        ]);

        // Create default Round 1
        $revision->rounds()->create([
            'round_number' => 1,
            'description' => 'Primeira rodada de revisão de arquivos.',
            'status' => 'pendente',
        ]);

        return redirect()->route('revisoes.show', $revision->id)
            ->with('success', 'Projeto de Revisão criado com sucesso com a Rodada 1 iniciada!');
    }

    public function show($id)
    {
        $revision = ProjectRevision::where('user_id', auth()->id())
            ->with(['author', 'project', 'rounds' => function ($q) {
                $q->orderBy('round_number', 'desc');
            }, 'rounds.files.annotations'])
            ->findOrFail($id);

        return view('revisions.show', compact('revision'));
    }

    public function destroy($id)
    {
        $revision = ProjectRevision::where('user_id', auth()->id())->findOrFail($id);
        $revision->delete();

        return redirect()->route('revisoes.index')
            ->with('success', 'Projeto de revisão excluído com sucesso.');
    }

    public function searchAuthors(Request $request)
    {
        $search = $request->get('q');
        $authors = Author::where('user_id', auth()->id())
            ->where('name', 'like', "%{$search}%")
            ->limit(10)
            ->get(['id', 'name', 'avatar']);

        return response()->json($authors);
    }

    public function getProjectsByAuthor($author)
    {
        $authorId = $author instanceof \App\Models\Author ? $author->id : $author;

        // Get projects associated with this author through pivot (ignora analisando e rejeitado)
        $projects = Project::whereHas('authors', function ($q) use ($authorId) {
            $q->where('authors.id', $authorId);
        })
        ->whereNotIn('status', ['analisando', 'rejeitado'])
        ->get(['id', 'title', 'status']);

        // Ordena para que os projetos 'aprovado' fiquem primeiro
        $sortedProjects = $projects->sortBy(function ($project) {
            return $project->status === 'aprovado' ? 0 : 1;
        })->values();

        return response()->json($sortedProjects);
    }
}
