<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Models\Permission;
use App\Models\Roles;
use App\Models\Permissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RoleResource extends Resource
{
    protected static ?string $model = Roles::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Peran';
    protected static ?string $modelLabel = 'Peran';
    protected static ?string $pluralModelLabel = 'Peran';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        $modules = Permissions::select('module')->distinct()->pluck('module')->toArray();

        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Informasi Peran')
                            ->schema([
                                Forms\Components\Hidden::make('cooperation_id')
                                    ->default(fn() => Auth::user()->cooperation_id)
                                    ->dehydrated(true)
                                    ->required(),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->label('Nama Peran')
                                    ->unique(ignoreRecord: true),
                                Forms\Components\Textarea::make('description')
                                    ->label('Deskripsi'),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Hak Akses')
                            ->schema(array_map(function ($module) {
                                return Forms\Components\CheckboxList::make('permissions.' . strtolower($module))
                                    ->label(ucfirst($module))
                                    ->options(
                                        Permissions::where('module', $module)
                                            ->pluck('description', 'id')
                                            ->toArray()
                                    )
                                    ->columns(2)
                                    ->searchable();
                            }, $modules))
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 3]),
            ])
            ->columns(5);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Peran')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->tooltip(function ($record): ?string {
                        return strlen($record->description) > 50 ? $record->description : null;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Jumlah Hak Akses')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Status')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->placeholder('Semua'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->modalDescription('Apakah Anda yakin ingin menghapus peran ini? Semua pengguna yang memiliki peran ini akan kehilangan akses terkait.')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Apakah Anda yakin ingin menghapus peran-peran yang dipilih? Semua pengguna yang memiliki peran-peran ini akan kehilangan akses terkait.'),
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('cooperation_id', Auth::user()->cooperation_id);
    }

    public static function canAccess(): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return false;
    }
}
