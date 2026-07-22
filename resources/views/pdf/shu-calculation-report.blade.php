<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Perhitungan SHU</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #333; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { background-color: #e8f5e8; font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>KARYA TANTRI ABADI</h1>
        <p>Laporan Perhitungan Sisa Hasil Usaha (SHU)</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tahun</th>
                <th>Total Pendapatan</th>
                <th>Total Pengeluaran</th>
                <th>Total SHU</th>
                <th>Tanggal Distribusi</th>
                <th>Status</th>
                <th>Dihitung Oleh</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shuData as $shu)
            <tr>
                <td class="text-center">{{ $shu->year }}</td>
                <td class="text-right">Rp {{ number_format($shu->total_revenue ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($shu->total_expenses ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($shu->total_shu, 0, ',', '.') }}</td>
                <td class="text-center">{{ $shu->distribution_date ? $shu->distribution_date->format('d/m/Y') : '-' }}</td>
                <td class="text-center">
                    @switch($shu->status)
                        @case('calculated')
                            Dihitung
                            @break
                        @case('distributed')
                            Didistribusi
                            @break
                        @case('pending')
                            Pending
                            @break
                        @default
                            {{ $shu->status }}
                    @endswitch
                </td>
                <td>{{ $shu->calculator->name ?? '-' }}</td>
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

