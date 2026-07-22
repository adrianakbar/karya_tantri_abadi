<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductSalesResource\Pages;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ProductSalesResource extends Resource
{
    protected static ?string $model = Sale::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $modelLabel = 'Penjualan';
    protected static ?string $pluralModelLabel = 'Daftar Penjualan';

    public static function getNavigationGroup(): ?string
    {
        return 'Barang & Inventaris';
    }

    public static function getNavigationLabel(): string
    {
        return 'Penjualan Produk';
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
        return $form->schema([
            self::getGeneralInformationSection(),
            self::getProductDetailsSection(),
            self::getSummaryAndStatusSection(),
            self::getAdditionalInformationSection(),
        ]);
    }

    private static function getGeneralInformationSection(): Section
    {
        return Section::make('Informasi Umum')
            ->schema([
                Grid::make(2)->schema([
                    Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->label('Nama Pelanggan')
                        ->searchable()
                        ->preload()
                        ->nullable(),

                ]),

                Grid::make(2)->schema([

                    Select::make('payment_method')
                        ->label('Metode Pembayaran')
                        ->options(self::getPaymentMethodOptions())
                        ->required()
                        ->default('cash'),
                    DatePicker::make('sale_date')
                        ->label('Tanggal Penjualan')
                        ->default(now())
                        ->required(),
                ]),
            ]);
    }

    private static function getProductDetailsSection(): Section
    {
        return Section::make('Detail Produk')
            ->schema([
                Repeater::make('details')
                    ->relationship()
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->options(Product::query()->where('current_stock', '>', 0)->pluck('name', 'id'))
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $product = Product::find($state);
                                if ($product) {
                                    $set('unit_price', $product->selling_price ?? 0);
                                    $set('max_quantity', $product->current_stock);
                                    self::updateDetailTotal($get, $set);
                                }
                            })
                            ->extraAttributes(['onkeydown' => 'if(event.key==="Enter"){event.preventDefault();}'])
                            ->columnSpan(4),


                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->minValue(1)
                            ->reactive()
                            ->live(onBlur: true)
                            ->rules([
                                function (Get $get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $productId = $get('product_id');
                                        if ($productId) {
                                            $product = Product::find($productId);
                                            if ($product && $value > $product->current_stock) {
                                                $fail("Jumlah tidak boleh lebih dari stok yang tersedia ({$product->current_stock}).");
                                            }
                                        }
                                    };
                                },
                            ])
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateDetailTotal($get, $set);
                                self::updateTotalAmount($get, $set);
                            })
                            ->extraAttributes(['onkeydown' => 'if(event.key==="Enter"){event.preventDefault();}'])
                            ->helperText(function (Get $get) {
                                $productId = $get('product_id');
                                if ($productId) {
                                    $product = Product::find($productId);
                                    if ($product) {
                                        return "Stok tersedia: {$product->current_stock}";
                                    }
                                }
                                return null;
                            })
                            ->columnSpan(2),

                        TextInput::make('unit_price')
                            ->label('Harga Satuan')
                            ->numeric()
                            ->required()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateDetailTotal($get, $set);
                                self::updateTotalAmount($get, $set);
                            })
                            ->extraAttributes(['onkeydown' => 'if(event.key==="Enter"){event.preventDefault();}'])
                            ->columnSpan(2),



                        TextInput::make('total_price')
                            ->label('Total Harga')
                            ->numeric()
                            ->prefix('Rp')
                            ->readonly()
                            ->default(0)
                            ->dehydrated(true)
                            ->dehydrateStateUsing(fn(Get $get) => floatval($get('quantity') ?? 0) * floatval($get('unit_price') ?? 0))
                            ->columnSpan(2),
                    ])
                    ->columns(10)
                    ->addActionLabel('Tambah Item')
                    ->deleteAction(fn($action) => $action->after(fn(Get $get, Set $set) => self::updateTotalAmount($get, $set)))
                    ->minItems(1)
                    ->afterStateUpdated(fn(Get $get, Set $set) => self::updateTotalAmount($get, $set)),
            ]);
    }

    private static function getSummaryAndStatusSection(): Section
    {
        return Section::make('Ringkasan & Status')
            ->schema([
                Grid::make(3)->schema([
                    TextInput::make('tax_amount')
                        ->label('Pajak (%)')
                        ->numeric()
                        ->suffix('%')
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(0)
                        ->dehydrated(true)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn(Get $get, Set $set) => self::updateTotalAmount($get, $set)),

                    TextInput::make('discount_amount')
                        ->label('Diskon (%)')
                        ->numeric()
                        ->suffix('%')
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(0)
                        ->dehydrated(true)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn(Get $get, Set $set) => self::updateTotalAmount($get, $set)),

                    TextInput::make('total_amount')
                        ->label('Total Akhir')
                        ->prefix('Rp')
                        ->numeric()
                        ->readonly()
                        ->default(0)
                        ->dehydrated(true)
                        ->dehydrateStateUsing(function (Get $get) {
                            $items = $get('details') ?? [];
                            $subtotal = collect($items)->sum(fn($i) => ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0));

                            $taxPercent = floatval($get('tax_amount') ?? 0);
                            $discountPercent = floatval($get('discount_amount') ?? 0);

                            $taxAmount = ($subtotal * $taxPercent) / 100;
                            $discountAmount = ($subtotal * $discountPercent) / 100;

                            return max(0, $subtotal + $taxAmount - $discountAmount);
                        }),
                ]),

                Select::make('status')
                    ->options(self::getStatusOptions())
                    ->required()
                    ->default('pending'),
            ]);
    }

    private static function getAdditionalInformationSection(): Section
    {
        return Section::make('Informasi Tambahan')
            ->schema([
                Grid::make(2)->schema([
                    Select::make('processed_by')
                        ->label('Diproses Oleh')
                        ->options(User::query()->pluck('name', 'id'))
                        ->searchable()
                        ->default(fn() => Auth::user()?->id),
                    Hidden::make('cooperation_id')
                        ->default(fn() => Auth::user()->cooperation_id),
                ]),

                // Hidden subtotal field untuk menyimpan nilai subtotal
                Hidden::make('subtotal')
                    ->default(0)
                    ->dehydrated(true),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->nullable()
                    ->rows(3)
                    ->maxLength(500),
            ]);
    }

    public static function updateDetailTotal(Get $get, Set $set): void
    {
        $quantity = floatval($get('quantity') ?? 0);
        $unitPrice = floatval($get('unit_price') ?? 0);
        $set('total_price', $quantity * $unitPrice);
    }

    /**
     * Update total amount with tax and discount calculations
     */
    public static function updateTotalAmount(Get $get, Set $set): void
    {
        $items = $get('details') ?? [];
        $subtotal = collect($items)->sum('total_price');

        // Set subtotal (hidden field)
        $set('subtotal', $subtotal);

        $taxPercent = floatval($get('tax_amount') ?? 0);
        $discountPercent = floatval($get('discount_amount') ?? 0);

        // Calculate tax and discount amounts
        $taxAmount = ($subtotal * $taxPercent) / 100;
        $discountAmount = ($subtotal * $discountPercent) / 100;

        // Calculate final total
        $totalAmount = $subtotal + $taxAmount - $discountAmount;

        // Ensure total is not negative
        $set('total_amount', max(0, $totalAmount));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sale_number')
                    ->label('Nomor Penjualan')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Tidak ada'),

                Tables\Columns\TextColumn::make('sale_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('details_count')
                    ->label('Jml Item')
                    ->counts('details')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('tax_amount')
                    ->label('Pajak (%)')
                    ->formatStateUsing(fn($state) => $state . '%')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('discount_amount')
                    ->label('Diskon (%)')
                    ->formatStateUsing(fn($state) => $state . '%')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Akhir')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Pembayaran')
                    ->formatStateUsing(fn(string $state): string => self::getPaymentMethodLabel($state))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'cash' => 'success',
                        'transfer' => 'info',
                        'credit_card', 'debit_card' => 'warning',
                        'e_wallet' => 'primary',
                        default => 'gray',
                    })
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn(string $state): string => self::getStatusLabel($state))
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(self::getStatusOptions()),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options(self::getPaymentMethodOptions()),

                Tables\Filters\Filter::make('pending_sales')
                    ->label('⏳ Menunggu Penyelesaian')
                    ->query(fn($query) => $query->where('status', 'pending'))
                    ->toggle(),

                Tables\Filters\Filter::make('sale_date')
                    ->form([
                        Select::make('period')
                            ->label('Periode Penjualan')
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
                            ->afterStateUpdated(fn(Set $set) => $set('date_from', null) + $set('date_to', null)),
                        DatePicker::make('date_from')
                            ->label('Dari Tanggal')
                            ->visible(fn(Get $get) => $get('period') === 'custom')
                            ->maxDate(fn(Get $get) => $get('date_to') ?: now()),
                        DatePicker::make('date_to')
                            ->label('Sampai Tanggal')
                            ->visible(fn(Get $get) => $get('period') === 'custom')
                            ->minDate(fn(Get $get) => $get('date_from'))
                            ->maxDate(now()),
                    ])
                    ->query(function ($query, array $data) {
                        if (empty($data['period'])) {
                            return $query;
                        }

                        return $query->when(
                            $data['period'],
                            function ($query, $period) use ($data) {
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
                // Action::make('print')
                //     ->label('Print')
                //     ->icon('heroicon-o-printer')
                //     ->url(fn($record) => route('sales.print', $record), true)
                //     ->color('success'),
                Tables\Actions\Action::make('complete_sale')
                    ->label('Selesaikan Penjualan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Penyelesaian Penjualan')
                    ->modalDescription('Apakah Anda yakin ingin menyelesaikan penjualan ini? Stok produk akan otomatis berkurang.')
                    ->action(function ($record) {
                        $insufficientStock = [];
                        foreach ($record->details()->with('product')->get() as $detail) {
                            $product = $detail->product->fresh();
                            if ($product->current_stock < $detail->quantity) {
                                $insufficientStock[] = "{$product->name} (stok saat ini: {$product->current_stock}, dibutuhkan: {$detail->quantity})";
                            }
                        }

                        if (!empty($insufficientStock)) {
                            Notification::make()
                                ->danger()
                                ->title('Stok tidak mencukupi!')
                                ->body('Produk berikut stoknya tidak mencukupi: ' . implode(', ', $insufficientStock))
                                ->persistent()
                                ->send();
                            return;
                        }

                        $record->update(['status' => 'completed']);

                        Notification::make()
                            ->success()
                            ->title('Penjualan berhasil diselesaikan!')
                            ->body('Stok produk telah dikurangi otomatis.')
                            ->send();
                    }),
                Tables\Actions\ViewAction::make()->label('Lihat'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->label('Hapus')->requiresConfirmation()
                    ->modalHeading('Konfirmasi Penghapusan Penjualan')
                    ->modalDescription('Apakah Anda yakin ingin menghapus penjualan ini? Aksi ini tidak dapat dibatalkan.')
                    ->action(function ($record) {
                        $record->delete();

                        Notification::make()
                            ->success()
                            ->title('Penjualan berhasil dihapus!')
                            ->body('Data penjualan telah dihapus dari sistem.')
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('sale_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductSales::route('/'),
            'create' => Pages\CreateProductSales::route('/create'),
            'edit' => Pages\EditProductSales::route('/{record}/edit'),
        ];
    }

    private static function getPaymentMethodOptions(): array
    {
        return [
            'cash' => 'Tunai',
            'transfer' => 'Transfer Bank',
            'credit_card' => 'Kartu Kredit',
            'debit_card' => 'Kartu Debit',
            'e_wallet' => 'E-Wallet',
        ];
    }

    private static function getStatusOptions(): array
    {
        return [
            'pending' => 'Dalam Proses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
    }

    private static function getPaymentMethodLabel(string $state): string
    {
        return match ($state) {
            'cash' => 'Tunai',
            'transfer' => 'Transfer',
            'credit_card' => 'Kartu Kredit',
            'debit_card' => 'Kartu Debit',
            'e_wallet' => 'E-Wallet',
            default => $state,
        };
    }

    private static function getStatusLabel(string $state): string
    {
        return match ($state) {
            'pending' => 'Diproses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $state,
        };
    }
}
