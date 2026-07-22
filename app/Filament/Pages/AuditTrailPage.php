<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use App\Models\AuthLog;
use App\Models\DataChangeLog;
use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Support\Facades\Auth;

class AuditTrailPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static string $view = 'filament.pages.audit-trail-page';

    protected static ?string $navigationLabel = 'Dashboard Audit';

    protected static ?string $title = 'Log Aktivitas (Audit Trail)';

    protected static ?int $navigationSort = 50;

    protected static ?string $navigationGroup = 'Log Aktivitas';

    public static function getNavigationBadge(): ?string
    {
        $cooperationId = Auth::user()?->cooperation_id;
        
        if (!$cooperationId) {
            return null;
        }

        $todayLogins = AuthLog::forCooperation($cooperationId)
            ->whereDate('created_at', today())
            ->where('action', 'login')
            ->count();

        return $todayLogins > 0 ? (string) $todayLogins : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getViewData(): array
    {
        $cooperationId = Auth::user()?->cooperation_id;

        if (!$cooperationId) {
            return [
                'todayLogins' => 0,
                'todayFailedLogins' => 0,
                'todayDataChanges' => 0,
            ];
        }

        return [
            'todayLogins' => AuthLog::forCooperation($cooperationId)
                ->whereDate('created_at', today())
                ->where('action', 'login')
                ->count(),
            'todayFailedLogins' => AuthLog::forCooperation($cooperationId)
                ->whereDate('created_at', today())
                ->where('action', 'failed_login')
                ->count(),
            'todayDataChanges' => DataChangeLog::forCooperation($cooperationId)
                ->whereDate('changed_at', today())
                ->count(),
        ];
    }

    public static function canAccess(): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
