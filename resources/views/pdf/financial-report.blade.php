<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Arus Kas</title>
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
        <p>Laporan Arus Kas</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i') }}</p>
    </div>

    <!-- Summary -->
    <div class="summary">
        @php
            $totalInflow = $data->where('type', 'inflow')->sum('amount');
            $totalOutflow = $data->where('type', 'outflow')->sum('amount');
            $netFlow = $totalInflow - $totalOutflow;
            $transactionCount = $data->count();
        @endphp

        <div class="summary-item">
            <div class="summary-value">{{ $transactionCount }}</div>
            <div class="summary-label">Total Transaksi</div>
        </div>

        <div class="summary-item">
            <div class="summary-value">Rp {{ number_format($totalInflow, 0, ',', '.') }}</div>
            <div class="summary-label">Total Pemasukan</div>
        </div>

        <div class="summary-item">
            <div class="summary-value">Rp {{ number_format($totalOutflow, 0, ',', '.') }}</div>
            <div class="summary-label">Total Pengeluaran</div>
        </div>

        <div class="summary-item">
            <div class="summary-value">Rp {{ number_format($netFlow, 0, ',', '.') }}</div>
            <div class="summary-label">Arus Kas Bersih</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Jenis</th>
                <th>Kategori</th>
                <th>Jumlah</th>
                <th>Saldo Akhir</th>
                <th>Referensi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $transaction)
            <tr>
                <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y') }}</td>
                <td>{{ $transaction->description ?? '-' }}</td>
                <td class="text-center">
                    @switch($transaction->type)
                        @case('inflow') Pemasukan @break
                        @case('outflow') Pengeluaran @break
                        @case('transfer') Transfer @break
                        @default {{ $transaction->type }}
                    @endswitch
                </td>
                <td>{{ $transaction->category ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($transaction->amount ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ $transaction->balance_after ? 'Rp ' . number_format($transaction->balance_after, 0, ',', '.') : '-' }}</td>
                <td>
                    @switch($transaction->reference_type)
                        @case('App\\Models\\Sale') Penjualan @break
                        @case('App\\Models\\Purchase') Pembelian @break
                        @case('App\\Models\\Expense') Pengeluaran @break
                        @case('App\\Models\\SavingsTransaction') Simpanan @break
                        @case('App\\Models\\LoanPayment') Pembayaran Pinjaman @break
                        @default {{ $transaction->reference_type ?: '-' }}
                    @endswitch
                </td>
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
