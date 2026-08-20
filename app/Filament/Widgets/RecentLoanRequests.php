<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentLoanRequests extends BaseWidget
{
    protected static ?string $heading = 'Pengajuan Pinjaman Terbaru (Menunggu)';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $cooperationId = auth()->user()->cooperation_id;

        return $table
            ->query(
                Loan::query()
                    ->where('cooperation_id', $cooperationId)
                    ->where('status', 'pending')
                    ->latest('application_date')
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('borrower_name')
                    ->label('Nama Pemohon')
                    ->getStateUsing(fn (Loan $record) => $record->borrower_name)
                    ->searchable(query: fn (Builder $q, string $s) => $q
                        ->where('applicant_name', 'like', "%{$s}%")
                        ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', "%{$s}%"))),

                TextColumn::make('user.member_number')
                    ->label('No. Anggota')
                    ->placeholder('—'),

                TextColumn::make('principal_amount')
                    ->label('Nominal')
                    ->money('IDR', locale: 'id'),

                TextColumn::make('tenor_months')
                    ->label('Tenor')
                    ->suffix(' bln'),

                TextColumn::make('application_date')
                    ->label('Tgl Pengajuan')
                    ->date('d M Y'),
            ])
            ->paginated(false)
            ->emptyStateHeading('Tidak ada pengajuan menunggu')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
