<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RecentActivitiesWidget extends BaseWidget
{
    protected static ?string $heading = 'Aktivitas Terbaru';

    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        $cooperationId = Auth::user()?->cooperation_id;

        return ActivityLog::query()
            ->when($cooperationId, fn ($query) => $query->forCooperation($cooperationId))
            ->with('user')
            ->whereDate('created_at', now()->today()) // Default ke hari ini
            ->latest('created_at')
            ->limit(50);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('created_at')
                ->label('Waktu')
                ->dateTime('d/m/Y H:i:s')
                ->description(fn ($record) => $this->formatRelativeTime($record->created_at))
                ->sortable(),

            Tables\Columns\TextColumn::make('user.name')
                ->label('Pengguna')
                ->searchable(),

            Tables\Columns\TextColumn::make('action')
                ->label('Aksi')
                ->formatStateUsing(fn (string $state): string => $this->translateAction($state))
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'create' => 'success',
                    'update' => 'warning',
                    'delete' => 'danger',
                    'login' => 'info',
                    'logout' => 'gray',
                    'view' => 'primary',
                    default => 'secondary',
                }),

            Tables\Columns\TextColumn::make('module')
                ->label('Modul')
                ->formatStateUsing(fn (string $state): string => $this->translateModule($state))
                ->badge()
                ->color('primary'),

            Tables\Columns\TextColumn::make('description')
                ->label('Deskripsi')
                ->formatStateUsing(fn (string $state): string => $this->translateDescription($state))
                ->limit(50)
                ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                    $state = $column->getState();
                    $translated = $this->translateDescription($state);
                    if (strlen($translated) <= 50) {
                        return null;
                    }
                    return $translated;
                }),

            Tables\Columns\TextColumn::make('ip_address')
                ->label('Alamat IP')
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    protected function getTableActions(): array
    {
        return [];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('period')
                ->label('Periode')
                ->options([
                    'today' => 'Hari Ini',
                    '1day' => '1 Hari yang Lalu',
                    '2days' => '2 Hari yang Lalu',
                    '3days' => '3 Hari yang Lalu',
                    '7days' => '7 Hari Terakhir',
                    '30days' => '30 Hari Terakhir',
                ])
                ->default('today')
                ->query(function (Builder $query, array $data) {
                    if (!isset($data['value']) || empty($data['value'])) {
                        return $query;
                    }

                    return match ($data['value']) {
                        'today' => $query->whereDate('created_at', now()->today()),
                        '1day' => $query->whereDate('created_at', now()->subDay()->startOfDay()),
                        '2days' => $query->whereDate('created_at', now()->subDays(2)->startOfDay()),
                        '3days' => $query->whereDate('created_at', now()->subDays(3)->startOfDay()),
                        '7days' => $query->where('created_at', '>=', now()->subDays(7)->startOfDay()),
                        '30days' => $query->where('created_at', '>=', now()->subDays(30)->startOfDay()),
                        default => $query,
                    };
                }),
        ];
    }

    public function getTableRecordsPerPageSelectOptions(): array
    {
        return [5, 10, 15];
    }

    public function getDefaultTableRecordsPerPageSelectOption(): int
    {
        return 10;
    }

    /**
     * Translate action to Indonesian
     */
    protected function translateAction(string $action): string
    {
        return match ($action) {
            'create' => 'Buat',
            'update' => 'Ubah',
            'delete' => 'Hapus',
            'login' => 'Masuk',
            'logout' => 'Keluar',
            'view' => 'Lihat',
            'export' => 'Ekspor',
            'import' => 'Impor',
            'approve' => 'Setujui',
            'reject' => 'Tolak',
            'created' => 'Dibuat',
            'updated' => 'Diubah',
            'deleted' => 'Dihapus',
            default => ucfirst(str_replace('_', ' ', $action)),
        };
    }

    /**
     * Translate module to Indonesian
     */
    protected function translateModule(string $module): string
    {
        return match ($module) {
            'product' => 'Produk',
            'purchase' => 'Pembelian',
            'sale' => 'Penjualan',
            'savings' => 'Tabungan',
            'loan' => 'Pinjaman',
            'expense' => 'Pengeluaran',
            'user' => 'Pengguna',
            'auth' => 'Autentikasi',
            'report' => 'Laporan',
            'system' => 'Sistem',
            'stock' => 'Stok',
            'inventory' => 'Inventori',
            'member' => 'Anggota',
            'cooperation' => 'Organisasi',
            'settings' => 'Pengaturan',
            default => ucfirst(str_replace('_', ' ', $module)),
        };
    }

    /**
     * Translate description to Indonesian
     */
    protected function translateDescription(string $description): string
    {
        // Common patterns translation
        $translations = [
            'Created' => 'Membuat',
            'Updated' => 'Mengubah',
            'Deleted' => 'Menghapus',
            'Viewed' => 'Melihat',
            'Exported' => 'Mengekspor',
            'Imported' => 'Mengimpor',
            'Logged in' => 'Masuk ke sistem',
            'Logged out' => 'Keluar dari sistem',
            'Product' => 'Produk',
            'Purchase' => 'Pembelian',
            'Sale' => 'Penjualan',
            'Savings' => 'Tabungan',
            'Loan' => 'Pinjaman',
            'Expense' => 'Pengeluaran',
            'User' => 'Pengguna',
            'Report' => 'Laporan',
            'Stock' => 'Stok',
            'Inventory' => 'Inventori',
            'Member' => 'Anggota',
            'transaction' => 'transaksi',
            'payment' => 'pembayaran',
            'adjustment' => 'penyesuaian',
            'category' => 'kategori',
            'type' => 'tipe',
            'successfully' => 'berhasil',
            'failed' => 'gagal',
        ];

        $translated = $description;
        foreach ($translations as $english => $indonesian) {
            $translated = str_replace($english, $indonesian, $translated);
        }

        return $translated;
    }

    /**
     * Format relative time in Indonesian
     */
    protected function formatRelativeTime($datetime): string
    {
        if (!$datetime) {
            return '-';
        }

        // Ensure we have a Carbon instance
        if (!$datetime instanceof \Carbon\Carbon) {
            $datetime = \Carbon\Carbon::parse($datetime);
        }

        $now = now();
        
        // Calculate the difference in seconds (absolute value)
        $diffInSeconds = abs($now->diffInSeconds($datetime, false));
        
        // Check if datetime is in the future
        if ($datetime->isFuture()) {
            return 'Baru saja';
        }

        if ($diffInSeconds < 60) {
            return 'Baru saja';
        } elseif ($diffInSeconds < 3600) {
            $minutes = floor($diffInSeconds / 60);
            return $minutes . ' menit yang lalu';
        } elseif ($diffInSeconds < 86400) {
            $hours = floor($diffInSeconds / 3600);
            return $hours . ' jam yang lalu';
        } elseif ($diffInSeconds < 604800) {
            $days = floor($diffInSeconds / 86400);
            return $days . ' hari yang lalu';
        } elseif ($diffInSeconds < 2592000) {
            $weeks = floor($diffInSeconds / 604800);
            return $weeks . ' minggu yang lalu';
        } else {
            $months = floor($diffInSeconds / 2592000);
            return $months . ' bulan yang lalu';
        }
    }
}
