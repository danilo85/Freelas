<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'site_title',
        'site_subtitle',
        'site_description',
        'about_title',
        'about_text',
        'skills',
        'contact_email',
        'contact_phone',
        'behance_url',
        'faq_items',
    ];

    protected $casts = [
        'faq_items' => 'array',
    ];

    /**
     * Relacionamento: as configurações pertencem a um usuário.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
