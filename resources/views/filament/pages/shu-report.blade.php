<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Tabs and Actions -->
        <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8">
                <button 
                    wire:click="setActiveTab('calculation')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'calculation' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Perhitungan SHU
                </button>
                <button 
                    wire:click="setActiveTab('distribution')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'distribution' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Distribusi Anggota
                </button>
            </nav>

            <!-- Export Actions -->
            <div class="flex space-x-2">
                @if($activeTab === 'calculation')
                    <x-filament::button
                        wire:click="exportShuCalculationExcel"
                        color="success"
                        size="sm">
                        <x-heroicon-o-document-arrow-down class="w-4 h-4 mr-2" />
                        Export Excel
                    </x-filament::button>
                    <x-filament::button
                        wire:click="exportShuCalculationPdf"
                        color="danger"
                        size="sm">
                        <x-heroicon-o-document class="w-4 h-4 mr-2" />
                        Export PDF
                    </x-filament::button>
                @else
                    <x-filament::button
                        wire:click="exportShuDistributionExcel"
                        color="success"
                        size="sm">
                        <x-heroicon-o-document-arrow-down class="w-4 h-4 mr-2" />
                        Export Excel
                    </x-filament::button>
                    <x-filament::button
                        wire:click="exportShuDistributionPdf"
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
                $currentYear = now()->year;
                $lastYear = $currentYear - 1;
                
                // SHU tahun lalu
                $lastYearShu = \App\Models\ShuDistribution::where('cooperation_id', $cooperationId)
                    ->where('year', $lastYear)
                    ->first();
                
                // Data SHU tahun ini (jika sudah dihitung)
                $currentYearShu = \App\Models\ShuDistribution::where('cooperation_id', $cooperationId)
                    ->where('year', $currentYear)
                    ->first();

                // Jika belum ada data SHU tahun ini, hitung proyeksi
                if ($currentYearShu) {
                    $currentRevenue = $currentYearShu->total_revenue;
                    $currentExpenses = $currentYearShu->total_expenses;
                    $projectedShu = $currentYearShu->total_shu;
                } else {
                // Total revenue tahun ini
                $currentRevenue = \App\Models\Sale::where('cooperation_id', $cooperationId)
                        ->where('status', 'completed')
                    ->whereYear('sale_date', $currentYear)
                    ->sum('total_amount');
                
                // Total expenses tahun ini
                $currentExpenses = \App\Models\Purchase::where('cooperation_id', $cooperationId)
                    ->whereYear('purchase_date', $currentYear)
                    ->sum('total_amount') +
                    \App\Models\Expense::where('cooperation_id', $cooperationId)
                        ->where('status', 'approved')
                    ->whereYear('expense_date', $currentYear)
                    ->sum('amount');
                
                // Proyeksi SHU tahun ini
                $projectedShu = $currentRevenue - $currentExpenses;
                }
                
                // Total anggota yang mendapat bagian
                $membersWithShares = \App\Models\ShuMemberShare::whereHas('distribution', function($query) use ($cooperationId, $lastYear) {
                        $query->where('cooperation_id', $cooperationId)->where('year', $lastYear);
                    })
                    ->count();

                // Statistik tambahan SHU
                $totalMembers = \App\Models\User::where('cooperation_id', $cooperationId)->count();
                $shuParticipationRate = $totalMembers > 0 ? ($membersWithShares / $totalMembers) * 100 : 0;

                // Distribusi SHU berdasarkan komponen
                $savingsContribution = \App\Models\ShuMemberShare::whereHas('distribution', function($query) use ($cooperationId, $lastYear) {
                        $query->where('cooperation_id', $cooperationId)->where('year', $lastYear);
                    })
                    ->sum('savings_contribution');

                $transactionContribution = \App\Models\ShuMemberShare::whereHas('distribution', function($query) use ($cooperationId, $lastYear) {
                        $query->where('cooperation_id', $cooperationId)->where('year', $lastYear);
                    })
                    ->sum('transaction_contribution');

                // Persentase distribusi
                $totalContributions = $savingsContribution + $transactionContribution;
                $savingsPercentage = $totalContributions > 0 ? ($savingsContribution / $totalContributions) * 100 : 50;
                $transactionPercentage = $totalContributions > 0 ? ($transactionContribution / $totalContributions) * 100 : 50;

                // SHU trend data for the last year (12 months)
                $shuTrendData = [];
                $shuTrendLabels = [];
                for ($month = 1; $month <= 12; $month++) {
                    $monthName = \Carbon\Carbon::create()->month($month)->locale('id')->monthName;
                    $shuTrendLabels[] = substr($monthName, 0, 3); // Short month name

                    // For demonstration, we'll show monthly SHU distribution if available
                    // In a real scenario, this would be monthly data
                    $monthlyShu = \App\Models\ShuDistribution::where('cooperation_id', $cooperationId)
                        ->where('year', $lastYear)
                        ->first();

                    // Distribute the annual SHU across months for visualization
                    // In practice, this should be actual monthly data
                    $monthlyAmount = $monthlyShu ? $monthlyShu->total_shu / 12 : 0;
                    $shuTrendData[] = $monthlyAmount / 1000000; // Convert to millions
                }

                // Top members data for distribution tab
                $topMembers = [];
                $topMemberLabels = [];
                if ($activeTab === 'distribution') {
                    $topMembersData = \App\Models\ShuMemberShare::whereHas('distribution', function($query) use ($cooperationId, $lastYear) {
                            $query->where('cooperation_id', $cooperationId)->where('year', $lastYear);
                        })
                        ->with('user')
                        ->orderBy('shu_amount', 'desc')
                        ->limit(10)
                        ->get();

                    foreach ($topMembersData as $member) {
                        $topMemberLabels[] = $member->user->name ?? 'Unknown';
                        $topMembers[] = $member->shu_amount / 1000; // Convert to thousands
                    }
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
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">SHU {{ $lastYear }}</p>
                        <p class="text-lg font-semibold text-green-600 dark:text-green-400">
                            Rp {{ number_format($lastYearShu->total_shu ?? 0, 0, ',', '.') }}
                        </p>
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
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Proyeksi SHU {{ $currentYear }}</p>
                        <p class="text-lg font-semibold text-{{ $projectedShu >= 0 ? 'blue' : 'red' }}-600 dark:text-{{ $projectedShu >= 0 ? 'blue' : 'red' }}-400">
                            Rp {{ number_format($projectedShu, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Anggota Penerima</p>
                        <p class="text-lg font-semibold text-purple-600 dark:text-purple-400">{{ number_format($membersWithShares) }} Orang</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-orange-100 dark:bg-orange-900 rounded-lg">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Rata-rata per Anggota</p>
                        <p class="text-lg font-semibold text-orange-600 dark:text-orange-400">
                            Rp {{ number_format($membersWithShares > 0 ? ($lastYearShu->total_shu ?? 0) / $membersWithShares : 0, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-indigo-100 dark:bg-indigo-900 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Tingkat Partisipasi</p>
                        <p class="text-lg font-semibold text-indigo-600 dark:text-indigo-400">
                            {{ number_format($shuParticipationRate, 1) }}%
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $membersWithShares }}/{{ $totalMembers }} anggota</p>
                    </div>
                </div>
            </div>
        </div>

        @if($activeTab === 'calculation')
        <!-- SHU Calculation Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Komponen SHU -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Komponen Perhitungan SHU {{ $currentYear }}</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Pendapatan</span>
                        <span class="text-lg font-semibold text-green-600 dark:text-green-400">
                            Rp {{ number_format($currentRevenue, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Pengeluaran</span>
                        <span class="text-lg font-semibold text-red-600 dark:text-red-400">
                            Rp {{ number_format($currentExpenses, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="border-t pt-3">
                    <div class="border-t pt-3">
                        <div class="flex justify-between items-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <span class="text-base font-semibold text-gray-700 dark:text-gray-300">Proyeksi SHU</span>
                            <span class="text-xl font-bold text-{{ $projectedShu >= 0 ? 'blue' : 'red' }}-600 dark:text-{{ $projectedShu >= 0 ? 'blue' : 'red' }}-400">
                                Rp {{ number_format($projectedShu, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <!-- Detailed Revenue Breakdown -->
                    <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Rincian Pendapatan</h4>
                        <div class="space-y-1 text-xs">
                            @php
                                $salesRevenue = \App\Models\Sale::where('cooperation_id', $cooperationId)
                                    ->where('status', 'completed')
                                    ->whereYear('sale_date', $currentYear)
                                    ->sum('total_amount');

                                $savingsRevenue = \App\Models\SavingsTransaction::where('cooperation_id', $cooperationId)
                                    ->whereYear('transaction_date', $currentYear)
                                    ->where('status', 'completed')
                                    ->sum('amount');
                            @endphp
                            <div class="flex justify-between">
                                <span>Penjualan Produk</span>
                                <span>Rp {{ number_format($salesRevenue, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Simpanan Anggota</span>
                                <span>Rp {{ number_format($savingsRevenue, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Expense Breakdown -->
                    <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Rincian Pengeluaran</h4>
                        <div class="space-y-1 text-xs">
                            @php
                                $purchaseExpenses = \App\Models\Purchase::where('cooperation_id', $cooperationId)
                                    ->whereYear('purchase_date', $currentYear)
                                    ->sum('total_amount');

                                $operationalExpenses = \App\Models\Expense::where('cooperation_id', $cooperationId)
                                    ->where('status', 'approved')
                                    ->whereYear('expense_date', $currentYear)
                                    ->sum('amount');
                            @endphp
                            <div class="flex justify-between">
                                <span>Pembelian Barang</span>
                                <span>Rp {{ number_format($purchaseExpenses, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Pengeluaran Operasional</span>
                                <span>Rp {{ number_format($operationalExpenses, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trend SHU -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Trend SHU Bulanan ({{ $lastYear }})</h3>
                <div class="h-64">
                    <canvas id="shuTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- SHU Distribution Rules -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Aturan Distribusi SHU</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <h4 class="font-medium text-gray-900 dark:text-white">Berdasarkan Simpanan ({{ number_format($savingsPercentage, 1) }}%)</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Bagian SHU yang didistribusikan berdasarkan total simpanan anggota selama tahun berjalan.
                        Semakin besar simpanan, semakin besar bagian SHU yang diterima.
                    </p>
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <p class="text-xs text-blue-600 dark:text-blue-400">
                            Formula: (Simpanan Anggota / Total Simpanan) × {{ number_format($savingsPercentage, 1) }}% × Total SHU
                        </p>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                            Kontribusi: Rp {{ number_format($savingsContribution, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
                <div class="space-y-3">
                    <h4 class="font-medium text-gray-900 dark:text-white">Berdasarkan Transaksi ({{ number_format($transactionPercentage, 1) }}%)</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Bagian SHU yang didistribusikan berdasarkan total transaksi/pembelian anggota selama tahun berjalan.
                        Semakin aktif bertransaksi, semakin besar bagian SHU yang diterima.
                    </p>
                    <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <p class="text-xs text-green-600 dark:text-green-400">
                            Formula: (Transaksi Anggota / Total Transaksi) × {{ number_format($transactionPercentage, 1) }}% × Total SHU
                        </p>
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                            Kontribusi: Rp {{ number_format($transactionContribution, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- SHU Performance Insights -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Analisis Kinerja SHU</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @php
                    // Growth rate calculation
                    $previousYearShu = \App\Models\ShuDistribution::where('cooperation_id', $cooperationId)
                        ->where('year', $lastYear - 1)
                        ->first();

                    $shuGrowth = 0;
                    if ($previousYearShu && $lastYearShu) {
                        $shuGrowth = (($lastYearShu->total_shu - $previousYearShu->total_shu) / $previousYearShu->total_shu) * 100;
                    }

                    // Profitability ratio
                    $totalRevenueLastYear = $lastYearShu ? $lastYearShu->total_revenue : 0;
                    $profitabilityRatio = $totalRevenueLastYear > 0 ? (($lastYearShu->total_shu ?? 0) / $totalRevenueLastYear) * 100 : 0;

                    // Member satisfaction (paid vs total)
                    $totalShares = \App\Models\ShuMemberShare::whereHas('distribution', function($query) use ($cooperationId, $lastYear) {
                        $query->where('cooperation_id', $cooperationId)->where('year', $lastYear);
                    })->count();

                    $paidShares = \App\Models\ShuMemberShare::whereHas('distribution', function($query) use ($cooperationId, $lastYear) {
                        $query->where('cooperation_id', $cooperationId)->where('year', $lastYear);
                    })->where('status', 'paid')->count();

                    $paymentRate = $totalShares > 0 ? ($paidShares / $totalShares) * 100 : 0;
                @endphp

                <div class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-green-600 dark:text-green-400">Pertumbuhan SHU</p>
                            <p class="text-2xl font-bold text-green-700 dark:text-green-300">
                                {{ $shuGrowth >= 0 ? '+' : '' }}{{ number_format($shuGrowth, 1) }}%
                            </p>
                        </div>
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <p class="text-xs text-green-600 dark:text-green-400 mt-2">Dibandingkan tahun sebelumnya</p>
                </div>

                <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Rasio Profitabilitas</p>
                            <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                                {{ number_format($profitabilityRatio, 1) }}%
                            </p>
                        </div>
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">SHU terhadap total pendapatan</p>
                </div>

                <div class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-purple-600 dark:text-purple-400">Tingkat Pembayaran</p>
                            <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">
                                {{ number_format($paymentRate, 1) }}%
                            </p>
                        </div>
                        <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <p class="text-xs text-purple-600 dark:text-purple-400 mt-2">{{ $paidShares }}/{{ $totalShares }} anggota telah dibayar</p>
                </div>
            </div>
        </div>
        @else
        <!-- Distribution Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Distribusi per Anggota -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top 10 Penerima SHU</h3>
                <div class="h-64">
                    <canvas id="topMembersChart"></canvas>
                </div>
            </div>

            <!-- Komponen Distribusi -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Komponen Distribusi</h3>
                <div class="h-64">
                    <canvas id="distributionComponentChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Payment Status -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status Pembayaran SHU {{ $lastYear }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @php
                    $paidMembers = \App\Models\ShuMemberShare::whereHas('distribution', function($query) use ($cooperationId, $lastYear) {
                            $query->where('cooperation_id', $cooperationId)->where('year', $lastYear);
                        })
                        ->where('status', 'paid')
                        ->count();
                    
                    $pendingMembers = \App\Models\ShuMemberShare::whereHas('distribution', function($query) use ($cooperationId, $lastYear) {
                            $query->where('cooperation_id', $cooperationId)->where('year', $lastYear);
                        })
                        ->where('status', 'pending')
                        ->count();
                    
                    $totalDistributed = \App\Models\ShuMemberShare::whereHas('distribution', function($query) use ($cooperationId, $lastYear) {
                            $query->where('cooperation_id', $cooperationId)->where('year', $lastYear);
                        })
                        ->where('status', 'paid')
                        ->sum('shu_amount');
                @endphp

                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-600 dark:text-green-400">Sudah Dibayar</p>
                            <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ $paidMembers }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Belum Dibayar</p>
                            <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-300">{{ $pendingMembers }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Total Terdistribusi</p>
                            <p class="text-lg font-bold text-blue-700 dark:text-blue-300">Rp {{ number_format($totalDistributed, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Detail Table akan ditampilkan di bawah melalui Livewire table -->
        <div class="mt-6">
            {{ $this->table }}
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
        });

        function initializeCharts() {
            @if($activeTab === 'calculation')
            // Grafik Trend SHU
            const shuTrendCtx = document.getElementById('shuTrendChart')?.getContext('2d');
            if (shuTrendCtx) {
                new Chart(shuTrendCtx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($shuTrendLabels) !!},
                        datasets: [{
                            label: 'SHU (Juta Rupiah)',
                            data: {!! json_encode($shuTrendData) !!},
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            tension: 0.4,
                            fill: true
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
                                        return value + 'M';
                                    }
                                }
                            }
                        }
                    }
                });
            }
            @else
            // Grafik Top Members
            const topMembersCtx = document.getElementById('topMembersChart')?.getContext('2d');
            if (topMembersCtx) {
                new Chart(topMembersCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($topMemberLabels) !!},
                        datasets: [{
                            label: 'Bagian SHU (Ribu Rupiah)',
                            data: {!! json_encode($topMembers) !!},
                            backgroundColor: 'rgba(59, 130, 246, 0.8)'
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
                                        return value + 'K';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Grafik Komponen Distribusi
            const distributionComponentCtx = document.getElementById('distributionComponentChart')?.getContext('2d');
            if (distributionComponentCtx) {
                new Chart(distributionComponentCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Bagian Simpanan', 'Bagian Transaksi'],
                        datasets: [{
                            data: [{!! $savingsPercentage !!}, {!! $transactionPercentage !!}],
                            backgroundColor: [
                                'rgb(34, 197, 94)',
                                'rgb(59, 130, 246)'
                            ]
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
            @endif
        }

        // Re-initialize charts when tab changes
        Livewire.on('tabChanged', () => {
            setTimeout(() => {
                initializeCharts();
            }, 100);
        });
    </script>
    @endpush
</x-filament-panels::page>
