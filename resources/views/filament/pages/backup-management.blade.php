<x-filament-panels::page>
    <div class="space-y-6">

        <!-- Info Card -->
        <div
            class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg p-4">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 mt-0.5">
                    <x-heroicon-o-information-circle class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-medium text-primary-800 dark:text-primary-200">Informasi Backup Otomatis</h3>
                    <p class="text-sm text-primary-600 dark:text-primary-300 mt-1">
                        Backup database otomatis dijadwalkan setiap hari Senin pukul 02:00 WIB.
                        Klik tombol Download untuk mengunduh file backup yang tersedia.
                    </p>
                </div>
            </div>
        </div>

        <!-- Backup Files Table -->
        @if (count($backupFiles) > 0)
            <div class="rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-5 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">
                        File Backup Tersedia
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800/50">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        File Details</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Ukuran</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Tanggal Backup</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($backupFiles as $backup)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center space-x-3">
                                                <div class="flex-shrink-0">
                                                    <div
                                                        class="h-10 w-10 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center">
                                                        <x-heroicon-o-archive-box
                                                            class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p
                                                        class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                        {{ $backup['name'] }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                        {{ $backup['path'] }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                                <x-heroicon-o-document-text class="h-3 w-3 mr-1" />
                                                {{ $backup['size'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">{{ $backup['date'] }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium 
                                                @if ($backup['age_color'] === 'success') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
                                                @elseif($backup['age_color'] === 'warning') 
                                                    bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300
                                                @else 
                                                    bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 @endif">
                                                @if ($backup['age_color'] === 'success')
                                                    <x-heroicon-o-check-circle class="h-3 w-3 mr-1" />
                                                @elseif($backup['age_color'] === 'warning')
                                                    <x-heroicon-o-clock class="h-3 w-3 mr-1" />
                                                @else
                                                    <x-heroicon-o-exclamation-triangle class="h-3 w-3 mr-1" />
                                                @endif
                                                {{ $backup['age'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="flex items-center justify-end space-x-2">
                                                <!-- Download Button -->
                                                <button type="button"
                                                    wire:click="downloadBackup('{{ $backup['name'] }}')"
                                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-900 transition-colors duration-150">
                                                    <x-heroicon-o-arrow-down-tray class="h-4 w-4 mr-2" />
                                                    Download
                                                </button>

                                                <!-- Delete Button -->
                                                <button type="button"
                                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-900 transition-colors duration-150"
                                                    onclick="confirmDelete('{{ $backup['name'] }}')">
                                                    <x-heroicon-o-trash class="h-4 w-4 mr-2" />
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div
                class="text-center py-16 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                <div
                    class="mx-auto h-16 w-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
                    <x-heroicon-o-archive-box class="h-8 w-8 text-gray-400 dark:text-gray-500" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Belum Ada File Backup</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                    Buat backup pertama dengan menekan tombol "Backup Database Sekarang" di bagian atas halaman.
                </p>
            </div>
        @endif
    </div>

    <script>
        function confirmDelete(fileName) {
            // Create a beautiful custom confirm dialog
            const isConfirmed = confirm(
                `⚠️ Konfirmasi Hapus File Backup\n\n` +
                `Apakah Anda yakin ingin menghapus file backup:\n"${fileName}"\n\n` +
                `Tindakan ini tidak dapat dibatalkan dan file akan hilang secara permanen.`
            );

            if (isConfirmed) {
                @this.call('deleteBackup', fileName);
            }
        }

        // Listen for download file event from Livewire
        document.addEventListener('livewire:init', function() {
            Livewire.on('downloadFile', (event) => {
                const fileName = event.fileName;
                if (fileName) {
                    // Create download URL
                    const downloadUrl = `/download-backup/${encodeURIComponent(fileName)}`;

                    // Create temporary link and trigger download
                    const link = document.createElement('a');
                    link.href = downloadUrl;
                    link.download = fileName;
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            });
        });
    </script>
</x-filament-panels::page>
