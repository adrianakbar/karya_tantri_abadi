<?php

namespace App\Http\Controllers;

use App\Models\SavingsTransaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class SavingsReceiptController extends Controller
{
    public function print(SavingsTransaction $transaction)
    {
        $pdf = PDF::loadView('savings.receipt', [
            'transaction' => $transaction->load(['user', 'savingsType', 'processedBy']),
        ]);
        return $pdf->stream('receipt-'.$transaction->transaction_number.'.pdf');
    }
}
