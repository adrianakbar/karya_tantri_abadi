<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Title and Welcome -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-xl shadow-lg p-6">
            <h1 class="text-2xl font-bold">Dashboard Laporan Karya Tantri Abadi</h1>
            <p class="text-blue-100 mt-1">Ringkasan data keuangan, aktivitas simpan pinjam, dan simpan pinjam Karya Tantri Abadi.</p>
        </div>

        <!-- Summary Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Revenue Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5 border-l-4 border-emerald-500">
                <div class="flex items-center">
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg">
                        <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pendapatan Bulan Ini</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">
                            Rp {{ number_format($this->getMonthlyRevenue(), 0, '.', ',') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Expenses Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5 border-l-4 border-red-500">
                <div class="flex items-center">
                    <div class="p-3 bg-red-50 dark:bg-red-900/30 rounded-lg">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 3H3m4 10L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v4a2 2 0 01-2 2H9a2 2 0 01-2-2v-4m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pengeluaran Bulan Ini</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">
                            Rp {{ number_format($this->getMonthlyExpenses(), 0, '.', ',') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Savings Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Simpanan</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">
                            Rp {{ number_format($this->getTotalSavings(), 0, '.', ',') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Outstanding Loans Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
                        <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Pinjaman Aktif</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">
                            Rp {{ number_format($this->getTotalOutstandingLoans(), 0, '.', ',') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Second Row: Additional Stats and SHU Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- SHU Statistics -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow space-y-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white border-b pb-2">Informasi SHU</h3>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500">SHU Tahun Lalu:</span>
                    <span class="font-bold text-gray-800 dark:text-gray-200">
                        Rp {{ number_format($this->getLastYearShu(), 0, '.', ',') }}
                    </span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500">Proyeksi SHU Tahun Ini:</span>
                    <span class="font-bold text-blue-600 dark:text-blue-400">
                        Rp {{ number_format($this->getProjectedShu(), 0, '.', ',') }}
                    </span>
                </div>
                <div class="pt-2 border-t text-xs text-gray-500">
                    Proyeksi SHU didasarkan pada selisih total penjualan dengan total pembelian dan pengeluaran tahun ini.
                </div>
            </div>

            <!-- Health and Member Stats -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow space-y-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white border-b pb-2">Kesehatan Organisasi</h3>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500">Jumlah Anggota:</span>
                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ $this->getTotalMembers() }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500">Status Keuangan:</span>
                    @if($this->getMonthlyRevenue() >= $this->getMonthlyExpenses())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Sehat
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Defisit
                        </span>
                    @endif
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500">Pinjaman Aktif Berjalan:</span>
                    <span class="font-bold text-purple-600">{{ $this->getActiveLoansCount() }}</span>
                </div>
            </div>

            <!-- Low Stock Alerts -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow space-y-4">
                <div class="flex justify-between items-center border-b pb-2">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Peringatan Stok</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                        {{ $this->getLowStockCount() }}
                    </span>
                </div>
                
                <div class="max-h-32 overflow-y-auto space-y-2">
                    @forelse($this->getLowStockProducts() as $product)
                        <div class="flex justify-between items-center text-xs p-2 bg-amber-50 dark:bg-amber-900/10 rounded">
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $product->name }}</span>
                            <span class="text-red-600 dark:text-red-400 font-bold">Stok: {{ $product->current_stock }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">Semua produk memiliki stok yang cukup.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Third Row: Recent Transactions and Quick Access Links -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Transactions Table -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow lg:col-span-2 space-y-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white border-b pb-2">Transaksi Terbaru</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No Transaksi</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipe</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($this->getRecentTransactions() as $tx)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-900 dark:text-white">{{ $tx['number'] }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                            {{ $tx['type'] === 'Penjualan' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $tx['type'] === 'Pembelian' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $tx['type'] === 'Simpanan' ? 'bg-purple-100 text-purple-800' : '' }}">
                                            {{ $tx['type'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-500">{{ $tx['date']->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-bold text-gray-800 dark:text-gray-200">
                                        Rp {{ number_format($tx['amount'], 0, '.', ',') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">Tidak ada transaksi terbaru.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Access Links -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow space-y-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white border-b pb-2">Akses Cepat Laporan</h3>
                <div class="grid grid-cols-1 gap-2">
                    <a href="/admin/financial-report" class="flex items-center p-3 rounded-lg border border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700 transition">
                        <div class="p-2 bg-blue-100 rounded text-blue-600 mr-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Laporan Keuangan</span>
                    </a>

                    <a href="/admin/savings-report" class="flex items-center p-3 rounded-lg border border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700 transition">
                        <div class="p-2 bg-green-100 rounded text-green-600 mr-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Laporan Simpanan</span>
                    </a>

                    <a href="/admin/loan-report" class="flex items-center p-3 rounded-lg border border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700 transition">
                        <div class="p-2 bg-purple-100 rounded text-purple-600 mr-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Laporan Pinjaman</span>
                    </a>

                    <a href="/admin/expense-report" class="flex items-center p-3 rounded-lg border border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700 transition">
                        <div class="p-2 bg-red-100 rounded text-red-600 mr-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Laporan Pengeluaran</span>
                    </a>

                    <a href="/admin/shu-report" class="flex items-center p-3 rounded-lg border border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700 transition">
                        <div class="p-2 bg-indigo-100 rounded text-indigo-600 mr-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Laporan SHU</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
