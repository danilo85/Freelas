<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EditorialRevision extends Model
{
    protected $fillable = [
        'user_id',
        'revisor_id',
        'client_id',
        'project_id',
        'title',
        'description',
        'share_token',
        'status',
        'deadline_at',
        'password',
        'storage_disk',
        'is_active',
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function revisor()
    {
        return $this->belongsTo(User::class, 'revisor_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function files()
    {
        return $this->hasMany(EditorialRevisionFile::class);
    }

    public function corrections()
    {
        return $this->hasMany(EditorialRevisionCorrection::class);
    }

    public function glossaries()
    {
        return $this->hasMany(EditorialRevisionGlossary::class);
    }
}
