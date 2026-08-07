<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortfolioItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'portfolio_category_id',
        'client_id',
        'project_id',
        'title',
        'slug',
        'description',
        'technologies',
        'redirect_url',
        'thumb_path',
        'gallery_spacing',
        'status',
        'is_featured',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    /**
     * Relacionamento: o trabalho pertence a um usuário (tenancy).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento: o trabalho pertence a uma categoria de portfólio.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PortfolioCategory::class, 'portfolio_category_id');
    }

    /**
     * Relacionamento: o trabalho opcionalmente pertence a um cliente.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relacionamento: o trabalho opcionalmente está vinculado a um projeto de origem.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relacionamento muitos-para-muitos: autores vinculados a este trabalho.
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'author_portfolio_item');
    }

    /**
     * Relacionamento: galeria de imagens vinculada a este trabalho.
     */
    public function images(): HasMany
    {
        return $this->hasMany(PortfolioImage::class)->orderBy('order')->orderBy('id');
    }
}
