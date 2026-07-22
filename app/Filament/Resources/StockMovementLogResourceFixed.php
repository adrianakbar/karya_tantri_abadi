<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementLogResource\Pages;
use App\Models\StockMovementLog;
use App\Models\Product;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class StockMovementLogResource extends Resource
{
    protected static ?string $model = StockMovementLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $modelLabel = 'Riwayat Stok';
    protected static ?string $pluralModelLabel = 'Riwayat Perubahan Stok';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'stock-movement-logs';

    public static function getNavigationGroup(): ?string
    {
        return 'Barang & Inventaris';
    }

    public static function getNavigationLabel(): string
    {
        return 'Riwayat Stok';
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            $user = auth()->user();
            if (!$user || !$user->cooperation_id) {
                return null;
            }
            
            $today = StockMovementLog::where('cooperation_id', $user->cooperation_id)
                ->whereDate('created_at', today())
                ->count();
            
            return $today > 0 ? (string) $today : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable(),
                    
                Forms\Components\Select::make('type')
                    ->label('Tipe Perubahan')
                    ->options([
                        'in' => 'Penambahan Stok',
                        'out' => 'Pengurangan Stok',
                        'adjustment' => 'Penyesuaian Stok',
                        'sale' => 'Penjualan',
                        'purchase' => 'Pembelian',
                        'return' => 'Retur',
                        'damaged' => 'Rusak/Hilang',
                    ])
                    ->required(),
                    
                Forms\Components\TextInput::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->required(),
                    
                Forms\Components\TextInput::make('stock_before')
                    ->label('Stok Sebelum')
                    ->numeric()
                    ->disabled(),
                    
                Forms\Components\TextInput::make('stock_after')
                    ->label('Stok Sesudah')
                    ->numeric()
                    ->disabled(),
                    
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->nullable(),
                    
                Forms\Components\TextInput::make('reference_type')
                    ->label('Referensi Tipe')
                    ->nullable(),
                    
                Forms\Components\TextInput::make('reference_id')
                    ->label('Referensi ID')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn($state) => match($state) {
                        'in' => 'Masuk',
                        'out' => 'Keluar', 
                        'adjustment' => 'Penyesuaian',
                        'sale' => 'Penjualan',
                        'purchase' => 'Pembelian',
                        'return' => 'Retur',
                        'damaged' => 'Rusak/Hilang',
                        default => ucfirst($state),
                    })
                    ->colors([
                        'success' => ['in', 'purchase', 'return'],
                        'danger' => ['out', 'sale', 'damaged'],
                        'warning' => ['adjustment'],
                        'primary' => ['other'],
                    ]),
                    
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->formatStateUsing(function ($record) {
                        $prefix = in_array($record->type, ['in', 'purchase', 'return']) ? '+' : '-';
                        return $prefix . number_format($record->quantity);
                    })
                    ->color(fn($record) => in_array($record->type, ['in', 'purchase', 'return']) ? 'success' : 'danger'),
                    
                Tables\Columns\TextColumn::make('stock_before')
                    ->label('Stok Sebelum')
                    ->formatStateUsing(fn($state) => number_format($state)),
                    
                Tables\Columns\TextColumn::make('stock_after')
                    ->label('Stok Sesudah')
                    ->formatStateUsing(fn($state) => number_format($state))
                    ->color(fn($record) => $record->stock_after < ($record->product->min_stock ?? 0) ? 'warning' : 'success'),
                    
                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return $record->notes;
                    }),
                    
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->default('System'),
                    
                Tables\Columns\TextColumn::make('reference_type')
                    ->label('Referensi')
                    ->formatStateUsing(function ($record) {
                        if ($record->reference_type && $record->reference_id) {
                            return $record->reference_type . ' #' . $record->reference_id;
                        }
                        return '-';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                    
                SelectFilter::make('type')
                    ->label('Tipe Perubahan')
                    ->options([
                        'in' => 'Penambahan Stok',
                        'out' => 'Pengurangan Stok',
                        'adjustment' => 'Penyesuaian Stok',
                        'sale' => 'Penjualan',
                        'purchase' => 'Pembelian',
                        'return' => 'Retur',
                        'damaged' => 'Rusak/Hilang',
                    ]),
                    
                Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn($query) => $query->whereDate('created_at', today()))
                    ->toggle(),
                    
                Filter::make('this_week')
                    ->label('Minggu Ini')
                    ->query(fn($query) => $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]))
                    ->toggle(),
                    
                Filter::make('this_month')
                    ->label('Bulan Ini')
                    ->query(fn($query) => $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year))
                    ->toggle(),
                    
                Filter::make('stock_alerts')
                    ->label('⚠️ Stok Rendah Setelah Perubahan')
                    ->query(function ($query) {
                        return $query->whereHas('product', function ($productQuery) {
                            $productQuery->whereColumn('stock_movement_logs.stock_after', '<', 'products.min_stock');
                        });
                    })
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Lihat'),
            ])
            ->bulkActions([
                // Tidak ada bulk actions untuk log - data historical tidak boleh dihapus
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();
                if ($user && $user->cooperation_id) {
                    return $query->where('cooperation_id', $user->cooperation_id);
                }
                return $query->whereRaw('1 = 0'); // Return empty if no cooperation
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockMovementLogs::route('/'),
            'view' => Pages\ViewStockMovementLog::route('/{record}'),
        ];
    }
}
