<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Tabs and Actions -->
        <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8">
                <button 
                    wire:click="setActiveTab('loans')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'loans' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Data Pinjaman
                </button>
                <button 
                    wire:click="setActiveTab('payments')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'payments' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Riwayat Cicilan
                </button>
            </nav>

            <!-- Export Actions -->
            <div class="flex space-x-2">
                @if($activeTab === 'loans')
                    <x-filament::button
                        wire:click="exportLoansExcel"
                        color="success"
                        size="sm">
                        <x-heroicon-o-document-arrow-down class="w-4 h-4 mr-2" />
                        Export Excel
                    </x-filament::button>
                    <x-filament::button
                        wire:click="exportLoansPdf"
                        color="danger"
                        size="sm">
                        <x-heroicon-o-document class="w-4 h-4 mr-2" />
                        Export PDF
                    </x-filament::button>
                @else
                    <x-filament::button
                        wire:click="exportPaymentsExcel"
                        color="success"
                        size="sm">
                        <x-heroicon-o-document-arrow-down class="w-4 h-4 mr-2" />
                        Export Excel
                    </x-filament::button>
                    <x-filament::button
                        wire:click="exportPaymentsPdf"
                        color="danger"
                        size="sm">
                        <x-heroicon-o-document class="w-4 h-4 mr-2" />
                        Export PDF
                    </x-filament::button>
                @endif
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $cooperationId = auth()->user()->cooperation_id;
                
                // Total Pinjaman Aktif
                $activeLoans = \App\Models\Loan::where('cooperation_id', $cooperationId)
                    ->where('status', 'active')
                    ->count();
                
                // Total Outstanding
                $totalOutstanding = \App\Models\Loan::where('cooperation_id', $cooperationId)
                    ->where('status', 'active')
                    ->sum('remaining_balance');
                
                // Cicilan Bulan Ini
                $monthlyPayments = \App\Models\LoanPayment::whereHas('loan', function($query) use ($cooperationId) {
                        $query->where('cooperation_id', $cooperationId);
                    })
                    ->whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year)
                    ->sum('total_amount');
                
                // Tunggakan
                $overdueLoans = \App\Models\Loan::where('cooperation_id', $cooperationId)
                    ->where('status', 'active')
                    ->where('due_date', '<', now())
                    ->count();

                // Data grafik pinjaman per jenis
                $loanTypes = \App\Models\LoanType::all();

                $loanTypeLabels = [];
                $loanTypeData = [];
                $loanTypeColors = [
                    'rgb(59, 130, 246)',  // Biru
                    'rgb(34, 197, 94)',   // Hijau
                    'rgb(251, 146, 60)',  // Oranye
                    'rgb(168, 85, 247)',  // Ungu
                    'rgb(239, 68, 68)',   // Merah
                    'rgb(16, 185, 129)',  // Emerald
                    'rgb(245, 158, 11)',  // Kuning
                    'rgb(99, 102, 241)',  // Indigo
                ];
                foreach ($loanTypes as $index => $type) {
                    $loanTypeLabels[] = $type->name;
                    $loanTypeData[] = \App\Models\Loan::where('cooperation_id', $cooperationId)
                        ->where('loan_type_id', $type->id)
                        ->count();           
                }

                // Data grafik status pinjaman
                $loanStatusLabels = ['Pending','Active', 'Overdue', 'Rejected', 'Approved', 'Disbursed', 'Completed'];
                $loanStatusColors = [
                    'rgb(59, 130, 246)',  // Biru
                    'rgb(34, 197, 94)',   // Hijau
                    'rgb(251, 146, 60)',  // Oranye
                    'rgb(168, 85, 247)',  // Ungu
                    'rgb(239, 68, 68)',   // Merah
                    'rgb(16, 185, 129)',  // Emerald
                    'rgb(245, 158, 11)',  // Kuning
                    'rgb(99, 102, 241)',  // Indigo
                ];
                $loanStatusData = [
                    \App\Models\Loan::where('cooperation_id', $cooperationId)
                        ->where('status', 'pending')
                        ->count(),
                    \App\Models\Loan::where('cooperation_id', $cooperationId)
                        ->where('status', 'active')
                        ->count(),
                    \App\Models\Loan::where('cooperation_id', $cooperationId)
                        ->where('status', 'overdue')
                        ->count(),
                    \App\Models\Loan::where('cooperation_id', $cooperationId)
                        ->where('status', 'rejected')
                        ->count(),
                    \App\Models\Loan::where('cooperation_id', $cooperationId)
                        ->where('status', 'approved')
                        ->count(),
                    \App\Models\Loan::where('cooperation_id', $cooperationId)
                        ->where('status', 'disbursed')
                        ->count(),
                    \App\Models\Loan::where('cooperation_id', $cooperationId)    
                        ->where('status', 'completed')
                        ->count(),
                ];

                // Trend Pembayaran 6 Bulan Terakhir
                $paymentTrendLabels = [];
                $paymentTrendData = [];
                
                for ($i = 5; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $monthName = $date->translatedFormat('M');
                    
                    $paymentTrendLabels[] = $monthName;
                    
                    $monthlyPayments = \App\Models\LoanPayment::where('cooperation_id', $cooperationId)
                        ->whereYear('created_at', $date->year)
                        // ->whereMonth('payment_date', $date->month)
                        ->sum('total_amount');
                    
                    $paymentTrendData[] = $monthlyPayments;
                    // dd($paymentTrendData);
                }
            @endphp

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pinjaman Aktif</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($activeLoans) }} Pinjaman</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Tagihan Tersisa</p>
                        <p class="text-lg font-semibold text-red-600 dark:text-red-400">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Cicilan Bulan Ini</p>
                        <p class="text-lg font-semibold text-green-600 dark:text-green-400">Rp {{ number_format($monthlyPayments, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-orange-100 dark:bg-orange-900 rounded-lg">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Tunggakan</p>
                        <p class="text-lg font-semibold text-orange-600 dark:text-orange-400">{{ number_format($overdueLoans) }} Pinjaman</p>
                    </div>
                </div>
            </div>
        </div>

        @if($activeTab === 'loans')
        <!-- Charts Section for Loans -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Grafik Pinjaman per Jenis -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Pinjaman per Jenis</h3>
                <div class="h-64">
                    <canvas id="loanTypeChart"></canvas>
                </div>
            </div>

            <!-- Grafik Status Pinjaman -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status Pinjaman</h3>
                <div class="h-64">
                    <canvas id="loanStatusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Borrowers -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Anggota dengan Pinjaman Terbesar</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ranking</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Anggota</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No. Anggota</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Pinjaman</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sisa Pinjaman</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            $topBorrowers = \App\Models\Loan::where('cooperation_id', $cooperationId)
                                ->with('user')
                                ->select('user_id', \DB::raw('SUM(principal_amount) as total_loans'), \DB::raw('SUM(remaining_balance) as total_remaining'))
                                ->groupBy('user_id')
                                ->orderByDesc('total_loans')
                                ->limit(10)
                                ->get();
                        @endphp
                        
                        @foreach($topBorrowers as $index => $borrower)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $borrower->user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $borrower->user->member_number ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600 dark:text-blue-400">
                                Rp {{ number_format($borrower->total_loans, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600 dark:text-red-400">
                                Rp {{ number_format($borrower->total_remaining, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <!-- Charts Section for Payments -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Grafik Trend Pembayaran -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Trend Pembayaran Cicilan</h3>
                <div class="h-64">
                    <canvas id="paymentTrendChart"></canvas>
                </div>
            </div>
        </div>
        @endif

        <!-- Detail Table -->
        <div class="mt-6">
            {{ $this->table }}
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Variabel untuk menyimpan instance chart
        let loanTypeChart = null;
        let loanStatusChart = null;
        let paymentTrendChart = null;

        function initializeCharts() {
            destroyExistingCharts();

            @if($activeTab === 'loans')
            // Grafik Pinjaman per Jenis
            const loanTypeCtx = document.getElementById('loanTypeChart')?.getContext('2d');
            if (loanTypeCtx) {
                loanTypeChart = new Chart(loanTypeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($loanTypeLabels) !!},
                        datasets: [{
                            data: {!! json_encode($loanTypeData) !!},
                            backgroundColor: {!! json_encode(array_slice($loanTypeColors, 0, count($loanTypeLabels))) !!}
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
            }

            // Grafik Status Pinjaman
            const loanStatusCtx = document.getElementById('loanStatusChart')?.getContext('2d');
            if (loanStatusCtx) {
                loanStatusChart = new Chart(loanStatusCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($loanStatusLabels) !!},
                        datasets: [{
                            data: {!! json_encode($loanStatusData) !!},
                            backgroundColor: {!! json_encode(array_slice($loanStatusColors, 0, count($loanStatusLabels))) !!}
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
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
            @else
            // Grafik Trend Pembayaran
            const paymentTrendCtx = document.getElementById('paymentTrendChart')?.getContext('2d');
            if (paymentTrendCtx) {
                paymentTrendChart = new Chart(paymentTrendCtx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($paymentTrendLabels) !!},
                        datasets: [{
                            label: 'Total Pembayaran',
                            data: {!! json_encode($paymentTrendData) !!},
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
            }
            @endif
        }

        function destroyExistingCharts() {
            if (loanTypeChart) {
                loanTypeChart.destroy();
                loanTypeChart = null;
            }
            if (loanStatusChart) {
                loanStatusChart.destroy();
                loanStatusChart = null;
            }
            if (paymentTrendChart) {
                paymentTrendChart.destroy();
                paymentTrendChart = null;
            }
        }

        // Initialize charts saat pertama kali load
        document.addEventListener('DOMContentLoaded', function() {
            // Tunggu sebentar untuk memastikan Livewire sudah siap
            setTimeout(initializeCharts, 100);
        });

        // Gunakan MutationObserver untuk mendeteksi perubahan tab
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    // Cek apakah ada canvas yang baru ditambahkan
                    const hasLoanChart = document.getElementById('loanTypeChart');
                    const hasPaymentChart = document.getElementById('paymentTrendChart');
                    
                    if (hasLoanChart || hasPaymentChart) {
                        setTimeout(initializeCharts, 50);
                    }
                }
            });
        });

        // Start observing
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Juga initialize ketika window load
        window.addEventListener('load', initializeCharts);
    </script>
    @endpush
</x-filament-panels::page>
