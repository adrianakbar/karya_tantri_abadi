<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\LoanType;
use App\Models\User;
use App\Exports\LoanReportExport;
use App\Exports\LoanPaymentExport;
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

class LoanReport extends Page implements Tables\Contracts\HasTable, Forms\Contracts\HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Laporan Pinjaman & Cicilan';
    protected static ?string $navigationGroup = 'Laporan';
    protected static string $view = 'filament.pages.loan-report';
    protected static ?string $title = 'Laporan Pinjaman & Cicilan';

    public $activeTab = 'loans';

    public static function getNavigationGroup(): ?string
    {
        // Group under 'Laporan' for Bendahara and other panels; hide group for SPV
        $panelId = \Filament\Facades\Filament::getCurrentPanel()?->getId();
        return $panelId === 'spv' ? null : 'Laporan';
    }

    public function table(Table $table): Table
    {
        if ($this->activeTab === 'loans') {
            return $this->loansTable($table);
        } else {
            return $this->paymentsTable($table);
        }
    }

    protected function loansTable(Table $table): Table
    {
        return $table
            ->query(Loan::query()->where('cooperation_id', Auth::user()->cooperation_id))
            ->columns([
                Tables\Columns\TextColumn::make('loan_number')
                    ->label('No. Pinjaman')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Anggota')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.member_number')
                    ->label('No. Anggota')
                    ->searchable(),
                Tables\Columns\TextColumn::make('loanType.name')
                    ->label('Jenis Pinjaman'),
                Tables\Columns\TextColumn::make('principal_amount')
                    ->label('Jumlah Pinjaman')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('interest_rate')
                    ->label('Bunga (%)')
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%'),
                Tables\Columns\TextColumn::make('tenor_months')
                    ->label('Tenor (Bulan)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('monthly_payment')
                    ->label('Cicilan/Bulan')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('remaining_balance')
                    ->label('Sisa Pinjaman')
                    ->money('IDR')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'primary' => 'active',
                        'gray' => 'completed',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'pending' => 'Pending',
                            'approved' => 'Disetujui',
                            'active' => 'Aktif',
                            'completed' => 'Lunas',
                            'rejected' => 'Ditolak',
                            default => $state,
                        };
                    }),
                Tables\Columns\TextColumn::make('disbursement_date')
                    ->label('Tgl Pencairan')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d/m/Y'),
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
                        return $query->when($data['period_type'] ?? null, function ($query, $type) use ($data) {
                            return match ($type) {
                                'daily' => $query->whereDate('disbursement_date', $data['specific_date'] ?? null),
                                'weekly' => $query->when($data['month'] ?? null, function ($query) use ($data) {
                                    $year = $data['year'] ?? now()->year;
                                    $month = $data['month'];
                                    return $query->whereBetween('disbursement_date', [
                                        Carbon::create($year, $month, 1)->startOfMonth(),
                                        Carbon::create($year, $month, 1)->endOfMonth()
                                    ]);
                                }),
                                'monthly' => $query->whereMonth('disbursement_date', $data['month'] ?? null)->whereYear('disbursement_date', $data['year'] ?? null),
                                'yearly' => $query->whereYear('disbursement_date', $data['year'] ?? null),
                                'custom' => $query->whereBetween('disbursement_date', [$data['from_date'] ?? null, $data['to_date'] ?? null]),
                                default => $query
                            };
                        });
                    }),
                Filter::make('jenis_pinjaman')
                    ->form([
                        Select::make('loan_type_id')
                            ->label('Jenis Pinjaman')
                            ->options(LoanType::all()->pluck('name', 'id'))
                            ->placeholder('Semua Jenis'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['loan_type_id'],
                            fn (Builder $query, $typeId): Builder => $query->where('loan_type_id', $typeId),
                        );
                    }),
                Filter::make('status')
                    ->form([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Disetujui',
                                'active' => 'Aktif',
                                'completed' => 'Lunas',
                                'rejected' => 'Ditolak',
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
            ->defaultSort('disbursement_date', 'desc');
    }

    protected function paymentsTable(Table $table): Table
    {
        return $table
            ->query(LoanPayment::query()->whereHas('loan', function ($query) {
                $query->where('cooperation_id', Auth::user()->cooperation_id);
            }))
            ->columns([
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Tanggal Bayar')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_number')
                    ->label('No. Pembayaran')
                    ->searchable(),
                Tables\Columns\TextColumn::make('loan.loan_number')
                    ->label('No. Pinjaman')
                    ->searchable(),
                Tables\Columns\TextColumn::make('loan.user.name')
                    ->label('Nama Anggota')
                    ->searchable(),
                Tables\Columns\TextColumn::make('installment_number')
                    ->label('Cicilan Ke-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('principal_amount')
                    ->label('Pokok')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('interest_amount')
                    ->label('Bunga')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Bayar')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('remaining_balance')
                    ->label('Sisa Pinjaman')
                    ->money('IDR'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'pending',
                        'danger' => 'overdue',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'paid' => 'Lunas',
                            'pending' => 'Pending',
                            'overdue' => 'Terlambat',
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
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['period_type'] ?? null, function ($query, $type) use ($data) {
                            return match ($type) {
                                'daily' => $query->whereDate('payment_date', $data['specific_date'] ?? null),
                                'weekly' => $query->when($data['month'] ?? null, function ($query) use ($data) {
                                    $year = $data['year'] ?? now()->year;
                                    $month = $data['month'];
                                    return $query->whereBetween('payment_date', [
                                        Carbon::create($year, $month, 1)->startOfMonth(),
                                        Carbon::create($year, $month, 1)->endOfMonth()
                                    ]);
                                }),
                                'monthly' => $query->whereMonth('payment_date', $data['month'] ?? null)->whereYear('payment_date', $data['year'] ?? null),
                                'yearly' => $query->whereYear('payment_date', $data['year'] ?? null),
                                'custom' => $query->whereBetween('payment_date', [$data['from_date'] ?? null, $data['to_date'] ?? null]),
                                default => $query
                            };
                        });
                    }),
                Filter::make('status_bayar')
                    ->form([
                        Select::make('status')
                            ->label('Status Pembayaran')
                            ->options([
                                'paid' => 'Lunas',
                                'pending' => 'Pending',
                                'overdue' => 'Terlambat',
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
            ->defaultSort('payment_date', 'desc');
    }

    public function getTableQuery(): Builder
    {
        if ($this->activeTab === 'loans') {
            return Loan::query()->where('cooperation_id', Auth::user()->cooperation_id);
        } else {
            return LoanPayment::query()->whereHas('loan', function ($query) {
                $query->where('cooperation_id', Auth::user()->cooperation_id);
            });
        }
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
        $this->dispatch('tabChanged');
    }

    public function exportLoansExcel()
    {
        return Excel::download(new LoanReportExport(), 'laporan-pinjaman-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportLoansPdf()
    {
        $loanData = Loan::where('cooperation_id', Auth::user()->cooperation_id)
            ->with(['user', 'loanType'])
            ->get();

        $pdf = Pdf::loadView('pdf.loan-report', [
            'loanData' => $loanData,
            'cooperation' => Auth::user()->cooperation,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'laporan-pinjaman-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportPaymentsExcel()
    {
        return Excel::download(new LoanPaymentExport(), 'laporan-pembayaran-pinjaman-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportPaymentsPdf()
    {
        $paymentData = LoanPayment::whereHas('loan', function ($query) {
                $query->where('cooperation_id', Auth::user()->cooperation_id);
            })
            ->with(['loan.user', 'loan.loanType'])
            ->orderBy('payment_date', 'desc')
            ->get();

        $pdf = Pdf::loadView('pdf.loan-payment-report', [
            'paymentData' => $paymentData,
            'cooperation' => Auth::user()->cooperation,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'laporan-pembayaran-pinjaman-' . now()->format('Y-m-d') . '.pdf');
    }

    public function getTitle(): string
    {
        return 'Laporan Pinjaman & Cicilan';
    }
}
