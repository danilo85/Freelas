<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EditorialRevisionGlossary extends Model
{
    protected $fillable = [
        'editorial_revision_id',
        'correct_term',
        'incorrect_terms',
        'description',
    ];

    public function editorialRevision()
    {
        return $this->belongsTo(EditorialRevision::class);
    }
}
