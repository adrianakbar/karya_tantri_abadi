<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BackupManagement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Backup Data';
    protected static ?string $title = 'Manajemen Backup Database';
    protected static string $view = 'filament.pages.backup-management';
    protected static ?int $navigationSort = 10;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public $backupFiles = [];

    public function mount()
    {
        $this->backupFiles = $this->getBackupList();
    }

    public function refreshBackups()
    {
        $this->backupFiles = $this->getBackupList();
        Notification::make()
            ->title('Data Backup Diperbarui')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backup_database')
                ->label('Backup Database Sekarang')
                ->icon('heroicon-o-circle-stack')
                ->color('success')
                ->action(function () {
                    try {
                        Artisan::call('backup:run', ['--only-db' => true]);
                        $this->refreshBackups();
                        Notification::make()
                            ->title('Backup Database Berhasil')
                            ->body('Backup database telah berhasil dibuat pada ' . now()->format('d M Y H:i:s'))
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Backup Database Gagal')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Backup Database')
                ->modalDescription('Apakah Anda yakin ingin membuat backup database sekarang? Proses ini akan membuat file backup baru.')
                ->modalSubmitActionLabel('Ya, Backup Sekarang'),

            Action::make('refresh')
                ->label('Refresh Data')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => $this->refreshBackups()),
        ];
    }

    public function downloadBackup($fileName)
    {
        try {
            $disk = Storage::disk(config('backup.backup.destination.disks')[0] ?? 'local');
            $backupName = config('backup.backup.name');
            $filePath = $backupName . '/' . $fileName;
            
            if (!$disk->exists($filePath)) {
                Notification::make()
                    ->title('File Tidak Ditemukan')
                    ->body('File backup tidak ditemukan di storage.')
                    ->danger()
                    ->send();
                return;
            }

            // Log download activity
            Log::info("Backup file downloaded by user: " . Auth::user()->email . " - File: " . $fileName);

            // Set session untuk validasi download
            session(['backup_download_validated_' . $fileName => true]);
            
            // Trigger download via JavaScript
            $this->dispatch('downloadFile', fileName: $fileName);
            
            Notification::make()
                ->title('Download Dimulai')
                ->body('File backup akan segera didownload.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Download Gagal')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function deleteBackup($fileName)
    {
        try {
            $disk = Storage::disk(config('backup.backup.destination.disks')[0] ?? 'local');
            $backupName = config('backup.backup.name');
            $filePath = $backupName . '/' . $fileName;
            
            if ($disk->exists($filePath)) {
                $disk->delete($filePath);
                
                Log::info("Backup file deleted by user: " . Auth::user()->email . " - File: " . $fileName);
                
                $this->refreshBackups();
                
                Notification::make()
                    ->title('File Backup Dihapus')
                    ->body('File backup berhasil dihapus dari storage.')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('File Tidak Ditemukan')
                    ->body('File backup tidak ditemukan di storage.')
                    ->warning()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Hapus File Gagal')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getBackupList()
    {
        $disk = Storage::disk(config('backup.backup.destination.disks')[0] ?? 'local');
        $backupName = config('backup.backup.name');
        
        if (!$disk->exists($backupName)) {
            return [];
        }
        
        $files = $disk->files($backupName);
        $backups = [];
        
        foreach ($files as $file) {
            $lastModified = $disk->lastModified($file);
            $carbonDate = Carbon::createFromTimestamp($lastModified);
            
            $backups[] = [
                'name' => basename($file),
                'size' => $this->formatBytes($disk->size($file)),
                'date' => $carbonDate->format('d M Y H:i:s'),
                'created_at' => $carbonDate,
                'age' => $carbonDate->diffForHumans(),
                'path' => $file,
                'age_color' => $this->getAgeColor($carbonDate),
            ];
        }
        
        return collect($backups)->sortByDesc('created_at')->values()->all();
    }
    
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }

    private function getAgeColor($createdAt)
    {
        $daysDiff = $createdAt->diffInDays(now());
        
        if ($daysDiff <= 7) {
            return 'success'; // Hijau untuk backup baru (< 1 minggu)
        } elseif ($daysDiff <= 30) {
            return 'warning'; // Kuning untuk backup sedang (1 minggu - 1 bulan)
        } else {
            return 'danger'; // Merah untuk backup lama (> 1 bulan)
        }
    }
}