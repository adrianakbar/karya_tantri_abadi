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
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Exports\IncomeReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class IncomeReport extends Page implements Tables\Contracts\HasTable, Forms\Contracts\HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Laporan Pemasukan';
    protected static ?string $navigationGroup = 'Laporan';
    protected static string $view = 'filament.pages.income-report';
    protected static ?string $title = 'Laporan Pemasukan';

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
        $panelId = \Filament\Facades\Filament::getCurrentPanel()?->getId();
        return $panelId === 'spv' ? null : 'Laporan';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Sale::query()->where('cooperation_id', Auth::user()->cooperation_id))
            ->columns([
                Tables\Columns\TextColumn::make('sale_date')
                    ->label('Tanggal Penjualan')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_number')
                    ->label('No. Penjualan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Penjualan')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'pending',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'completed' => 'Selesai',
                            'pending' => 'Pending',
                            'cancelled' => 'Dibatalkan',
                            default => $state,
                        };
                    }),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->badge()
                    ->colors([
                        'primary' => 'cash',
                        'success' => 'transfer',
                        'warning' => 'credit',
                    ]),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(50),
            ])
            ->filters([
                Filter::make('periode')
                    ->form([
                        Select::make('period_type')
                            ->label('Periode')
                            ->options([
                                'daily' => 'Harian',
                                'weekly' => 'Mingguan',
                                'monthly' => 'Bulanan',
                                'yearly' => 'Tahunan',
                                'custom' => 'Custom'
                            ])
                            ->placeholder('Semua Periode')
                            ->live(),
                        DatePicker::make('specific_date')
                            ->label('Tanggal Spesifik')
                            ->visible(fn ($get) => $get('period_type') === 'daily'),
                        Select::make('month')
                            ->label('Bulan')
                            ->options([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                                4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ])
                            ->placeholder('Pilih Bulan')
                            ->visible(fn ($get) => in_array($get('period_type'), ['monthly', 'weekly'])),
                        Select::make('year')
                            ->label('Tahun')
                            ->options(array_combine(range(2020, 2030), range(2020, 2030)))
                            ->placeholder('Pilih Tahun')
                            ->visible(fn ($get) => in_array($get('period_type'), ['monthly', 'weekly', 'yearly'])),
                        DatePicker::make('from_date')
                            ->label('Dari Tanggal')
                            ->visible(fn ($get) => $get('period_type') === 'custom'),
                        DatePicker::make('to_date')
                            ->label('Sampai Tanggal')
                            ->visible(fn ($get) => $get('period_type') === 'custom'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $periodType = $data['period_type'];
                        $specificDate = $data['specific_date'];
                        $month = $data['month'];
                        $year = $data['year'];
                        $fromDate = $data['from_date'];
                        $toDate = $data['to_date'];

                        return $query->when($periodType, function (Builder $query) use ($periodType, $specificDate, $month, $year, $fromDate, $toDate) {
                            switch ($periodType) {
                                case 'daily':
                                    return $query->whereDate('sale_date', $specificDate);
                                case 'weekly':
                                    $startOfWeek = Carbon::create($year, $month)->startOfWeek();
                                    $endOfWeek = Carbon::create($year, $month)->endOfWeek();
                                    return $query->whereBetween('sale_date', [$startOfWeek, $endOfWeek]);
                                case 'monthly':
                                    return $query->whereMonth('sale_date', $month)->whereYear('sale_date', $year);
                                case 'yearly':
                                    return $query->whereYear('sale_date', $year);
                                case 'custom':
                                    return $query->whereBetween('sale_date', [$fromDate, $toDate]);
                                default:
                                    return $query;
                            }
                        });
                    }),
                Filter::make('amount')
                    ->form([
                        Select::make('amount_range')
                            ->label('Rentang Jumlah')
                            ->options([
                                'small' => '< Rp 1.000.000',
                                'medium' => 'Rp 1.000.000 - Rp 5.000.000',
                                'large' => '> Rp 5.000.000',
                            ])
                            ->placeholder('Semua Jumlah'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['amount_range'], function (Builder $query, $range) {
                            return match($range) {
                                'small' => $query->where('total_amount', '<', 1000000),
                                'medium' => $query->whereBetween('total_amount', [1000000, 5000000]),
                                'large' => $query->where('total_amount', '>', 5000000),
                                default => $query,
                            };
                        });
                    }),
                Filter::make('status')
                    ->form([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->placeholder('Semua Status'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['status'],
                            fn (Builder $query, $status): Builder => $query->where('status', $status),
                        );
                    }),
            ])
            ->headerActions([
                // Action::make('summary')
                //     ->label('Ringkasan')
                //     ->icon('heroicon-o-document-chart-bar')
                //     ->color('info')
                //     ->action(function () {
                //         $this->dispatch('open-modal', id: 'income-summary');
                //     }),
                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        return Excel::download(new IncomeReportExport(), 'laporan-pemasukan-' . now()->format('Y-m-d') . '.xlsx');
                    }),
                Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document')
                    ->color('danger')
                    ->action(function () {
                        $sales = Sale::where('cooperation_id', Auth::user()->cooperation_id)
                            ->where('status', 'completed')
                            ->with(['customer'])
                            ->orderBy('sale_date', 'desc')
                            ->get();

                        $pdf = Pdf::loadView('pdf.income-report', [
                            'sales' => $sales,
                            'cooperation' => Auth::user()->cooperation,
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'laporan-pemasukan-' . now()->format('Y-m-d') . '.pdf');
                    }),
            ])
            ->defaultSort('sale_date', 'desc')
            ->striped();
    }

    public function getIncomeSummary()
    {
        $cooperationId = Auth::user()->cooperation_id;

        return [
            'total_income' => Sale::where('cooperation_id', $cooperationId)
                ->where('status', 'completed')
                ->sum('total_amount'),
            'monthly_income' => Sale::where('cooperation_id', $cooperationId)
                ->where('status', 'completed')
                ->whereMonth('sale_date', now()->month)
                ->whereYear('sale_date', now()->year)
                ->sum('total_amount'),
            'by_customer' => Sale::where('cooperation_id', $cooperationId)
                ->where('status', 'completed')
                ->with('customer')
                ->select('customer_id', DB::raw('SUM(total_amount) as total'))
                ->groupBy('customer_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->customer->name ?? 'Unknown' => $item->total];
                }),
            'pending_sales' => Sale::where('cooperation_id', $cooperationId)
                ->where('status', 'pending')
                ->count(),
        ];
    }

    public function getTitle(): string
    {
        return 'Laporan Pemasukan';
    }
}
