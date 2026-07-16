<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Helpers\NumberToWordsHelper;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PublicReceiptController extends Controller
{
    public function show($token)
    {
        $payment = Payment::with(['project.client.user', 'relatedProjects'])->where('token', $token)->firstOrFail();

        $amount = (float) $payment->amount;
        $amountFormatted = number_format($amount, 2, ',', '.');
        $amountInWords = NumberToWordsHelper::converter($amount);

        $monthsPt = [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
            5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
            9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'
        ];
        
        $paidDate = Carbon::parse($payment->paid_at);
        $day = $paidDate->day;
        $monthName = $monthsPt[$paidDate->month];
        $year = $paidDate->year;
        
        $formattedDateString = "Marília, {$monthName}, {$day} de {$year}";

        return view('payments.receipt', compact('payment', 'amountFormatted', 'amountInWords', 'formattedDateString'));
    }
}
