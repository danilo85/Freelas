<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'file_path',
        'code_snippet',
        'file_size',
        'mime_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
