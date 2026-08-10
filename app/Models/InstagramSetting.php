<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstagramSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'logo_path',
        'arrow_path',
        'saved_themes',
    ];

    protected $casts = [
        'saved_themes' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
