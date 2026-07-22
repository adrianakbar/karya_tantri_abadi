<?php

namespace App\Filament\Resources\Anggota;

use App\Models\Sale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\Anggota\ProductSalesResource\Pages;

class ProductSalesResource extends Resource
{
    protected static ?string $model = Sale::class;
    protected static ?string $slug = 'pembelian-produk';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Pembelian';
    protected static ?string $modelLabel = 'Pembelian Produk';
    protected static ?string $pluralModelLabel = 'Riwayat Pembelian Produk';
    protected static ?string $navigationGroup = null;

    public static function getEloquentQuery(): Builder
    {
        // Hanya tampilkan transaksi dimana user adalah customer
        return parent::getEloquentQuery()
            ->where('customer_id', Auth::id());
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
                Forms\Components\Section::make('Informasi Transaksi')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('sale_number')
                                ->label('No. Transaksi')
                                ->disabled(),
                            Forms\Components\DatePicker::make('sale_date')
                                ->label('Tanggal')
                                ->disabled(),
                        ]),
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('payment_method')
                                ->label('Metode Pembayaran')
                                ->disabled(),
                            Forms\Components\TextInput::make('status')
                                ->label('Status')
                                ->disabled(),
                        ]),
                    ]),

                Forms\Components\Section::make('Detail Produk')
                    ->schema([
                        Forms\Components\Repeater::make('details')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produk')
                                    ->relationship('product', 'name')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpan(4),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->disabled()
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('total_price')
                                    ->label('Total Harga')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->columnSpan(2),
                            ])
                            ->columns(10)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ]),

                Forms\Components\Section::make('Ringkasan')
                    ->schema([
                        Forms\Components\Grid::make(4)->schema([
                            Forms\Components\TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->numeric()
                                ->prefix('Rp')
                                ->disabled(),
                            Forms\Components\TextInput::make('discount_amount')
                                ->label('Diskon (%)')
                                ->numeric()
                                ->suffix('%')
                                ->disabled(),
                            Forms\Components\TextInput::make('tax_amount')
                                ->label('Pajak (%)')
                                ->numeric()
                                ->suffix('%')
                                ->disabled(),
                            Forms\Components\TextInput::make('total_amount')
                                ->label('Total Akhir')
                                ->numeric()
                                ->prefix('Rp')
                                ->disabled(),
                        ]),
                    ]),

                Forms\Components\Section::make('Catatan')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Keterangan')
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sale_number')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('products')
                    ->label('Produk')
                    ->getStateUsing(fn($record) => $record->details()->with('product')->get()->pluck('product.name')->implode(', '))
                    ->toggleable()
                    ->limit(40)
                    ->tooltip(fn($record) => $record->details()->with('product')->get()->pluck('product.name')->implode(', ')),
                Tables\Columns\TextColumn::make('details_count')
                    ->label('Jml Item')
                    ->counts('details')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('sale_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'transfer' => 'info',
                        'credit' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('sale_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'cash' => 'Tunai',
                        'transfer' => 'Transfer',
                        'credit' => 'Kredit',
                    ]),
                Tables\Filters\Filter::make('sale_date')
                    ->form([
                        Forms\Components\Select::make('period')
                            ->label('Periode Pembelian')
                            ->options([
                                'today' => 'Hari Ini',
                                'yesterday' => 'Kemarin',
                                'this_week' => 'Minggu Ini',
                                'last_week' => 'Minggu Lalu',
                                'this_month' => 'Bulan Ini',
                                'last_month' => 'Bulan Lalu',
                                'this_year' => 'Tahun Ini',
                                'custom' => 'Kustom',
                            ])
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('date_from', null) + $set('date_to', null)),
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Dari Tanggal')
                            ->visible(fn (Forms\Get $get) => $get('period') === 'custom')
                            ->maxDate(fn (Forms\Get $get) => $get('date_to') ?: now()),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('Sampai Tanggal')
                            ->visible(fn (Forms\Get $get) => $get('period') === 'custom')
                            ->minDate(fn (Forms\Get $get) => $get('date_from'))
                            ->maxDate(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['period'])) {
                            return $query;
                        }

                        return $query->when(
                            $data['period'],
                            function (Builder $query, $period) use ($data) {
                                $now = now();
                                
                                switch ($period) {
                                    case 'today':
                                        return $query->whereDate('sale_date', $now->toDateString());
                                    case 'yesterday':
                                        return $query->whereDate('sale_date', $now->subDay()->toDateString());
                                    case 'this_week':
                                        return $query->whereBetween('sale_date', [
                                            $now->startOfWeek()->toDateString(),
                                            $now->endOfWeek()->toDateString()
                                        ]);
                                    case 'last_week':
                                        return $query->whereBetween('sale_date', [
                                            $now->subWeek()->startOfWeek()->toDateString(),
                                            $now->subWeek()->endOfWeek()->toDateString()
                                        ]);
                                    case 'this_month':
                                        return $query->whereMonth('sale_date', $now->month)
                                            ->whereYear('sale_date', $now->year);
                                    case 'last_month':
                                        return $query->whereMonth('sale_date', $now->subMonth()->month)
                                            ->whereYear('sale_date', $now->subMonth()->year);
                                    case 'this_year':
                                        return $query->whereYear('sale_date', $now->year);
                                    case 'custom':
                                        if (!empty($data['date_from']) && !empty($data['date_to'])) {
                                            return $query->whereBetween('sale_date', [
                                                $data['date_from'],
                                                $data['date_to']
                                            ]);
                                        }
                                        if (!empty($data['date_from'])) {
                                            return $query->whereDate('sale_date', '>=', $data['date_from']);
                                        }
                                        if (!empty($data['date_to'])) {
                                            return $query->whereDate('sale_date', '<=', $data['date_to']);
                                        }
                                        return $query;
                                    default:
                                        return $query;
                                }
                            }
                        );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (empty($data['period'])) {
                            return null;
                        }

                        $labels = [
                            'today' => 'Hari Ini',
                            'yesterday' => 'Kemarin',
                            'this_week' => 'Minggu Ini',
                            'last_week' => 'Minggu Lalu',
                            'this_month' => 'Bulan Ini',
                            'last_month' => 'Bulan Lalu',
                            'this_year' => 'Tahun Ini',
                        ];

                        if ($data['period'] === 'custom' && !empty($data['date_from']) && !empty($data['date_to'])) {
                            return 'Periode: ' . date('d M Y', strtotime($data['date_from'])) . ' - ' . date('d M Y', strtotime($data['date_to']));
                        }

                        return 'Periode: ' . ($labels[$data['period']] ?? $data['period']);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Lihat'),
            ])
            ->bulkActions([
                // Remove bulk actions for anggota
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Anggota\ProductSalesResource\Pages\ListProductSales::route('/'),
            'view' => \App\Filament\Resources\Anggota\ProductSalesResource\Pages\ViewProductSales::route('/{record}'),
        ];
    }
}
