<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class PdfToDocxConverter
{
    /**
     * Converte um arquivo PDF em um documento Word (.docx) preservando layout, fontes e imagens.
     *
     * @param string $pdfPath Caminho absoluto ou relativo do PDF no disk
     * @param string $disk Nome do disk de armazenamento ('public', 'local', etc.)
     * @return string|null Caminho relativo do arquivo .docx gerado ou null em caso de falha
     */
    public static function convert(string $pdfPath, string $disk = 'public'): ?string
    {
        try {
            $storageDisk = Storage::disk($disk);
            if (!$storageDisk->exists($pdfPath)) {
                return null;
            }

            $absolutePdf = $storageDisk->path($pdfPath);
            $docxFilename = pathinfo($pdfPath, PATHINFO_FILENAME) . '_convertido.docx';
            $docxPath = pathinfo($pdfPath, PATHINFO_DIRNAME) . '/' . $docxFilename;
            $absoluteDocx = $storageDisk->path($docxPath);

            // Script inline em Python utilizando a biblioteca pdf2docx
            $pyScript = "import sys\nfrom pdf2docx import Converter\ntry:\n    cv = Converter(r'{$absolutePdf}')\n    cv.convert(r'{$absoluteDocx}', start=0, end=None)\n    cv.close()\n    print('CONVERTED_OK')\nexcept Exception as e:\n    print(f'ERROR: {e}')\n";

            $process = new Process(['python', '-c', $pyScript]);
            $process->setTimeout(180);
            $process->run();

            if ($process->isSuccessful() && file_exists($absoluteDocx)) {
                Log::info("PDF para Word convertido com sucesso: {$docxPath}");
                return $docxPath;
            }
        } catch (\Throwable $e) {
            Log::error("Erro na conversão PDF -> DOCX: " . $e->getMessage());
        }

        return null;
    }
}
