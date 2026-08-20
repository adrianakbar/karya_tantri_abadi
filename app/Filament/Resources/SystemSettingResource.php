<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemSettingResource\Pages;
use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SystemSettingResource extends Resource
{
    protected static ?string $model = SystemSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Pengaturan Sistem';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('General')
                            ->schema([
                                Hidden::make('cooperation_id')
                                    ->default(fn() => auth()->user()->cooperation_id)
                                    ->dehydrated(true)
                                    ->required(),
                                Forms\Components\Select::make('category')
                                    ->options([
                                        'general' => 'Umum',
                                        'ui_theme' => 'Tema UI',
                                        'notification' => 'Notifikasi',
                                        'backup' => 'Backup & Restore',
                                        'report_schedule' => 'Jadwal Laporan',
                                        'financial' => 'Keuangan',
                                        'inventory' => 'Inventaris',
                                    ])
                                    ->required()
                                    ->label('Kategori'),
                                Forms\Components\TextInput::make('key')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->label('Kunci Pengaturan'),
                                Forms\Components\Select::make('type')
                                    ->options([
                                        'string' => 'Text',
                                        'number' => 'Angka',
                                        'boolean' => 'Ya/Tidak',
                                        'json' => 'JSON',
                                        'file' => 'File',
                                    ])
                                    ->required()
                                    ->reactive()
                                    ->label('Tipe Data'),
                            ]),
                        Forms\Components\Tabs\Tab::make('Value')
                            ->schema([
                                Forms\Components\TextInput::make('value')
                                    ->label('Nilai')
                                    ->visible(fn ($get) => $get('type') === 'string'),
                                Forms\Components\TextInput::make('value')
                                    ->numeric()
                                    ->label('Nilai')
                                    ->visible(fn ($get) => $get('type') === 'number'),
                                Forms\Components\Toggle::make('value')
                                    ->label('Nilai')
                                    ->visible(fn ($get) => $get('type') === 'boolean'),
                                Forms\Components\FileUpload::make('value')
                                    ->label('File')
                                    ->visible(fn ($get) => $get('type') === 'file'),
                                Forms\Components\Textarea::make('value')
                                    ->label('JSON Value')
                                    ->visible(fn ($get) => $get('type') === 'json'),
                                Forms\Components\Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->columnSpan('full'),
                                Forms\Components\Toggle::make('is_system')
                                    ->label('Pengaturan Sistem')
                                    ->helperText('Jika diaktifkan, pengaturan ini hanya bisa diubah oleh administrator sistem')
                                    ->default(false)
                                    ->disabled(fn ($record) => $record?->is_system && !auth()->user()->hasRole('admin')),
                            ]),
                    ])->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('key')
                    ->label('Kunci')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Nilai')
                    ->limit(50)
                    ->tooltip(function ($record): ?string {
                        return strlen($record->value) > 50 ? $record->value : null;
                    })
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_system')
                    ->boolean()
                    ->label('Sistem')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('category')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'general' => 'Umum',
                        'ui_theme' => 'Tema UI',
                        'notification' => 'Notifikasi',
                        'backup' => 'Backup & Restore',
                        'report_schedule' => 'Jadwal Laporan',
                        'financial' => 'Keuangan',
                        'inventory' => 'Inventaris',
                    ])
                    ->label('Kategori'),
                Tables\Filters\TernaryFilter::make('is_system')
                    ->label('Pengaturan Sistem'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => !$record->is_system || auth()->user()->hasRole('admin')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole('admin')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystemSettings::route('/'),
            'create' => Pages\CreateSystemSetting::route('/create'),
            'edit' => Pages\EditSystemSetting::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('cooperation_id', auth()->user()->cooperation_id);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
