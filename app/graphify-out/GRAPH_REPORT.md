# Graph Report - app  (2026-08-20)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 1323 nodes · 3044 edges · 68 communities (43 shown, 25 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 61 edges (avg confidence: 0.85)
- Token cost: 6,683 input · 2,715 output

## Graph Freshness
- Built from commit: `c01f736b`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Report Export Classes
- User Auth & Member Cards
- Resource Page Namespaces
- Resource List Pages
- Report Dashboard
- Create Record Pages
- Product Sales Resource
- Member API Endpoints
- Core Models
- Filament Panel Setup
- Edit Record Pages
- Income & Loan Reports
- Authentication Events
- Report Pages & PDF
- Resource Forms & Pages
- Product Purchase Calculations
- Product Category Resource
- Loan Resource
- Sale Printing & Observer
- Inventory Report
- Low Stock Notifications
- Audit Trail Page
- Product Purchase Forms
- Savings Type Resource
- Loan Creation
- Permission Resource
- User Role Resource
- Data Change Log
- Audit Trail Service
- View Record Pages
- Purchase Stock Updates
- Stock Movement Tracking
- User Resource
- Product Resource
- Stock Movement Log Resource
- Audit Trail Observer
- Audit Log Cleanup
- Auth Log Resource
- Purchase Detail Stock
- Dashboard Recap Charts
- Loan Type Resource
- Logout Handling
- Recent Activities Widget
- Product & Stock Commands
- SHU Distribution Report
- Role List Page
- Product Sales Resource
- Activity Log Resource
- Product Sales View Page
- Data Change Log Resource
- Loan Application Resource
- Savings Resource
- SHU Calculation
- Role Resource
- Auth Events
- Loan Resource
- Savings Resource
- Product List Page
- Loan Application & Calculator
- Report Export Service
- Create Loan Page
- Dashboard Page
- System Settings Resource
- Logout Response
- User Roles Pivot
- Event Service Provider
- Backup Management

## God Nodes (most connected - your core abstractions)
1. `User` - 72 edges
2. `Product` - 61 edges
3. `Sale` - 51 edges
4. `Loan` - 46 edges
5. `Purchase` - 39 edges
6. `AuditTrailService` - 34 edges
7. `SavingsTransaction` - 30 edges
8. `InventoryReport` - 27 edges
9. `StockMovementLog` - 27 edges
10. `ProductSalesResource` - 27 edges

## Surprising Connections (you probably didn't know these)
- `MemberCardController` --inherits--> `Controller`  [EXTRACTED]
  Http/Controllers/MemberCardController.php → Http/Controllers/Controller.php
- `Customer` --inherits--> `User`  [EXTRACTED]
  Models/Customer.php → Models/User.php
- `SalePrintController` --inherits--> `Controller`  [EXTRACTED]
  Http/Controllers/SalePrintController.php → Http/Controllers/Controller.php
- `MemberApiController` --inherits--> `Controller`  [EXTRACTED]
  Http/Controllers/Api/MemberApiController.php → Http/Controllers/Controller.php
- `LoginController` --inherits--> `Controller`  [EXTRACTED]
  Http/Controllers/Auth/LoginController.php → Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (68 total, 25 thin omitted)

### Community 0 - "Report Export Classes"
Cohesion: 0.08
Nodes (18): FinancialReportExport, IncomeReportExport, InventoryReportExport, LoanPaymentExport, LoanReportExport, ReportExport, SavingsReportExport, ShuCalculationExport (+10 more)

### Community 1 - "User Auth & Member Cards"
Cohesion: 0.06
Nodes (19): Filament\Facades\Filament, Filament\Models\Contracts\FilamentUser, MemberCardController, Illuminate\Database\Eloquent\Relations\BelongsToMany, Illuminate\Foundation\Auth\User, Illuminate\Foundation\Support\Providers\AuthServiceProvider, Illuminate\Http\Exceptions\HttpResponseException, Illuminate\Notifications\Notifiable (+11 more)

### Community 2 - "Resource Page Namespaces"
Cohesion: 0.12
Nodes (26): App\Filament\Resources\ActivityLogResource\Pages, App\Filament\Resources\Anggota\LoanResource\Pages, App\Filament\Resources\Anggota\ProductSalesResource\Pages, App\Filament\Resources\Anggota\SavingResource\Pages, App\Filament\Resources\AuthLogResource\Pages, App\Filament\Resources\DataChangeLogResource\Pages, App\Filament\Resources\LoanTypeResource\Pages, App\Filament\Resources\LoanTypeResource\RelationManagers (+18 more)

### Community 3 - "Resource List Pages"
Cohesion: 0.05
Nodes (14): ListActivityLogs, ListLoans, ListProductSales, ListSavings, ListAuthLogs, ListDataChangeLogs, ListLoans, Filament\Resources\Pages\ListRecords (+6 more)

### Community 4 - "Report Dashboard"
Cohesion: 0.19
Nodes (4): Carbon, ReportDashboard, Illuminate\Support\Carbon, Illuminate\Support\Collection

### Community 5 - "Create Record Pages"
Cohesion: 0.07
Nodes (14): Filament\Actions, CreateLoanType, Filament\Resources\Pages\CreateRecord, CreatePermission, CreateProductCategory, CreateProduct, CreateRole, CreateSaving (+6 more)

### Community 6 - "Product Sales Resource"
Cohesion: 0.11
Nodes (3): Filament\Forms\Components\Section, ProductSalesResource, Section

### Community 7 - "Member API Endpoints"
Cohesion: 0.08
Nodes (16): Closure, MemberApiController, LoginController, BackupDownloadController, Controller, SavingsReceiptController, LogUserActivity, Illuminate\Contracts\View\View (+8 more)

### Community 8 - "Core Models"
Cohesion: 0.14
Nodes (7): Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Support\Str, Cooperation, Customer, LoanType, ProductCategory, Supplier

### Community 9 - "Filament Panel Setup"
Cohesion: 0.18
Nodes (21): Filament\Http\Middleware\Authenticate, Filament\Http\Middleware\AuthenticateSession, Filament\Http\Middleware\DisableBladeIconComponents, Filament\Http\Middleware\DispatchServingFilamentEvent, Filament\Pages, Filament\Panel, Filament\PanelProvider, Filament\Support\Colors\Color (+13 more)

### Community 10 - "Edit Record Pages"
Cohesion: 0.07
Nodes (9): EditLoanType, Filament\Resources\Pages\EditRecord, EditProduct, EditProductSales, EditRole, EditSaving, EditStockMovementLog, EditSystemSetting (+1 more)

### Community 11 - "Income & Loan Reports"
Cohesion: 0.07
Nodes (5): Filament\Forms\Contracts\HasForms, IncomeReport, LoanReport, ShuReport, Filament\Tables\Contracts\HasTable

### Community 12 - "Authentication Events"
Cohesion: 0.25
Nodes (7): Illuminate\Auth\Events\Failed, Illuminate\Auth\Events\Lockout, Illuminate\Auth\Events\Login, Illuminate\Auth\Events\PasswordReset, Illuminate\Auth\Events\Registered, Illuminate\Auth\Events\Verified, AuthenticationListener

### Community 13 - "Report Pages & PDF"
Cohesion: 0.23
Nodes (16): Barryvdh\DomPDF\Facade\Pdf, Carbon\Carbon, Filament\Forms\Components\DatePicker, Filament\Forms\Components\Select, Filament\Forms\Concerns\InteractsWithForms, Filament\Notifications\Notification, Filament\Pages\Page, Pengaturan (+8 more)

### Community 14 - "Resource Forms & Pages"
Cohesion: 0.12
Nodes (22): App\Filament\Resources\LoanResource\Pages, App\Filament\Resources\Petugas\PengajuanResource\Pages, App\Filament\Resources\ProductPurchaseResource\Pages, App\Filament\Resources\ProductResource\Pages, App\Filament\Resources\ProductSalesResource\Pages, App\Filament\Resources\UserResource\Pages, App\Filament\Resources\UserResource\RelationManagers, Filament\Forms\Components\FileUpload (+14 more)

### Community 15 - "Product Purchase Calculations"
Cohesion: 0.12
Nodes (3): Filament\Forms\Get, Filament\Forms\Set, ProductPurchaseResource

### Community 16 - "Product Category Resource"
Cohesion: 0.09
Nodes (3): EditProductCategory, ListProductCategories, ProductCategoryResource

### Community 18 - "Sale Printing & Observer"
Cohesion: 0.18
Nodes (3): SalePrintController, Sale, SaleObserver

### Community 19 - "Inventory Report"
Cohesion: 0.11
Nodes (3): InventoryReport, SaleDetail, SaleDetailObserver

### Community 20 - "Low Stock Notifications"
Cohesion: 0.24
Nodes (7): Illuminate\Bus\Queueable, Illuminate\Contracts\Queue\ShouldQueue, Illuminate\Foundation\Bus\Dispatchable, Illuminate\Queue\InteractsWithQueue, Illuminate\Queue\SerializesModels, SendLowStockNotificationJob, ProductObserver

### Community 21 - "Audit Trail Page"
Cohesion: 0.05
Nodes (13): AuditTrailPage, FinancialReport, StockOverviewWidget, StockMovementStatsWidget, AuditTrailStatsWidget, InventoryOverview, RekapKeuanganStats, RekapPinjamanStats (+5 more)

### Community 22 - "Product Purchase Forms"
Cohesion: 0.11
Nodes (4): CreateProductPurchase, EditProductPurchase, Expense, ExpenseCategory

### Community 23 - "Savings Type Resource"
Cohesion: 0.12
Nodes (3): EditSavingsType, ListSavingsTypes, SavingsTypeResource

### Community 24 - "Loan Creation"
Cohesion: 0.12
Nodes (3): CreateLoan, Loan, LoanService

### Community 25 - "Permission Resource"
Cohesion: 0.12
Nodes (3): EditPermission, ListPermissions, PermissionResource

### Community 29 - "View Record Pages"
Cohesion: 0.13
Nodes (9): Filament\Infolists, Filament\Infolists\Infolist, ViewActivityLog, ViewLoan, ViewSaving, ViewAuthLog, ViewDataChangeLog, Filament\Resources\Pages\ViewRecord (+1 more)

### Community 31 - "Stock Movement Tracking"
Cohesion: 0.18
Nodes (3): Illuminate\Support\Facades\Log, StockMovementLog, StockMovementService

### Community 32 - "User Resource"
Cohesion: 0.16
Nodes (5): Filament\Actions\Action, Filament\Forms\Components\CheckboxList, EditUser, ListUsers, UserResource

### Community 33 - "Product Resource"
Cohesion: 0.07
Nodes (3): ProductResource, ViewStockMovementLog, StockMovementLogResource

### Community 35 - "Audit Trail Observer"
Cohesion: 0.30
Nodes (4): Illuminate\Database\Eloquent\Model, RolePermissions, StockAdjusmentDetail, AuditTrailObserver

### Community 36 - "Audit Log Cleanup"
Cohesion: 0.08
Nodes (5): CleanupAuditLogs, Illuminate\Support\Facades\Config, Illuminate\Support\Facades\Request, ActivityLog, AuthLog

### Community 38 - "Purchase Detail Stock"
Cohesion: 0.24
Nodes (4): Illuminate\Support\ServiceProvider, PurchaseDetail, PurchaseDetailObserver, AppServiceProvider

### Community 39 - "Dashboard Recap Charts"
Cohesion: 0.09
Nodes (6): Filament\Widgets\ChartWidget, RekapAnggota, RekapAnggotaBaru, RekapPenjualan, RekapPinjaman, RekapSimpanan

### Community 42 - "Recent Activities Widget"
Cohesion: 0.19
Nodes (3): RecentActivitiesWidget, RecentLoanRequests, Filament\Widgets\TableWidget

### Community 43 - "Product & Stock Commands"
Cohesion: 0.15
Nodes (5): CheckLowStockCommand, GenerateAuditTestData, CreateProductSales, Illuminate\Console\Command, Product

### Community 44 - "SHU Distribution Report"
Cohesion: 0.12
Nodes (5): Illuminate\Database\Eloquent\Relations\BelongsTo, LoanPayment, Report, ShuMemberShare, UserRole

### Community 52 - "Savings Resource"
Cohesion: 0.11
Nodes (3): SavingResource, SavingsTransaction, SavingsType

### Community 53 - "SHU Calculation"
Cohesion: 0.13
Nodes (3): Illuminate\Database\Eloquent\Relations\HasMany, ShuDistribution, StockAdjusment

### Community 56 - "Auth Events"
Cohesion: 0.25
Nodes (7): Failed, Logout, Lockout, Login, PasswordReset, Registered, Verified

### Community 62 - "Loan Application & Calculator"
Cohesion: 0.12
Nodes (3): ViewLoan, CreatePengajuan, LoanCalculator

### Community 71 - "Logout Response"
Cohesion: 0.50
Nodes (3): Filament\Http\Responses\Auth\Contracts\LogoutResponse, LogoutResponse, Illuminate\Http\RedirectResponse

### Community 77 - "Backup Management"
Cohesion: 0.18
Nodes (3): BackupManagement, Illuminate\Database\Eloquent\Relations\MorphTo, Notification

## Knowledge Gaps
- **1 isolated node(s):** `Dashboard`
  These have ≤1 connection - possible missing edges or undocumented components.
- **25 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User Auth & Member Cards` to `User Resource`, `Resource Page Namespaces`, `Report Dashboard`, `Product Sales Resource`, `Dashboard Recap Charts`, `Member API Endpoints`, `Core Models`, `Purchase Detail Stock`, `Product & Stock Commands`, `Income & Loan Reports`, `Report Pages & PDF`, `Resource Forms & Pages`, `Savings Resource`, `Low Stock Notifications`, `Product Purchase Forms`?**
  _High betweenness centrality (0.095) - this node is a cross-community bridge._
- **Why does `Product` connect `Product & Stock Commands` to `Report Export Classes`, `Resource Page Namespaces`, `Report Dashboard`, `Product Sales Resource`, `Core Models`, `Report Pages & PDF`, `Resource Forms & Pages`, `Product Purchase Calculations`, `Sale Printing & Observer`, `Inventory Report`, `Low Stock Notifications`, `Audit Trail Page`, `Product Purchase Forms`, `Purchase Stock Updates`, `Stock Movement Tracking`, `Audit Trail Observer`, `Purchase Detail Stock`, `Product List Page`, `Report Export Service`?**
  _High betweenness centrality (0.078) - this node is a cross-community bridge._
- **Why does `Loan` connect `Loan Creation` to `Report Export Classes`, `Resource Page Namespaces`, `Create Loan Page`, `Report Dashboard`, `Audit Trail Observer`, `Purchase Detail Stock`, `Dashboard Recap Charts`, `Member API Endpoints`, `Core Models`, `Recent Activities Widget`, `Income & Loan Reports`, `Report Pages & PDF`, `Resource Forms & Pages`, `Audit Trail Page`, `SHU Calculation`, `Product Purchase Forms`, `Loan Application & Calculator`?**
  _High betweenness centrality (0.057) - this node is a cross-community bridge._
- **What connects `Dashboard` to the rest of the system?**
  _1 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Report Export Classes` be split into smaller, more focused modules?**
  _Cohesion score 0.08283730158730158 - nodes in this community are weakly interconnected._
- **Should `User Auth & Member Cards` be split into smaller, more focused modules?**
  _Cohesion score 0.055811571940604196 - nodes in this community are weakly interconnected._
- **Should `Resource Page Namespaces` be split into smaller, more focused modules?**
  _Cohesion score 0.12403100775193798 - nodes in this community are weakly interconnected._