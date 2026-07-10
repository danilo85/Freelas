<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'client_id',
        'status',
        'total_value',
        'initial_payment_percent',
        'term',
        'budget_date',
        'expiration_date',
        'additional_info',
        'kanban_column_id',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function kanbanColumn(): BelongsTo
    {
        return $this->belongsTo(KanbanColumn::class, 'kanban_column_id');
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Relacionamento muitos-para-muitos com Autores.
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ProjectHistory::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ProjectAttachment::class);
    }

    public function revisions()
    {
        return $this->hasMany(ProjectRevision::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function relatedPayments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'payment_related_projects');
    }

    public function getRemainingBalanceAttribute(): float
    {
        $paidSum = $this->payments->sum('amount');
        return max(0.00, (float) $this->total_value - (float) $paidSum);
    }

    protected static function booted()
    {
        static::created(function ($project) {
            $project->histories()->create([
                'user_id' => auth()->id(),
                'action' => 'criado',
                'title' => $project->title,
                'description' => $project->description,
                'total_value' => $project->total_value,
                'initial_payment_percent' => $project->initial_payment_percent,
                'term' => $project->term,
                'budget_date' => $project->budget_date,
                'expiration_date' => $project->expiration_date,
                'additional_info' => $project->additional_info,
                'status' => $project->status,
            ]);
        });

        static::updated(function ($project) {
            $action = 'atualizado';
            if ($project->isDirty('status')) {
                if ($project->status === 'aprovado') {
                    $action = 'aprovado';
                } elseif ($project->status === 'rejeitado') {
                    $action = 'rejeitado';
                }
            }

            $project->histories()->create([
                'user_id' => auth()->id(),
                'action' => $action,
                'title' => $project->title,
                'description' => $project->description,
                'total_value' => $project->total_value,
                'initial_payment_percent' => $project->initial_payment_percent,
                'term' => $project->term,
                'budget_date' => $project->budget_date,
                'expiration_date' => $project->expiration_date,
                'additional_info' => $project->additional_info,
                'status' => $project->status,
            ]);
        });
    }
}
