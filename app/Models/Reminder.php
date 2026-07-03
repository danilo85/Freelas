<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'color',
        'is_pinned',
        'is_archived',
        'remind_at',
        'type',
        'items',
        'image_path',
        'sort_order'
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_archived' => 'boolean',
        'remind_at' => 'datetime',
        'items' => 'array',
        'sort_order' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
