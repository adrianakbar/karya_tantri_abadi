<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Struk Penjualan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }

        .receipt {
            width: 250px;
            /* ±80mm */
            margin: auto;
            padding: 10px;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 2px 0;
        }

        td.qty {
            width: 25px;
            text-align: center;
        }

        td.price,
        td.total {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="receipt">
        {{-- HEADER TOKO --}}
        <div class="center">
            <img src="{{ public_path('img/logo-karya-tantri-abadi.png') }}" alt="Logo" width="60"><br>
            <strong>{{ $sale->cooperation->name ?? 'Nama Toko' }}</strong><br>
            {{ $sale->cooperation->address ?? 'Alamat Toko' }}<br>
            Telp: {{ $sale->cooperation->phone ?? '-' }}
        </div>
        <div class="line"></div>

        {{-- INFO TRANSAKSI --}}
        <p>
            Tanggal : {{ $sale->sale_date->format('d-m-Y H:i') }}<br>
            Kasir : {{ $sale->user->name ?? 'Admin' }}<br>
            Pelanggan : {{ $sale->customer->name ?? ($sale->customer_name ?? 'Umum') }}<br>
            No : {{ $sale->sale_number }}
        </p>
        <div class="line"></div>

        {{-- DETAIL PRODUK --}}
        <table>
            @foreach ($sale->details as $item)
                <tr>
                    <td colspan="4">{{ $item->product->name }}</td>
                </tr>
                <tr>
                    <td class="qty">{{ $item->quantity }} x</td>
                    <td class="price">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="total" colspan="2">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </table>
        <div class="line"></div>
        @php
            $subtotal = $sale->subtotal;
            $taxValue = ($subtotal * ($sale->tax_amount ?? 0)) / 100;
            $discountValue = ($subtotal * ($sale->discount_amount ?? 0)) / 100;
        @endphp
        {{-- TOTAL --}}
        <table>
            <tr>
                <td>Subtotal</td>
                <td class="right">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Pajak ({{ $sale->tax_amount }}%)</td>
                <td class="right">Rp {{ number_format($taxValue, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Diskon ({{ $sale->discount_amount }}%)</td>
                <td class="right">Rp {{ number_format($discountValue, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Total</strong></td>
                <td class="right"><strong>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td>Bayar (Cash)</td>
                <td class="right">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Kembali</td>
                <td class="right">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</td>
            </tr>
        </table>
        <div class="line"></div>

        {{-- FOOTER --}}
        <div class="center">
            <p>Terima kasih telah berbelanja</p>
            <p>Simpan struk ini sebagai bukti pembelian</p>
        </div>
    </div>
</body>

</html>
