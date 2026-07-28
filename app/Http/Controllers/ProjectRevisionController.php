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
        $totalFilesSizeBytes = 0;

        foreach ($revisions as $rev) {
            foreach ($rev->rounds as $round) {
                if ($round->status === 'pendente' || $round->status === 'em_ajuste') {
                    $activeRoundsCount++;
                }
                foreach ($round->files as $file) {
                    $pendingAdjustmentsCount += $file->annotations->where('status', 'aberto')->count();
                    $totalFilesSizeBytes += (float) $file->file_size;
                }
            }
        }

        // Formata o tamanho total
        if ($totalFilesSizeBytes >= 1073741824) {
            $formattedStorageSize = number_format($totalFilesSizeBytes / 1073741824, 2, ',', '.') . ' GB';
        } elseif ($totalFilesSizeBytes >= 1048576) {
            $formattedStorageSize = number_format($totalFilesSizeBytes / 1048576, 2, ',', '.') . ' MB';
        } else {
            $formattedStorageSize = number_format($totalFilesSizeBytes / 1024, 2, ',', '.') . ' KB';
        }

        // Limite do Servidor (ex: 10 GB)
        $storageLimitBytes = 10 * 1024 * 1024 * 1024; // 10 GB
        $storageLimitFormatted = '10 GB';
        $storagePercent = min(100, ($totalFilesSizeBytes / $storageLimitBytes) * 100);

        return view('revisions.index', compact(
            'revisions',
            'authors',
            'totalProjects',
            'activeRoundsCount',
            'pendingAdjustmentsCount',
            'formattedStorageSize',
            'storageLimitFormatted',
            'storagePercent'
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

    public function destroy(Request $request, $id)
    {
        $revision = ProjectRevision::with('rounds.files.annotations')->where('user_id', auth()->id())->findOrFail($id);
        
        $shouldBackup = $request->input('backup') === '1';
        $downloadUrl = null;

        if ($shouldBackup && $revision->rounds->isNotEmpty()) {
            $zipFileName = 'backup_revisao_' . \Str::slug($revision->title) . '_' . time() . '.zip';
            
            // Ensure backups directory exists
            $backupsDir = storage_path('app/public/backups');
            if (!file_exists($backupsDir)) {
                mkdir($backupsDir, 0755, true);
            }
            
            $zipFilePath = $backupsDir . '/' . $zipFileName;

            $zip = new \ZipArchive();
            if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                // Add README
                $readmeContent = "BACKUP COMPLETO DE REVISÃO\n=========================\n";
                $readmeContent .= "Projeto: " . $revision->title . "\nSubtítulo: " . ($revision->subtitle ?? 'N/A') . "\n";
                $readmeContent .= "Data de Exportação: " . date('d/m/Y H:i:s') . "\n";
                $zip->addFromString('LEIA-ME.txt', $readmeContent);

                foreach ($revision->rounds as $round) {
                    $roundFolder = 'Rodada_' . $round->round_number;
                    
                    // Add files
                    foreach ($round->files as $file) {
                        if (\Storage::disk('public')->exists($file->file_path)) {
                            $physicalPath = \Storage::disk('public')->path($file->file_path);
                            $zipPath = $roundFolder . '/' . ($file->folder_name ? $file->folder_name . '/' : '') . $file->filename;
                            $zip->addFile($physicalPath, $zipPath);
                        }
                    }

                    // Add Report
                    $reportContent = "RELATÓRIO DE ANOTAÇÕES - RODADA #" . $round->round_number . "\n=========================================================\n";
                    $reportContent .= "Projeto: " . $revision->title . "\nRodada: " . $round->round_number . "\n";
                    $reportContent .= "Data de Envio: " . $round->created_at->format('d/m/Y H:i') . "\n=========================================================\n\n";

                    $index = 1;
                    foreach ($round->files as $file) {
                        if ($file->annotations->isEmpty()) continue;
                        $reportContent .= "ARQUIVO: " . ($file->folder_name ? $file->folder_name . '/' : '') . $file->filename . "\n---------------------------------------------------------\n";
                        foreach ($file->annotations as $anno) {
                            $reportContent .= "Ajuste #{$index}\n- Posição: Página " . $anno->page_number . "\n- Observação: " . $anno->comment . "\n- Status: " . ucfirst($anno->status) . "\n- Autor: " . ($anno->author ? $anno->author->name : 'Revisor Geral') . "\n\n";
                            $index++;
                        }
                    }
                    if ($index === 1) {
                        $reportContent .= "Nenhuma anotação nesta rodada.\n";
                    }
                    $zip->addFromString($roundFolder . '/Relatorio_Anotacoes_Rodada_' . $round->round_number . '.txt', $reportContent);
                }
                $zip->close();
                $downloadUrl = asset('storage/backups/' . $zipFileName);
            }
        }

        // Physically delete all round files from the disk to free space
        foreach ($revision->rounds as $round) {
            foreach ($round->files as $file) {
                if (\Storage::disk('public')->exists($file->file_path)) {
                    \Storage::disk('public')->delete($file->file_path);
                }
            }
        }

        // Delete from database
        $revision->delete();

        // Clean up old backup ZIP files (older than 1 hour)
        $oldZipFiles = glob(storage_path('app/public/backups/backup_revisao_*.zip'));
        if ($oldZipFiles) {
            foreach ($oldZipFiles as $oldFile) {
                if (time() - filemtime($oldFile) > 3600) {
                    @unlink($oldFile);
                }
            }
        }

        $response = redirect()->route('revisoes.index');
        if ($downloadUrl) {
            $response->with('download_backup_url', $downloadUrl)
                     ->with('success', 'Projeto de revisão e arquivos físicos excluídos com sucesso. O download do backup foi iniciado.');
        } else {
            $response->with('success', 'Projeto de revisão e arquivos físicos excluídos com sucesso.');
        }

        return $response;
    }

    public function downloadBackup($id)
    {
        $revision = ProjectRevision::with(['rounds.files.annotations', 'project.client'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        if ($revision->rounds->isEmpty()) {
            return redirect()->back()->with('error', 'Esta revisão não possui rodadas de arquivos para backup.');
        }

        // Clean up old backup files older than 1 hour to prevent disk build-up
        $oldZipFiles = glob(storage_path('app/public/backup_revisao_*.zip'));
        if ($oldZipFiles) {
            foreach ($oldZipFiles as $oldFile) {
                if (time() - filemtime($oldFile) > 3600) {
                    @unlink($oldFile);
                }
            }
        }

        $zipFileName = 'backup_revisao_' . \Str::slug($revision->title) . '_' . time() . '.zip';
        $zipFilePath = storage_path('app/public/' . $zipFileName);

        $zip = new \ZipArchive();
        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            
            // Add a readme file to root of ZIP
            $readmeContent = "BACKUP COMPLETO DE REVISÃO\n";
            $readmeContent .= "=========================\n";
            $readmeContent .= "Projeto: " . $revision->title . "\n";
            $readmeContent .= "Subtítulo: " . ($revision->subtitle ?? 'N/A') . "\n";
            $readmeContent .= "Data de Exportação: " . date('d/m/Y H:i:s') . "\n";
            $zip->addFromString('LEIA-ME.txt', $readmeContent);

            foreach ($revision->rounds as $round) {
                $roundFolder = 'Rodada_' . $round->round_number;
                
                // 1. Add all files in their folders
                foreach ($round->files as $file) {
                    if (\Storage::disk('public')->exists($file->file_path)) {
                        $physicalPath = \Storage::disk('public')->path($file->file_path);
                        
                        // Structure: Rodada_X/Folder/filename.ext
                        $zipPath = $roundFolder . '/' . ($file->folder_name ? $file->folder_name . '/' : '') . $file->filename;
                        $zip->addFile($physicalPath, $zipPath);
                    }
                }

                // 2. Generate and add annotations report for this round
                $reportContent = "RELATÓRIO DE ANOTAÇÕES E AJUSTES - RODADA #" . $round->round_number . "\n";
                $reportContent .= "=========================================================\n";
                $reportContent .= "Projeto: " . $revision->title . "\n";
                $reportContent .= "Rodada: " . $round->round_number . "\n";
                $reportContent .= "Status: " . ucfirst($round->status) . "\n";
                $reportContent .= "Data de Envio: " . $round->created_at->format('d/m/Y H:i') . "\n";
                $reportContent .= "=========================================================\n\n";

                $index = 1;
                foreach ($round->files as $file) {
                    $annotations = $file->annotations;
                    if ($annotations->isEmpty()) continue;

                    $reportContent .= "ARQUIVO: " . ($file->folder_name ? $file->folder_name . '/' : '') . $file->filename . "\n";
                    $reportContent .= "---------------------------------------------------------\n";
                    foreach ($annotations as $anno) {
                        $reportContent .= "Ajuste #{$index}\n";
                        $reportContent .= "- Página/Posição: Página " . $anno->page_number . "\n";
                        $reportContent .= "- Observação: " . $anno->comment . "\n";
                        $reportContent .= "- Status: " . ucfirst($anno->status) . "\n";
                        $reportContent .= "- Autor: " . ($anno->author ? $anno->author->name : 'Revisor Geral') . "\n";
                        $reportContent .= "- Data: " . $anno->created_at->format('d/m/Y H:i') . "\n\n";
                        $index++;
                    }
                    $reportContent .= "\n";
                }

                if ($index === 1) {
                    $reportContent .= "Nenhuma anotação registrada para esta rodada.\n";
                }

                $zip->addFromString($roundFolder . '/Relatorio_Anotacoes_Rodada_' . $round->round_number . '.txt', $reportContent);
            }

            $zip->close();
        } else {
            abort(500, 'Não foi possível gerar o arquivo ZIP de backup.');
        }

        return response()->download($zipFilePath);
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
