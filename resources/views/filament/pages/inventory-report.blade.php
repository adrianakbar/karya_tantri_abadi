<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header with Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Produk</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ \App\Models\Product::where('cooperation_id', auth()->user()->cooperation_id)->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pembelian</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ \App\Models\Purchase::where('cooperation_id', auth()->user()->cooperation_id)->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H3"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Penjualan</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ \App\Models\Sale::where('cooperation_id', auth()->user()->cooperation_id)->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Stok Rendah</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ \App\Models\Product::where('cooperation_id', auth()->user()->cooperation_id)->whereColumn('current_stock', '<=', 'min_stock')->where('current_stock', '>', 0)->count() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button
                        type="button"
                        wire:click="setActiveTab('stock')"
                        class="py-4 px-6 text-sm font-medium border-b-2 {{ $activeTab === 'stock' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300' }}"
                    >
                        <div class="flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            Stok Barang
                        </div>
                    </button>

                    <button
                        type="button"
                        wire:click="setActiveTab('purchases')"
                        class="py-4 px-6 text-sm font-medium border-b-2 {{ $activeTab === 'purchases' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300' }}"
                    >
                        <div class="flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 3H3m4 10L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v4a2 2 0 01-2 2H9a2 2 0 01-2-2v-4m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                            </svg>
                            Pembelian
                        </div>
                    </button>

                    <button
                        type="button"
                        wire:click="setActiveTab('sales')"
                        class="py-4 px-6 text-sm font-medium border-b-2 {{ $activeTab === 'sales' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300' }}"
                    >
                        <div class="flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Penjualan
                        </div>
                    </button>

                    <button
                        type="button"
                        wire:click="setActiveTab('profit_loss')"
                        class="py-4 px-6 text-sm font-medium border-b-2 {{ $activeTab === 'profit_loss' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300' }}"
                    >
                        <div class="flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Laba Rugi
                        </div>
                    </button>
                </nav>
            </div>

            <!-- Export Actions -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 flex justify-end space-x-3">
                @if($activeTab === 'stock')
                    <x-filament::button
                        wire:click="exportInventoryExcel('stock')"
                        color="success"
                        size="sm">
                        <x-heroicon-o-document-arrow-down class="w-4 h-4 mr-2" />
                        Export Excel
                    </x-filament::button>
                    <x-filament::button
                        wire:click="exportInventoryPdf('stock')"
                        color="danger"
                        size="sm">
                        <x-heroicon-o-document class="w-4 h-4 mr-2" />
                        Export PDF
                    </x-filament::button>
                @elseif($activeTab === 'purchases')
                    <x-filament::button
                        wire:click="exportInventoryExcel('purchases')"
                        color="success"
                        size="sm">
                        <x-heroicon-o-document-arrow-down class="w-4 h-4 mr-2" />
                        Export Pembelian Excel
                    </x-filament::button>
                    <x-filament::button
                        wire:click="exportInventoryPdf('purchases')"
                        color="danger"
                        size="sm">
                        <x-heroicon-o-document class="w-4 h-4 mr-2" />
                        Export Pembelian PDF
                    </x-filament::button>
                @elseif($activeTab === 'sales')
                    <x-filament::button
                        wire:click="exportInventoryExcel('sales')"
                        color="success"
                        size="sm">
                        <x-heroicon-o-document-arrow-down class="w-4 h-4 mr-2" />
                        Export Penjualan Excel
                    </x-filament::button>
                    <x-filament::button
                        wire:click="exportInventoryPdf('sales')"
                        color="danger"
                        size="sm">
                        <x-heroicon-o-document class="w-4 h-4 mr-2" />
                        Export Penjualan PDF
                    </x-filament::button>
                @elseif($activeTab === 'profit_loss')
                    <x-filament::button
                        wire:click="exportInventoryExcel('profit_loss')"
                        color="success"
                        size="sm">
                        <x-heroicon-o-document-arrow-down class="w-4 h-4 mr-2" />
                        Export Laba Rugi Excel
                    </x-filament::button>
                    <x-filament::button
                        wire:click="exportInventoryPdf('profit_loss')"
                        color="danger"
                        size="sm">
                        <x-heroicon-o-document class="w-4 h-4 mr-2" />
                        Export Laba Rugi PDF
                    </x-filament::button>
                @endif
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                @if($activeTab === 'stock')
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Laporan Persediaan Barang</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Menampilkan data stok produk saat ini dengan informasi kategori, harga, dan jumlah tersedia.</p>
                        
                        <!-- Stock Alerts -->
                        @php
                            $lowStockCount = \App\Models\Product::where('cooperation_id', auth()->user()->cooperation_id)
                                ->whereColumn('current_stock', '<=', 'min_stock')
                                ->where('current_stock', '>', 0)
                                ->count();
                        @endphp
                        @if($lowStockCount > 0)
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4 mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-sm font-medium text-red-800 dark:text-red-200">Peringatan Stok Rendah</h4>
                                        <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                                            {{ $lowStockCount }} produk memiliki stok di bawah minimum.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @elseif($activeTab === 'purchases')
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Laporan Pembelian</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Menampilkan data pembelian produk dengan informasi supplier, tanggal, dan total pembelian.</p>
                    </div>
                @elseif($activeTab === 'sales')
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Laporan Penjualan</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Menampilkan data penjualan harian dan bulanan dengan informasi pelanggan, produk, dan total penjualan.</p>
                    </div>
                @elseif($activeTab === 'profit_loss')
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Laporan Laba Rugi Penjualan</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Analisis laba rugi per produk dengan perhitungan margin dan persentase keuntungan.</p>
                    </div>
                @endif

                <!-- Table Content -->
                <div class="mt-6">
                    {{ $this->table }}
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
