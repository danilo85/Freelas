<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EditorialRevisionCorrection extends Model
{
    protected $fillable = [
        'editorial_revision_id',
        'editorial_revision_file_id',
        'page_number',
        'original_text',
        'suggested_text',
        'justification',
        'category',
        'priority',
        'status',
        'source',
        'created_by',
    ];

    public function editorialRevision()
    {
        return $this->belongsTo(EditorialRevision::class);
    }

    public function file()
    {
        return $this->belongsTo(EditorialRevisionFile::class, 'editorial_revision_file_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments()
    {
        return $this->hasMany(EditorialRevisionComment::class);
    }
}
