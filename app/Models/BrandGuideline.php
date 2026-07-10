<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrandGuideline extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'brand_name',
        'logo_primary',
        'logo_secondary',
        'logo_symbol',
        'logo_description',
        'logo_horizontal_desc',
        'logo_vertical_desc',
        'logo_symbol_desc',
        'color_palette',
        'typography',
        'social_media',
        'stationery',
        'share_token',
        'is_active',
        'final_package',
    ];

    protected $casts = [
        'color_palette' => 'array',
        'typography' => 'array',
        'social_media' => 'array',
        'stationery' => 'array',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
