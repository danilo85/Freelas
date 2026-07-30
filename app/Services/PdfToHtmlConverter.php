<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class PdfToHtmlConverter
{
    /**
     * Garante a inclusão do Autoloader das classes do Smalot PdfParser.
     */
    protected static function registerAutoloader(): void
    {
        if (!class_exists('Smalot\\PdfParser\\Parser')) {
            spl_autoload_register(function ($class) {
                $prefix = 'Smalot\\PdfParser\\';
                $base_dir = base_path('vendor/smalot/pdfparser/src/Smalot/PdfParser/');
                $len = strlen($prefix);
                if (strncmp($prefix, $class, $len) !== 0) return;
                $relative_class = substr($class, $len);
                $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
                if (file_exists($file)) {
                    require $file;
                }
            });
        }
    }

    /**
     * Extrai o texto completo de um arquivo PDF e o converte em HTML rico preservando a diagramação página por página.
     */
    public static function convertToHtml(string $filePath, string $diskName = 'public'): string
    {
        self::registerAutoloader();

        try {
            $disk = Storage::disk($diskName);
            if (!$disk->exists($filePath)) {
                $filePathOnDisk = storage_path('app/public/' . $filePath);
            } else {
                $filePathOnDisk = $disk->path($filePath);
            }

            if (!file_exists($filePathOnDisk)) {
                return '<p class="p-6 text-slate-500 italic">Arquivo PDF não localizado no armazenamento.</p>';
            }

            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePathOnDisk);

            $pages = $pdf->getPages();
            $totalPages = count($pages);
            $html = '';

            foreach ($pages as $index => $page) {
                $text = trim($page->getText());
                if (empty($text)) continue;

                $lines = explode("\n", $text);
                $pageContentHtml = '';
                $inList = false;

                foreach ($lines as $line) {
                    $cleanLine = trim($line);
                    if ($cleanLine === '') continue;

                    // Detecta marcadores de tópicos ou listas (ex: •, -, a.1), 1.)
                    if (preg_match('/^(?:[•\-\*]|\b[a-z0-9]+\))\s+(.+)/i', $cleanLine, $matches)) {
                        if (!$inList) {
                            $pageContentHtml .= '<ul class="list-disc pl-5 my-3 space-y-1.5 text-slate-900 font-serif text-base">';
                            $inList = true;
                        }
                        $pageContentHtml .= '<li class="leading-relaxed">' . e($matches[1]) . '</li>';
                        continue;
                    }

                    if ($inList) {
                        $pageContentHtml .= '</ul>';
                        $inList = false;
                    }

                    // Títulos de seção ou cabeçalhos em maiúsculo
                    if (mb_strlen($cleanLine) < 70 && mb_strtoupper($cleanLine) === $cleanLine && preg_match('/[A-Z]/', $cleanLine)) {
                        $pageContentHtml .= '<h3 class="font-outfit font-black text-lg text-slate-900 mt-6 mb-3 uppercase tracking-tight border-b border-slate-200 pb-1">' . e($cleanLine) . '</h3>';
                    } elseif (preg_match('/^(?:Objetivos|Materiais|Sessão|Capítulo|Introdução|Conclusão):/i', $cleanLine)) {
                        $pageContentHtml .= '<h4 class="font-bold text-slate-800 text-base mt-4 mb-2">' . e($cleanLine) . '</h4>';
                    } else {
                        $pageContentHtml .= '<p class="mb-3.5 leading-relaxed text-slate-900 font-serif text-base text-justify">' . e($cleanLine) . '</p>';
                    }
                }

                if ($inList) {
                    $pageContentHtml .= '</ul>';
                }

                if (!empty($pageContentHtml)) {
                    $pageNum = $index + 1;
                    $html .= '<div class="pdf-page-card bg-white border border-slate-300 rounded-[2px] paper-shadow p-10 mb-10 relative select-text" data-page="' . $pageNum . '" style="width: 210mm; min-height: 297mm; box-sizing: border-box; margin-left: auto; margin-right: auto;">';
                    $html .= '<div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-6 select-none">';
                    $html .= '<span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 bg-slate-900 text-white rounded">📄 Página ' . $pageNum . ' de ' . $totalPages . ' (PDF Original)</span>';
                    $html .= '<span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Formato A4 • Layout Preservado</span>';
                    $html .= '</div>';
                    $html .= '<div class="pdf-page-body">' . $pageContentHtml . '</div>';
                    $html .= '</div>';
                }
            }

            if (empty($html)) {
                return '<p class="p-6 text-slate-500 italic">O arquivo PDF é escaneado em imagem ou não possui camada de texto extraível.</p>';
            }

            return $html;

        } catch (\Throwable $e) {
            return '<p class="p-6 text-rose-500 font-bold">Falha ao extrair texto do PDF: ' . e($e->getMessage()) . '</p>';
        }
    }
}
