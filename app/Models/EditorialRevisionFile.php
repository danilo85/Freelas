<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EditorialRevisionFile extends Model
{
    protected $fillable = [
        'editorial_revision_id',
        'filename',
        'file_path',
        'file_size',
        'mime_type',
        'file_type',
        'version',
        'is_final',
    ];

    protected $casts = [
        'is_final' => 'boolean',
    ];

    public function editorialRevision()
    {
        return $this->belongsTo(EditorialRevision::class);
    }

    public function corrections()
    {
        return $this->hasMany(EditorialRevisionCorrection::class);
    }
}
