<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\EditorialRevisionFile;
use App\Services\PdfToHtmlConverter;

$files = EditorialRevisionFile::all();

foreach ($files as $file) {
    if ($file->file_type === 'pdf') {
        $html = PdfToHtmlConverter::convertToHtml($file->file_path, 'public');
        $file->extracted_text = $html;
        $file->save();
        echo "Cleaned PDF File ID {$file->id}: Length " . strlen($html) . "\n";
    } else {
        $text = $file->extracted_text;
        if ($text && (str_contains($text, '-word-word') || str_contains($text, 'border - purple') || str_contains($text, 'mark=""'))) {
            $clean = preg_replace('/-word-word[^>]*>/i', '', $text);
            $clean = preg_replace('/\s*mark=""\s*bg\s*-\s*purple\s*-\s*[0-9]+=""\s*text\s*-\s*purple\s*-\s*[0-9]+=""\s*font\s*-\s*bold=""\s*px\s*-\s*1\.5=""\s*py\s*-\s*0\.5=""\s*rounded=""\s*border=""\s*border\s*-\s*purple\s*-\s*[0-9]+=""\s*inline\s*-\s*block[^>]*>/i', '', $clean);
            $clean = preg_replace('/\s*tag\s*bg\s*-\s*purple\s*-\s*[0-9]+\s*text\s*-\s*purple\s*-\s*[0-9]+\s*border\s*border\s*-\s*purple\s*-\s*[0-9]+\s*font\s*-\s*bold\s*px\s*-\s*1\.5\s*py\s*-\s*0\.5\s*rounded\s*shadow\s*-\s*xs\s*inline\s*-\s*block[^>]*>/i', '', $clean);
            $clean = preg_replace('/\s*mark=""/i', '', $clean);
            $file->extracted_text = $clean;
            $file->save();
            echo "Cleaned Corrupted Word File ID {$file->id}\n";
        }
    }
}
