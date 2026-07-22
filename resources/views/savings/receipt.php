<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kwitansi Simpanan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .receipt-title {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
        }
        .receipt-info {
            margin-bottom: 20px;
        }
        .receipt-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .receipt-info td {
            padding: 5px;
        }
        .amount {
            font-size: 16px;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
        }
        .signature {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $transaction->cooperation->name }}</h1>
        <div>{{ $transaction->cooperation->address }}</div>
    </div>

    <div class="receipt-title">KWITANSI SIMPANAN</div>

    <div class="receipt-info">
        <table>
            <tr>
                <td width="200">No. Transaksi</td>
                <td>: {{ $transaction->transaction_number }}</td>
            </tr>
            <tr>
                <td>No. Kwitansi</td>
                <td>: {{ $transaction->receipt_number }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>: {{ $transaction->transaction_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td>Nama Anggota</td>
                <td>: {{ $transaction->user->name }}</td>
            </tr>
            <tr>
                <td>Jenis Simpanan</td>
                <td>: {{ $transaction->savingsType->name }}</td>
            </tr>
            <tr>
                <td>Nominal</td>
                <td class="amount">: Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    @if($transaction->notes)
    <div style="margin: 20px 0">
        <strong>Catatan:</strong><br>
        {{ $transaction->notes }}
    </div>
    @endif

    <div class="footer">
        <div>{{ $transaction->transaction_date->format('d F Y') }}</div>
        <div class="signature">
            <div>Petugas,</div>
            <br><br><br>
            <div>{{ $transaction->processedBy->name }}</div>
        </div>
    </div>
</body>
</html>
