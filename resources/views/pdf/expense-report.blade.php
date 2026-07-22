<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pengeluaran</title>
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
        <p>Laporan Pengeluaran</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i') }}</p>
    </div>

    <!-- Summary -->
    <div class="summary">
        @php
            $totalExpenses = $expenses->count();
            $totalAmount = $expenses->sum('amount');
            $approvedCount = $expenses->where('status', 'approved')->count();
            $averageAmount = $totalExpenses > 0 ? $totalAmount / $totalExpenses : 0;
        @endphp

        <div class="summary-item">
            <div class="summary-value">{{ $totalExpenses }}</div>
            <div class="summary-label">Total Pengeluaran</div>
        </div>

        <div class="summary-item">
            <div class="summary-value">{{ $approvedCount }}</div>
            <div class="summary-label">Disetujui</div>
        </div>

        <div class="summary-item">
            <div class="summary-value">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
            <div class="summary-label">Total Nilai</div>
        </div>

        <div class="summary-item">
            <div class="summary-value">Rp {{ number_format($averageAmount, 0, ',', '.') }}</div>
            <div class="summary-label">Rata-rata per Pengeluaran</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. Pengeluaran</th>
                <th>Kategori</th>
                <th>Keterangan</th>
                <th>Jumlah</th>
                <th>Penerima</th>
                <th>No. Kuitansi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
            <tr>
                <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                <td>{{ $expense->expense_number }}</td>
                <td>{{ $expense->category->name ?? 'Lainnya' }}</td>
                <td>{{ $expense->notes ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                <td>{{ $expense->recipient ?? '-' }}</td>
                <td>{{ $expense->receipt_number ?? '-' }}</td>
                <td class="text-center">Disetujui</td>
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
