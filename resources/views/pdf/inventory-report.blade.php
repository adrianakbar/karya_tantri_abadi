<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Inventori</title>
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
        .summary-item { display: inline-block; margin: 0 12px; text-align: center; }
        .summary-value { font-size: 14px; font-weight: bold; color: #2d5a2d; }
        .summary-label { font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>KARYA TANTRI ABADI</h1>
        @if($type === 'stock')
            <p>Laporan Persediaan Barang</p>
        @elseif($type === 'purchases')
            <p>Laporan Pembelian Barang</p>
        @elseif($type === 'sales')
            <p>Laporan Penjualan Barang</p>
        @else
            <p>Laporan Laba Rugi Inventori</p>
        @endif
        <p>Dicetak pada: {{ date('d/m/Y H:i') }}</p>
    </div>

    @if($type === 'stock')
        <!-- Stock Report -->
        <div class="summary">
            @php
                $totalProducts = $data->count();
                $inStock = $data->where('stock_quantity', '>', 0)->count();
                $outOfStock = $data->where('stock_quantity', '=', 0)->count();
                $totalValue = $data->sum(function($product) {
                    return ($product->purchase_price ?? 0) * $product->stock_quantity;
                });
            @endphp

            <div class="summary-item">
                <div class="summary-value">{{ $totalProducts }}</div>
                <div class="summary-label">Total Produk</div>
            </div>

            <div class="summary-item">
                <div class="summary-value">{{ $inStock }}</div>
                <div class="summary-label">Tersedia</div>
            </div>

            <div class="summary-item">
                <div class="summary-value">{{ $outOfStock }}</div>
                <div class="summary-label">Habis</div>
            </div>

            <div class="summary-item">
                <div class="summary-value">Rp {{ number_format($totalValue, 0, ',', '.') }}</div>
                <div class="summary-label">Total Nilai Stok</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $product)
                <tr>
                    <td>{{ $product->code }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name ?? 'Unknown' }}</td>
                    <td class="text-center">{{ $product->stock_quantity }}</td>
                    <td class="text-right">Rp {{ number_format($product->purchase_price ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($product->selling_price ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $product->stock_quantity > 0 ? 'Tersedia' : 'Habis' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

    @elseif($type === 'purchases')
        <!-- Purchases Report -->
        <div class="summary">
            @php
                $totalPurchases = $data->count();
                $totalItems = $data->sum(function($purchase) {
                    return $purchase->details->sum('quantity');
                });
                $totalValue = $data->sum('total_amount');
            @endphp

            <div class="summary-item">
                <div class="summary-value">{{ $totalPurchases }}</div>
                <div class="summary-label">Total Pembelian</div>
            </div>

            <div class="summary-item">
                <div class="summary-value">{{ $totalItems }}</div>
                <div class="summary-label">Total Item</div>
            </div>

            <div class="summary-item">
                <div class="summary-value">Rp {{ number_format($totalValue, 0, ',', '.') }}</div>
                <div class="summary-label">Total Nilai</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>No. Pembelian</th>
                    <th>Supplier</th>
                    <th>Total Item</th>
                    <th>Total Nilai</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $purchase)
                <tr>
                    <td>{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                    <td>{{ $purchase->purchase_number }}</td>
                    <td>{{ $purchase->supplier->name ?? 'Unknown' }}</td>
                    <td class="text-center">{{ $purchase->details->sum('quantity') }}</td>
                    <td class="text-right">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @switch($purchase->status)
                            @case('pending') Pending @break
                            @case('received') Diterima @break
                            @case('cancelled') Dibatalkan @break
                            @default {{ $purchase->status }}
                        @endswitch
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    @elseif($type === 'sales')
        <!-- Sales Report -->
        <div class="summary">
            @php
                $totalSales = $data->count();
                $totalItems = $data->sum(function($sale) {
                    return $sale->details->sum('quantity');
                });
                $totalValue = $data->sum('total_amount');
                $totalProfit = $data->sum(function($sale) {
                    $profit = 0;
                    foreach ($sale->details as $detail) {
                        $cost = ($detail->product->purchase_price ?? 0) * $detail->quantity;
                        $revenue = $detail->total_price;
                        $profit += $revenue - $cost;
                    }
                    return $profit;
                });
            @endphp

            <div class="summary-item">
                <div class="summary-value">{{ $totalSales }}</div>
                <div class="summary-label">Total Penjualan</div>
            </div>

            <div class="summary-item">
                <div class="summary-value">{{ $totalItems }}</div>
                <div class="summary-label">Total Item</div>
            </div>

            <div class="summary-item">
                <div class="summary-value">Rp {{ number_format($totalValue, 0, ',', '.') }}</div>
                <div class="summary-label">Total Pendapatan</div>
            </div>

            <div class="summary-item">
                <div class="summary-value">Rp {{ number_format($totalProfit, 0, ',', '.') }}</div>
                <div class="summary-label">Total Keuntungan</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>No. Penjualan</th>
                    <th>Customer</th>
                    <th>Total Item</th>
                    <th>Total Pendapatan</th>
                    <th>Keuntungan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $sale)
                <tr>
                    <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                    <td>{{ $sale->sale_number }}</td>
                    <td>{{ $sale->customer->name ?? 'Unknown' }}</td>
                    <td class="text-center">{{ $sale->details->sum('quantity') }}</td>
                    <td class="text-right">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                    <td class="text-right">
                        @php
                            $profit = 0;
                            foreach ($sale->details as $detail) {
                                $cost = ($detail->product->purchase_price ?? 0) * $detail->quantity;
                                $revenue = $detail->total_price;
                                $profit += $revenue - $cost;
                            }
                        @endphp
                        Rp {{ number_format($profit, 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    @else
        <!-- Profit Loss Report -->
        <div class="summary">
            @php
                $totalProducts = $data->count();
                $totalCost = $data->sum('purchase_cost');
                $totalPotential = $data->sum('potential_sales');
                $totalProfit = $data->sum('profit');
            @endphp

            <div class="summary-item">
                <div class="summary-value">{{ $totalProducts }}</div>
                <div class="summary-label">Total Produk</div>
            </div>

            <div class="summary-item">
                <div class="summary-value">Rp {{ number_format($totalCost, 0, ',', '.') }}</div>
                <div class="summary-label">Total Modal</div>
            </div>

            <div class="summary-item">
                <div class="summary-value">Rp {{ number_format($totalPotential, 0, ',', '.') }}</div>
                <div class="summary-label">Potensi Penjualan</div>
            </div>

            <div class="summary-item">
                <div class="summary-value">Rp {{ number_format($totalProfit, 0, ',', '.') }}</div>
                <div class="summary-label">Total Keuntungan</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Kode Produk</th>
                    <th>Nama Produk</th>
                    <th>Stok</th>
                    <th>Modal</th>
                    <th>Potensi Penjualan</th>
                    <th>Keuntungan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                <tr>
                    <td>{{ $item['product']->code }}</td>
                    <td>{{ $item['product']->name }}</td>
                    <td class="text-center">{{ $item['product']->stock_quantity }}</td>
                    <td class="text-right">Rp {{ number_format($item['purchase_cost'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['potential_sales'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['profit'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis oleh sistem Karya Tantri Abadi</p>
        <p>&copy; {{ date('Y') }} Karya Tantri Abadi. All rights reserved.</p>
    </div>
</body>
</html>
