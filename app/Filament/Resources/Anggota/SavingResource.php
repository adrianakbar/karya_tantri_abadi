<?php

namespace App\Filament\Resources\Anggota;

use App\Models\SavingsTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\Anggota\SavingResource\Pages;

class SavingResource extends Resource
{
    protected static ?string $model = SavingsTransaction::class;
    protected static ?string $slug = 'simpanan';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Simpanan';
    protected static ?string $modelLabel = 'Simpanan';
    protected static ?string $pluralModelLabel = 'Riwayat Simpanan';
    protected static ?string $navigationGroup = null;

    public static function getEloquentQuery(): Builder
    {
        // Hanya tampilkan simpanan milik user yang login
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('transaction_number')
                    ->label('No. Transaksi')
                    ->disabled(),
                Forms\Components\Select::make('savings_type_id')
                    ->label('Jenis Simpanan')
                    ->relationship('savingsType', 'name')
                    ->disabled(),
                Forms\Components\DatePicker::make('transaction_date')
                    ->label('Tanggal Transaksi')
                    ->disabled(),
                Forms\Components\TextInput::make('amount')
                    ->label('Jumlah')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'completed' => 'Selesai',
                        'pending' => 'Pending',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->disabled(),
                Forms\Components\Textarea::make('notes')
                    ->label('Keterangan')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_number')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('savingsType.name')
                    ->label('Jenis Simpanan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'Selesai',
                        'pending' => 'Pending',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'completed' => 'Selesai',
                        'pending' => 'Pending',
                        'cancelled' => 'Dibatalkan',
                    ]),
                Tables\Filters\SelectFilter::make('savings_type_id')
                    ->label('Jenis Simpanan')
                    ->relationship('savingsType', 'name'),
                Tables\Filters\Filter::make('transaction_date')
                    ->form([
                        Forms\Components\Select::make('period')
                            ->label('Periode Transaksi')
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
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('date_from', null) + $set('date_to', null)),
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Dari Tanggal')
                            ->visible(fn (Forms\Get $get) => $get('period') === 'custom')
                            ->maxDate(fn (Forms\Get $get) => $get('date_to') ?: now()),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('Sampai Tanggal')
                            ->visible(fn (Forms\Get $get) => $get('period') === 'custom')
                            ->minDate(fn (Forms\Get $get) => $get('date_from'))
                            ->maxDate(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['period'])) {
                            return $query;
                        }

                        return $query->when(
                            $data['period'],
                            function (Builder $query, $period) use ($data) {
                                $now = now();
                                
                                switch ($period) {
                                    case 'today':
                                        return $query->whereDate('transaction_date', $now->toDateString());
                                    case 'yesterday':
                                        return $query->whereDate('transaction_date', $now->subDay()->toDateString());
                                    case 'this_week':
                                        return $query->whereBetween('transaction_date', [
                                            $now->startOfWeek()->toDateString(),
                                            $now->endOfWeek()->toDateString()
                                        ]);
                                    case 'last_week':
                                        return $query->whereBetween('transaction_date', [
                                            $now->subWeek()->startOfWeek()->toDateString(),
                                            $now->subWeek()->endOfWeek()->toDateString()
                                        ]);
                                    case 'this_month':
                                        return $query->whereMonth('transaction_date', $now->month)
                                            ->whereYear('transaction_date', $now->year);
                                    case 'last_month':
                                        return $query->whereMonth('transaction_date', $now->subMonth()->month)
                                            ->whereYear('transaction_date', $now->subMonth()->year);
                                    case 'this_year':
                                        return $query->whereYear('transaction_date', $now->year);
                                    case 'custom':
                                        if (!empty($data['date_from']) && !empty($data['date_to'])) {
                                            return $query->whereBetween('transaction_date', [
                                                $data['date_from'],
                                                $data['date_to']
                                            ]);
                                        }
                                        if (!empty($data['date_from'])) {
                                            return $query->whereDate('transaction_date', '>=', $data['date_from']);
                                        }
                                        if (!empty($data['date_to'])) {
                                            return $query->whereDate('transaction_date', '<=', $data['date_to']);
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
                Tables\Actions\ViewAction::make()->label('Lihat'),
            ])
            ->bulkActions([
                // Remove bulk actions for anggota
            ]);
    }

    public static function canCreate(): bool
    {
        return false; // Anggota tidak bisa membuat transaksi simpanan langsung
    }

    public static function canEdit($record): bool
    {
        return false; // Anggota tidak bisa edit transaksi simpanan
    }

    public static function canDelete($record): bool
    {
        return false; // Anggota tidak bisa hapus transaksi simpanan
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Anggota\SavingResource\Pages\ListSavings::route('/'),
            'view' => \App\Filament\Resources\Anggota\SavingResource\Pages\ViewSaving::route('/{record}'),
        ];
    }
}
