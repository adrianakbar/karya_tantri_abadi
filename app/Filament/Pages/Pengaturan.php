<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Pengaturan extends Page
{
    protected static ?string $navigationIcon = "heroicon-o-cog-6-tooth";
    protected static ?string $navigationGroup = null;
    protected static ?string $navigationLabel = "Pengaturan";
    protected static ?string $title = "Pengaturan";
    protected static ?int $navigationSort = 99;
    protected static string $view = "filament.pages.pengaturan";

    public string $activeTab = "role";

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole("admin") ?? false;
    }
}
