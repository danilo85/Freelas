<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$r = App\Models\EditorialRevision::where('share_token', 'hvjE66FugqRd0Z1DCehmpuk8WIhZxIlp')->first();

if (!$r) {
    echo "REVISION NOT FOUND\n";
    exit;
}

echo "Revision ID: {$r->id} | Disk: {$r->storage_disk}\n";

foreach ($r->files as $f) {
    echo "File ID: {$f->id} | Name: {$f->filename} | Path: {$f->file_path} | Type: {$f->file_type}\n";
    if ($f->file_type === 'word') {
        $html = App\Services\DocxToHtmlConverter::convertToHtml($f->file_path, $r->storage_disk ?: 'public');
        echo "HTML Length: " . strlen($html) . "\n";
        echo "HTML Snippet: " . substr(strip_tags($html), 0, 100) . "\n";
    }
}
