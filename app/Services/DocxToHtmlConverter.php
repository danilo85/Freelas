<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class DocxToHtmlConverter
{
    /**
     * Converte o arquivo .docx em HTML rico preservando formatação, fontes, tamanhos, cores, imagens (base64) e parágrafos.
     */
    public static function convertToHtml(string $docxPath, string $disk = 'public'): string
    {
        try {
            $disksToTry = array_unique([$disk, 'public', 'local']);
            $absolutePath = null;

            foreach ($disksToTry as $d) {
                if ($d && Storage::disk($d)->exists($docxPath)) {
                    try {
                        $absolutePath = Storage::disk($d)->path($docxPath);
                        if (file_exists($absolutePath)) break;
                    } catch (\Throwable $e) {}
                }
            }

            if (!$absolutePath || !file_exists($absolutePath)) {
                $directPath = storage_path('app/public/' . $docxPath);
                if (file_exists($directPath)) {
                    $absolutePath = $directPath;
                }
            }

            if (!$absolutePath || !file_exists($absolutePath)) {
                return '<p class="text-slate-500 italic p-6">Arquivo Word não localizado no servidor.</p>';
            }

            $zip = new \ZipArchive();
            if ($zip->open($absolutePath) !== true) {
                return '<p class="text-slate-500 italic p-6">Erro ao abrir arquivo .docx.</p>';
            }

            // Mapeia todas as imagens em word/media/ diretamente em Data URI (Base64) de alta fidelidade
            $mediaMap = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (str_starts_with($filename, 'word/media/')) {
                    $imageContent = $zip->getFromIndex($i);
                    $imageBasename = basename($filename);
                    $ext = strtolower(pathinfo($imageBasename, PATHINFO_EXTENSION));
                    $mime = match($ext) {
                        'png' => 'image/png',
                        'jpg', 'jpeg' => 'image/jpeg',
                        'gif' => 'image/gif',
                        'svg' => 'image/svg+xml',
                        default => 'image/png'
                    };
                    $base64 = 'data:' . $mime . ';base64,' . base64_encode($imageContent);
                    $mediaMap[$imageBasename] = $base64;
                }
            }

            // Lê o XML principal do documento e os relacionamentos
            $xmlData = $zip->getFromName('word/document.xml');
            $relationshipsXml = $zip->getFromName('word/_rels/document.xml.rels');
            $zip->close();

            if (!$xmlData) {
                return '<p class="text-slate-500 italic p-6">XML do documento inválido.</p>';
            }

            // Mapeia IDs de relacionamentos (rId -> Base64 Data URI)
            $relMap = [];
            if ($relationshipsXml) {
                $relsDoc = new \DOMDocument();
                @$relsDoc->loadXML($relationshipsXml);
                foreach ($relsDoc->getElementsByTagName('Relationship') as $rel) {
                    $id = $rel->getAttribute('Id');
                    $target = $rel->getAttribute('Target');
                    $imgName = basename($target);
                    if (isset($mediaMap[$imgName])) {
                        $relMap[$id] = $mediaMap[$imgName];
                    }
                }
            }

            // Processa o DOM do document.xml
            $dom = new \DOMDocument();
            @$dom->loadXML($xmlData, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);

            $body = $dom->getElementsByTagName('body')->item(0);
            if (!$body) {
                return '<p class="text-slate-500 italic p-6">Corpo do documento não encontrado.</p>';
            }

            $html = '';

            foreach ($body->childNodes as $node) {
                if ($node->nodeName === 'w:p') {
                    $html .= self::parseParagraph($node, $relMap);
                } elseif ($node->nodeName === 'w:tbl') {
                    $html .= self::parseTable($node, $relMap);
                }
            }

            return $html ?: '<p class="text-slate-400 italic p-6">Documento em branco.</p>';
        } catch (\Throwable $e) {
            return '<p class="text-rose-500 italic p-6">Erro ao converter Word em HTML: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }

    private static function parseParagraph(\DOMNode $node, array $relMap): string
    {
        $align = '';

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
            return '<p class="mb-3">&nbsp;</p>';
        }

        $style = $align ? ' style="' . $align . '"' : '';
        return '<p class="mb-3 leading-relaxed text-slate-900 font-serif text-base"' . $style . '>' . $pContent . '</p>';
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
            } elseif ($child->nodeName === 'w:drawing' || $child->nodeName === 'w:pict') {
                // Procura imagens incorporadas no XML do Word
                $blips = $child->getElementsByTagName('blip');
                if ($blips->length > 0) {
                    foreach ($blips as $blip) {
                        $embedId = $blip->getAttribute('r:embed');
                        if (isset($relMap[$embedId])) {
                            $runContent .= '<img src="' . $relMap[$embedId] . '" class="my-4 max-w-full rounded shadow-sm mx-auto block">';
                        }
                    }
                } else {
                    $imagedatas = $child->getElementsByTagName('imagedata');
                    foreach ($imagedatas as $imgData) {
                        $embedId = $imgData->getAttribute('r:id');
                        if (isset($relMap[$embedId])) {
                            $runContent .= '<img src="' . $relMap[$embedId] . '" class="my-4 max-w-full rounded shadow-sm mx-auto block">';
                        }
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
