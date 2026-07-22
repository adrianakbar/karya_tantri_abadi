<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataChangeLogResource\Pages;
use App\Models\DataChangeLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;

class DataChangeLogResource extends Resource
{
    protected static ?string $model = DataChangeLog::class;

    protected static ?string $slug = 'data-change-logs';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Perubahan Data';

    protected static ?string $modelLabel = 'Riwayat Perubahan Data';

    protected static ?string $pluralModelLabel = 'Riwayat Perubahan Data';

    protected static ?string $navigationGroup = 'Log Aktivitas';

    protected static ?int $navigationSort = 53;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->disabled(),
                Forms\Components\TextInput::make('table_name')
                    ->disabled(),
                Forms\Components\TextInput::make('record_id')
                    ->disabled(),
                Forms\Components\TextInput::make('action')
                    ->disabled(),
                Forms\Components\KeyValue::make('old_values')
                    ->label('Nilai Lama')
                    ->disabled(),
                Forms\Components\KeyValue::make('new_values')
                    ->label('Nilai Baru')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('changed_at')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('table_name')
                    ->label('Tabel')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('record_id')
                    ->label('ID Record')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('action')
                    ->label('Aksi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'create' => 'success',
                        'update' => 'warning',
                        'delete' => 'danger',
                        default => 'secondary',
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('changes_summary')
                    ->label('Ringkasan Perubahan')
                    ->getStateUsing(function ($record) {
                        if ($record->action === 'create') {
                            return 'Record baru dibuat';
                        } elseif ($record->action === 'delete') {
                            return 'Record dihapus';
                        } elseif ($record->action === 'update' && $record->new_values) {
                            $newValues = is_array($record->new_values) ? $record->new_values : json_decode($record->new_values, true);
                            if (is_array($newValues)) {
                                $changedFields = array_keys($newValues);
                                return 'Perubahan: ' . implode(', ', $changedFields);
                            }
                        }
                        return 'Tidak ada perubahan';
                    }),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label('Aksi')
                    ->options([
                        'create' => 'Create',
                        'update' => 'Update',
                        'delete' => 'Delete',
                    ]),
                SelectFilter::make('table_name')
                    ->label('Tabel')
                    ->options(function () {
                        return DataChangeLog::distinct('table_name')
                            ->pluck('table_name', 'table_name')
                            ->mapWithKeys(function ($value, $key) {
                                return [$key => ucwords(str_replace('_', ' ', $value))];
                            })
                            ->toArray();
                    }),
                SelectFilter::make('user_id')
                    ->label('Pengguna')
                    ->relationship('user', 'name'),
                Filter::make('changed_at')
                    ->form([
                        Forms\Components\DatePicker::make('changed_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('changed_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['changed_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('changed_at', '>=', $date),
                            )
                            ->when(
                                $data['changed_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('changed_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Lihat'),
                Tables\Actions\Action::make('compare')
                    ->label('Bandingkan')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('info')
                    ->visible(fn ($record) => $record->action === 'update')
                    ->modalContent(function ($record) {
                        return view('filament.modals.compare-changes', [
                            'oldValues' => $record->old_values ?? [],
                            'newValues' => $record->new_values ?? [],
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ])
            ->bulkActions([
                // No bulk actions for security
            ])
            ->defaultSort('changed_at', 'desc');
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
            'index' => Pages\ListDataChangeLogs::route('/'),
            'view' => Pages\ViewDataChangeLog::route('/{record}'),
        ];
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

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        if ($user && $user->cooperation_id) {
            return $query->where('cooperation_id', $user->cooperation_id);
        }
        return $query->whereRaw('1 = 0');
    }
}
