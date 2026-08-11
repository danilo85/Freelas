<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstagramPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'instagram_account_id',
        'media_type',
        'media_path',
        'media_urls',
        'caption',
        'has_logo_overlay',
        'has_arrow_overlay',
        'status',
        'scheduled_at',
        'published_at',
        'instagram_media_id',
        'error_message',
    ];

    protected $casts = [
        'media_urls' => 'array',
        'has_logo_overlay' => 'boolean',
        'has_arrow_overlay' => 'boolean',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function instagramAccount()
    {
        return $this->belongsTo(InstagramAccount::class);
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
