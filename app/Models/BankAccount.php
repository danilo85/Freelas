<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_name',
        'account_name',
        'account_type',
        'person_type',
        'agency',
        'account_number',
        'initial_balance',
        'observations',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
    ];

    /**
     * Relação com o usuário proprietário da conta.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relação com os pagamentos recebidos nesta conta.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Relação com as transações desta conta bancária.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Retorna estilos de branding, cores e apelido visual do banco selecionado.
     */
    public function getBrandStyleAttribute(): array
    {
        $name = mb_strtolower($this->bank_name);

        if (str_contains($name, 'itau') || str_contains($name, 'itaú')) {
            return [
                'bg' => 'from-orange-500 to-amber-600',
                'text' => 'text-white',
                'badge' => 'bg-blue-900/40 text-blue-100',
                'accent' => 'text-yellow-300',
                'icon' => 'Itaú',
            ];
        } elseif (str_contains($name, 'nubank') || str_contains($name, 'roxo')) {
            return [
                'bg' => 'from-purple-800 to-indigo-950',
                'text' => 'text-white',
                'badge' => 'bg-purple-900/40 text-purple-100',
                'accent' => 'text-fuchsia-400',
                'icon' => 'Nu',
            ];
        } elseif (str_contains($name, 'bradesco')) {
            return [
                'bg' => 'from-red-600 to-rose-800',
                'text' => 'text-white',
                'badge' => 'bg-red-900/40 text-red-100',
                'accent' => 'text-slate-200',
                'icon' => 'Bradesco',
            ];
        } elseif (str_contains($name, 'santander')) {
            return [
                'bg' => 'from-red-700 to-red-900',
                'text' => 'text-white',
                'badge' => 'bg-red-950/40 text-red-100',
                'accent' => 'text-slate-100',
                'icon' => 'Santander',
            ];
        } elseif (str_contains($name, 'caixa')) {
            return [
                'bg' => 'from-blue-700 to-sky-900',
                'text' => 'text-white',
                'badge' => 'bg-blue-950/40 text-blue-100',
                'accent' => 'text-orange-400',
                'icon' => 'Caixa',
            ];
        } elseif (str_contains($name, 'banco do brasil') || str_contains($name, 'bb')) {
            return [
                'bg' => 'from-yellow-500 via-amber-600 to-blue-800',
                'text' => 'text-white',
                'badge' => 'bg-blue-950/40 text-yellow-100',
                'accent' => 'text-yellow-300',
                'icon' => 'BB',
            ];
        } elseif (str_contains($name, 'inter')) {
            return [
                'bg' => 'from-orange-500 to-orange-700',
                'text' => 'text-white',
                'badge' => 'bg-orange-950/40 text-orange-100',
                'accent' => 'text-white',
                'icon' => 'Inter',
            ];
        } elseif (str_contains($name, 'c6')) {
            return [
                'bg' => 'from-neutral-800 to-zinc-950',
                'text' => 'text-white',
                'badge' => 'bg-zinc-800/40 text-zinc-300',
                'accent' => 'text-yellow-500',
                'icon' => 'C6 Bank',
            ];
        } elseif (str_contains($name, 'neon')) {
            return [
                'bg' => 'from-cyan-400 to-blue-600',
                'text' => 'text-white',
                'badge' => 'bg-blue-900/40 text-cyan-100',
                'accent' => 'text-white',
                'icon' => 'Neon',
            ];
        } elseif (str_contains($name, 'stone')) {
            return [
                'bg' => 'from-emerald-600 to-green-800',
                'text' => 'text-white',
                'badge' => 'bg-emerald-950/40 text-emerald-100',
                'accent' => 'text-emerald-300',
                'icon' => 'Stone',
            ];
        } elseif (str_contains($name, 'pagbank') || str_contains($name, 'pagseguro')) {
            return [
                'bg' => 'from-lime-600 to-emerald-700',
                'text' => 'text-white',
                'badge' => 'bg-emerald-950/40 text-lime-100',
                'accent' => 'text-yellow-300',
                'icon' => 'PagBank',
            ];
        } elseif (str_contains($name, 'mercado pago')) {
            return [
                'bg' => 'from-sky-400 to-blue-600',
                'text' => 'text-white',
                'badge' => 'bg-blue-950/40 text-sky-100',
                'accent' => 'text-white',
                'icon' => 'Mercado Pago',
            ];
        }

        // Custom / Outros
        return [
            'bg' => 'from-slate-700 to-slate-900',
            'text' => 'text-white',
            'badge' => 'bg-slate-800/40 text-slate-355',
            'accent' => 'text-emerald-400',
            'icon' => 'Banco',
        ];
    }
}
