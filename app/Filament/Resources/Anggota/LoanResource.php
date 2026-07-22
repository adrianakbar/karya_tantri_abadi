<?php

namespace App\Filament\Resources\Anggota;

use App\Models\Loan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\Anggota\LoanResource\Pages;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;
    protected static ?string $slug = 'pinjaman';
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Daftar Pinjaman';
    protected static ?string $modelLabel = 'Pinjaman';
    protected static ?string $pluralModelLabel = 'Daftar Pinjaman';
    protected static ?string $navigationGroup = null;

    public static function getEloquentQuery(): Builder
    {
        // Anggota melihat pinjaman organisasinya (read-only)
        $user = Auth::user();

        return parent::getEloquentQuery()
            ->where('cooperation_id', $user?->cooperation_id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('loan_number')
                    ->label('No. Pinjaman')
                    ->disabled(),
                Forms\Components\DatePicker::make('application_date')
                    ->label('Tanggal Pengajuan')
                    ->disabled(),
                Forms\Components\DatePicker::make('disbursement_date')
                    ->label('Tanggal Pencairan')
                    ->disabled(),
                Forms\Components\TextInput::make('net_disbursement')
                    ->label('Cair Bersih')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled()
                    ->helperText('Jumlah dana yang diterima (73% dari nominal)'),
                Forms\Components\TextInput::make('tenor_months')
                    ->label('Tenor')
                    ->suffix(' bulan')
                    ->disabled(),
                Forms\Components\TextInput::make('payment_frequency')
                    ->label('Frekuensi Angsuran')
                    ->formatStateUsing(fn ($state) => $state === 'monthly' ? 'Bulanan' : 'Mingguan')
                    ->disabled(),
                Forms\Components\TextInput::make('monthly_payment')
                    ->label('Angsuran per Periode')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled(),
                Forms\Components\TextInput::make('remaining_balance')
                    ->label('Sisa Hutang')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'disbursed' => 'Dicairkan',
                        'active' => 'Aktif',
                        'completed' => 'Lunas',
                        'overdue' => 'Jatuh Tempo',
                    ])
                    ->disabled(),
                Forms\Components\Textarea::make('purpose')
                    ->label('Tujuan Pinjaman')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('loan_number')
                    ->label('No. Pinjaman')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('application_date')
                    ->label('Tgl Pengajuan')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('net_disbursement')
                    ->label('Cair Bersih')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('monthly_payment')
                    ->label('Angsuran')
                    ->money('IDR')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('remaining_balance')
                    ->label('Sisa Hutang')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_frequency')
                    ->label('Frekuensi')
                    ->formatStateUsing(fn (?string $state) => $state === 'monthly' ? 'Bulanan' : 'Mingguan'),
                Tables\Columns\TextColumn::make('tenor_months')
                    ->label('Tenor')
                    ->formatStateUsing(fn ($state) => $state . ' bln'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'disbursed' => 'Dicairkan',
                        'active' => 'Aktif',
                        'completed' => 'Lunas',
                        'overdue' => 'Jatuh Tempo',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => fn ($state) => in_array($state, ['approved', 'disbursed', 'active', 'completed'], true),
                        'danger' => fn ($state) => in_array($state, ['rejected', 'overdue'], true),
                    ]),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Detail'),
            ])
            ->bulkActions([]);
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoans::route('/'),
            'view' => Pages\ViewLoan::route('/{record}'),
        ];
    }
}
