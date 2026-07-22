<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pembayaran Pinjaman</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #333; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; font-size: 10px; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
        .summary { margin: 20px 0; padding: 15px; background-color: #f9f9f9; border-radius: 5px; }
        .summary-item { display: inline-block; margin: 0 12px; text-align: center; }
        .summary-value { font-size: 14px; font-weight: bold; color: #2d5a2d; }
        .summary-label { font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>KARYA TANTRI ABADI</h1>
        <p>Laporan Pembayaran Pinjaman Anggota</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i') }}</p>
    </div>

    <!-- Summary -->
    <div class="summary">
        @php
            $totalPayments = $paymentData->count();
            $paidPayments = $paymentData->where('status', 'paid')->count();
            $totalPaidAmount = $paymentData->where('status', 'paid')->sum('total_amount');
            $totalPrincipal = $paymentData->where('status', 'paid')->sum('principal_amount');
            $totalInterest = $paymentData->where('status', 'paid')->sum('interest_amount');
        @endphp

        <div class="summary-item">
            <div class="summary-value">{{ $paidPayments }}/{{ $totalPayments }}</div>
            <div class="summary-label">Pembayaran Lunas</div>
        </div>

        <div class="summary-item">
            <div class="summary-value">Rp {{ number_format($totalPaidAmount, 0, ',', '.') }}</div>
            <div class="summary-label">Total Dibayar</div>
        </div>

        <div class="summary-item">
            <div class="summary-value">Rp {{ number_format($totalPrincipal, 0, ',', '.') }}</div>
            <div class="summary-label">Total Pokok</div>
        </div>

        <div class="summary-item">
            <div class="summary-value">Rp {{ number_format($totalInterest, 0, ',', '.') }}</div>
            <div class="summary-label">Total Bunga</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No. Bayar</th>
                <th>No. Pinjaman</th>
                <th>Nama Anggota</th>
                <th>Jenis Pinjaman</th>
                <th>Angsuran Ke</th>
                <th>Jatuh Tempo</th>
                <th>Tanggal Bayar</th>
                <th>Pokok</th>
                <th>Bunga</th>
                <th>Denda</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($paymentData as $payment)
            <tr>
                <td>{{ $payment->payment_number }}</td>
                <td>{{ $payment->loan->loan_number }}</td>
                <td>{{ $payment->loan->user->name ?? 'Unknown' }}</td>
                <td>{{ $payment->loan->loanType->name ?? 'Unknown' }}</td>
                <td class="text-center">{{ $payment->installment_number }}</td>
                <td class="text-center">{{ $payment->due_date ? $payment->due_date->format('d/m/Y') : '-' }}</td>
                <td class="text-center">{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : '-' }}</td>
                <td class="text-right">Rp {{ number_format($payment->principal_amount, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($payment->interest_amount, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($payment->penalty_amount, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($payment->total_amount, 0, ',', '.') }}</td>
                <td class="text-center">
                    @switch($payment->status)
                        @case('paid')
                            Lunas
                            @break
                        @case('pending')
                            Pending
                            @break
                        @case('overdue')
                            Terlambat
                            @break
                        @default
                            {{ $payment->status }}
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

