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
     * unindo quebras de linha no meio de parágrafos, removendo tags vetoriais <>
     * e gerando um HTML limpo, estruturado e fluido.
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
                $rawText = $page->getText();

                // 1. SANITIZAÇÃO UTF-8 SEGURA (EVITA NULL EM REGEX)
                $cleanText = @iconv('UTF-8', 'UTF-8//IGNORE', $rawText);
                if (empty($cleanText)) {
                    $cleanText = $rawText;
                }

                // 2. LIMPEZA DE TAGS VETORIAIS DE KERNING (ex: M<>e<>mó<>r<>i<>a -> Memória)
                $cleanText = str_replace('<>', '', $cleanText);

                // 3. TRATAMENTO DE EMOJIS VETORIAIS DO PDF ( -> 🧠)
                $cleanText = str_replace("\xEF\xBF\xBD", '🧠 ', $cleanText);
                $cleanText = str_replace('', '🧠 ', $cleanText);
                $cleanText = trim($cleanText);

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

                            $imgInfo = @getimagesizefromstring($content);
                            if (!$imgInfo || empty($imgInfo['mime'])) continue;

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
                    // Ignora erros individuais de extração
                }

                if (empty($cleanText) && empty($pageImagesHtml)) continue;

                // ALGORITMO INTELIGENTE DE REFAZIMENTO DE PARÁGRAFOS (UN-WRAPPING):
                $rawLines = explode("\n", $cleanText);
                $stitchedLines = [];
                $currentBuffer = '';

                foreach ($rawLines as $l) {
                    $trimmed = trim($l);
                    if ($trimmed === '') {
                        if ($currentBuffer !== '') {
                            $stitchedLines[] = $currentBuffer;
                            $currentBuffer = '';
                        }
                        continue;
                    }

                    // Identifica se a linha é título, cartão de cor ou marcador
                    $isHeaderOrCard = preg_match('/^(?:[•\-\*]|\b[a-z0-9]+\))\s+/i', $trimmed) ||
                                      preg_match('/^(?:Vermelho|Azul|Verde|Amarelo|Laranja|Roxo|Rosa|Preto|Branco|Marrom|CARTA\s*\d+)/i', $trimmed) ||
                                      (mb_strlen($trimmed) < 75 && preg_match('/^(?:Objetivos|Materiais|Sessão|Capítulo|Introdução|Conclusão|Instruções|Modelo|Dinâmica|Treino|Nível):/i', $trimmed)) ||
                                      (mb_strlen($trimmed) < 70 && mb_strtoupper($trimmed) === $trimmed && preg_match('/[A-Z]/', $trimmed));

                    if ($isHeaderOrCard) {
                        if ($currentBuffer !== '') {
                            $stitchedLines[] = $currentBuffer;
                            $currentBuffer = '';
                        }
                        $stitchedLines[] = $trimmed;
                    } else {
                        if ($currentBuffer === '') {
                            $currentBuffer = $trimmed;
                        } else {
                            $lastChar = mb_substr($currentBuffer, -1);
                            if (in_array($lastChar, ['.', '!', '?', ':', ';'])) {
                                $stitchedLines[] = $currentBuffer;
                                $currentBuffer = $trimmed;
                            } else {
                                $currentBuffer .= ' ' . $trimmed;
                            }
                        }
                    }
                }
                if ($currentBuffer !== '') {
                    $stitchedLines[] = $currentBuffer;
                }

                $pageContentHtml = '';
                $inList = false;

                foreach ($stitchedLines as $line) {
                    $cleanLine = trim($line);
                    if ($cleanLine === '') continue;

                    // Formata Cartões de Cores e Desafios de Vetores PDF em Cards Elegantes
                    if (preg_match('/^(?:Vermelho|Azul|Verde|Amarelo|Laranja|Roxo|Rosa|Preto|Branco|Marrom)\b/i', $cleanLine)) {
                        $pageContentHtml .= '<div class="my-3 p-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 font-bold text-sm flex items-center gap-2 shadow-xs">' . e($cleanLine) . '</div>';
                        continue;
                    }

                    if (preg_match('/^CARTA\s*\d+/i', $cleanLine)) {
                        $pageContentHtml .= '<h4 class="font-outfit font-black text-slate-900 text-base mt-5 mb-2 pb-1 border-b border-slate-200 flex items-center gap-2">🃏 ' . e($cleanLine) . '</h4>';
                        continue;
                    }

                    // Detecta tópicos de lista
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

                    // Títulos principais (Banner azul no original)
                    if (mb_strlen($cleanLine) < 90 && (preg_match('/Memória Estratégica|Versão Cores|Instruções|Modelo dos Cartões/i', $cleanLine) || (mb_strtoupper($cleanLine) === $cleanLine && preg_match('/[A-Z]/', $cleanLine)))) {
                        $pageContentHtml .= '<div class="bg-gradient-to-r from-blue-600 to-sky-600 text-white font-outfit p-5 rounded-xl my-4 shadow-md text-center">';
                        $pageContentHtml .= '<h3 class="font-black text-xl tracking-tight uppercase mb-1">' . e($cleanLine) . '</h3>';
                        $pageContentHtml .= '</div>';
                    } elseif (preg_match('/^(?:Objetivos|Materiais|Sessão|Capítulo|Introdução|Conclusão|Nível\s*\d+):/i', $cleanLine)) {
                        $pageContentHtml .= '<h4 class="font-bold text-slate-800 text-base mt-4 mb-2 bg-blue-50/80 px-3 py-1.5 rounded border-l-4 border-blue-500">' . e($cleanLine) . '</h4>';
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
                    $html .= '<span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Texto Flutuante & Vetores Organizados</span>';
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
