<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'payment_id',
        'description',
        'category_id',
        'bank_account_id',
        'credit_card_id',
        'type', // 'entrada', 'saida'
        'amount',
        'due_date',
        'paid_at',
        'status', // 'pendente', 'pago'
        'attachment_path',
        'classification', // 'PF', 'PJ'
        'group_code',
        'installment_number',
        'total_installments',
        'recurrence',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'date',
        'amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class, 'category_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function creditCard(): BelongsTo
    {
        return $this->belongsTo(CreditCard::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Hooks para manter sincronizado com a tabela de pagamentos.
     */
    protected static function booted()
    {
        static::updated(function ($transaction) {
            if ($transaction->payment_id && $transaction->isDirty(['amount', 'paid_at', 'bank_account_id'])) {
                $payment = \App\Models\Payment::find($transaction->payment_id);
                if ($payment) {
                    $payment->updateQuietly([
                        'amount' => $transaction->amount,
                        'paid_at' => $transaction->paid_at,
                        'bank_account_id' => $transaction->bank_account_id,
                    ]);
                }
            }
        });

        static::deleted(function ($transaction) {
            if ($transaction->payment_id) {
                $payment = \App\Models\Payment::find($transaction->payment_id);
                if ($payment) {
                    $payment->delete();
                }
            }
        });
    }
}
