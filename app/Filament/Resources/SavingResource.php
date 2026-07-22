<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SavingResource\Pages;
use App\Models\SavingsTransaction;
use App\Models\SavingsType;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SavingResource extends Resource
{
    protected static ?string $model = SavingsTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Transaksi';

    // saya ingin buat sub navigasi dibawah grup transaksi 
    protected static ?string $navigationLabel = 'Daftar Tabungan';
    protected static ?string $pluralModelLabel = 'Daftar Tabungan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Hidden::make('cooperation_id')
                    ->default(fn() => \Illuminate\Support\Facades\Auth::user()->cooperation_id)
                    ->dehydrated(true)
                    ->required(),
                
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Anggota'),

                Hidden::make('transaction_number')
                    ->default(fn() => Str::random(20))
                    ->dehydrated(true)
                    ->required(),

                Forms\Components\Select::make('savings_type_id')
                    ->relationship('savingsType', 'name', function (Builder $query) {
                        return $query->where('is_active', true);
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        if ($state) {
                            $savingsType = SavingsType::find($state);
                            if ($savingsType && $savingsType->amount) {
                                $set('amount', $savingsType->amount);
                            }
                        }
                    })
                    ->label('Jenis Simpanan'),

                Forms\Components\TextInput::make('amount')
                    ->label('Nominal')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->rules(['required', 'numeric', 'min:0']),

                Forms\Components\DatePicker::make('transaction_date')
                    ->label('Tanggal Transaksi')
                    ->required()
                    ->default(now()),

                Forms\Components\TextInput::make('receipt_number')
                    ->label('Nomor Kwitansi')
                    ->maxLength(50),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->default('completed')
                    ->required(),

                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_number')
                    ->label('No. Transaksi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Anggota')
                    ->searchable(),
                Tables\Columns\TextColumn::make('savingsType.name')
                    ->label('Jenis Simpanan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('savings_type_id')
                    ->relationship('savingsType', 'name')
                    ->label('Jenis Simpanan')
                    ->preload()
                    ->multiple(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->multiple(),
                Tables\Filters\Filter::make('transaction_date')
                    ->form([
                        Forms\Components\Select::make('period')
                            ->label('Periode')
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
            ->emptyStateHeading('Data tidak ditemukan')
            ->emptyStateDescription('Belum ada data tabungan yang tersedia.')
            ->actions([
                Tables\Actions\ViewAction::make()->label('Lihat'),
                Tables\Actions\EditAction::make()->label('Edit'),
                Tables\Actions\Action::make('print')
                    ->label('Cetak')
                    ->icon('heroicon-o-printer')
                    ->url(fn (SavingsTransaction $record) => route('savings.print', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSavings::route('/'),
            'create' => Pages\CreateSaving::route('/create'),
            'edit' => Pages\EditSaving::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'savingsType']);
    }
}
