<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevisionAnnotation extends Model
{
    protected $fillable = [
        'revision_file_id',
        'page_number',
        'drawing_data',
        'comment',
        'status',
        'author_id',
        'attachment_path',
    ];

    public function revisionFile()
    {
        return $this->belongsTo(RevisionFile::class);
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }
}
