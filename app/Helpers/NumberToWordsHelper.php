<?php

namespace App\Helpers;

class NumberToWordsHelper
{
    public static function converter($amount)
    {
        $amount = (float) $amount;
        if ($amount == 0) {
            return 'zero reais';
        }

        $singular = ['centavo', 'real', 'mil', 'milhão', 'bilhão', 'trilhão'];
        $plural = ['centavos', 'reais', 'mil', 'milhões', 'bilhões', 'trilhões'];

        $c = ['', 'cem', 'duzentos', 'trezentos', 'quatrocentos', 'quinhentos', 'seiscentos', 'setecentos', 'oitocentos', 'novecentos'];
        $d = ['', 'dez', 'vinte', 'trinta', 'quarenta', 'cinquenta', 'sessenta', 'setenta', 'oitenta', 'noventa'];
        $d10 = ['dez', 'onze', 'doze', 'treze', 'quatorze', 'quinze', 'dezesseis', 'dezessete', 'dezoito', 'dezenove'];
        $u = ['', 'um', 'dois', 'três', 'quatro', 'cinco', 'seis', 'sete', 'oito', 'nove'];

        // Split into integer and decimal
        $formatted = number_format($amount, 2, '.', '');
        list($integerPart, $decimalPart) = explode('.', $formatted);

        // Parse integer part
        $integerWords = self::parsePart($integerPart, $singular, $plural, $c, $d, $d10, $u, true);
        
        // Parse decimal part
        $decimalWords = '';
        if ((int)$decimalPart > 0) {
            $decimalWords = self::parsePart($decimalPart, $singular, $plural, $c, $d, $d10, $u, false);
        }

        if ($integerWords && $decimalWords) {
            return $integerWords . ' e ' . $decimalWords;
        }

        return $integerWords ?: $decimalWords;
    }

    private static function parsePart($numberStr, $singular, $plural, $c, $d, $d10, $u, $isInteger = true)
    {
        $num = (int)$numberStr;
        if ($num == 0) return '';

        if (!$isInteger) {
            // For cents
            $words = self::convertThreeDigits($num, $c, $d, $d10, $u);
            $suffix = $num == 1 ? $singular[0] : $plural[0];
            return $words . ' ' . $suffix;
        }

        // For integer part (reais), group by 3 digits from right to left
        $chunks = [];
        $len = strlen($numberStr);
        for ($i = $len; $i > 0; $i -= 3) {
            $start = max(0, $i - 3);
            $length = $i - $start;
            $chunks[] = (int)substr($numberStr, $start, $length);
        }

        $wordsArray = [];
        foreach ($chunks as $index => $value) {
            if ($value == 0) continue;

            $chunkWords = self::convertThreeDigits($value, $c, $d, $d10, $u);

            // Handle special singular case for "mil"
            if ($index == 1 && $value == 1) {
                $chunkWords = '';
            }

            $suffix = '';
            if ($index == 0) {
                // Reais
                // Handled at the end to cover the entire integer amount
            } else {
                $suffix = ' ' . ($value == 1 ? $singular[$index + 1] : $plural[$index + 1]);
            }

            $wordsArray[] = trim($chunkWords . $suffix);
        }

        // Reverse to restore original order (from highest denomination to lowest)
        $wordsArray = array_reverse($wordsArray);

        // Join chunks
        $result = '';
        $count = count($wordsArray);
        for ($i = 0; $i < $count; $i++) {
            if ($i > 0) {
                $lastChunk = $chunks[0];
                if ($i == $count - 1 && ($lastChunk < 100 || $lastChunk % 100 == 0)) {
                    $result .= ' e ';
                } else {
                    $result .= ', ';
                }
            }
            $result .= $wordsArray[$i];
        }

        // Add "reais" or "real"
        $currencySuffix = $num == 1 ? $singular[1] : $plural[1];
        
        // Special case: if value is ending in million/billion with exact zeros (e.g. 1.000.000,00) we say "de reais"
        if ($num >= 1000000 && ($num % 1000000 == 0)) {
            $currencySuffix = 'de ' . $plural[1];
        }

        return $result . ' ' . $currencySuffix;
    }

    private static function convertThreeDigits($num, $c, $d, $d10, $u)
    {
        if ($num == 100) return 'cem';

        $centena = (int)($num / 100);
        $resto = $num % 100;
        $dezena = (int)($resto / 10);
        $unidade = $resto % 10;

        $words = '';

        if ($centena > 0) {
            $words = $c[$centena];
            if ($resto > 0) {
                $words .= ' e ';
            }
        }

        if ($resto > 0) {
            if ($resto >= 10 && $resto < 20) {
                $words .= $d10[$resto - 10];
            } else {
                if ($dezena > 0) {
                    $words .= $d[$dezena];
                    if ($unidade > 0) {
                        $words .= ' e ';
                    }
                }
                if ($unidade > 0) {
                    $words .= $u[$unidade];
                }
            }
        }

        return $words;
    }
}
