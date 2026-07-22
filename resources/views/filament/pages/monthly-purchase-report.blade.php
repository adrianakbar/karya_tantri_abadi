<x-filament-panels::page>
    <x-filament::card>
        <form wire:submit.prevent="generateReport">
            {{ $this->form }}
        </form>
    </x-filament::card>

    @if (!empty($data))
        <x-filament::card class="mt-6">
            <div class="space-y-6">
                <div class="text-xl font-bold">
                    Laporan Pembelian: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 text-left">No. Invoice</th>
                                <th class="px-4 py-2 text-left">Tanggal</th>
                                <th class="px-4 py-2 text-left">Supplier</th>
                                <th class="px-4 py-2 text-right">Total Item</th>
                                <th class="px-4 py-2 text-right">Total Pembelian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($data as $purchase)
                                <tr>
                                    <td class="px-4 py-2">{{ $purchase['invoice_number'] }}</td>
                                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($purchase['purchase_date'])->format('d M Y') }}</td>
                                    <td class="px-4 py-2">{{ $purchase['supplier'] }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($purchase['total_items']) }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($purchase['total_amount']) }}</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td colspan="5" class="px-4 py-2">
                                        <table class="w-full text-xs">
                                            <thead>
                                                <tr>
                                                    <th class="px-2 py-1 text-left">Produk</th>
                                                    <th class="px-2 py-1 text-right">Jumlah</th>
                                                    <th class="px-2 py-1 text-right">Harga</th>
                                                    <th class="px-2 py-1 text-right">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                @foreach ($purchase['details'] as $detail)
                                                    <tr>
                                                        <td class="px-2 py-1">{{ $detail['product'] }}</td>
                                                        <td class="px-2 py-1 text-right">{{ number_format($detail['quantity']) }}</td>
                                                        <td class="px-2 py-1 text-right">{{ number_format($detail['price']) }}</td>
                                                        <td class="px-2 py-1 text-right">{{ number_format($detail['subtotal']) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-bold bg-gray-50">
                                <td colspan="3" class="px-4 py-2 text-right">Total</td>
                                <td class="px-4 py-2 text-right">
                                    {{ number_format(collect($data)->sum('total_items')) }}
                                </td>
                                <td class="px-4 py-2 text-right">
                                    {{ number_format(collect($data)->sum('total_amount')) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </x-filament::card>
    @endif
</x-filament-panels::page>
