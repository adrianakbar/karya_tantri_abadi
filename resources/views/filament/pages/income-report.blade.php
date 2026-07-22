<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $cooperationId = auth()->user()->cooperation_id;

                // Total Pemasukan
                $totalIncome = App\Models\Sale::where('cooperation_id', $cooperationId)
                    ->where('status', 'completed')
                    ->sum('total_amount');

                // Pemasukan Bulan Ini
                $monthlyIncome = App\Models\Sale::where('cooperation_id', $cooperationId)
                    ->where('status', 'completed')
                    ->whereMonth('sale_date', now()->month)
                    ->whereYear('sale_date', now()->year)
                    ->sum('total_amount');

                // Rata-rata Pemasukan Harian
                $dailyAverage = App\Models\Sale::where('cooperation_id', $cooperationId)
                    ->where('status', 'completed')
                    ->whereMonth('sale_date', now()->month)
                    ->whereYear('sale_date', now()->year)
                    ->avg('total_amount');

                // Pending Sales
                $pendingSales = App\Models\Sale::where('cooperation_id', $cooperationId)
                    ->where('status', 'pending')
                    ->count();

                // data grafik trend pemasukan
                $incomeTrends = App\Models\Sale::where('cooperation_id', $cooperationId)
                    ->where('status', 'completed')
                    ->whereYear('sale_date', now()->year)
                    ->orderBy('sale_date', 'asc')
                    ->get();

                $incomeTrendLabels = [];
                $incomeTrendColors = [
                    'rgb(34, 197, 94)',   // Hijau
                    'rgb(59, 130, 246)',  // Biru
                    'rgb(251, 146, 60)',  // Oranye
                    'rgb(168, 85, 247)',  // Ungu
                    'rgb(239, 68, 68)',   // Merah
                    'rgb(16, 185, 129)',  // Emerald
                    'rgb(245, 158, 11)',  // Kuning
                    'rgb(99, 102, 241)',  // Indigo
            ];
                $incomeTrendData = [];
                foreach ($incomeTrends as $income) {
                    $incomeTrendLabels[] = $income->sale_date->format('M');
                    $incomeTrendData[] = $income->total_amount;
                }

            @endphp

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Pemasukan</p>
                        <p class="text-lg font-semibold text-green-600 dark:text-green-400">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-emerald-100 dark:bg-emerald-900 rounded-lg">
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pemasukan Bulan Ini</p>
                        <p class="text-lg font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($monthlyIncome, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Rata-rata Harian</p>
                        <p class="text-lg font-semibold text-blue-600 dark:text-blue-400">Rp {{ number_format($dailyAverage ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Purchase</p>
                        <p class="text-lg font-semibold text-yellow-600 dark:text-yellow-400">{{ number_format($pendingSales) }} Items</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Grafik Trend Pemasukan -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Trend Pemasukan (6 Bulan Terakhir)</h3>
                <div class="h-64">
                    <canvas id="incomeTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Breakdown by Supplier -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Breakdown Pemasukan per Customer</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @php
                    $customerBreakdown = App\Models\Sale::where('cooperation_id', $cooperationId)
                        ->where('status', 'completed')
                        ->whereYear('sale_date', now()->year)
                        ->with('customer')
                        ->select('customer_id', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
                        ->groupBy('customer_id')
                        ->get();
                @endphp

                @foreach($customerBreakdown as $breakdown)
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $breakdown->customer->name ?? 'Lainnya' }}</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">Rp {{ number_format($breakdown->total, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $breakdown->count }} transaksi</p>
                        </div>
                        <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Top Income -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Pemasukan Terbesar Bulan Ini</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No. Penjualan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            $topIncome = App\Models\Sale::where('cooperation_id', $cooperationId)
                                ->where('status', 'completed')
                                ->whereMonth('sale_date', now()->month)
                                ->whereYear('sale_date', now()->year)
                                ->with(['customer'])
                                ->orderByDesc('total_amount')
                                ->limit(10)
                                ->get();
                        @endphp

                        @foreach($topIncome as $income)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $income->sale_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                {{ $income->sale_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $income->customer->name ?? 'Lainnya' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 dark:text-green-400">
                                Rp {{ number_format($income->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($income->status == 'completed') bg-green-100 text-green-800
                                    @elseif($income->status == 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800 @endif">
                                    @if($income->status == 'completed') Selesai
                                    @elseif($income->status == 'pending') Pending
                                    @else Dibatalkan @endif
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Detail Table akan ditampilkan di bawah melalui Livewire table -->
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Grafik Trend Pemasukan
        const incomeTrendCtx = document.getElementById('incomeTrendChart').getContext('2d');
        new Chart(incomeTrendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($incomeTrendLabels) !!},
                datasets: [{
                    data: {!! json_encode($incomeTrendData) !!},
                    backgroundColor: {!! json_encode(array_slice($incomeTrendColors, 0, count($incomeTrendLabels))) !!}
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000).toFixed(0) + 'M';
                            }
                        }
                    }
                }
            }
        });
    </script>
    @endpush

    <!-- Income Table -->
    <div class="mt-6">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
