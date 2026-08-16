<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use App\Models\Loan;
use App\Models\CashFlow;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Observers\ProductObserver;
use App\Observers\PurchaseObserver;
use App\Observers\PurchaseDetailObserver;
use App\Observers\SaleObserver;
use App\Observers\SaleDetailObserver;
use App\Observers\AuditTrailObserver;
use App\Listeners\ClearTourFlagOnLogout;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Binding login/logout response default Filament (role-based redirect
        // ditangani canAccessPanel di User model, bukan custom response).
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Listen for logout events to reset tour
        \Illuminate\Support\Facades\Event::listen(
            Logout::class,
            ClearTourFlagOnLogout::class
        );

        // Registrasi observer dengan cara eksplisit
        Product::observe(ProductObserver::class);
        Purchase::observe(PurchaseObserver::class);
        PurchaseDetail::observe(PurchaseDetailObserver::class);
        Sale::observe(SaleObserver::class);
        SaleDetail::observe(SaleDetailObserver::class);

        // Register Audit Trail Observer for all relevant models
        User::observe(AuditTrailObserver::class);
        Product::observe(AuditTrailObserver::class);
        Purchase::observe(AuditTrailObserver::class);
        Sale::observe(AuditTrailObserver::class);
        Loan::observe(AuditTrailObserver::class);
        CashFlow::observe(AuditTrailObserver::class);
        Expense::observe(AuditTrailObserver::class);
        ExpenseCategory::observe(AuditTrailObserver::class);
    }
}
