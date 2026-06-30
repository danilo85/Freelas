<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevisionRound extends Model
{
    protected $fillable = [
        'project_revision_id',
        'round_number',
        'description',
        'status',
    ];

    public function projectRevision()
    {
        return $this->belongsTo(ProjectRevision::class);
    }

    public function files()
    {
        return $this->hasMany(RevisionFile::class);
    }
}
