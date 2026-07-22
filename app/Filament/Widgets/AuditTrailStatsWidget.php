<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AuditTrailStatsWidget extends BaseWidget
{
    protected static bool $isLazy = false;
    
    // Prevent this widget from being discovered on dashboard
    protected static ?string $pollingInterval = null;
    
    protected function getStats(): array
    {
        // Cards removed as requested by user
        return [];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
