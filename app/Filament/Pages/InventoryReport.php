<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use App\Services\ReportExportService;
use App\Exports\InventoryReportExport;
use App\Exports\ReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryReport extends Page implements Tables\Contracts\HasTable, Forms\Contracts\HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Laporan Barang & Inventaris';
    protected static ?string $navigationGroup = 'Laporan';
    protected static string $view = 'filament.pages.inventory-report';
    protected static ?string $title = 'Laporan Inventaris Komprehensif';

    public $activeTab = 'stock';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return false;
    }


    public static function getNavigationGroup(): ?string
    {
        // Group under 'Laporan' for Bendahara and other panels; hide group for SPV
        $panelId = \Filament\Facades\Filament::getCurrentPanel()?->getId();
        return $panelId === 'spv' ? null : 'Laporan';
    }

    public function table(Table $table): Table
    {
        switch ($this->activeTab) {
            case 'stock':
                return $this->stockTable($table);
            case 'purchases':
                return $this->purchasesTable($table);
            case 'sales':
                return $this->salesTable($table);
            case 'profit_loss':
                return $this->profitLossTable($table);
            default:
                return $this->stockTable($table);
        }
    }

    protected function stockTable(Table $table): Table
    {
        return $table
            ->query(Product::query()->where('cooperation_id', Auth::user()->cooperation_id))
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode Produk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image_url')
                    ->disk('public')
                    ->circular()
                    ->label('Gambar'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan'),
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Stok Tersedia')
                    ->sortable()
                    ->color(fn ($record) => $record->current_stock <= $record->min_stock ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Stok Minimum')
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('Harga Beli')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('selling_price')
                    ->label('Harga Jual')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('stock_value')
                    ->label('Nilai Stok')
                    ->getStateUsing(fn ($record) => $record->current_stock * $record->purchase_price)
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('stock_status')
                    ->label('Status Stok')
                    ->getStateUsing(function ($record) {
                        if ($record->current_stock <= 0) {
                            return 'Habis';
                        } elseif ($record->current_stock <= $record->min_stock) {
                            return 'Rendah';
                        } else {
                            return 'Normal';
                        }
                    })
                    ->colors([
                        'danger' => fn ($state) => in_array($state, ['Habis', 'Rendah']),
                        'success' => fn ($state) => $state === 'Normal',
                    ]),
                Tables\Columns\BadgeColumn::make('is_active')
                    ->label('Status Produk')
                    ->formatStateUsing(fn($state) => $state ? 'Aktif' : 'Tidak Aktif')
                    ->colors([
                        'success' => fn($state) => $state === true,
                        'danger' => fn($state) => $state === false,
                    ]),
            ])
            ->filters([
                Filter::make('kategori')
                    ->form([
                        Select::make('category_id')
                            ->label('Kategori')
                            ->options(ProductCategory::pluck('name', 'id'))
                            ->placeholder('Semua Kategori'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['category_id'] ?? null,
                            fn (Builder $query, $categoryId): Builder => $query->where('product_category_id', $categoryId),
                        );
                    }),
                Filter::make('status_stok')
                    ->form([
                        Select::make('stock_status')
                            ->label('Status Stok')
                            ->options([
                                'low' => 'Stok Rendah',
                                'out' => 'Stok Habis',
                                'normal' => 'Stok Normal',
                            ])
                            ->placeholder('Semua Status'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $stockStatus = $data['stock_status'] ?? null;
                        return $query->when(
                            $stockStatus === 'low',
                            fn (Builder $query): Builder => $query->whereColumn('current_stock', '<=', 'min_stock')->where('current_stock', '>', 0)
                        )->when(
                            $stockStatus === 'out',
                            fn (Builder $query): Builder => $query->where('current_stock', '<=', 0)
                        )->when(
                            $stockStatus === 'normal',
                            fn (Builder $query): Builder => $query->whereColumn('current_stock', '>', 'min_stock')
                        );
                    }),
                Filter::make('status_produk')
                    ->form([
                        Select::make('is_active')
                            ->label('Status Produk')
                            ->options([
                                1 => 'Aktif',
                                0 => 'Tidak Aktif',
                            ])
                            ->placeholder('Semua Status'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            isset($data['is_active']) && $data['is_active'] !== '',
                            fn (Builder $query): Builder => $query->where('is_active', $data['is_active']),
                        );
                    }),
            ])
            ->headerActions([
                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        $exportService = new ReportExportService();
                        $filters = $this->tableFilters ?? [];
                        
                        $data = $exportService->exportInventoryReport($filters);
                        $filename = 'laporan-inventaris-' . now()->format('Y-m-d-H-i-s') . '.xlsx';
                        
                        return Excel::download(new ReportExport($data, 'Laporan Inventaris'), $filename);
                    }),
                Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document')
                    ->color('danger')
                    ->action(function () {
                        Notification::make()
                            ->title('Export PDF')
                            ->body('Fitur export PDF akan segera tersedia.')
                            ->info()
                            ->send();
                    }),
            ])
            ->defaultSort('current_stock', 'asc');
    }

    protected function purchasesTable(Table $table): Table
    {
        return $table
            ->query(Purchase::query()->where('cooperation_id', Auth::user()->cooperation_id))
            ->columns([
                Tables\Columns\TextColumn::make('purchase_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_number')
                    ->label('No. Pembelian')
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Supplier')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_items')
                    ->label('Total Item')
                    ->getStateUsing(fn ($record) => $record->details->sum('quantity'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->label('Diskon')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('tax_amount')
                    ->label('Pajak')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'pending' => 'Pending',
                            'completed' => 'Selesai',
                            'cancelled' => 'Dibatalkan',
                            default => $state,
                        };
                    }),
            ])
            ->filters([
                Filter::make('periode_pembelian')
                    ->form([
                        DatePicker::make('from')
                            ->label('Dari Tanggal')
                            ->placeholder('Pilih tanggal mulai'),
                        DatePicker::make('until')
                            ->label('Sampai Tanggal')
                            ->placeholder('Pilih tanggal akhir'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('purchase_date', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('purchase_date', '<=', $date),
                            );
                    }),
                Filter::make('bulan')
                    ->form([
                        Select::make('month')
                            ->label('Bulan')
                            ->options([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                                4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ])
                            ->placeholder('Pilih Bulan'),
                        Select::make('year')
                            ->label('Tahun')
                            ->options(array_combine(range(2020, 2030), range(2020, 2030)))
                            ->placeholder('Pilih Tahun'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['month'] ?? null,
                                fn (Builder $query, $month): Builder => $query->whereMonth('purchase_date', $month),
                            )
                            ->when(
                                $data['year'] ?? null,
                                fn (Builder $query, $year): Builder => $query->whereYear('purchase_date', $year),
                            );
                    }),
            ])
            ->defaultSort('purchase_date', 'desc');
    }

    protected function salesTable(Table $table): Table
    {
        return $table
            ->query(Sale::query()->where('cooperation_id', Auth::user()->cooperation_id))
            ->columns([
                Tables\Columns\TextColumn::make('sale_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_number')
                    ->label('No. Penjualan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_items')
                    ->label('Total Item')
                    ->getStateUsing(fn ($record) => $record->details->sum('quantity'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->label('Diskon')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('tax_amount')
                    ->label('Pajak')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->colors([
                        'success' => 'cash',
                        'warning' => 'credit',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'cash' => 'Tunai',
                            'credit' => 'Kredit',
                            default => $state,
                        };
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'pending' => 'Pending',
                            'completed' => 'Selesai',
                            'cancelled' => 'Dibatalkan',
                            default => $state,
                        };
                    }),
            ])
            ->filters([
                Filter::make('periode_penjualan')
                    ->form([
                        DatePicker::make('from')
                            ->label('Dari Tanggal')
                            ->placeholder('Pilih tanggal mulai'),
                        DatePicker::make('until')
                            ->label('Sampai Tanggal')
                            ->placeholder('Pilih tanggal akhir'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('sale_date', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('sale_date', '<=', $date),
                            );
                    }),
                Filter::make('bulanan')
                    ->form([
                        Select::make('month')
                            ->label('Bulan')
                            ->options([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                                4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ])
                            ->placeholder('Pilih Bulan'),
                        Select::make('year')
                            ->label('Tahun')
                            ->options(array_combine(range(2020, 2030), range(2020, 2030)))
                            ->placeholder('Pilih Tahun'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['month'] ?? null,
                                fn (Builder $query, $month): Builder => $query->whereMonth('sale_date', $month),
                            )
                            ->when(
                                $data['year'] ?? null,
                                fn (Builder $query, $year): Builder => $query->whereYear('sale_date', $year),
                            );
                    }),
            ])
            ->defaultSort('sale_date', 'desc');
    }

    protected function profitLossTable(Table $table): Table
    {
        return $table
            ->query($this->getProfitLossQuery())
            ->columns([
                Tables\Columns\TextColumn::make('sale.sale_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product.category.name')
                    ->label('Kategori'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty Terjual')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->label('Harga Jual')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('product.purchase_price')
                    ->label('HPP')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total Pendapatan')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Total HPP')
                    ->getStateUsing(fn ($record) => $record->product->purchase_price * $record->quantity)
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('gross_profit')
                    ->label('Laba Kotor')
                    ->getStateUsing(fn ($record) => $record->total_price - ($record->product->purchase_price * $record->quantity))
                    ->money('IDR')
                    ->sortable()
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('margin_percent')
                    ->label('Margin (%)')
                    ->getStateUsing(function ($record) {
                        $grossProfit = $record->total_price - ($record->product->purchase_price * $record->quantity);
                        return $record->total_price > 0 ? ($grossProfit / $record->total_price) * 100 : 0;
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                    ->sortable()
                    ->color(function ($state) {
                        if ($state >= 30) return 'success';
                        if ($state >= 15) return 'warning';
                        return 'danger';
                    }),
            ])
            ->filters([
                Filter::make('periode_laba_rugi')
                    ->form([
                        DatePicker::make('from')
                            ->label('Dari Tanggal')
                            ->placeholder('Pilih tanggal mulai'),
                        DatePicker::make('until')
                            ->label('Sampai Tanggal')
                            ->placeholder('Pilih tanggal akhir'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereHas('sale', function ($q) use ($date) {
                                    $q->whereDate('sale_date', '>=', $date);
                                }),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereHas('sale', function ($q) use ($date) {
                                    $q->whereDate('sale_date', '<=', $date);
                                }),
                            );
                    }),
                Filter::make('kategori_produk')
                    ->form([
                        Select::make('category_id')
                            ->label('Kategori Produk')
                            ->options(ProductCategory::pluck('name', 'id'))
                            ->placeholder('Semua Kategori'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['category_id'] ?? null,
                            fn (Builder $query, $categoryId): Builder => $query->whereHas('product.category', function ($q) use ($categoryId) {
                                $q->where('id', $categoryId);
                            }),
                        );
                    }),
                // Note: Margin range filter removed for now due to complexity with Eloquent relationships
                // Can be re-implemented later if needed
            ])
            ->defaultSort('sale.sale_date', 'desc');
    }

    protected function getProfitLossQuery()
    {
        return SaleDetail::with(['sale', 'product.category'])
            ->whereHas('sale', function ($query) {
                $query->where('cooperation_id', Auth::user()->cooperation_id);
            })
            ->select([
                'sale_details.id',
                'sale_details.sale_id',
                'sale_details.product_id',
                'sale_details.quantity',
                'sale_details.unit_price',
                'sale_details.total_price',
            ]);
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    // Helper methods for summary cards
    public function getTotalProducts(): int
    {
        return Product::where('cooperation_id', Auth::user()->cooperation_id)->count();
    }

    public function getMonthlyPurchases(): int
    {
        return Purchase::where('cooperation_id', Auth::user()->cooperation_id)
            ->whereMonth('purchase_date', now()->month)
            ->whereYear('purchase_date', now()->year)
            ->count();
    }

    public function getMonthlySales(): int
    {
        return Sale::where('cooperation_id', Auth::user()->cooperation_id)
            ->whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year)
            ->count();
    }

    public function getLowStockProducts()
    {
        return Product::where('cooperation_id', Auth::user()->cooperation_id)
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->where('current_stock', '>', 0)
            ->get();
    }

    // Helper methods for profit/loss analysis
    public function getTotalRevenue(): float
    {
        return SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->where('sales.cooperation_id', Auth::user()->cooperation_id)
            ->whereMonth('sales.sale_date', now()->month)
            ->whereYear('sales.sale_date', now()->year)
            ->sum('sale_details.total_price');
    }

    public function getTotalCost(): float
    {
        return SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->where('sales.cooperation_id', Auth::user()->cooperation_id)
            ->whereMonth('sales.sale_date', now()->month)
            ->whereYear('sales.sale_date', now()->year)
            ->selectRaw('SUM(sale_details.quantity * products.purchase_price) as total_cost')
            ->value('total_cost') ?? 0;
    }

    public function getGrossProfit(): float
    {
        return $this->getTotalRevenue() - $this->getTotalCost();
    }

    public function exportInventoryExcel($type)
    {
        return Excel::download(new InventoryReportExport($type), 'laporan-inventori-' . $type . '-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportInventoryPdf($type)
    {
        $data = [];
        $cooperation = Auth::user()->cooperation;

        switch ($type) {
            case 'stock':
                $data = Product::where('cooperation_id', Auth::user()->cooperation_id)
                    ->with(['category'])
                    ->get();
                break;
            case 'purchases':
                $data = Purchase::where('cooperation_id', Auth::user()->cooperation_id)
                    ->with(['supplier', 'details.product'])
                    ->orderBy('purchase_date', 'desc')
                    ->get();
                break;
            case 'sales':
                $data = Sale::where('cooperation_id', Auth::user()->cooperation_id)
                    ->where('status', 'completed')
                    ->with(['customer', 'details.product'])
                    ->orderBy('sale_date', 'desc')
                    ->get();
                break;
            case 'profit_loss':
                $data = Product::where('cooperation_id', Auth::user()->cooperation_id)
                    ->get()
                    ->map(function ($product) {
                        $purchaseCost = ($product->purchase_price ?? 0) * $product->stock_quantity;
                        $potentialSales = ($product->selling_price ?? 0) * $product->stock_quantity;
                        $profit = $potentialSales - $purchaseCost;

                        return [
                            'product' => $product,
                            'purchase_cost' => $purchaseCost,
                            'potential_sales' => $potentialSales,
                            'profit' => $profit,
                        ];
                    });
                break;
        }

        $pdf = Pdf::loadView('pdf.inventory-report', [
            'data' => $data,
            'type' => $type,
            'cooperation' => $cooperation,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'laporan-inventori-' . $type . '-' . now()->format('Y-m-d') . '.pdf');
    }

    protected function getRecordKey($record): string
    {
        // For profit/loss table, use the sale_detail id as the record key
        if ($this->activeTab === 'profit_loss') {
            return $record->id;
        }

        // For other tables, use the default behavior
        return parent::getRecordKey($record);
    }

    public function getTitle(): string
    {
        return 'Laporan Inventaris Komprehensif';
    }
}
