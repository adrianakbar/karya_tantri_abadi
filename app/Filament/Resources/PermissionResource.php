<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionResource\Pages;
use App\Models\Permissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PermissionResource extends Resource
{
    protected static ?string $model = Permissions::class;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Hak Akses';
    protected static ?string $modelLabel = 'Hak Akses';
    protected static ?string $pluralModelLabel = 'Hak Akses';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Informasi Hak Akses')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->label('Nama')
                                    ->placeholder('view_dashboard, manage_users, etc')
                                    ->unique(ignoreRecord: true),
                                Forms\Components\Select::make('module')
                                    ->required()
                                    ->label('Modul')
                                    ->options([
                                        'dashboard' => 'Dashboard',
                                        'users' => 'Pengguna',
                                        'roles' => 'Peran',
                                        'permissions' => 'Hak Akses',
                                        'products' => 'Produk',
                                        'transactions' => 'Transaksi',
                                        'reports' => 'Laporan',
                                        'settings' => 'Pengaturan',
                                    ]),
                                Forms\Components\Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('module')
                    ->label('Modul')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles_count')
                    ->counts('roles')
                    ->label('Digunakan Oleh')
                    ->sortable(),
            ])
            ->defaultSort('module', 'name')
            ->filters([
                Tables\Filters\SelectFilter::make('module')
                    ->multiple()
                    ->label('Modul')
                    ->options([
                        'dashboard' => 'Dashboard',
                        'users' => 'Pengguna',
                        'roles' => 'Peran',
                        'permissions' => 'Hak Akses',
                        'products' => 'Produk',
                        'transactions' => 'Transaksi',
                        'reports' => 'Laporan',
                        'settings' => 'Pengaturan',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->modalDescription('Apakah Anda yakin ingin menghapus hak akses ini? Semua peran yang memiliki hak akses ini akan kehilangan akses terkait.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Apakah Anda yakin ingin menghapus hak akses yang dipilih? Semua peran yang memiliki hak akses ini akan kehilangan akses terkait.'),
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
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
}
