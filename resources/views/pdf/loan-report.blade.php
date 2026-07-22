<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pinjaman</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #333; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; font-size: 11px; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
        .summary { margin: 20px 0; padding: 15px; background-color: #f9f9f9; border-radius: 5px; }
        .summary-item { display: inline-block; margin: 0 15px; text-align: center; }
        .summary-value { font-size: 16px; font-weight: bold; color: #2d5a2d; }
        .summary-label { font-size: 11px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>KARYA TANTRI ABADI</h1>
        <p>Laporan Pinjaman Anggota</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i') }}</p>
    </div>

    <!-- Summary -->
    <div class="summary">
        @php
            $totalLoans = $loanData->count();
            $activeLoans = $loanData->where('status', 'active')->count();
            $completedLoans = $loanData->where('status', 'completed')->count();
            $totalLoanAmount = $loanData->sum('loan_amount');
        @endphp

        <div class="summary-item">
            <div class="summary-value">{{ $totalLoans }}</div>
            <div class="summary-label">Total Pinjaman</div>
        </div>

        <div class="summary-item">
            <div class="summary-value">{{ $activeLoans }}</div>
            <div class="summary-label">Pinjaman Aktif</div>
        </div>

        <div class="summary-item">
            <div class="summary-value">{{ $completedLoans }}</div>
            <div class="summary-label">Sudah Lunas</div>
        </div>

        <div class="summary-item">
            <div class="summary-value">Rp {{ number_format($totalLoanAmount, 0, ',', '.') }}</div>
            <div class="summary-label">Total Nilai Pinjaman</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No. Pinjaman</th>
                <th>Nama Anggota</th>
                <th>No. Anggota</th>
                <th>Jenis Pinjaman</th>
                <th>Jumlah Pinjaman</th>
                <th>Bunga</th>
                <th>Tenor</th>
                <th>Tanggal Cair</th>
                <th>Jatuh Tempo</th>
                <th>Status</th>
                <th>Diproses Oleh</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loanData as $loan)
            <tr>
                <td>{{ $loan->loan_number }}</td>
                <td>{{ $loan->user->name ?? 'Unknown' }}</td>
                <td class="text-center">{{ $loan->user->member_number ?? '-' }}</td>
                <td>{{ $loan->loanType->name ?? 'Unknown' }}</td>
                <td class="text-right">Rp {{ number_format($loan->loan_amount, 0, ',', '.') }}</td>
                <td class="text-center">{{ $loan->interest_rate }}%</td>
                <td class="text-center">{{ $loan->tenor }} bulan</td>
                <td class="text-center">{{ $loan->disbursement_date ? $loan->disbursement_date->format('d/m/Y') : '-' }}</td>
                <td class="text-center">{{ $loan->due_date ? $loan->due_date->format('d/m/Y') : '-' }}</td>
                <td class="text-center">
                    @switch($loan->status)
                        @case('active')
                            Aktif
                            @break
                        @case('completed')
                            Lunas
                            @break
                        @case('defaulted')
                            Macet
                            @break
                        @case('pending')
                            Pending
                            @break
                        @default
                            {{ $loan->status }}
                    @endswitch
                </td>
                <td>{{ $loan->processor->name ?? '-' }}</td>
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

