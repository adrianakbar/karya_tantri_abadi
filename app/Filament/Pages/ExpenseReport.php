<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Models\Expense;
use App\Models\ExpenseCategory;
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
use App\Exports\ExpenseReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExpenseReport extends Page implements Tables\Contracts\HasTable, Forms\Contracts\HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationLabel = 'Laporan Pengeluaran';
    protected static ?string $navigationGroup = 'Laporan';
    protected static string $view = 'filament.pages.expense-report';
    protected static ?string $title = 'Laporan Pengeluaran';

    public static function getNavigationGroup(): ?string
    {
        // Group under 'Laporan' for Bendahara and other panels; hide group for SPV
        $panelId = \Filament\Facades\Filament::getCurrentPanel()?->getId();
        return $panelId === 'spv' ? null : 'Laporan';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Expense::query()->where('cooperation_id', Auth::user()->cooperation_id))
            ->columns([
                Tables\Columns\TextColumn::make('expense_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expense_number')
                    ->label('No. Pengeluaran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->colors([
                        'primary' => 'Operasional',
                        'warning' => 'Perawatan',
                        'success' => 'Marketing',
                        'danger' => 'Lainnya',
                    ]),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Keterangan')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('recipient')
                    ->label('Penerima')
                    ->searchable(),
                Tables\Columns\TextColumn::make('receipt_number')
                    ->label('No. Kuitansi')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('processor.name')
                //     ->label('Diproses Oleh'),
                // Tables\Columns\TextColumn::make('approver.name')
                //     ->label('Disetujui Oleh'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'approved',
                        'warning' => 'pending',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'approved' => 'Disetujui',
                            'pending' => 'Pending',
                            'rejected' => 'Ditolak',
                            default => $state,
                        };
                    }),
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
                    ]),
                Filter::make('kategori')
                    ->form([
                        Select::make('expense_category_id')
                            ->label('Kategori Pengeluaran')
                            ->options(ExpenseCategory::all()->pluck('name', 'id'))
                            ->placeholder('Semua Kategori'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['expense_category_id'] ?? null,
                            fn (Builder $query, $categoryId): Builder => $query->where('expense_category_id', $categoryId),
                        );
                    }),
                Filter::make('jumlah')
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
                        return $query->when($data['amount_range'] ?? null, function (Builder $query, $range) {
                            return match($range) {
                                'small' => $query->where('amount', '<', 1000000),
                                'medium' => $query->whereBetween('amount', [1000000, 5000000]),
                                'large' => $query->where('amount', '>', 5000000),
                                default => $query,
                            };
                        });
                    }),
                Filter::make('status')
                    ->form([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'approved' => 'Disetujui',
                                'pending' => 'Pending',
                                'rejected' => 'Ditolak',
                            ])
                            ->placeholder('Semua Status'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['status'] ?? null,
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
                //         $this->dispatch('open-modal', id: 'expense-summary');
                //     }),
                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        return Excel::download(new ExpenseReportExport(), 'laporan-pengeluaran-' . now()->format('Y-m-d') . '.xlsx');
                    }),
                Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document')
                    ->color('danger')
                    ->action(function () {
                        $expenses = Expense::where('cooperation_id', Auth::user()->cooperation_id)
                            ->where('status', 'approved')
                            ->with(['category', 'processor', 'approver'])
                            ->orderBy('expense_date', 'desc')
                            ->get();

                        $pdf = Pdf::loadView('pdf.expense-report', [
                            'expenses' => $expenses,
                            'cooperation' => Auth::user()->cooperation,
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'laporan-pengeluaran-' . now()->format('Y-m-d') . '.pdf');
                    }),
            ])
            ->defaultSort('expense_date', 'desc')
            ->striped();
    }

    public function getExpenseSummary()
    {
        $cooperationId = Auth::user()->cooperation_id;
        
        return [
            'total_expenses' => Expense::where('cooperation_id', $cooperationId)
                ->where('status', 'approved')
                ->sum('amount'),
            'monthly_expenses' => Expense::where('cooperation_id', $cooperationId)
                ->where('status', 'approved')
                ->whereMonth('expense_date', now()->month)
                ->whereYear('expense_date', now()->year)
                ->sum('amount'),
            'by_category' => Expense::where('cooperation_id', $cooperationId)
                ->where('status', 'approved')
                ->with('category')
                ->select('expense_category_id', DB::raw('SUM(amount) as total'))
                ->groupBy('expense_category_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->category->name => $item->total];
                }),
            'pending_approval' => Expense::where('cooperation_id', $cooperationId)
                ->where('status', 'pending')
                ->count(),
        ];
    }

    public function getTitle(): string
    {
        return 'Laporan Pengeluaran';
    }
}
