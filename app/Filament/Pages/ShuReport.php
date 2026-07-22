<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Models\ShuDistribution;
use App\Models\ShuMemberShare;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\User;
use App\Exports\ShuCalculationExport;
use App\Exports\ShuDistributionExport;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ShuReport extends Page implements Tables\Contracts\HasTable, Forms\Contracts\HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Laporan SHU';
    protected static ?string $navigationGroup = 'Laporan';
    protected static string $view = 'filament.pages.shu-report';
    protected static ?string $title = 'Laporan SHU (Sisa Hasil Usaha)';

    public $activeTab = 'calculation';

    public static function getNavigationGroup(): ?string
    {
        // Group under 'Laporan' for Bendahara and other panels; hide group for SPV
        $panelId = \Filament\Facades\Filament::getCurrentPanel()?->getId();
        return $panelId === 'spv' ? null : 'Laporan';
    }

    public function table(Table $table): Table
    {
        if ($this->activeTab === 'calculation') {
            return $this->calculationTable($table);
        } else {
            return $this->distributionTable($table);
        }
    }

    protected function calculationTable(Table $table): Table
    {
        return $table
            ->query(ShuDistribution::query()->where('cooperation_id', Auth::user()->cooperation_id))
            ->columns([
                Tables\Columns\TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Total Pendapatan')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('total_expenses')
                    ->label('Total Pengeluaran')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('total_shu')
                    ->label('Total SHU')
                    ->money('IDR')
                    ->sortable()
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('distribution_date')
                    ->label('Tanggal Distribusi')
                    ->date('d/m/Y'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'calculated',
                        'success' => 'distributed',
                        'primary' => 'pending',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'calculated' => 'Dihitung',
                            'distributed' => 'Didistribusi',
                            'pending' => 'Pending',
                            default => $state,
                        };
                    }),
                Tables\Columns\TextColumn::make('calculator.name')
                    ->label('Dihitung Oleh'),
                Tables\Columns\TextColumn::make('distributor.name')
                    ->label('Didistribusi Oleh'),
            ])
            ->filters([
                Filter::make('tahun')
                    ->form([
                        Select::make('year')
                            ->label('Tahun')
                            ->options(array_combine(range(2020, 2030), range(2020, 2030)))
                            ->default(now()->year),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['year'],
                            fn (Builder $query, $year): Builder => $query->where('year', $year),
                        );
                    }),
                Filter::make('status')
                    ->form([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'calculated' => 'Dihitung',
                                'distributed' => 'Didistribusi',
                                'pending' => 'Pending',
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
                Action::make('calculate_shu')
                    ->label('Hitung SHU Otomatis')
                    ->icon('heroicon-o-calculator')
                    ->color('primary')
                    ->action(function () {
                        $this->calculateAutoShu();
                    }),
            ])
            ->defaultSort('year', 'desc');
    }

    protected function distributionTable(Table $table): Table
    {
        return $table
            ->query(ShuMemberShare::query()->whereHas('distribution', function ($query) {
                $query->where('cooperation_id', Auth::user()->cooperation_id);
            }))
            ->columns([
                Tables\Columns\TextColumn::make('distribution.year')
                    ->label('Tahun')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Anggota')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.member_number')
                    ->label('No. Anggota')
                    ->searchable(),
                Tables\Columns\TextColumn::make('savings_contribution')
                    ->label('Bagian Simpanan')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('transaction_contribution')
                    ->label('Bagian Transaksi')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('shu_amount')
                    ->label('Total Bagian')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'pending',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'paid' => 'Dibayar',
                            'pending' => 'Pending',
                            default => $state,
                        };
                    }),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Tanggal Bayar')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->filters([
                Filter::make('tahun_distribusi')
                    ->form([
                        Select::make('year')
                            ->label('Tahun Distribusi')
                            ->options(array_combine(range(2020, 2030), range(2020, 2030)))
                            ->default(now()->year),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['year'],
                            fn (Builder $query, $year): Builder => $query->whereHas('distribution', function ($q) use ($year) {
                                $q->where('year', $year);
                            }),
                        );
                    }),
                Filter::make('anggota')
                    ->form([
                        Select::make('user_id')
                            ->label('Anggota')
                            ->options(User::where('cooperation_id', Auth::user()->cooperation_id)->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Semua Anggota'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['user_id'],
                            fn (Builder $query, $userId): Builder => $query->where('user_id', $userId),
                        );
                    }),
                Filter::make('status')
                    ->form([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'paid' => 'Dibayar',
                                'pending' => 'Pending',
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
            ->defaultSort('shu_amount', 'desc');
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    public function exportShuCalculationExcel()
    {
        return Excel::download(new ShuCalculationExport(), 'laporan-perhitungan-shu-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportShuCalculationPdf()
    {
        $shuData = ShuDistribution::where('cooperation_id', Auth::user()->cooperation_id)
            ->with(['calculator', 'distributor'])
            ->get();

        $pdf = Pdf::loadView('pdf.shu-calculation-report', [
            'shuData' => $shuData,
            'cooperation' => Auth::user()->cooperation,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'laporan-perhitungan-shu-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportShuDistributionExcel()
    {
        return Excel::download(new ShuDistributionExport(), 'laporan-distribusi-shu-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportShuDistributionPdf()
    {
        $distributionData = ShuMemberShare::whereHas('distribution', function ($query) {
                $query->where('cooperation_id', Auth::user()->cooperation_id);
            })
            ->with(['distribution', 'user'])
            ->orderBy('shu_amount', 'desc')
            ->get();

        $pdf = Pdf::loadView('pdf.shu-distribution-report', [
            'distributionData' => $distributionData,
            'cooperation' => Auth::user()->cooperation,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'laporan-distribusi-shu-' . now()->format('Y-m-d') . '.pdf');
    }

    protected function calculateRevenue(int $year): float
    {
        return Sale::where('cooperation_id', Auth::user()->cooperation_id)
            ->whereYear('sale_date', $year)
            ->sum('total_amount');
    }

    protected function calculateExpenses(int $year): float
    {
        $purchases = Purchase::where('cooperation_id', Auth::user()->cooperation_id)
            ->whereYear('purchase_date', $year)
            ->sum('total_amount');

        $expenses = Expense::where('cooperation_id', Auth::user()->cooperation_id)
            ->whereYear('expense_date', $year)
            ->sum('amount');

        return $purchases + $expenses;
    }

    public function calculateAutoShu(): void
    {
        $year = now()->year - 1; // SHU untuk tahun sebelumnya
        $cooperationId = Auth::user()->cooperation_id;

        // Hitung total pendapatan
        $totalRevenue = $this->calculateRevenue($year);

        // Hitung total pengeluaran
        $totalExpenses = $this->calculateExpenses($year);

        // Hitung SHU
        $totalShu = $totalRevenue - $totalExpenses;

        if ($totalShu > 0) {
            // Buat atau update record SHU Distribution
            $shuDistribution = ShuDistribution::updateOrCreate([
                'cooperation_id' => $cooperationId,
                'year' => $year,
            ], [
                'total_revenue' => $totalRevenue,
                'total_expenses' => $totalExpenses,
                'total_shu' => $totalShu,
                'calculated_by' => Auth::id(),
                'status' => 'calculated',
                'notes' => "SHU dihitung otomatis untuk tahun {$year}",
            ]);

            // Hitung bagian untuk setiap anggota
            $this->calculateMemberShares($shuDistribution);

            Notification::make()
                ->title('Perhitungan SHU Berhasil')
                ->body("SHU untuk tahun {$year} telah dihitung: Rp " . number_format($totalShu, 0, ',', '.'))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Tidak Ada SHU')
                ->body("Tidak ada SHU untuk tahun {$year} karena pengeluaran melebihi pendapatan")
                ->warning()
                ->send();
        }
    }

    protected function calculateMemberShares(ShuDistribution $distribution): void
    {
        $cooperationId = Auth::user()->cooperation_id;
        $year = $distribution->year;

        // Ambil semua anggota aktif
        $members = User::where('cooperation_id', $cooperationId)->get();

        // Total simpanan dan transaksi untuk perhitungan persentase
        $totalSavings = 0;
        $totalTransactions = 0;

        foreach ($members as $member) {
            $memberSavings = $member->savingsTransactions()
                ->whereYear('transaction_date', $year)
                ->sum('amount');
            $memberTransactions = $member->sales()
                ->whereYear('sale_date', $year)
                ->sum('total_amount');

            $totalSavings += $memberSavings;
            $totalTransactions += $memberTransactions;
        }

        // Distribusi SHU (contoh: 50% dari simpanan, 50% dari transaksi)
        $savingsShare = $distribution->total_shu * 0.5;
        $transactionShare = $distribution->total_shu * 0.5;

        foreach ($members as $member) {
            $memberSavings = $member->savingsTransactions()
                ->whereYear('transaction_date', $year)
                ->sum('amount');
            $memberTransactions = $member->sales()
                ->whereYear('sale_date', $year)
                ->sum('total_amount');

            $savingsPortion = $totalSavings > 0 ? ($memberSavings / $totalSavings) * $savingsShare : 0;
            $transactionPortion = $totalTransactions > 0 ? ($memberTransactions / $totalTransactions) * $transactionShare : 0;
            $totalShare = $savingsPortion + $transactionPortion;
            $percentage = $distribution->total_shu > 0 ? ($totalShare / $distribution->total_shu) * 100 : 0;

            if ($totalShare > 0) {
                ShuMemberShare::updateOrCreate([
                    'shu_distribution_id' => $distribution->id,
                    'user_id' => $member->id,
                ], [
                    'savings_contribution' => $savingsPortion,
                    'transaction_contribution' => $transactionPortion,
                    'shu_amount' => $totalShare,
                    'status' => 'pending',
                ]);
            }
        }
    }

    public function getTitle(): string
    {
        return 'Laporan SHU (Sisa Hasil Usaha)';
    }

    public static function canAccess(): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
