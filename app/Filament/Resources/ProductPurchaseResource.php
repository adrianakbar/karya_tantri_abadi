<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductPurchaseResource\Pages;
use App\Models\Product;
use App\Models\Purchase;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProductPurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $modelLabel = 'Pembelian Produk';
    protected static ?string $pluralModelLabel = 'Daftar Pembelian Produk';

    public static function getNavigationGroup(): ?string
    {
        return 'Barang & Inventaris';
    }

    public static function getNavigationLabel(): string
    {
        return 'Pembelian Produk';
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
            Section::make('Informasi Umum')->schema([
                Grid::make(2)->schema([
                    TextInput::make('purchase_number')
                        ->label('Nomor Pembelian')
                        ->default('Nomor Pembelian Otomatis')
                        ->readonly()
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('invoice_number')
                        ->label('Supplier')
                        ->nullable(),
                ]),

                Grid::make(2)->schema([
                    DatePicker::make('purchase_date')
                        ->label('Tanggal Pembelian')
                        ->default(now())
                        ->required(),
                ]),
            ]),

            Section::make('Detail Produk')->schema([
                Repeater::make('details')
                    ->relationship()
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->options(Product::query()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if ($state) {
                                    $product = Product::find($state);
                                    $set('unit_price', $product?->purchase_price ?? 0);
                                    $quantity = floatval($get('quantity') ?? 1);
                                    $unitPrice = floatval($product?->purchase_price ?? 0);
                                    $set('total_price', $quantity * $unitPrice);
                                    self::calculateTotals($get, $set);
                                }
                            })
                            ->columnSpan(4),

                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $quantity = floatval($get('quantity') ?? 0);
                                $unitPrice = floatval($get('unit_price') ?? 0);
                                $set('total_price', $quantity * $unitPrice);
                            })
                            ->columnSpan(2),

                        TextInput::make('unit_price')
                            ->label('Harga Satuan')
                            ->numeric()
                            ->required()
                            ->prefix('Rp')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $quantity = floatval($get('quantity') ?? 0);
                                $unitPrice = floatval($get('unit_price') ?? 0);
                                $set('total_price', $quantity * $unitPrice);
                            })
                            ->columnSpan(2),

                        TextInput::make('total_price')
                            ->label('Total Harga')
                            ->numeric()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calculateTotals($get, $set);
                            })
                            ->prefix('Rp')
                            ->readonly()
                            ->default(0)
                            ->dehydrated(true)
                            ->columnSpan(2),
                    ])
                    ->columns(10)
                    ->addActionLabel('Tambah Item')
                    ->reactive()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        self::calculateTotals($get, $set);
                    }),
            ]),

            Section::make('Ringkasan & Status')->schema([
                Grid::make(3)->schema([
                    TextInput::make('tax_amount')
                        ->label('Pajak')
                        ->numeric()
                        ->prefix('Rp')
                        ->live(onBlur: true)
                        ->default(0)
                        ->dehydrated(true)
                        ->afterStateUpdated(fn(Get $get, Set $set) => self::calculateTotals($get, $set)),

                    TextInput::make('discount_amount')
                        ->label('Diskon')
                        ->numeric()
                        ->prefix('Rp')
                        ->live(onBlur: true)
                        ->default(0)
                        ->dehydrated(true)
                        ->afterStateUpdated(fn(Get $get, Set $set) => self::calculateTotals($get, $set)),

                    TextInput::make('total_amount')
                        ->label('Subtotal')
                        ->numeric()
                        ->prefix('Rp')
                        ->readonly()
                        ->default(0)
                        ->dehydrated(true),
                ]),

                TextInput::make('grand_total')
                    ->label('Grand Total')
                    ->numeric()
                    ->prefix('Rp')
                    ->readonly()
                    ->reactive()
                    ->default(0)
                    ->dehydrated(true),

                Select::make('status')
                    ->options([
                        'pending' => 'Dalam Proses',
                        'received' => 'Diterima',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->required()
                    ->default('pending'),
            ]),

            Section::make('Catatan Tambahan')->schema([
                Textarea::make('notes')->label(false),
            ]),

            Hidden::make('cooperation_id')->default(fn() => Auth::user()?->cooperation_id ?? 1),
        ]);
    }

    public static function calculateTotals(Get $get, Set $set): void
    {
        $items = $get('details') ?? [];
        $subtotal = 0;

        foreach ($items as $item) {
            if (is_array($item) && isset($item['total_price'])) {
                $subtotal += floatval($item['total_price']);
            }
        }

        $tax = floatval($get('tax_amount') ?? 0);
        $discount = floatval($get('discount_amount') ?? 0);
        $grandTotal = $subtotal + $tax - $discount;

        $set('total_amount', $subtotal);
        $set('grand_total', $grandTotal);
    }

    public static function getSubtotal(Get $get): float
    {
        $items = $get('details') ?? [];
        $subtotal = 0;

        foreach ($items as $item) {
            if (is_array($item) && isset($item['total_price'])) {
                $subtotal += floatval($item['total_price']);
            }
        }

        return $subtotal;
    }

    public static function getGrandTotal(Get $get): float
    {
        $subtotal = self::getSubtotal($get);
        $tax = floatval($get('tax_amount') ?? 0);
        $discount = floatval($get('discount_amount') ?? 0);

        return $subtotal + $tax - $discount;
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('purchase_number')->label('Nomor Pembelian')->searchable()->sortable(),
                TextColumn::make('invoice_number')->label('Supplier')->searchable()->sortable(),
                TextColumn::make('purchase_date')->label('Tanggal')->date('d M Y')->sortable(),
                TextColumn::make('details_count')
                    ->label('Jml Item')
                    ->counts('details')
                    ->badge()
                    ->color('info'),
                TextColumn::make('grand_total')->label('Grand Total')->money('IDR')->sortable(),

                BadgeColumn::make('status')
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'pending' => 'Diproses',
                            'received' => 'Diterima',
                            'cancelled' => 'Dibatalkan',
                            default => $state,
                        };
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'received',
                        'danger' => 'cancelled',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Pembelian')
                    ->options([
                        'pending' => 'Dalam Proses',
                        'received' => 'Diterima',
                        'cancelled' => 'Dibatalkan',
                    ]),
                Tables\Filters\Filter::make('pending_purchases')
                    ->label('⏳ Menunggu Penerimaan')
                    ->query(fn($query) => $query->where('status', 'pending'))
                    ->toggle(),
                Tables\Filters\Filter::make('purchase_date')
                    ->form([
                        Select::make('period')
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
                                        return $query->whereDate('purchase_date', $now->toDateString());
                                    case 'yesterday':
                                        return $query->whereDate('purchase_date', $now->subDay()->toDateString());
                                    case 'this_week':
                                        return $query->whereBetween('purchase_date', [
                                            $now->startOfWeek()->toDateString(),
                                            $now->endOfWeek()->toDateString()
                                        ]);
                                    case 'last_week':
                                        return $query->whereBetween('purchase_date', [
                                            $now->subWeek()->startOfWeek()->toDateString(),
                                            $now->subWeek()->endOfWeek()->toDateString()
                                        ]);
                                    case 'this_month':
                                        return $query->whereMonth('purchase_date', $now->month)
                                            ->whereYear('purchase_date', $now->year);
                                    case 'last_month':
                                        return $query->whereMonth('purchase_date', $now->subMonth()->month)
                                            ->whereYear('purchase_date', $now->subMonth()->year);
                                    case 'this_year':
                                        return $query->whereYear('purchase_date', $now->year);
                                    case 'custom':
                                        if (!empty($data['date_from']) && !empty($data['date_to'])) {
                                            return $query->whereBetween('purchase_date', [
                                                $data['date_from'],
                                                $data['date_to']
                                            ]);
                                        }
                                        if (!empty($data['date_from'])) {
                                            return $query->whereDate('purchase_date', '>=', $data['date_from']);
                                        }
                                        if (!empty($data['date_to'])) {
                                            return $query->whereDate('purchase_date', '<=', $data['date_to']);
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
                Tables\Actions\Action::make('mark_received')
                    ->label('Terima Barang')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Penerimaan Barang')
                    ->modalDescription('Apakah Anda yakin ingin menandai pembelian ini sebagai diterima? Stok produk akan otomatis bertambah.')
                    ->action(function ($record) {
                        // Update status pembelian
                        $record->update(['status' => 'received']);

                        // Update Expense terkait
                        \App\Models\Expense::where('receipt_number', $record->invoice_number)
                            ->update([
                                'status' => 'approved',
                                'notes' => "Pengeluaran disetujui otomatis setelah barang diterima untuk {$record->purchase_number}",
                            ]);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Pembelian berhasil diterima!')
                            ->body('Stok produk dan data pengeluaran telah diperbarui otomatis.')
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()->label('Lihat')
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductPurchases::route('/'),
            'create' => Pages\CreateProductPurchase::route('/create'),
            'edit' => Pages\EditProductPurchase::route('/{record}/edit'),
        ];
    }
}
