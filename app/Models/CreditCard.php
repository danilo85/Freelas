<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'card_name',
        'bank_name',
        'limit',
        'closing_day',
        'due_day',
        'flag',
        'last_four_digits',
        'observations',
    ];

    protected $casts = [
        'limit' => 'decimal:2',
        'closing_day' => 'integer',
        'due_day' => 'integer',
    ];

    /**
     * Relação com o usuário proprietário do cartão.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relação com as transações realizadas neste cartão.
     */
    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Retorna a estilização visual baseada no banco e na bandeira.
     */
    public function getBrandStyleAttribute(): array
    {
        $name = mb_strtolower($this->bank_name);
        
        $style = [
            'bg' => 'from-slate-700 to-slate-900',
            'text' => 'text-white',
            'badge' => 'bg-slate-800/40 text-slate-300',
            'icon' => 'Banco',
        ];

        if (str_contains($name, 'itau') || str_contains($name, 'itaú')) {
            $style = [
                'bg' => 'from-orange-500 to-amber-600',
                'text' => 'text-white',
                'badge' => 'bg-blue-900/40 text-blue-100',
                'icon' => 'Itaú',
            ];
        } elseif (str_contains($name, 'nubank') || str_contains($name, 'roxo')) {
            $style = [
                'bg' => 'from-purple-800 to-indigo-950',
                'text' => 'text-white',
                'badge' => 'bg-purple-900/40 text-purple-100',
                'icon' => 'Nu',
            ];
        } elseif (str_contains($name, 'bradesco')) {
            $style = [
                'bg' => 'from-red-600 to-rose-800',
                'text' => 'text-white',
                'badge' => 'bg-red-900/40 text-red-100',
                'icon' => 'Bradesco',
            ];
        } elseif (str_contains($name, 'santander')) {
            $style = [
                'bg' => 'from-red-700 to-red-900',
                'text' => 'text-white',
                'badge' => 'bg-red-950/40 text-red-100',
                'icon' => 'Santander',
            ];
        } elseif (str_contains($name, 'caixa')) {
            $style = [
                'bg' => 'from-blue-700 to-sky-900',
                'text' => 'text-white',
                'badge' => 'bg-blue-950/40 text-blue-100',
                'icon' => 'Caixa',
            ];
        } elseif (str_contains($name, 'banco do brasil') || str_contains($name, 'bb')) {
            $style = [
                'bg' => 'from-yellow-500 via-amber-600 to-blue-800',
                'text' => 'text-white',
                'badge' => 'bg-blue-950/40 text-yellow-100',
                'icon' => 'BB',
            ];
        } elseif (str_contains($name, 'inter')) {
            $style = [
                'bg' => 'from-orange-500 to-orange-700',
                'text' => 'text-white',
                'badge' => 'bg-orange-950/40 text-orange-100',
                'icon' => 'Inter',
            ];
        } elseif (str_contains($name, 'c6')) {
            $style = [
                'bg' => 'from-neutral-800 to-zinc-950',
                'text' => 'text-white',
                'badge' => 'bg-zinc-800/40 text-zinc-300',
                'icon' => 'C6 Bank',
            ];
        } elseif (str_contains($name, 'neon')) {
            $style = [
                'bg' => 'from-cyan-400 to-blue-600',
                'text' => 'text-white',
                'badge' => 'bg-blue-900/40 text-cyan-100',
                'icon' => 'Neon',
            ];
        } elseif (str_contains($name, 'stone')) {
            $style = [
                'bg' => 'from-emerald-600 to-green-800',
                'text' => 'text-white',
                'badge' => 'bg-emerald-950/40 text-emerald-100',
                'icon' => 'Stone',
            ];
        } elseif (str_contains($name, 'pagbank') || str_contains($name, 'pagseguro')) {
            $style = [
                'bg' => 'from-lime-600 to-emerald-700',
                'text' => 'text-white',
                'badge' => 'bg-emerald-950/40 text-lime-100',
                'icon' => 'PagBank',
            ];
        } elseif (str_contains($name, 'mercado pago')) {
            $style = [
                'bg' => 'from-sky-400 to-blue-600',
                'text' => 'text-white',
                'badge' => 'bg-blue-950/40 text-sky-100',
                'icon' => 'Mercado Pago',
            ];
        }

        // Bandeira
        $flag = strtolower($this->flag ?? 'outros');
        $style['flag_name'] = ucfirst($flag);
        
        switch ($flag) {
            case 'visa':
                $style['flag_badge'] = 'bg-blue-600 text-white';
                $style['flag_label'] = 'VISA';
                break;
            case 'mastercard':
                $style['flag_badge'] = 'bg-orange-500 text-white';
                $style['flag_label'] = 'Mastercard';
                break;
            case 'elo':
                $style['flag_badge'] = 'bg-yellow-500 text-slate-900';
                $style['flag_label'] = 'Elo';
                break;
            case 'amex':
                $style['flag_badge'] = 'bg-cyan-600 text-white';
                $style['flag_label'] = 'Amex';
                break;
            case 'hipercard':
                $style['flag_badge'] = 'bg-red-600 text-white';
                $style['flag_label'] = 'Hipercard';
                break;
            default:
                $style['flag_badge'] = 'bg-slate-500 text-white';
                $style['flag_label'] = 'Card';
                break;
        }

        return $style;
    }
}
