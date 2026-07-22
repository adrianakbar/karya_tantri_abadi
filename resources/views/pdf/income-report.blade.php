<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pemasukan</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #333; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; font-size: 10px; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
        .summary { margin: 20px 0; padding: 15px; background-color: #f9f9f9; border-radius: 5px; }
        .summary-item { display: inline-block; margin: 0 15px; text-align: center; }
        .summary-value { font-size: 14px; font-weight: bold; color: #2d5a2d; }
        .summary-label { font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>KARYA TANTRI ABADI</h1>
        <p>Laporan Pemasukan</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i') }}</p>
    </div>

    <!-- Summary -->
    <div class="summary">
        @php
            $totalSales = $sales->count();
            $totalIncome = $sales->sum('total_amount');
            $totalItems = $sales->sum(function($sale) {
                return $sale->details->sum('quantity');
            });
            $averageSale = $totalSales > 0 ? $totalIncome / $totalSales : 0;
        @endphp

        <div class="summary-item">
            <div class="summary-value">{{ $totalSales }}</div>
            <div class="summary-label">Total Penjualan</div>
        </div>

        <div class="summary-item">
            <div class="summary-value">{{ $totalItems }}</div>
            <div class="summary-label">Total Item Terjual</div>
        </div>

        <div class="summary-item">
            <div class="summary-value">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
            <div class="summary-label">Total Pemasukan</div>
        </div>

        <div class="summary-item">
            <div class="summary-value">Rp {{ number_format($averageSale, 0, ',', '.') }}</div>
            <div class="summary-label">Rata-rata per Penjualan</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal Penjualan</th>
                <th>No. Penjualan</th>
                <th>Customer</th>
                <th>Subtotal</th>
                <th>Total Penjualan</th>
                <th>Status</th>
                <th>Metode Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            <tr>
                <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                <td>{{ $sale->sale_number }}</td>
                <td>{{ $sale->customer->name ?? 'Unknown' }}</td>
                <td class="text-right">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                <td class="text-center">Selesai</td>
                <td>{{ $sale->payment_method ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis oleh sistem Karya Tantri Abadi</p>
        <p>&copy; {{ date('Y') }} Karya Tantri Abadi. All rights reserved.</p>
    </div>
</body>
</html>
