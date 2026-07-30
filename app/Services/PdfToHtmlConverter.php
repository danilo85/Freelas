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
     * Extrai o texto completo E AS IMAGENS EMBUTIDAS de cada página de um PDF,
     * incorporando-as como Data URIs Base64 100% seguras (sem dependência de symlinks).
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
                $pageNum = $index + 1;
                $text = trim($page->getText());
                
                // Extrai Imagens XObjects da Página e converte em Base64 Data URI
                $pageImagesHtml = '';
                try {
                    $xobjects = $page->getXObjects();
                    $processedHashes = [];

                    foreach ($xobjects as $name => $xobj) {
                        $subtype = $xobj->getHeader()->get('Subtype') ? $xobj->getHeader()->get('Subtype')->getContent() : '';
                        if ($subtype === 'Image') {
                            $content = $xobj->getContent();
                            if (empty($content) || strlen($content) < 100) continue;

                            // Valida se os bytes correspondem a uma imagem válida (JPEG / PNG / GIF / WEBP)
                            $imgInfo = @getimagesizefromstring($content);
                            if (!$imgInfo || empty($imgInfo['mime'])) continue;

                            // Evita duplicatas da mesma imagem na página
                            $contentHash = md5($content);
                            if (in_array($contentHash, $processedHashes)) continue;
                            $processedHashes[] = $contentHash;

                            $mime = $imgInfo['mime'];
                            $base64Data = 'data:' . $mime . ';base64,' . base64_encode($content);

                            $pageImagesHtml .= '<div class="my-4 text-center select-none">';
                            $pageImagesHtml .= '<img src="' . $base64Data . '" class="max-w-full rounded shadow-sm border border-slate-200 mx-auto block my-3" style="max-height: 380px; object-fit: contain;" alt="Imagem da Página ' . $pageNum . '">';
                            $pageImagesHtml .= '</div>';
                        }
                    }
                } catch (\Throwable $eImg) {
                    // Ignora erros de imagens não-padrão
                }

                if (empty($text) && empty($pageImagesHtml)) continue;

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

                if (!empty($pageContentHtml) || !empty($pageImagesHtml)) {
                    $html .= '<div class="pdf-page-card bg-white border border-slate-300 rounded-[2px] paper-shadow p-10 mb-10 relative select-text" data-page="' . $pageNum . '" style="width: 210mm; min-height: 297mm; box-sizing: border-box; margin-left: auto; margin-right: auto;">';
                    $html .= '<div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-6 select-none">';
                    $html .= '<span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 bg-slate-900 text-white rounded">📄 Página ' . $pageNum . ' de ' . $totalPages . ' (PDF Original)</span>';
                    $html .= '<span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Texto & Imagens Preservados</span>';
                    $html .= '</div>';
                    $html .= '<div class="pdf-page-body">' . $pageContentHtml . $pageImagesHtml . '</div>';
                    $html .= '</div>';
                }
            }

            if (empty($html)) {
                return '<p class="p-6 text-slate-500 italic">O arquivo PDF não possui texto nem imagens extraíveis.</p>';
            }

            return $html;

        } catch (\Throwable $e) {
            return '<p class="p-6 text-rose-500 font-bold">Falha ao extrair texto e imagens do PDF: ' . e($e->getMessage()) . '</p>';
        }
    }
}
