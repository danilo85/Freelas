<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileShare extends Model
{
    protected $fillable = [
        'user_id',
        'share_token',
        'title',
        'description',
        'expires_at',
        'download_limit',
        'download_count',
        'view_count',
        'password',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(FileShareItem::class);
    }
}
