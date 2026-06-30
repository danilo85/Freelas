<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectRevision extends Model
{
    protected $fillable = [
        'user_id',
        'author_id',
        'project_id',
        'title',
        'subtitle',
        'share_token',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function rounds()
    {
        return $this->hasMany(RevisionRound::class);
    }
}
