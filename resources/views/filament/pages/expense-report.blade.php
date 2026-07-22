<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $cooperationId = auth()->user()->cooperation_id;
                
                // Total Pengeluaran
                $totalExpenses = \App\Models\Expense::where('cooperation_id', $cooperationId)
                    ->where('status', 'approved')
                    ->sum('amount');
                
                // Pengeluaran Bulan Ini
                $monthlyExpenses = \App\Models\Expense::where('cooperation_id', $cooperationId)
                    ->where('status', 'approved')
                    ->whereMonth('expense_date', now()->month)
                    ->whereYear('expense_date', now()->year)
                    ->sum('amount');
                
                // Rata-rata Pengeluaran Harian
                $dailyAverage = \App\Models\Expense::where('cooperation_id', $cooperationId)
                    ->where('status', 'approved')
                    ->whereMonth('expense_date', now()->month)
                    ->whereYear('expense_date', now()->year)
                    ->avg('amount');
                
                // Pending Approval
                $pendingApproval = \App\Models\Expense::where('cooperation_id', $cooperationId)
                    ->where('status', 'pending')
                    ->count();

                // data grafik pengeluaran per kategori
                $expenseCategories = \App\Models\ExpenseCategory::all();
                $expenseCategoryColors = [
                    'rgb(59, 130, 246)',  // Biru
                    'rgb(34, 197, 94)',   // Hijau
                    'rgb(251, 146, 60)',  // Oranye
                    'rgb(168, 85, 247)',  // Ungu
                    'rgb(239, 68, 68)',   // Merah
                    'rgb(16, 185, 129)',  // Emerald
                    'rgb(245, 158, 11)',  // Kuning
                    'rgb(99, 102, 241)',  // Indigo
                ];
                $expenseCategoryData = [];
                $expenseCategoryLabels = [];
                foreach ($expenseCategories as $category) {
                    $expenseCategoryData[] = \App\Models\Expense::where('cooperation_id', $cooperationId)
                        ->where('status', 'approved')
                        ->where('expense_category_id', $category->id)
                        ->sum('amount');
                    $expenseCategoryLabels[] = $category->name;
                }

                // data grafik trend pengeluaran
                $expenseTrends = \App\Models\Expense::where('cooperation_id', $cooperationId)
                    ->where('status', 'approved')
                    ->whereYear('expense_date', now()->year)
                    ->orderBy('expense_date', 'asc')
                    ->get();

                $expenseTrendLabels = [];
                $expenseTrendColors = [
                    'rgb(59, 130, 246)',  // Biru
                    'rgb(34, 197, 94)',   // Hijau
                    'rgb(251, 146, 60)',  // Oranye
                    'rgb(168, 85, 247)',  // Ungu
                    'rgb(239, 68, 68)',   // Merah
                    'rgb(16, 185, 129)',  // Emerald
                    'rgb(245, 158, 11)',  // Kuning
                    'rgb(99, 102, 241)',  // Indigo
            ];
                $expenseTrendData = [];
                foreach ($expenseTrends as $expense) {
                    $expenseTrendLabels[] = $expense->expense_date->format('M');
                    $expenseTrendData[] = $expense->amount;
                }

            @endphp

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Pengeluaran</p>
                        <p class="text-lg font-semibold text-red-600 dark:text-red-400">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-orange-100 dark:bg-orange-900 rounded-lg">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm6-3a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pengeluaran Bulan Ini</p>
                        <p class="text-lg font-semibold text-orange-600 dark:text-orange-400">Rp {{ number_format($monthlyExpenses, 0, ',', '.') }}</p>
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
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Approval</p>
                        <p class="text-lg font-semibold text-yellow-600 dark:text-yellow-400">{{ number_format($pendingApproval) }} Items</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Grafik Pengeluaran per Kategori -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Pengeluaran per Kategori</h3>
                <div class="h-64">
                    <canvas id="expenseCategoryChart"></canvas>
                </div>
            </div>

            <!-- Grafik Trend Pengeluaran -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Trend Pengeluaran (6 Bulan Terakhir)</h3>
                <div class="h-64">
                    <canvas id="expenseTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Breakdown by Category -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Breakdown Pengeluaran per Kategori</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @php
                    $categoryBreakdown = \App\Models\Expense::where('cooperation_id', $cooperationId)
                        ->where('status', 'approved')
                        // ->whereMonth('expense_date', now()->month)
                        ->whereYear('expense_date', now()->year)
                        ->with('category')
                        ->select('expense_category_id', \DB::raw('SUM(amount) as total'), \DB::raw('COUNT(*) as count'))
                        ->groupBy('expense_category_id')
                        ->get();
                @endphp

                @foreach($categoryBreakdown as $breakdown)
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $breakdown->category->name ?? 'Lainnya' }}</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">Rp {{ number_format($breakdown->total, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $breakdown->count }} transaksi</p>
                        </div>
                        <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                            <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Top Expenses -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Pengeluaran Terbesar Bulan Ini</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Keterangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Penerima</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            $topExpenses = \App\Models\Expense::where('cooperation_id', $cooperationId)
                                ->where('status', 'approved')
                                ->whereMonth('expense_date', now()->month)
                                ->whereYear('expense_date', now()->year)
                                ->with(['category'])
                                ->orderByDesc('amount')
                                ->limit(10)
                                ->get();
                        @endphp
                        
                        @foreach($topExpenses as $expense)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $expense->expense_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                {{ Str::limit($expense->description, 50) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ $expense->category->name ?? 'Lainnya' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $expense->recipient }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600 dark:text-red-400">
                                Rp {{ number_format($expense->amount, 0, ',', '.') }}
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
        // Grafik Pengeluaran per Kategori
        const expenseCategoryCtx = document.getElementById('expenseCategoryChart').getContext('2d');
        new Chart(expenseCategoryCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($expenseCategoryLabels) !!},
                datasets: [{
                    data: {!! json_encode($expenseCategoryData) !!},
                    backgroundColor: {!! json_encode(array_slice($expenseCategoryColors, 0, count($expenseCategories))) !!}
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // Grafik Trend Pengeluaran
        const expenseTrendCtx = document.getElementById('expenseTrendChart').getContext('2d');
        new Chart(expenseTrendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($expenseTrendLabels) !!},
                datasets: [{
                    data: {!! json_encode($expenseTrendData) !!},
                    backgroundColor: {!! json_encode(array_slice($expenseTrendColors, 0, count($expenseTrendLabels))) !!}
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

    <!-- Expense Table -->
    <div class="mt-6">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
