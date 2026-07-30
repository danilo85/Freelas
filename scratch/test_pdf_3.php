<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$file = App\Models\EditorialRevisionFile::find(3);
$filePathOnDisk = storage_path('app/public/' . $file->file_path);

$parser = new \Smalot\PdfParser\Parser();
$pdf = $parser->parseFile($filePathOnDisk);
$pages = $pdf->getPages();

foreach ($pages as $i => $page) {
    echo "--- PAGE " . ($i + 1) . " ---\n";
    $text = $page->getText();
    echo "Raw Length: " . strlen($text) . "\n";
    echo "Preview: " . substr($text, 0, 500) . "\n";
}
