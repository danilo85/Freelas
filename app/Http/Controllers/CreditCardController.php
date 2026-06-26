<?php

namespace App\Http\Controllers;

use App\Models\CreditCard;
use Illuminate\Http\Request;

class CreditCardController extends Controller
{
    /**
     * Exibe o formulário de cadastro de cartão.
     */
    public function create()
    {
        return view('credit_cards.create');
    }

    /**
     * Armazena um novo cartão de crédito para o usuário autenticado.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'card_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'custom_bank_name' => 'nullable|required_if:bank_name,Outro|string|max:255',
            'limit' => 'required|string',
            'closing_day' => 'required|integer|between:1,31',
            'due_day' => 'required|integer|between:1,31',
            'flag' => 'required|in:visa,mastercard,elo,amex,hipercard,outros',
            'last_four_digits' => 'nullable|string|size:4',
            'observations' => 'nullable|string',
        ]);

        $bankName = $validated['bank_name'];
        if ($bankName === 'Outro') {
            $bankName = $validated['custom_bank_name'];
        }

        // Limpeza de valor formatado em reais
        $cleanLimit = $validated['limit'];
        $cleanLimit = str_replace('R$', '', $cleanLimit);
        $cleanLimit = trim($cleanLimit);
        $cleanLimit = str_replace('.', '', $cleanLimit);
        $cleanLimit = str_replace(',', '.', $cleanLimit);
        $limit = (float) $cleanLimit;

        CreditCard::create([
            'user_id' => auth()->id(),
            'card_name' => $validated['card_name'],
            'bank_name' => $bankName,
            'limit' => $limit,
            'closing_day' => $validated['closing_day'],
            'due_day' => $validated['due_day'],
            'flag' => $validated['flag'],
            'last_four_digits' => $validated['last_four_digits'] ?? null,
            'observations' => $validated['observations'] ?? null,
        ]);

        return redirect()->route('bank-accounts.index', ['tab' => 'cards'])->with('success', 'Cartão de crédito cadastrado com sucesso!');
    }

    /**
     * Exibe o formulário de edição de um cartão.
     */
    public function edit(CreditCard $creditCard)
    {
        // Tenancy Check
        abort_if($creditCard->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        return view('credit_cards.edit', compact('creditCard'));
    }

    /**
     * Atualiza os dados de um cartão de crédito.
     */
    public function update(Request $request, CreditCard $creditCard)
    {
        // Tenancy Check
        abort_if($creditCard->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        $validated = $request->validate([
            'card_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'custom_bank_name' => 'nullable|required_if:bank_name,Outro|string|max:255',
            'limit' => 'required|string',
            'closing_day' => 'required|integer|between:1,31',
            'due_day' => 'required|integer|between:1,31',
            'flag' => 'required|in:visa,mastercard,elo,amex,hipercard,outros',
            'last_four_digits' => 'nullable|string|size:4',
            'observations' => 'nullable|string',
        ]);

        $bankName = $validated['bank_name'];
        if ($bankName === 'Outro') {
            $bankName = $validated['custom_bank_name'];
        }

        // Limpeza de valor
        $cleanLimit = $validated['limit'];
        $cleanLimit = str_replace('R$', '', $cleanLimit);
        $cleanLimit = trim($cleanLimit);
        $cleanLimit = str_replace('.', '', $cleanLimit);
        $cleanLimit = str_replace(',', '.', $cleanLimit);
        $limit = (float) $cleanLimit;

        $creditCard->update([
            'card_name' => $validated['card_name'],
            'bank_name' => $bankName,
            'limit' => $limit,
            'closing_day' => $validated['closing_day'],
            'due_day' => $validated['due_day'],
            'flag' => $validated['flag'],
            'last_four_digits' => $validated['last_four_digits'] ?? null,
            'observations' => $validated['observations'] ?? null,
        ]);

        return redirect()->route('bank-accounts.index', ['tab' => 'cards'])->with('success', 'Cartão de crédito atualizado com sucesso!');
    }

    /**
     * Exclui um cartão de crédito.
     */
    public function destroy(CreditCard $creditCard)
    {
        // Tenancy Check
        abort_if($creditCard->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        $creditCard->delete();

        return redirect()->route('bank-accounts.index', ['tab' => 'cards'])->with('success', 'Cartão de crédito excluído com sucesso!');
    }

    /**
     * Exibe o extrato detalhado das compras de um cartão de crédito.
     */
    public function show(CreditCard $creditCard)
    {
        $userId = auth()->id();
        abort_if($creditCard->user_id !== $userId, 403, 'Ação não autorizada.');

        // Busca todas as transações (despesas) vinculadas a este cartão em ordem cronológica
        $transactions = \App\Models\Transaction::where('credit_card_id', $creditCard->id)
            ->with('category')
            ->orderBy('due_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Limites
        // Vamos somar os gastos do mês atual
        $usedLimit = (float) \App\Models\Transaction::where('credit_card_id', $creditCard->id)
            ->where('type', 'saida')
            ->whereMonth('due_date', \Carbon\Carbon::now()->month)
            ->whereYear('due_date', \Carbon\Carbon::now()->year)
            ->sum('amount');

        $availableLimit = (float) $creditCard->limit - $usedLimit;

        return view('credit_cards.show', compact('creditCard', 'transactions', 'usedLimit', 'availableLimit'));
    }
}
