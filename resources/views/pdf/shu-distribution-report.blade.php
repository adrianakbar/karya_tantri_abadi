<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Distribusi SHU</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #333; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { background-color: #e8f5e8; font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
        .summary { margin: 20px 0; padding: 15px; background-color: #f9f9f9; border-radius: 5px; }
        .summary-item { display: inline-block; margin: 0 20px; text-align: center; }
        .summary-value { font-size: 18px; font-weight: bold; color: #2d5a2d; }
        .summary-label { font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>KARYA TANTRI ABADI</h1>
        <p>Laporan Distribusi Sisa Hasil Usaha (SHU) Anggota</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i') }}</p>
    </div>

    <!-- Summary -->
    <div class="summary">
        @php
            $totalDistributed = $distributionData->where('status', 'paid')->sum('shu_amount');
            $totalMembers = $distributionData->count();
            $paidMembers = $distributionData->where('status', 'paid')->count();
        @endphp

        <div class="summary-item">
            <div class="summary-value">Rp {{ number_format($totalDistributed, 0, ',', '.') }}</div>
            <div class="summary-label">Total Terdistribusi</div>
        </div>

        <div class="summary-item">
            <div class="summary-value">{{ $paidMembers }}/{{ $totalMembers }}</div>
            <div class="summary-label">Anggota Sudah Dibayar</div>
        </div>

        <div class="summary-item">
            <div class="summary-value">{{ $totalMembers > 0 ? round(($paidMembers / $totalMembers) * 100, 1) : 0 }}%</div>
            <div class="summary-label">Tingkat Pembayaran</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tahun</th>
                <th>Nama Anggota</th>
                <th>No. Anggota</th>
                <th>Bagian Simpanan</th>
                <th>Bagian Transaksi</th>
                <th>Total Bagian</th>
                <th>Status</th>
                <th>Tanggal Bayar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($distributionData as $share)
            <tr>
                <td class="text-center">{{ $share->distribution->year }}</td>
                <td>{{ $share->user->name ?? 'Unknown' }}</td>
                <td class="text-center">{{ $share->user->member_number ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($share->savings_contribution, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($share->transaction_contribution, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($share->shu_amount, 0, ',', '.') }}</td>
                <td class="text-center">
                    @switch($share->status)
                        @case('paid')
                            Dibayar
                            @break
                        @case('pending')
                            Pending
                            @break
                        @default
                            {{ $share->status }}
                    @endswitch
                </td>
                <td class="text-center">{{ $share->paid_at ? $share->paid_at->format('d/m/Y H:i') : '-' }}</td>
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

