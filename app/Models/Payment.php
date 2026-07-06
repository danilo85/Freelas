<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'amount',
        'paid_at',
        'payment_method',
        'bank_account',
        'bank_account_id',
        'observations',
        'invoice_path',
    ];

    protected $casts = [
        'paid_at' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Relação com a conta bancária vinculada.
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Relação com o projeto principal.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relação com os projetos adicionais contemplados.
     */
    public function relatedProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'payment_related_projects');
    }

    /**
     * Relação com a transação correspondente (Faturamento).
     */
    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    /**
     * Hooks para manter sincronizado com a tabela de transações.
     */
    protected static function booted()
    {
        static::created(function ($payment) {
            $userId = $payment->project->client->user_id ?? auth()->id();
            $categoryId = \App\Models\TransactionCategory::where('name', 'Freelance / Projetos')->value('id');

            $payment->transaction()->create([
                'user_id' => $userId,
                'project_id' => $payment->project_id,
                'type' => 'entrada',
                'amount' => $payment->amount,
                'paid_at' => $payment->paid_at,
                'due_date' => $payment->paid_at,
                'status' => 'pago',
                'description' => 'Recebimento: ' . $payment->project->title,
                'bank_account_id' => $payment->bank_account_id,
                'category_id' => $categoryId,
                'classification' => 'PJ',
            ]);
        });

        static::updated(function ($payment) {
            $transaction = $payment->transaction;
            if ($transaction) {
                $transaction->updateQuietly([
                    'amount' => $payment->amount,
                    'paid_at' => $payment->paid_at,
                    'due_date' => $payment->paid_at,
                    'bank_account_id' => $payment->bank_account_id,
                ]);
            }
        });
    }
}
