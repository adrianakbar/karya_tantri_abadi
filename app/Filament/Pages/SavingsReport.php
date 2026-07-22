<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Models\SavingsTransaction;
use App\Models\SavingsType;
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
use App\Exports\SavingsReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class SavingsReport extends Page implements Tables\Contracts\HasTable, Forms\Contracts\HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Laporan Tabungan';
    protected static ?string $navigationGroup = 'Laporan';
    protected static string $view = 'filament.pages.savings-report';
    protected static ?string $title = 'Laporan Tabungan';

    public static function getNavigationGroup(): ?string
    {
        // Group under 'Laporan' for Bendahara and other panels; hide group for SPV
        $panelId = \Filament\Facades\Filament::getCurrentPanel()?->getId();
        return $panelId === 'spv' ? null : 'Laporan';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(SavingsTransaction::with(['user', 'savingsType', 'processor'])->where('cooperation_id', Auth::user()->cooperation_id))
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_number')
                    ->label('No. Transaksi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Anggota')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.member_number')
                    ->label('No. Anggota')
                    ->searchable(),
                Tables\Columns\TextColumn::make('savingsType.name')
                    ->label('Jenis Tabungan')
                    ->badge()
                    ->colors([
                        'primary' => 'Simpanan Pokok',
                        'success' => 'Simpanan Wajib',
                        'warning' => 'Simpanan Sukarela',
                        'info' => 'Tabungan',
                    ]),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('processor.name')
                    ->label('Diproses Oleh'),
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
                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(50),
            ])
            ->filters([
                Filter::make('periode')
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
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            );
                    }),
                Filter::make('jenis_tabungan')
                    ->form([
                        Select::make('savings_type_id')
                            ->label('Jenis Tabungan')
                            ->options(SavingsType::all()->pluck('name', 'id'))
                            ->placeholder('Semua Jenis'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['savings_type_id'],
                            fn (Builder $query, $typeId): Builder => $query->where('savings_type_id', $typeId),
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
                                'completed' => 'Selesai',
                                'approved' => 'Disetujui',
                                'pending' => 'Pending',
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
            ->headerActions([
                // Action::make('summary')
                //     ->label('Ringkasan')
                //     ->icon('heroicon-o-document-chart-bar')
                //     ->color('info')
                //     ->action(function () {
                //         $this->dispatch('open-modal', id: 'savings-summary');
                //     }),
                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        return Excel::download(new SavingsReportExport(), 'laporan-simpanan-' . now()->format('Y-m-d') . '.xlsx');
                    }),
                Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document')
                    ->color('danger')
                    ->action(function () {
                        $savings = SavingsTransaction::where('cooperation_id', Auth::user()->cooperation_id)
                            ->where('status', 'completed')
                            ->with(['user', 'savingsType', 'processor'])
                            ->orderBy('transaction_date', 'desc')
                            ->get();

                        $pdf = Pdf::loadView('pdf.savings-report', [
                            'savings' => $savings,
                            'cooperation' => Auth::user()->cooperation,
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'laporan-simpanan-' . now()->format('Y-m-d') . '.pdf');
                    }),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->striped();
    }

    public function getSavingsSummary()
    {
        $cooperationId = Auth::user()->cooperation_id;
        
        return [
            'total_members' => User::where('cooperation_id', $cooperationId)->count(),
            'total_savings' => SavingsTransaction::where('cooperation_id', $cooperationId)
                ->where('status', 'approved')
                ->sum('amount'),
            'monthly_savings' => SavingsTransaction::where('cooperation_id', $cooperationId)
                ->where('status', 'approved')
                ->whereMonth('transaction_date', now()->month)
                ->whereYear('transaction_date', now()->year)
                ->sum('amount'),
            'by_type' => SavingsTransaction::where('cooperation_id', $cooperationId)
                ->where('status', 'approved')
                ->with('savingsType')
                ->select('savings_type_id', DB::raw('SUM(amount) as total'))
                ->groupBy('savings_type_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->savingsType->name => $item->total];
                }),
        ];
    }

    public function getTitle(): string
    {
        return 'Laporan Tabungan';
    }
}
