<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuthLogResource\Pages;
use App\Models\AuthLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;

class AuthLogResource extends Resource
{
    protected static ?string $model = AuthLog::class;

    protected static ?string $slug = 'auth-logs';

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'Riwayat Login';

    protected static ?string $modelLabel = 'Riwayat Login';

    protected static ?string $pluralModelLabel = 'Riwayat Login';

    protected static ?string $navigationGroup = 'Log Aktivitas';

    protected static ?int $navigationSort = 52;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->disabled(),
                Forms\Components\TextInput::make('action')
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
                    ->searchable()
                    ->default('Unknown'),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->sortable()
                    ->searchable()
                    ->default('Unknown'),
                Tables\Columns\TextColumn::make('action')
                    ->label('Aksi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'login' => 'success',
                        'logout' => 'warning',
                        'failed_login' => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_agent')
                    ->label('Browser/Device')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return self::getBrowserInfo($state);
                    })
                    ->formatStateUsing(function ($state) {
                        return self::getBrowserInfo($state);
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label('Aksi')
                    ->options([
                        'login' => 'Login',
                        'logout' => 'Logout',
                        'failed_login' => 'Failed Login',
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

    protected static function getBrowserInfo($userAgent): string
    {
        if (empty($userAgent)) {
            return 'Unknown';
        }

        // Simple browser detection
        if (str_contains($userAgent, 'Chrome')) {
            return 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox')) {
            return 'Firefox';
        } elseif (str_contains($userAgent, 'Safari')) {
            return 'Safari';
        } elseif (str_contains($userAgent, 'Edge')) {
            return 'Edge';
        } elseif (str_contains($userAgent, 'Opera')) {
            return 'Opera';
        }

        return 'Other';
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
            'index' => Pages\ListAuthLogs::route('/'),
            'view' => Pages\ViewAuthLog::route('/{record}'),
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

    public static function getNavigationBadge(): ?string
    {
        $cooperationId = \Illuminate\Support\Facades\Auth::user()?->cooperation_id;
        
        if (!$cooperationId) {
            return null;
        }

        $todayFailedLogins = AuthLog::forCooperation($cooperationId)
            ->whereDate('created_at', today())
            ->where('action', 'failed_login')
            ->count();

        return $todayFailedLogins > 0 ? (string) $todayFailedLogins : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $cooperationId = \Illuminate\Support\Facades\Auth::user()?->cooperation_id;
        
        if (!$cooperationId) {
            return null;
        }

        $todayFailedLogins = AuthLog::forCooperation($cooperationId)
            ->whereDate('created_at', today())
            ->where('action', 'failed_login')
            ->count();

        return $todayFailedLogins > 5 ? 'danger' : ($todayFailedLogins > 0 ? 'warning' : null);
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
