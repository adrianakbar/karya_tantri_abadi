<x-filament-panels::page>
    <div class="space-y-6">
        
        <!-- Header Section -->
        <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Log Aktivitas (Audit Trail)
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Pantau semua aktivitas pengguna, perubahan data, dan riwayat login dalam sistem
                    </p>
                </div>
            </div>
        </div>

        <!-- Navigation Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Riwayat Login -->
            <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/20">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Riwayat Login</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($todayLogins) }}</p>
                        <p class="text-sm text-gray-500">Hari ini</p>
                    </div>
                </div>
                <div class="mt-4">
                    <x-filament::button 
                        tag="a" 
                        href="{{ \App\Filament\Resources\AuthLogResource::getUrl('index') }}"
                        size="sm"
                        color="success"
                        class="w-full"
                    >
                        Lihat Riwayat Login
                    </x-filament::button>
                </div>
            </div>

            <!-- Perubahan Data -->
            <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900/20">
                        <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Perubahan Data</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($todayDataChanges) }}</p>
                        <p class="text-sm text-gray-500">Hari ini</p>
                    </div>
                </div>
                <div class="mt-4">
                    <x-filament::button 
                        tag="a" 
                        href="{{ \App\Filament\Resources\DataChangeLogResource::getUrl('index') }}"
                        size="sm"
                        color="warning"
                        class="w-full"
                    >
                        Lihat Perubahan Data
                    </x-filament::button>
                </div>
            </div>

            <!-- Failed Logins Warning -->
            <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/20">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.768 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Login Gagal</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($todayFailedLogins) }}</p>
                        <p class="text-sm text-gray-500">Hari ini</p>
                    </div>
                </div>
                <div class="mt-4">
                    @if($todayFailedLogins > 5)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Perhatian Tinggi
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Normal
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Security Alerts -->
        @if($todayFailedLogins > 5)
        <div class="rounded-lg bg-red-50 p-4 border border-red-200 dark:bg-red-900/20 dark:border-red-800">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                        Peringatan Keamanan
                    </h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                        <p>
                            Terdeteksi {{ $todayFailedLogins }} percobaan login gagal hari ini. 
                            Silakan periksa <a href="{{ \App\Filament\Resources\AuthLogResource::getUrl('index') }}" class="font-medium underline">riwayat login</a> untuk detail lebih lanjut.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</x-filament-panels::page>
