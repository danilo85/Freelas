<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class PdfToHtmlConverter
{
    /**
     * Gara a inclusão do Autoloader das classes do Smalot PdfParser.
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
     * Extrai o texto completo de um arquivo PDF e o converte em HTML rico e editável para a Folha A4.
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
            $html = '';

            foreach ($pages as $index => $page) {
                $text = trim($page->getText());
                if (empty($text)) continue;

                $lines = explode("\n", $text);
                $pageHtml = '';

                foreach ($lines as $line) {
                    $cleanLine = trim($line);
                    if ($cleanLine === '') continue;

                    if (mb_strlen($cleanLine) < 60 && mb_strtoupper($cleanLine) === $cleanLine && preg_match('/[A-Z]/', $cleanLine)) {
                        $pageHtml .= '<h3 class="text-base font-bold text-slate-900 mt-6 mb-3 uppercase tracking-tight">' . e($cleanLine) . '</h3>';
                    } else {
                        $pageHtml .= '<p class="mb-3 leading-relaxed text-slate-900 font-serif text-base">' . e($cleanLine) . '</p>';
                    }
                }

                if (!empty($pageHtml)) {
                    $html .= '<div class="pdf-page-block mb-8 pb-4 border-b border-slate-200" data-page="' . ($index + 1) . '">';
                    $html .= '<span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-2">--- PÁGINA ' . ($index + 1) . ' DO PDF ---</span>';
                    $html .= $pageHtml;
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
