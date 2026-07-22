<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Carbon\Carbon;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $slug = 'activity-logs';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Semua Aktivitas';

    protected static ?string $modelLabel = 'Log Aktivitas';

    protected static ?string $pluralModelLabel = 'Log Aktivitas';

    protected static ?string $navigationGroup = 'Log Aktivitas';

    protected static ?int $navigationSort = 51;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->disabled(),
                Forms\Components\TextInput::make('action')
                    ->required()
                    ->disabled(),
                Forms\Components\TextInput::make('module')
                    ->required()
                    ->disabled(),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->disabled(),
                Forms\Components\TextInput::make('ip_address')
                    ->disabled(),
                Forms\Components\Textarea::make('user_agent')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('action')
                    ->label('Aksi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'create' => 'success',
                        'update' => 'warning',
                        'delete' => 'danger',
                        'login' => 'info',
                        'logout' => 'gray',
                        'view' => 'primary',
                        default => 'secondary',
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('module')
                    ->label('Modul')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label('Aksi')
                    ->options([
                        'create' => 'Create',
                        'update' => 'Update',
                        'delete' => 'Delete',
                        'login' => 'Login',
                        'logout' => 'Logout',
                        'view' => 'View',
                        'page_access' => 'Page Access',
                    ]),
                SelectFilter::make('module')
                    ->label('Modul')
                    ->options([
                        'authentication' => 'Authentication',
                        'user_management' => 'User Management',
                        'inventory' => 'Inventory',
                        'sales' => 'Sales',
                        'purchases' => 'Purchases',
                        'loans' => 'Loans',
                        'savings' => 'Savings',
                        'cash_flow' => 'Cash Flow',
                        'expenses' => 'Expenses',
                        'reports' => 'Reports',
                        'dashboard' => 'Dashboard',
                    ]),
                SelectFilter::make('user_id')
                    ->label('Pengguna')
                    ->relationship('user', 'name'),
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Lihat'),
            ])
            ->bulkActions([
                // No bulk actions for security
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
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
