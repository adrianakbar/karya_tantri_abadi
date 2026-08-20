<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filter Form -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            {{ $this->form }}
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $cooperationId = auth()->user()->cooperation_id;
                $hasCashFlowData = \App\Models\CashFlow::where('cooperation_id', $cooperationId)->exists(); // Define it here

                $summary = $this->getCashFlowSummary();

                $currentBalance = $summary['current_balance'];
                $totalInflow = $summary['total_inflow'];
                $totalOutflow = $summary['total_outflow'];
                $netCashFlow = $summary['net_cash_flow'];

                $monthlyInflowTrend = $summary['monthly_inflow_trend'];
                $monthlyOutflowTrend = $summary['monthly_outflow_trend'];
                $balanceTrend = $summary['balance_trend'];

                $categoryBreakdown = $summary['category_breakdown'];
                $dailyTransactions = $summary['daily_transactions'];
            @endphp

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Saldo Kas Saat Ini</p>
                        <p class="text-lg font-semibold text-blue-600 dark:text-blue-400">Rp {{ number_format($currentBalance, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Pemasukan</p>
                        <p class="text-lg font-semibold text-green-600 dark:text-green-400">Rp {{ number_format($totalInflow, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Pengeluaran</p>
                        <p class="text-lg font-semibold text-red-600 dark:text-red-400">Rp {{ number_format($totalOutflow, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    @php
                        $netFlow = $netCashFlow;
                        $netFlowColor = $netFlow >= 0 ? 'green' : 'red';
                        $netFlowBg = $netFlow >= 0 ? 'green' : 'red';
                    @endphp
                    <div class="p-2 bg-{{ $netFlowBg }}-100 dark:bg-{{ $netFlowBg }}-900 rounded-lg">
                        <svg class="w-6 h-6 text-{{ $netFlowColor }}-600 dark:text-{{ $netFlowColor }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Arus Kas Bersih</p>
                        <p class="text-lg font-semibold text-{{ $netCashFlow >= 0 ? 'green' : 'red' }}-600 dark:text-{{ $netCashFlow >= 0 ? 'green' : 'red' }}-400">
                            {{ $netCashFlow >= 0 ? '+' : '' }}Rp {{ number_format($netCashFlow, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Cash Flow Trend Chart -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Trend Arus Kas Tahunan</h3>
                <div class="h-64">
                    <canvas id="cashFlowTrendChart"></canvas>
                </div>
            </div>

            <!-- Balance Trend Chart -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Trend Saldo Tahunan</h3>
                <div class="h-64">
                    <canvas id="balanceTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Inflow vs Outflow -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Perbandingan Pemasukan vs Pengeluaran Bulanan</h3>
            <div class="h-64">
                <canvas id="monthlyComparisonChart"></canvas>
            </div>
        </div>

        <!-- Category Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Inflow Categories -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Breakdown Pemasukan per Kategori</h3>
                <div class="space-y-3">
                    @if(isset($categoryBreakdown['inflow']))
                        @foreach($categoryBreakdown['inflow'] as $data)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $data['category'] }}</span>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Rp {{ number_format($data['total'], 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Outflow Categories -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Breakdown Pengeluaran per Kategori</h3>
                <div class="space-y-3">
                    @if(isset($categoryBreakdown['outflow']))
                        @foreach($categoryBreakdown['outflow'] as $data)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $data['category'] }}</span>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Rp {{ number_format($data['total'], 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <!-- Daily Transactions Heatmap -->
        @if($this->filterPeriod === 'monthly')
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Aktivitas Transaksi Harian Bulan Ini</h3>
                <div class="grid grid-cols-7 gap-2">
                    @php
                        $currentMonth = $this->filterMonth; // Make currentMonth available
                        $daysInMonth = \Carbon\Carbon::createFromDate($this->filterYear, $currentMonth, 1)->daysInMonth; // Use filtered year and month
                        $monthName = \Carbon\Carbon::createFromDate($this->filterYear, $currentMonth, 1)->locale('id')->monthName; // Use filtered year and month
                    @endphp

                    @for($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $dateKey = $this->filterYear . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                            $dailyData = $dailyTransactions[$dateKey] ?? ['inflow' => 0, 'outflow' => 0];
                            $totalActivity = $dailyData['inflow'] + $dailyData['outflow'];
                            $intensity = $totalActivity > 0 ? min(100, ($totalActivity / 1000000) * 100) : 0;
                            $bgColor = $intensity > 0 ? "rgba(34, 197, 94, " . ($intensity / 100) . ")" : "rgba(243, 244, 246, 1)";
                        @endphp
                        <div class="aspect-square rounded-lg flex items-center justify-center text-xs font-medium {{ $totalActivity > 0 ? 'text-white' : 'text-gray-500' }}"
                             style="background-color: {{ $bgColor }}">
                            {{ $day }}
                        </div>
                    @endfor
                </div>
                <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    Intensitas warna menunjukkan volume transaksi (lebih hijau = lebih aktif)
                </div>
            </div>
        @endif

        <!-- Recent Transactions -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Transaksi Terbaru</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Keterangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            if ($hasCashFlowData) {
                                $recentTransactions = \App\Models\CashFlow::where('cooperation_id', $cooperationId)
                                    ->orderBy('transaction_date', 'desc')
                                    ->limit(10)
                                    ->get();
                            } else {
                                // Get recent transactions from TransactionSummary, balance_after is already calculated in the model
                                $recentTransactions = \App\Models\TransactionSummary::forCooperation()
                                    ->orderBy('sort_date', 'desc')
                                    ->take(10)
                                    ->get();
                            }
                        @endphp

                        @foreach($recentTransactions as $transaction)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                {{ Str::limit($transaction->description, 40) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @php
                                        $categoryName = $transaction->category;
                                        $categoryClasses = 'bg-gray-100 text-gray-800'; // Default

                                        // Fetch dynamic expense categories once
                                        static $expenseCategoryNames = null;
                                        if ($expenseCategoryNames === null) {
                                            $cooperationId = auth()->user()->cooperation_id;
                                            $expenseCategoryNames = \App\Models\ExpenseCategory::where('cooperation_id', $cooperationId)
                                                ->pluck('name')
                                                ->toArray();
                                        }

                                        if ($categoryName == 'Penjualan Produk') {
                                            $categoryClasses = 'bg-primary-100 text-primary-800';
                                        } elseif ($categoryName == 'Simpanan Koperasi') {
                                            $categoryClasses = 'bg-success-100 text-success-800';
                                        } elseif ($categoryName == 'Pembelian/Restok') {
                                            $categoryClasses = 'bg-danger-100 text-danger-800';
                                        } elseif ($categoryName == 'Cicilan Pinjaman') {
                                            $categoryClasses = 'bg-info-100 text-info-800';
                                        } elseif ($categoryName == 'Pencairan Pinjaman') {
                                            $categoryClasses = 'bg-secondary-100 text-secondary-800';
                                        } elseif (in_array($categoryName, $expenseCategoryNames)) {
                                            $categoryClasses = 'bg-warning-100 text-warning-800'; // General expense category color
                                        }
                                    @endphp
                                    {{ $categoryClasses }}">
                                    {{ $transaction->category }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($transaction->type == 'inflow') bg-green-100 text-green-800
                                    @elseif($transaction->type == 'outflow') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ $transaction->type == 'inflow' ? 'Pemasukan' : ($transaction->type == 'outflow' ? 'Pengeluaran' : 'Transfer') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium
                                {{ $transaction->type == 'inflow' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $transaction->type == 'inflow' ? '+' : '-' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium
                                {{ $transaction->balance_after >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                Rp {{ number_format($transaction->balance_after, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <!-- Detail Table akan ditampilkan di bawah melalui Livewire table -->
    <div class="mt-6">
        {{ $this->table }}
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let cashFlowTrendChart, balanceTrendChart, monthlyComparisonChart; // Declare chart variables globally
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

        function renderCharts(inflowData, outflowData, balanceData) {
            // Destroy existing charts if they exist
            if (cashFlowTrendChart) cashFlowTrendChart.destroy();
            if (balanceTrendChart) balanceTrendChart.destroy();
            if (monthlyComparisonChart) monthlyComparisonChart.destroy();

            // Cash Flow Trend Chart (Line Chart)
            const cashFlowCtx = document.getElementById('cashFlowTrendChart').getContext('2d');
            cashFlowTrendChart = new Chart(cashFlowCtx, {
            type: 'line',
            data: {
                    labels: months,
                datasets: [{
                    label: 'Pemasukan',
                        data: inflowData,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4,
                        fill: true
                }, {
                    label: 'Pengeluaran',
                        data: outflowData,
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
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

            // Balance Trend Chart (Area Chart)
            const balanceCtx = document.getElementById('balanceTrendChart').getContext('2d');
            balanceTrendChart = new Chart(balanceCtx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Saldo Akhir',
                        data: balanceData,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
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

            // Monthly Comparison Chart (Bar Chart)
            const comparisonCtx = document.getElementById('monthlyComparisonChart').getContext('2d');
            monthlyComparisonChart = new Chart(comparisonCtx, {
                type: 'bar',
            data: {
                    labels: months,
                datasets: [{
                        label: 'Pemasukan',
                        data: inflowData,
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1
                    }, {
                        label: 'Pengeluaran',
                        data: outflowData,
                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        borderColor: 'rgb(239, 68, 68)',
                        borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                            position: 'top',
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
        }

        document.addEventListener('livewire:initialized', () => {
            // Initial chart render
            const initialChartData = {!! json_encode($this->getChartData()) !!};
            renderCharts(initialChartData.monthlyInflowTrend, initialChartData.monthlyOutflowTrend, initialChartData.balanceTrend);

            // Listen for Livewire event to update charts
            Livewire.on('updateCharts', (event) => {
                // Ensure the DOM has updated before rendering charts
                setTimeout(() => {
                    const { chartData } = event.detail; // Access chartData from event.detail
                    renderCharts(chartData.monthlyInflowTrend, chartData.monthlyOutflowTrend, chartData.balanceTrend);
                }, 0); // Use setTimeout with 0 delay to defer execution
            });
        });
    </script>
    @endpush
</x-filament-panels::page>