<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocxToHtmlConverter
{
    /**
     * Converte o arquivo .docx em HTML rico preservando formatação, fontes, tamanhos, cores, imagens e parágrafos.
     */
    public static function convertToHtml(string $docxPath, string $disk = 'public'): string
    {
        try {
            $storageDisk = Storage::disk($disk);
            if (!$storageDisk->exists($docxPath)) {
                return '<p class="text-slate-500 italic">Arquivo Word não encontrado.</p>';
            }

            $absolutePath = $storageDisk->path($docxPath);
            if (!file_exists($absolutePath)) {
                return '<p class="text-slate-500 italic">Caminho físico do arquivo inválido.</p>';
            }

            $zip = new \ZipArchive();
            if ($zip->open($absolutePath) !== true) {
                return '<p class="text-slate-500 italic">Erro ao abrir arquivo .docx.</p>';
            }

            // Extrai imagens para pasta de mídias pública
            $mediaMap = [];
            $mediaDirName = 'editorial_media/' . pathinfo($docxPath, PATHINFO_FILENAME);
            
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (str_starts_with($filename, 'word/media/')) {
                    $imageContent = $zip->getFromIndex($i);
                    $imageBasename = basename($filename);
                    $mediaPath = $mediaDirName . '/' . $imageBasename;
                    
                    Storage::disk('public')->put($mediaPath, $imageContent);
                    $publicUrl = Storage::disk('public')->url($mediaPath);
                    $mediaMap[$imageBasename] = $publicUrl;
                }
            }

            // Lê o XML principal do documento
            $xmlData = $zip->getFromName('word/document.xml');
            $relationshipsXml = $zip->getFromName('word/_rels/document.xml.rels');
            $zip->close();

            if (!$xmlData) {
                return '<p class="text-slate-500 italic">XML do documento inválido.</p>';
            }

            // Mapeia IDs de relacionamentos (rId -> arquivo de mídia)
            $relMap = [];
            if ($relationshipsXml) {
                $relsDoc = new \DOMDocument();
                @$relsDoc->loadXML($relationshipsXml);
                foreach ($relsDoc->getElementsByTagName('Relationship') as $rel) {
                    $id = $rel->getAttribute('Id');
                    $target = $rel->getAttribute('Target');
                    if (str_starts_with($target, 'media/')) {
                        $imgName = basename($target);
                        if (isset($mediaMap[$imgName])) {
                            $relMap[$id] = $mediaMap[$imgName];
                        }
                    }
                }
            }

            // Processa o DOM do document.xml
            $dom = new \DOMDocument();
            @$dom->loadXML($xmlData, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);

            $body = $dom->getElementsByTagName('body')->item(0);
            if (!$body) {
                return '<p class="text-slate-500 italic">Corpo do documento não encontrado.</p>';
            }

            $html = '';

            foreach ($body->childNodes as $node) {
                if ($node->nodeName === 'w:p') {
                    $html .= self::parseParagraph($node, $relMap);
                } elseif ($node->nodeName === 'w:tbl') {
                    $html .= self::parseTable($node, $relMap);
                }
            }

            return $html ?: '<p class="text-slate-400 italic">Documento em branco.</p>';
        } catch (\Throwable $e) {
            return '<p class="text-rose-500 italic">Erro ao converter Word em HTML: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }

    private static function parseParagraph(\DOMNode $node, array $relMap): string
    {
        $styleAttr = '';
        $align = '';

        // Procura propriedades do parágrafo
        foreach ($node->childNodes as $child) {
            if ($child->nodeName === 'w:pPr') {
                foreach ($child->childNodes as $pPrChild) {
                    if ($pPrChild->nodeName === 'w:jc') {
                        $val = $pPrChild->getAttribute('w:val');
                        if ($val === 'center') $align = 'text-align: center;';
                        elseif ($val === 'right') $align = 'text-align: right;';
                        elseif ($val === 'both') $align = 'text-align: justify;';
                    }
                }
            }
        }

        $pContent = '';

        foreach ($node->childNodes as $child) {
            if ($child->nodeName === 'w:r') {
                $pContent .= self::parseRun($child, $relMap);
            }
        }

        if (trim(strip_tags($pContent)) === '' && !str_contains($pContent, '<img')) {
            return '<p class="mb-4">&nbsp;</p>';
        }

        $style = $align ? ' style="' . $align . '"' : '';
        return '<p class="mb-4 leading-relaxed text-slate-800 font-serif text-base"' . $style . '>' . $pContent . '</p>';
    }

    private static function parseRun(\DOMNode $node, array $relMap): string
    {
        $isBold = false;
        $isItalic = false;
        $isUnderline = false;
        $fontSize = null;
        $color = null;
        $fontFamily = null;

        $runContent = '';

        foreach ($node->childNodes as $child) {
            if ($child->nodeName === 'w:rPr') {
                foreach ($child->childNodes as $rPrChild) {
                    if ($rPrChild->nodeName === 'w:b') $isBold = true;
                    if ($rPrChild->nodeName === 'w:i') $isItalic = true;
                    if ($rPrChild->nodeName === 'w:u') $isUnderline = true;
                    if ($rPrChild->nodeName === 'w:sz') {
                        $val = (int)$rPrChild->getAttribute('w:val');
                        if ($val > 0) $fontSize = ($val / 2) . 'pt';
                    }
                    if ($rPrChild->nodeName === 'w:color') {
                        $val = $rPrChild->getAttribute('w:val');
                        if ($val && $val !== 'auto') $color = '#' . $val;
                    }
                    if ($rPrChild->nodeName === 'w:rFonts') {
                        $fontFamily = $rPrChild->getAttribute('w:ascii') ?: $rPrChild->getAttribute('w:hAnsi');
                    }
                }
            } elseif ($child->nodeName === 'w:t') {
                $runContent .= htmlspecialchars($child->nodeValue);
            } elseif ($child->nodeName === 'w:br') {
                $runContent .= '<br>';
            } elseif ($child->nodeName === 'w:drawing') {
                // Procura imagens incorporadas
                $blips = $child->getElementsByTagName('blip');
                foreach ($blips as $blip) {
                    $embedId = $blip->getAttribute('r:embed');
                    if (isset($relMap[$embedId])) {
                        $imgUrl = $relMap[$embedId];
                        $runContent .= '<img src="' . $imgUrl . '" class="my-4 max-w-full rounded shadow-sm mx-auto block">';
                    }
                }
            }
        }

        if ($runContent === '') return '';

        $styles = [];
        if ($fontSize) $styles[] = 'font-size: ' . $fontSize;
        if ($color) $styles[] = 'color: ' . $color;
        if ($fontFamily) $styles[] = 'font-family: "' . $fontFamily . '", serif';

        $inlineStyle = !empty($styles) ? ' style="' . implode('; ', $styles) . '"' : '';

        if ($inlineStyle) {
            $runContent = '<span' . $inlineStyle . '>' . $runContent . '</span>';
        }
        if ($isBold) $runContent = '<strong>' . $runContent . '</strong>';
        if ($isItalic) $runContent = '<em>' . $runContent . '</em>';
        if ($isUnderline) $runContent = '<u>' . $runContent . '</u>';

        return $runContent;
    }

    private static function parseTable(\DOMNode $node, array $relMap): string
    {
        $html = '<table class="w-full border-collapse border border-slate-300 my-4 text-xs font-serif">';
        foreach ($node->getElementsByTagName('tr') as $tr) {
            $html .= '<tr>';
            foreach ($tr->getElementsByTagName('tc') as $tc) {
                $tcContent = '';
                foreach ($tc->getElementsByTagName('p') as $p) {
                    $tcContent .= self::parseParagraph($p, $relMap);
                }
                $html .= '<td class="border border-slate-300 p-2.5">' . $tcContent . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;
    }
}
