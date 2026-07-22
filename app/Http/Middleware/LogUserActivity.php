<?php

namespace App\Http\Middleware;

use App\Services\AuditTrailService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated users
        if (Auth::check()) {
            $this->logPageAccess($request);
        }

        return $response;
    }

    /**
     * Log page access for authenticated users
     */
    protected function logPageAccess(Request $request): void
    {
        $route = $request->route();
        if (!$route) {
            return;
        }

        $routeName = $route->getName();
        $uri = $request->path();
        $method = $request->method();

        // Skip logging for certain routes
        $skipRoutes = [
            'livewire.message',
            'livewire.upload-file',
            'filament.asset',
            'filament.app.css',
            'filament.app.js',
        ];

        if (in_array($routeName, $skipRoutes) || 
            str_contains($uri, 'livewire') ||
            str_contains($uri, 'assets') ||
            str_contains($uri, 'css') ||
            str_contains($uri, 'js')) {
            return;
        }

        // Only log specific HTTP methods
        if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
            return;
        }

        // Determine module based on route
        $module = $this->determineModule($uri, $routeName);
        
        // Create description
        $description = $this->createDescription($method, $uri, $routeName);

        AuditTrailService::logActivity(
            'page_access',
            $module,
            $description
        );
    }

    /**
     * Determine module based on URI or route name
     */
    protected function determineModule(string $uri, ?string $routeName): string
    {
        $segments = explode('/', $uri);
        
        // Check for admin/filament routes
        if (str_contains($uri, 'admin')) {
            if (str_contains($uri, 'users')) return 'user_management';
            if (str_contains($uri, 'products')) return 'inventory';
            if (str_contains($uri, 'sales')) return 'sales';
            if (str_contains($uri, 'purchases')) return 'purchases';
            if (str_contains($uri, 'loans')) return 'loans';
            if (str_contains($uri, 'savings')) return 'savings';
            if (str_contains($uri, 'cash-flows')) return 'cash_flow';
            if (str_contains($uri, 'expenses')) return 'expenses';
            if (str_contains($uri, 'reports')) return 'reports';
            if (str_contains($uri, 'dashboard')) return 'dashboard';
        }

        // Default module
        return 'general';
    }

    /**
     * Create description for the activity
     */
    protected function createDescription(string $method, string $uri, ?string $routeName): string
    {
        $action = match($method) {
            'GET' => 'Viewed',
            'POST' => 'Created/Submitted',
            'PUT', 'PATCH' => 'Updated',
            'DELETE' => 'Deleted',
            default => 'Accessed'
        };

        // Clean up URI for display
        $cleanUri = str_replace(['admin/', 'filament/'], '', $uri);
        $cleanUri = ucwords(str_replace(['-', '_', '/'], ' ', $cleanUri));

        return "{$action} {$cleanUri}";
    }
}
