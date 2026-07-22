<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\SavingsTransaction;
use App\Models\LoanPayment;
use App\Models\CashFlow;
use App\Models\TransactionSummary;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Exports\FinancialReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class FinancialReport extends Page implements Tables\Contracts\HasTable, Forms\Contracts\HasForms
{
    use InteractsWithTable, InteractsWithForms;

    public ?string $filterPeriod = 'all_time'; // Change default to all_time
    public ?int $filterMonth;
    public ?int $filterYear;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Laporan Arus Kas';
    protected static ?string $navigationGroup = 'Laporan';
    protected static string $view = 'filament.pages.financial-report';
    protected static ?string $title = 'Laporan Arus Kas';

    public static function getNavigationGroup(): ?string
    {
        // Group under 'Laporan' for Bendahara and other panels; hide group for SPV
        $panelId = \Filament\Facades\Filament::getCurrentPanel()?->getId();
        return $panelId === 'spv' ? null : 'Laporan';
    }

    public function mount(): void
    {
        $this->filterPeriod = 'all_time'; // Set initial filterPeriod to all_time
        $this->filterMonth = now()->month;
        $this->filterYear = now()->year;
        $this->form->fill();
    }

    public function table(Table $table): Table
    {
        $cooperationId = Auth::user()->cooperation_id;
        $hasCashFlowData = CashFlow::where('cooperation_id', $cooperationId)->exists();

        if ($hasCashFlowData) {
            $query = CashFlow::query()->where('cooperation_id', $cooperationId);
        } else {
            // Use TransactionSummary model directly, running balance is now calculated in the model's scope
            $query = TransactionSummary::forCooperation();
        }

        // Apply filters to the table query
        if ($this->filterPeriod === 'monthly') {
            $query->whereMonth('sort_date', $this->filterMonth)
                  ->whereYear('sort_date', $this->filterYear);
        } elseif ($this->filterPeriod === 'yearly') {
            $query->whereYear('sort_date', $this->filterYear);
        }
        // For 'all_time', no additional date filters are applied

        return $table
            ->query($query)
            ->defaultSort('sort_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->wrap() // Allows text to wrap
                    ->limit(50) // Limits characters to 50
                    ->tooltip(fn ($state) => $state) // Shows full description on hover
                    ->sortable()
                    ->searchable(), // Make this column searchable
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->color(function ($state) {
                        // Define colors for different categories
                        $colors = [
                            'Penjualan Produk' => 'primary',
                            'Simpanan Anggota' => 'success',
                            'Cicilan Pinjaman' => 'info',
                            'Pembelian/Restok' => 'danger',
                            'Pencairan Pinjaman' => 'secondary',
                            'Gaji Karyawan' => 'warning',
                            // Dynamically add colors for other expense categories if needed, or use a default
                        ];
                        // Get dynamic expense category names
                        $cooperationId = Auth::user()->cooperation_id;
                        $dynamicExpenseCategoryNames = \App\Models\ExpenseCategory::where('cooperation_id', $cooperationId)->pluck('name')->toArray();

                        if (in_array($state, $dynamicExpenseCategoryNames) && !isset($colors[$state])) {
                            return 'warning'; // Default color for dynamic expenses not explicitly defined
                        }

                        return $colors[$state] ?? 'gray'; // Default to gray if category not found
                    })
                    ->badge()
                    ->sortable()
                    ->searchable(), // Make this column searchable
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'inflow' => 'Pemasukan',
                        'outflow' => 'Pengeluaran',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'inflow' => 'success',
                        'outflow' => 'danger',
                        default => 'gray',
                    })
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->color(fn ($record) => $record->type === 'inflow' ? 'success' : 'danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance_after')
                    ->label('Saldo Akhir')
                    ->money('IDR')
                    ->color(fn ($record) => $record->balance_after >= 0 ? 'success' : 'danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference_type')
                    ->label('Tipe Referensi')
                    ->sortable()
                    ->searchable(), // Make this column searchable
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(function () {
                        $cooperationId = Auth::user()->cooperation_id;
                        $categories = \App\Models\ExpenseCategory::where('cooperation_id', $cooperationId)->pluck('name', 'name')->toArray();
                        return array_merge([
                            'Penjualan Produk' => 'Penjualan Produk',
                            'Simpanan Anggota' => 'Simpanan Anggota',
                            'Cicilan Pinjaman' => 'Cicilan Pinjaman',
                            'Pembelian/Restok' => 'Pembelian/Restok',
                            'Pencairan Pinjaman' => 'Pencairan Pinjaman',
                        ], $categories);
                    })
                    ->multiple()
                    ->preload(),
                Tables\Filters\Filter::make('transaction_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->placeholder(fn ($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('until')
                            ->placeholder(fn ($state): string => 'Dec 18, ' . now()->format('Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('sort_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('sort_date', '<=', $date),
                            );
                    })
            ])
            ->headerActions([
                Action::make('exportExcel')
                    ->label('Export Excel')
                    ->color('success')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(fn () => $this->exportExcel()),
                Action::make('exportPdf')
                    ->label('Export PDF')
                    ->color('danger')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(fn () => $this->exportPdf()),
            ]);
    }

    public function getCashFlowSummary()
    {
                $cooperationId = Auth::user()->cooperation_id;
        $hasCashFlowData = CashFlow::where('cooperation_id', $cooperationId)->exists(); // Define hasCashFlowData here
        
        $currentYear = now()->year;

        // Determine effective month and year for summary based on filters
        $effectiveMonth = $this->filterPeriod === 'monthly' ? $this->filterMonth : null;
        $effectiveYear = in_array($this->filterPeriod, ['monthly', 'yearly']) ? $this->filterYear : null; // Only apply filterYear if explicitly selected

        // Base query for CashFlow or TransactionSummary
        if ($hasCashFlowData) {
            $balanceQuery = CashFlow::where('cooperation_id', $cooperationId);
        } else {
            $balanceQuery = TransactionSummary::forCooperation();
        }

        // Apply filters to current balance query - get balance up to the end of the filtered period
        // For 'all_time', we get the absolute latest balance.
        // For 'monthly' or 'yearly', we get the balance up to the end of the selected month/year.
        if ($this->filterPeriod === 'monthly' && $effectiveMonth && $effectiveYear) {
            $endDate = Carbon::createFromDate($effectiveYear, $effectiveMonth)->endOfMonth();
            $balanceQuery->whereDate('sort_date', '<=', $endDate);
        } elseif ($this->filterPeriod === 'yearly' && $effectiveYear) {
            $endDate = Carbon::createFromDate($effectiveYear, 12)->endOfMonth(); // End of the year
            $balanceQuery->whereDate('sort_date', '<=', $endDate);
        }
        // If filterPeriod is 'all_time', no date limit is applied, so it gets the overall latest balance.

        $currentBalance = $balanceQuery->orderByDesc('sort_date')->first()?->balance_after ?? 0;

        // --- Calculations for dashboard cards (total inflow, total outflow, net cash flow) ---
        $transactionsForCardsQuery = null;
        if ($hasCashFlowData) {
            $transactionsForCardsQuery = CashFlow::where('cooperation_id', $cooperationId);
        } else {
            $transactionsForCardsQuery = TransactionSummary::forCooperation();
        }

        // Apply filters to transactions for cards
        if ($effectiveMonth) {
            $transactionsForCardsQuery->whereMonth('sort_date', $effectiveMonth);
        }
        if ($effectiveYear) {
            $transactionsForCardsQuery->whereYear('sort_date', $effectiveYear);
        }

        $transactionsForCards = $transactionsForCardsQuery->get();

        $totalInflow = $transactionsForCards->where('type', 'inflow')->sum('amount');
        $totalOutflow = $transactionsForCards->where('type', 'outflow')->sum('amount');
        $netCashFlow = $totalInflow - $totalOutflow;

        // --- Monthly trends (always for the selected year, regardless of period filter, defaulting to current year) ---
        $monthlyInflowTrend = collect([]);
        $monthlyOutflowTrend = collect([]);
        $balanceTrend = collect([]);

        $yearForTrends = $this->filterYear ?? $currentYear; // Use filterYear if set, otherwise current year for trends

        $yearlyTransactionsForTrends = null;
        if ($hasCashFlowData) {
            $yearlyTransactionsForTrends = CashFlow::where('cooperation_id', $cooperationId)
                ->whereYear('transaction_date', $yearForTrends)
                ->orderBy('transaction_date', 'asc')
                ->get();
        } else {
            $yearlyTransactionsForTrends = TransactionSummary::forCooperation()
                ->whereYear('sort_date', $yearForTrends)
                ->orderBy('sort_date', 'asc')
                ->get();
        }

        $runningBalanceMonthly = 0;
        foreach (range(1, 12) as $month) {
            $monthTransactions = $yearlyTransactionsForTrends->filter(function ($transaction) use ($month) {
                return Carbon::parse($transaction->sort_date)->month == $month;
            });

            $monthlyInflowTrend[$month] = $monthTransactions->where('type', 'inflow')->sum('amount');
            $monthlyOutflowTrend[$month] = $monthTransactions->where('type', 'outflow')->sum('amount');

            // Calculate cumulative balance for balanceTrend
            foreach ($monthTransactions as $transaction) {
                $runningBalanceMonthly += ($transaction->type == 'inflow' ? $transaction->amount : -$transaction->amount);
            }
            $balanceTrend[$month] = $runningBalanceMonthly;
        }

        // --- Category Breakdown ---
        $transactionsForCategoryBreakdownQuery = null;
        if ($hasCashFlowData) {
            $transactionsForCategoryBreakdownQuery = CashFlow::where('cooperation_id', $cooperationId);
        } else {
            $transactionsForCategoryBreakdownQuery = TransactionSummary::forCooperation();
        }

        if ($effectiveMonth) {
            $transactionsForCategoryBreakdownQuery->whereMonth('sort_date', $effectiveMonth);
        }
        if ($effectiveYear) {
            $transactionsForCategoryBreakdownQuery->whereYear('sort_date', $effectiveYear);
        }

        $transactionsForCategoryBreakdown = $transactionsForCategoryBreakdownQuery->get();

        $categoryBreakdownInflow = $transactionsForCategoryBreakdown->where('type', 'inflow')
            ->groupBy('category')
            ->map(fn ($group) => ['category' => $group->first()->category, 'type' => 'inflow', 'total' => $group->sum('amount')])
            ->values();
        
        $categoryBreakdownOutflow = $transactionsForCategoryBreakdown->where('type', 'outflow')
            ->groupBy('category')
            ->map(fn ($group) => ['category' => $group->first()->category, 'type' => 'outflow', 'total' => $group->sum('amount')])
            ->values();

        $categoryBreakdown = [
            'inflow' => $categoryBreakdownInflow->toArray(),
            'outflow' => $categoryBreakdownOutflow->toArray(),
        ];

        // --- Daily Transactions ---
        $transactionsForDailyTransactionsQuery = null;
        if ($hasCashFlowData) {
            $transactionsForDailyTransactionsQuery = CashFlow::where('cooperation_id', $cooperationId);
        } else {
            $transactionsForDailyTransactionsQuery = TransactionSummary::forCooperation();
        }

        if ($effectiveMonth) {
            $transactionsForDailyTransactionsQuery->whereMonth('sort_date', $effectiveMonth);
        }
        if ($effectiveYear) {
            $transactionsForDailyTransactionsQuery->whereYear('sort_date', $effectiveYear);
        }

        $transactionsForDailyTransactions = $transactionsForDailyTransactionsQuery->get();

        $dailyTransactions = $transactionsForDailyTransactions->groupBy(fn ($item) => Carbon::parse($item->transaction_date)->format('Y-m-d'))
            ->map(fn ($group) => [
                'date' => Carbon::parse($group->first()->transaction_date)->format('Y-m-d'),
                'inflow' => $group->where('type', 'inflow')->sum('amount'),
                'outflow' => $group->where('type', 'outflow')->sum('amount'),
            ])->keyBy('date')->toArray();

        return [
            'current_balance' => $currentBalance,
            'total_inflow' => $totalInflow,
            'total_outflow' => $totalOutflow,
            'net_cash_flow' => $netCashFlow,
            'monthly_inflow_trend' => $monthlyInflowTrend->toArray(),
            'monthly_outflow_trend' => $monthlyOutflowTrend->toArray(),
            'balance_trend' => $balanceTrend->toArray(),
            'category_breakdown' => $categoryBreakdown,
            'daily_transactions' => $dailyTransactions,
        ];
    }

    protected function getWeekOptions(): array
    {
        $weeks = [];
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        
        $weekNumber = 1;
        $currentDate = $startOfMonth->copy();
        
        while ($currentDate <= $endOfMonth) {
            $weekStart = $currentDate->copy()->startOfWeek();
            $weekEnd = $currentDate->copy()->endOfWeek();
            
            if ($weekEnd > $endOfMonth) {
                $weekEnd = $endOfMonth;
            }
            
            $weeks[$weekNumber] = "Minggu ke-{$weekNumber} ({$weekStart->format('d/m')} - {$weekEnd->format('d/m')})";
            
            $currentDate->addWeek();
            $weekNumber++;
        }
        
        return $weeks;
    }

    public function form(Form $form): Form
    {
        $currentYear = now()->year;
        $years = range($currentYear, $currentYear - 5); // Last 5 years

        return $form
            ->schema([
                Select::make('filterPeriod')
                    ->label('Periode')
                    ->options([
                        'all_time' => 'Semua Waktu',
                        'monthly' => 'Bulanan',
                        'yearly' => 'Tahunan',
                    ])
                    ->live()
                    ->default('all_time'), // Change default to all_time
                // Select::make('filterMonth')
                //     ->label('Bulan')
                //     ->options(array_combine(range(1, 12), ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']))
                //     ->default(now()->month)
                //     ->visible(fn (Forms\Get $get) => $get('filterPeriod') === 'monthly'),
                // Select::make('filterYear')
                //     ->label('Tahun')
                //     ->options(array_combine($years, $years))
                //     ->default(now()->year)
                //     ->visible(fn (Forms\Get $get) => in_array($get('filterPeriod'), ['monthly', 'yearly'])),
            ])
            ->columns(3);
    }

    public function getTitle(): string
    {
        return 'Laporan Arus Kas';
    }

    // Method to provide chart data to Blade
    public function getChartData(): array
    {
        $summary = $this->getCashFlowSummary();
        return [
            'monthlyInflowTrend' => array_map(fn($month) => $summary['monthly_inflow_trend'][$month] ?? 0, range(1, 12)),
            'monthlyOutflowTrend' => array_map(fn($month) => $summary['monthly_outflow_trend'][$month] ?? 0, range(1, 12)),
            'balanceTrend' => array_map(fn($month) => $summary['balance_trend'][$month] ?? 0, range(1, 12)),
        ];
    }

    // Livewire lifecycle hook to update charts when filters change
    public function updated($name, $value)
    {
        if (in_array($name, ['filterPeriod', 'filterMonth', 'filterYear'])) {
            // Re-render the charts with updated data
            $this->dispatch('updateCharts', chartData: $this->getChartData());
        }
    }

    public function exportPdf()
    {
        $cooperationId = Auth::user()->cooperation_id;
        $hasCashFlowData = CashFlow::where('cooperation_id', $cooperationId)->exists();

        if ($hasCashFlowData) {
            $data = CashFlow::where('cooperation_id', $cooperationId);
        } else {
            // Use TransactionSummary model directly, running balance is now calculated in the model's scope
            $data = TransactionSummary::forCooperation();
        }

        // Apply filters to the export data
        if ($this->filterPeriod === 'monthly') {
            $data->whereMonth('sort_date', $this->filterMonth)
                 ->whereYear('sort_date', $this->filterYear);
        } elseif ($this->filterPeriod === 'yearly') {
            $data->whereYear('sort_date', $this->filterYear);
        }

        $data = $data->orderBy('sort_date', 'desc')->get();

        $pdf = Pdf::loadView('pdf.financial-report', [
            'data' => $data,
            'cooperation' => Auth::user()->cooperation,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'laporan-arus-kas-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel()
    {
        $cooperationId = Auth::user()->cooperation_id;

        $fileName = 'laporan-arus-kas-' . now()->format('Y-m-d') . '.xlsx';

        // Get data using the same filtering logic as the table and PDF
        $hasCashFlowData = CashFlow::where('cooperation_id', $cooperationId)->exists();

        if ($hasCashFlowData) {
            $query = CashFlow::where('cooperation_id', $cooperationId);
        } else {
            $query = TransactionSummary::forCooperation();
        }

        if ($this->filterPeriod === 'monthly') {
            $query->whereMonth('sort_date', $this->filterMonth)
                 ->whereYear('sort_date', $this->filterYear);
        } elseif ($this->filterPeriod === 'yearly') {
            $query->whereYear('sort_date', $this->filterYear);
        }

        $data = $query->orderBy('sort_date', 'desc')->get();

        return Excel::download(new FinancialReportExport($data, $this->filterPeriod, $this->filterMonth, $this->filterYear), $fileName);
    }
}
