<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;

class SalePrintController extends Controller
{
    public function print(Sale $sale)
    {
        $sale->load('details.product', 'customer');

        $pdf = Pdf::loadView('components.print-receipt.single', compact('sale'))
            ->setPaper([0, 0, 300, 600], 'portrait'); 

        $filename = "struk-penjualan-{$sale->sale_number}-" . now()->format('Y-m-d-H-i-s') . ".pdf";

        return $pdf->download($filename);
    }
}
