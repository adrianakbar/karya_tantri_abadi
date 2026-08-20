# Graph Report - app  (2026-08-20)

## Corpus Check
- 190 files · ~42,861 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1321 nodes · 3032 edges · 77 communities (45 shown, 32 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 60 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `fdef5998`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Financial Report Exports
- User
- Filament\Tables\Table
- Filament\Resources\Pages\ListRecords
- ReportDashboard
- Filament\Actions
- Product Sales and Purchases
- SavingsTransaction
- Illuminate\Database\Eloquent\Factories\HasFactory
- PetugasPanelProvider.php
- Filament\Resources\Pages\EditRecord
- SHU Report Calculations
- AuthenticationListener
- Illuminate\Support\Facades\Auth
- Resources/LoanResource.php
- FinancialReport
- ProductCategoryResource
- LoanResource
- Sale
- InventoryReport
- Product
- AuditTrailPage.php
- ExpenseCategory
- Savings Type Management
- Loan Model
- Permission Management
- User Role Management
- DataChangeLog
- Audit Trail Service
- Filament\Infolists\Infolist
- Purchase
- StockMovementLog
- User Management
- StockMovementLogResource
- Stock Movement Log Resource
- Audit Trail Observer
- ActivityLog
- Auth Log Resource
- AppServiceProvider.php
- Filament\Widgets\ChartWidget
- LoanTypeResource
- Roles
- RecentActivitiesWidget
- Permissions
- Illuminate\Database\Eloquent\Relations\BelongsTo
- LoanReport
- Product Sales Resource
- AuthLog
- Activity Log Resource
- Filament\Resources\Pages\ViewRecord
- Data Change Log Resource
- Loan Application Resource
- Savings Resource
- Model Relationships
- SystemSetting
- Role Management
- Authentication Events
- Loan Resource
- Savings Resource
- Loan Application Creation
- ListProducts
- SendLowStockNotificationJob
- Loan Calculator
- EditLoan
- Report Export Service
- IncomeReport
- Create Loan Page
- Create Product Sale Page
- Dashboard Page
- User.php
- SystemSettingResource
- LogoutResponse
- RekapPenjualan
- Resources/ProductSalesResource/Pages/ListProductSales.php
- User Roles Pivot
- EventServiceProvider

## God Nodes (most connected - your core abstractions)
1. `User` - 72 edges
2. `Product` - 61 edges
3. `Sale` - 51 edges
4. `Loan` - 46 edges
5. `Purchase` - 39 edges
6. `AuditTrailService` - 34 edges
7. `SavingsTransaction` - 28 edges
8. `InventoryReport` - 27 edges
9. `ProductSalesResource` - 27 edges
10. `StockMovementLog` - 27 edges

## Surprising Connections (you probably didn't know these)
- `SalePrintController` --inherits--> `Controller`  [EXTRACTED]
  Http/Controllers/SalePrintController.php → Http/Controllers/Controller.php
- `Customer` --inherits--> `User`  [EXTRACTED]
  Models/Customer.php → Models/User.php
- `MemberApiController` --inherits--> `Controller`  [EXTRACTED]
  Http/Controllers/Api/MemberApiController.php → Http/Controllers/Controller.php
- `LoginController` --inherits--> `Controller`  [EXTRACTED]
  Http/Controllers/Auth/LoginController.php → Http/Controllers/Controller.php
- `BackupDownloadController` --inherits--> `Controller`  [EXTRACTED]
  Http/Controllers/BackupDownloadController.php → Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (77 total, 32 thin omitted)

### Community 0 - "Financial Report Exports"
Cohesion: 0.08
Nodes (18): FinancialReportExport, IncomeReportExport, InventoryReportExport, LoanPaymentExport, LoanReportExport, ReportExport, SavingsReportExport, ShuCalculationExport (+10 more)

### Community 1 - "User"
Cohesion: 0.20
Nodes (3): Illuminate\Foundation\Auth\User, HasMany, User

### Community 2 - "Filament\Tables\Table"
Cohesion: 0.14
Nodes (27): App\Filament\Resources\ActivityLogResource\Pages, App\Filament\Resources\Anggota\LoanResource\Pages, App\Filament\Resources\Anggota\ProductSalesResource\Pages, App\Filament\Resources\Anggota\SavingResource\Pages, App\Filament\Resources\AuthLogResource\Pages, App\Filament\Resources\DataChangeLogResource\Pages, App\Filament\Resources\LoanTypeResource\Pages, App\Filament\Resources\LoanTypeResource\RelationManagers (+19 more)

### Community 3 - "Filament\Resources\Pages\ListRecords"
Cohesion: 0.05
Nodes (14): ListActivityLogs, ListLoans, ListProductSales, ListSavings, ListAuthLogs, ListDataChangeLogs, ListLoans, Filament\Resources\Pages\ListRecords (+6 more)

### Community 4 - "ReportDashboard"
Cohesion: 0.19
Nodes (4): Carbon, ReportDashboard, Illuminate\Support\Carbon, Illuminate\Support\Collection

### Community 5 - "Filament\Actions"
Cohesion: 0.07
Nodes (14): Filament\Actions, CreateLoan, CreateLoanType, Filament\Resources\Pages\CreateRecord, CreatePermission, CreateProduct, CreateRole, CreateSaving (+6 more)

### Community 6 - "Product Sales and Purchases"
Cohesion: 0.06
Nodes (6): Filament\Forms\Components\Section, Filament\Forms\Get, Filament\Forms\Set, ProductPurchaseResource, ProductSalesResource, Section

### Community 7 - "SavingsTransaction"
Cohesion: 0.06
Nodes (19): Closure, RekapKeuanganStats, MemberApiController, LoginController, BackupDownloadController, Controller, MemberCardController, SavingsReceiptController (+11 more)

### Community 8 - "Illuminate\Database\Eloquent\Factories\HasFactory"
Cohesion: 0.15
Nodes (6): Illuminate\Database\Eloquent\Factories\HasFactory, Cooperation, Customer, SavingsType, Supplier, UserRole

### Community 9 - "PetugasPanelProvider.php"
Cohesion: 0.18
Nodes (21): Filament\Http\Middleware\Authenticate, Filament\Http\Middleware\AuthenticateSession, Filament\Http\Middleware\DisableBladeIconComponents, Filament\Http\Middleware\DispatchServingFilamentEvent, Filament\Pages, Filament\Panel, Filament\PanelProvider, Filament\Support\Colors\Color (+13 more)

### Community 10 - "Filament\Resources\Pages\EditRecord"
Cohesion: 0.08
Nodes (8): Filament\Resources\Pages\EditRecord, EditProductCategory, EditProduct, EditProductSales, EditRole, EditSaving, EditStockMovementLog, EditSystemSetting

### Community 11 - "SHU Report Calculations"
Cohesion: 0.10
Nodes (3): ShuReport, ShuDistribution, ShuMemberShare

### Community 12 - "AuthenticationListener"
Cohesion: 0.17
Nodes (9): Illuminate\Auth\Events\Failed, Illuminate\Auth\Events\Lockout, Illuminate\Auth\Events\Login, Illuminate\Auth\Events\Logout, Illuminate\Auth\Events\PasswordReset, Illuminate\Auth\Events\Registered, Illuminate\Auth\Events\Verified, AuthenticationListener (+1 more)

### Community 13 - "Illuminate\Support\Facades\Auth"
Cohesion: 0.33
Nodes (14): Barryvdh\DomPDF\Facade\Pdf, Carbon\Carbon, Filament\Forms\Components\DatePicker, Filament\Forms\Components\Select, Filament\Forms\Concerns\InteractsWithForms, Filament\Notifications\Notification, Filament\Pages\Page, Filament\Tables\Actions\Action (+6 more)

### Community 14 - "Resources/LoanResource.php"
Cohesion: 0.11
Nodes (22): App\Filament\Resources\LoanResource\Pages, App\Filament\Resources\Petugas\PengajuanResource\Pages, App\Filament\Resources\ProductPurchaseResource\Pages, App\Filament\Resources\ProductResource\Pages, App\Filament\Resources\ProductSalesResource\Pages, App\Filament\Resources\UserResource\Pages, App\Filament\Resources\UserResource\RelationManagers, Filament\Forms\Components\FileUpload (+14 more)

### Community 15 - "FinancialReport"
Cohesion: 0.10
Nodes (6): BackupManagement, FinancialReport, Illuminate\Database\Eloquent\Relations\MorphTo, CashFlow, Notification, TransactionSummary

### Community 16 - "ProductCategoryResource"
Cohesion: 0.09
Nodes (3): CreateProductCategory, ListProductCategories, ProductCategoryResource

### Community 18 - "Sale"
Cohesion: 0.18
Nodes (3): SalePrintController, Sale, SaleObserver

### Community 20 - "Product"
Cohesion: 0.18
Nodes (4): StockOverviewWidget, Illuminate\Support\Facades\Log, Product, ProductObserver

### Community 21 - "AuditTrailPage.php"
Cohesion: 0.13
Nodes (6): StockMovementStatsWidget, AuditTrailStatsWidget, InventoryOverview, RekapPinjamanStats, Filament\Widgets\StatsOverviewWidget, Filament\Widgets\StatsOverviewWidget\Stat

### Community 23 - "Savings Type Management"
Cohesion: 0.12
Nodes (3): EditSavingsType, ListSavingsTypes, SavingsTypeResource

### Community 25 - "Permission Management"
Cohesion: 0.12
Nodes (3): EditPermission, ListPermissions, PermissionResource

### Community 26 - "User Role Management"
Cohesion: 0.12
Nodes (3): EditUserRole, ListUserRoles, UserRoleResource

### Community 27 - "DataChangeLog"
Cohesion: 0.11
Nodes (3): Illuminate\Support\Facades\Config, Illuminate\Support\Facades\Request, DataChangeLog

### Community 29 - "Filament\Infolists\Infolist"
Cohesion: 0.14
Nodes (6): Filament\Infolists, Filament\Infolists\Infolist, ViewLoan, ViewSaving, ViewLoan, ViewPengajuan

### Community 30 - "Purchase"
Cohesion: 0.16
Nodes (3): CreateProductPurchase, Purchase, PurchaseObserver

### Community 32 - "User Management"
Cohesion: 0.16
Nodes (5): Filament\Actions\Action, Filament\Forms\Components\CheckboxList, EditUser, ListUsers, UserResource

### Community 33 - "StockMovementLogResource"
Cohesion: 0.07
Nodes (3): ProductResource, ViewStockMovementLog, StockMovementLogResource

### Community 35 - "Audit Trail Observer"
Cohesion: 0.41
Nodes (3): Illuminate\Database\Eloquent\Model, RolePermissions, AuditTrailObserver

### Community 36 - "ActivityLog"
Cohesion: 0.11
Nodes (5): CheckLowStockCommand, CleanupAuditLogs, GenerateAuditTestData, Illuminate\Console\Command, ActivityLog

### Community 38 - "AppServiceProvider.php"
Cohesion: 0.15
Nodes (6): Illuminate\Support\ServiceProvider, PurchaseDetail, SaleDetail, PurchaseDetailObserver, SaleDetailObserver, AppServiceProvider

### Community 39 - "Filament\Widgets\ChartWidget"
Cohesion: 0.12
Nodes (5): Filament\Widgets\ChartWidget, RekapAnggota, RekapAnggotaBaru, RekapPinjaman, RekapSimpanan

### Community 40 - "LoanTypeResource"
Cohesion: 0.12
Nodes (3): LoanTypeResource, EditLoanType, ListLoanTypes

### Community 41 - "Roles"
Cohesion: 0.19
Nodes (3): Illuminate\Database\Eloquent\Relations\BelongsToMany, Roles, RolePolicy

### Community 42 - "RecentActivitiesWidget"
Cohesion: 0.19
Nodes (3): RecentActivitiesWidget, RecentLoanRequests, Filament\Widgets\TableWidget

### Community 43 - "Permissions"
Cohesion: 0.21
Nodes (5): Illuminate\Foundation\Support\Providers\AuthServiceProvider, Illuminate\Support\Facades\Gate, Permissions, PermissionPolicy, AuthServiceProvider

### Community 44 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.12
Nodes (6): Illuminate\Database\Eloquent\Relations\BelongsTo, Expense, LoanPayment, Report, StockAdjusmentDetail, Builder

### Community 45 - "LoanReport"
Cohesion: 0.13
Nodes (4): Filament\Forms\Contracts\HasForms, LoanReport, SavingsReport, Filament\Tables\Contracts\HasTable

### Community 49 - "Filament\Resources\Pages\ViewRecord"
Cohesion: 0.19
Nodes (5): ViewActivityLog, ViewProductSales, ViewAuthLog, ViewDataChangeLog, Filament\Resources\Pages\ViewRecord

### Community 56 - "Authentication Events"
Cohesion: 0.25
Nodes (7): Failed, Logout, Lockout, Login, PasswordReset, Registered, Verified

### Community 61 - "SendLowStockNotificationJob"
Cohesion: 0.36
Nodes (6): Illuminate\Bus\Queueable, Illuminate\Contracts\Queue\ShouldQueue, Illuminate\Foundation\Bus\Dispatchable, Illuminate\Queue\InteractsWithQueue, Illuminate\Queue\SerializesModels, SendLowStockNotificationJob

### Community 68 - "Dashboard Page"
Cohesion: 0.40
Nodes (3): Filament\Pages\Concerns\HasWidgets, Dashboard, Filament\Widgets\AccountWidget

### Community 69 - "User.php"
Cohesion: 0.29
Nodes (5): Filament\Facades\Filament, Filament\Models\Contracts\FilamentUser, Illuminate\Http\Exceptions\HttpResponseException, Illuminate\Notifications\Notifiable, Laravel\Sanctum\HasApiTokens

### Community 71 - "LogoutResponse"
Cohesion: 0.50
Nodes (3): Filament\Http\Responses\Auth\Contracts\LogoutResponse, LogoutResponse, Illuminate\Http\RedirectResponse

## Knowledge Gaps
- **32 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `User Management`, `AppServiceProvider.php`, `Filament\Tables\Table`, `ReportDashboard`, `ActivityLog`, `Product Sales and Purchases`, `Filament\Widgets\ChartWidget`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `SavingsTransaction`, `User.php`, `SHU Report Calculations`, `Permissions`, `LoanReport`, `Illuminate\Support\Facades\Auth`, `Resources/LoanResource.php`, `Roles`, `SystemSetting`, `SendLowStockNotificationJob`?**
  _High betweenness centrality (0.122) - this node is a cross-community bridge._
- **Why does `Product` connect `Product` to `Financial Report Exports`, `Filament\Tables\Table`, `ReportDashboard`, `Product Sales and Purchases`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `Illuminate\Support\Facades\Auth`, `Resources/LoanResource.php`, `Sale`, `InventoryReport`, `AuditTrailPage.php`, `Purchase`, `StockMovementLog`, `Audit Trail Observer`, `ActivityLog`, `AppServiceProvider.php`, `ListProducts`, `SendLowStockNotificationJob`, `Report Export Service`, `Create Product Sale Page`?**
  _High betweenness centrality (0.073) - this node is a cross-community bridge._
- **Why does `Loan` connect `Loan Model` to `Financial Report Exports`, `Filament\Tables\Table`, `Create Loan Page`, `ReportDashboard`, `Filament\Actions`, `Audit Trail Observer`, `Filament\Widgets\ChartWidget`, `SavingsTransaction`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `RecentActivitiesWidget`, `AppServiceProvider.php`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `LoanReport`, `Illuminate\Support\Facades\Auth`, `Resources/LoanResource.php`, `AuditTrailPage.php`, `Model Relationships`, `Loan Application Creation`?**
  _High betweenness centrality (0.062) - this node is a cross-community bridge._
- **Should `Financial Report Exports` be split into smaller, more focused modules?**
  _Cohesion score 0.08283730158730158 - nodes in this community are weakly interconnected._
- **Should `Filament\Tables\Table` be split into smaller, more focused modules?**
  _Cohesion score 0.13530655391120508 - nodes in this community are weakly interconnected._
- **Should `Filament\Resources\Pages\ListRecords` be split into smaller, more focused modules?**
  _Cohesion score 0.05187074829931973 - nodes in this community are weakly interconnected._
- **Should `Filament\Actions` be split into smaller, more focused modules?**
  _Cohesion score 0.06659619450317125 - nodes in this community are weakly interconnected._