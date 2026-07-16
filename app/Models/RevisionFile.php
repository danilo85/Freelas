<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevisionFile extends Model
{
    protected $fillable = [
        'revision_round_id',
        'folder_name',
        'filename',
        'file_path',
        'file_type',
        'file_size',
    ];

    public function revisionRound()
    {
        return $this->belongsTo(RevisionRound::class);
    }

    public function annotations()
    {
        return $this->hasMany(RevisionAnnotation::class)->orderBy('page_number', 'asc')->orderBy('created_at', 'asc');
    }
}
