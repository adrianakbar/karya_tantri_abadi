<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $cooperationId = auth()->user()->cooperation_id;
                
                // Total Anggota
                $totalMembers = \App\Models\User::where('cooperation_id', $cooperationId)->count();
                
                // Total Simpanan
                $totalSavings = \App\Models\SavingsTransaction::where('cooperation_id', $cooperationId)
                    ->sum('amount');
                
                // Simpanan Bulan Ini
                $monthlySavings = \App\Models\SavingsTransaction::where('cooperation_id', $cooperationId)
                    ->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year)
                    ->sum('amount');
                
                // Rata-rata Simpanan per Anggota
                $avgSavingsPerMember = $totalMembers > 0 ? $totalSavings / $totalMembers : 0;

                $savingTypes = \App\Models\SavingsType::all();

                $savingsTypeLabels = [];
                $savingsTypeData = [];
                $savingsTypeColors = [
                    'rgb(59, 130, 246)',  // Biru
                    'rgb(34, 197, 94)',   // Hijau
                    'rgb(251, 146, 60)',  // Oranye
                    'rgb(168, 85, 247)',  // Ungu
                    'rgb(239, 68, 68)',   // Merah
                    'rgb(16, 185, 129)',  // Emerald
                    'rgb(245, 158, 11)',  // Kuning
                    'rgb(99, 102, 241)',  // Indigo
                ];

                foreach ($savingTypes as $index => $type) {
                    $savingsTypeLabels[] = $type->name;
                    
                    // Hitung total simpanan untuk jenis ini
                    $typeTotal = \App\Models\SavingsTransaction::where('cooperation_id', $cooperationId)
                        ->where('savings_type_id', $type->id)
                        ->sum('amount');
                    
                    $savingsTypeData[] = $typeTotal;
                }

                $monthlyTrendLabels = [];
                $monthlyTrendData = [];
                
                for ($i = 5; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $monthName = $date->translatedFormat('M'); // Nama bulan dalam bahasa Indonesia
                    $monthYear = $date->format('Y-m');
                    
                    $monthlyTrendLabels[] = $monthName;
                    
                    // Total simpanan per bulan
                    $monthlyTotal = \App\Models\SavingsTransaction::where('cooperation_id', $cooperationId)
                        ->whereYear('transaction_date', $date->year)
                        ->whereMonth('transaction_date', $date->month)
                        ->sum('amount');
                    
                    $monthlyTrendData[] = $monthlyTotal;
                }
            @endphp

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Anggota</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($totalMembers) }} Orang</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Simpanan</p>
                        <p class="text-lg font-semibold text-green-600 dark:text-green-400">Rp {{ number_format($totalSavings, 0, ',', '.') }}</p>
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
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Simpanan Bulan Ini</p>
                        <p class="text-lg font-semibold text-orange-600 dark:text-orange-400">Rp {{ number_format($monthlySavings, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Rata-rata per Anggota</p>
                        <p class="text-lg font-semibold text-purple-600 dark:text-purple-400">Rp {{ number_format($avgSavingsPerMember, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Grafik Simpanan per Jenis -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Simpanan per Jenis</h3>
                <div class="h-64">
                    <canvas id="savingsTypeChart"></canvas>
                </div>
            </div>

            <!-- Grafik Trend Simpanan -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Trend Simpanan (6 Bulan Terakhir)</h3>
                <div class="h-64">
                    <canvas id="savingsTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Savers -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Anggota dengan Simpanan Terbesar</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Anggota</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No. Anggota</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Simpanan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Transaksi Terakhir</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            $topSavers = \App\Models\SavingsTransaction::where('cooperation_id', $cooperationId)
                                ->where('status', 'completed')
                                ->with('user')
                                ->select('user_id', \DB::raw('SUM(amount) as total_savings'), \DB::raw('MAX(transaction_date) as last_transaction'))
                                ->groupBy('user_id')
                                ->orderByDesc('total_savings')
                                ->limit(10)
                                ->get();
                        @endphp
                        
                        @foreach($topSavers as $index => $saver)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                @if($index < 3)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ $index + 1 }}
                                    </span>
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $saver->user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $saver->user->member_number ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 dark:text-green-400">
                                Rp {{ number_format($saver->total_savings, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($saver->last_transaction)->format('d/m/Y') }}
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
        // Grafik Simpanan per Jenis
        const savingsTypeCtx = document.getElementById('savingsTypeChart').getContext('2d');
        new Chart(savingsTypeCtx, {
            type: 'doughnut',
            data: {
            labels: {!! json_encode($savingsTypeLabels) !!},
            datasets: [{
                data: {!! json_encode($savingsTypeData) !!},
                backgroundColor: {!! json_encode(array_slice($savingsTypeColors, 0, count($savingTypes))) !!}
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

        // Grafik Trend Simpanan
        const savingsTrendCtx = document.getElementById('savingsTrendChart').getContext('2d');
        new Chart(savingsTrendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($monthlyTrendLabels) !!},
                datasets: [{
                    label: 'Total Simpanan',
                    data: {!! json_encode($monthlyTrendData) !!},
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3,
                    pointBackgroundColor: 'rgb(34, 197, 94)',
                    pointBorderColor: 'white',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Rp ${context.raw.toLocaleString('id-ID')}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return 'Rp ' + (value / 1000000).toFixed(1) + 'Jt';
                                } else if (value >= 1000) {
                                    return 'Rp ' + (value / 1000).toFixed(0) + 'Rb';
                                }
                                return 'Rp ' + value;
                            }
                        }
                    }
                }
            }
        });
    </script>
    @endpush

    <!-- Savings Table -->
    <div class="mt-6">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
