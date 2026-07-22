# Copilot Instructions - Karya Tantri Abadi System

## Project Overview

**Stack**: Laravel 12 + Filament v3 + MySQL + Vite + TailwindCSS v4  
**Purpose**: Multi-tenant cooperative management system with comprehensive audit trails, inventory management, financial tracking, and role-based multi-panel architecture.

## Architecture Fundamentals

### Multi-Panel Structure (Critical)

The system uses **5 independent Filament panels** in `app/Providers/Filament/`:

1. **LoginPanelProvider** (`/`) - Unified entry point with custom `Login` class in `app/Filament/Pages/Auth/Login.php` that handles role-based redirects via `getRedirectUrl()` method
2. **AdminPanelProvider** (`/admin`) - Full CRUD access to all 18 resources
3. **AnggotaPanelProvider** (`/anggota`) - Members: only Savings, Loans, and Product Sales
4. **BendaharaPanelProvider** (`/bendahara`) - Treasurer: financial ops and inventory
5. **KepalayayasanPanelProvider** (`/kepalayayasan`) - Foundation head: reports-only (read-only)

**⚠️ Critical**: Resources are registered per-panel. Never add admin resources to member panels. Check `->resources([])` array in each PanelProvider.

### Authentication Flow

Login redirects are controlled by **two mechanisms** (see `LOGIN_REDIRECT_FIX_DOCUMENTATION.md`):
1. Custom `Login::getRedirectUrl()` in `app/Filament/Pages/Auth/Login.php`
2. Legacy `CustomLoginResponse` (exists but not actively bound in `AppServiceProvider`)

Redirect logic uses `UserRole` → `Roles` lookup with match expressions:
```php
match ($role->name) {
    'admin' => '/admin',
    'anggota' => '/anggota/simpanan',
    'bendahara' => '/bendahara',
    'kepala_yayasan', 'kepalayayasan' => '/kepalayayasan/financial-report',
    default => '/anggota/simpanan'
}
```

### Audit Trail System (Auto-Logging)

**Three-table architecture** (`config/audit_trail.php` controls enablement):
- `AuthLog` - Login/logout with IP and user agent via `AuditTrailService::logAuth()`
- `DataChangeLog` - Model CRUD with before/after snapshots via `AuditTrailService::logDataChange()`
- `ActivityLog` - User actions via `AuditTrailService::logActivity()`

**Observers** in `app/Observers/`:
- `AuditTrailObserver` - Attached to all models in `AppServiceProvider::boot()`, auto-logs created/updated/deleted events
- `ProductObserver`, `PurchaseObserver`, `SaleObserver` - Domain-specific logic (stock movements)

**Exclusions**: `StockMovementLog`, `ActivityLog`, `AuthLog`, `DataChangeLog` are excluded from audit logging to prevent recursion.

## Core Domain Models

**Financial**: `SavingsTransaction`, `Loan`, `LoanPayment`, `Expense`, `CashFlow`, `ShuDistribution`  
**Inventory**: `Product`, `Purchase`, `Sale`, `StockMovementLog` (reference-based tracking), `StockAdjustment`  
**Membership**: `User`, `UserRole`, `Roles`, `Permissions`, `Cooperation`  
**System**: `SystemSetting`, `Supplier`, `ProductCategory`, `ExpenseCategory`, `SavingsType`, `LoanType`

### Stock Movement Pattern

**Every inventory change** must create `StockMovementLog` entry:
```php
StockMovementLog::create([
    'cooperation_id' => $cooperation->id,
    'product_id' => $product->id,
    'reference_type' => Purchase::class, // or Sale::class, 'adjustment'
    'reference_id' => $purchase->id,
    'type' => 'in', // or 'out'
    'quantity' => $quantity,
    'stock_before' => $oldStock,
    'stock_after' => $newStock,
    'notes' => 'Purchase receipt #XXX',
    'created_by' => Auth::id(),
]);
```

Observers handle this automatically for `Purchase` and `Sale`. Manual adjustments must log explicitly.

## Development Workflows

### Quick Testing Scripts (Fish Shell Compatible)

Located in project root - **use these instead of manual commands**:

```bash
./test_audit_simple.sh       # Audit trail tables, config, observer registration
./test_login_redirect.sh     # Multi-role login flow with route verification
./test_reports.sh            # Report exports and period filtering
./test_stock.sh              # Inventory operations and stock logs
```

Each script clears caches, runs targeted tests, and outputs color-coded results. See `TESTING_SUMMARY.md` for details.

### Database Setup

**Database**: `db_koperasi_karya_tantri_abadi` (MySQL)  
**Seeding**: Run `php artisan db:seed --class=CompleteSystemSeeder` for full demo data (creates 4 users, products, transactions, loans - see `COMPLETE_SYSTEM_SEEDER_DOCUMENTATION.md`)  

**Migration naming**: Chronological with domain prefixes (e.g., `2025_08_02_183139_create_products_table.php`)

### Asset Pipeline

```bash
npm run dev      # Watch mode with Vite + TailwindCSS v4
npm run build    # Production build
```

Vite config in `vite.config.js`, TailwindCSS v4 uses `@tailwindcss/vite` plugin.

## Critical Conventions

### 1. Multi-Cooperation Data Isolation

**Always scope queries by `cooperation_id`**:
```php
Product::where('cooperation_id', Auth::user()->cooperation_id)->get();
```

Filament resources use `modifyQueryUsing()` in table builders. Check `AdminPanelProvider` resources for examples.

### 2. Report Architecture Pattern

Reports are Filament Pages in `app/Filament/Pages/` with:
- Table interface (`InteractsWithTable` trait)
- Date range filters (start_date/end_date)
- Export actions (PDF via `barryvdh/laravel-dompdf`, Excel via `maatwebsite/excel`)
- Panel-specific visibility (e.g., `KepalayayasanPanelProvider` only shows reports)

**Template**: Follow `FinancialReport.php` pattern:
```php
class FinancialReport extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;
    
    protected static string $view = 'filament.pages.financial-report';
    protected static ?string $navigationGroup = 'Laporan';
    
    public function table(Table $table): Table {
        return $table
            ->query($this->getFinancialQuery()) // Custom query builder
            ->columns([...])
            ->filters([...])
            ->headerActions([
                Action::make('exportPdf')->action('exportToPdf'),
                Action::make('exportExcel')->action('exportToExcel'),
            ]);
    }
}
```

### 3. Observer Registration

Observers **must be explicitly registered** in `AppServiceProvider::boot()`:
```php
Product::observe(ProductObserver::class);
Product::observe(AuditTrailObserver::class); // Multiple observers per model OK
```

Don't rely on auto-discovery - it's disabled for this project.

### 4. Test Suite Organization

**85+ test methods** in `tests/Feature/Reports/`:
- `*Test.php` - Basic functionality
- `*EnhancedTest.php` - Edge cases, multi-cooperation, role restrictions
- `ReportsTestSuite.php` - Master runner

**Test database**: Same as dev (`db_koperasi_karya_tantri_abadi`), configured in `phpunit.xml`. Uses real MySQL, not SQLite.

Run with: `php artisan test --filter=ReportTest` or `./vendor/bin/phpunit tests/Feature/Reports/`

## Integration Points

**PDF**: `barryvdh/laravel-dompdf` - Blade views in `resources/views/pdf/`  
**Excel**: `maatwebsite/excel` - Export classes in `app/Exports/` implementing `FromCollection`, `WithHeadings`  
**File Storage**: Public disk (`storage/app/public/`) symlinked to `public/storage/`

## Documentation Files

**20+ markdown docs** in root with domain-specific details:
- `AUDIT_TRAIL_DOCUMENTATION.md` - Complete audit system architecture
- `ANGGOTA_PANEL_DOCUMENTATION.md` - Member panel implementation
- `BENDAHARA_ROLE_DOCUMENTATION.md` - Treasurer panel resources
- `INVENTORY_REPORTS_DOCUMENTATION.md` - Stock reports and exports
- `LOGIN_REDIRECT_FIX_DOCUMENTATION.md` - Authentication flow debugging
- `REPORTS_TESTING_DOCUMENTATION.md` - Test suite structure

**Read these first** when working on specific modules - they contain implementation details and gotchas not obvious from code alone.

## Common Pitfalls

1. **Login loops**: Ensure `LoginPanelProvider` has `RedirectIfAuthenticated` middleware, not `Authenticate`
2. **Missing stock logs**: Inventory changes without `StockMovementLog` entries break reports
3. **Cross-panel resources**: Resources in wrong panel cause 404s - verify panel registration
4. **Cooperation ID missing**: Queries without `cooperation_id` filter leak data between tenants
5. **Observer not firing**: Check `AppServiceProvider::boot()` registration - auto-discovery is off

## Quick Start Checklist

- [ ] Run `composer install && npm install`
- [ ] Configure `.env` with MySQL credentials (`DB_DATABASE=db_koperasi_karya_tantri_abadi`)
- [ ] Run `php artisan migrate && php artisan db:seed --class=CompleteSystemSeeder`
- [ ] Run `php artisan storage:link` for file uploads
- [ ] Start dev server: `php artisan serve` (localhost:8000)
- [ ] Start Vite: `npm run dev`
- [ ] Test login with seeded users (see `CompleteSystemSeeder` output for credentials)
- [ ] Run `./test_audit_simple.sh` to verify audit trail
- [ ] Check all 5 panels are accessible based on role