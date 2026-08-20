# Graph Report - app  (2026-08-20)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 1321 nodes · 3032 edges · 80 communities (45 shown, 35 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 60 edges (avg confidence: 0.85)
- Token cost: 24,871 input · 9,440 output

## Graph Freshness
- Built from commit: `fdef5998`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Maatwebsite\Excel\Concerns\WithStyles
- User
- Filament\Tables\Table
- Filament\Resources\Pages\ListRecords
- ReportDashboard
- Filament\Actions
- ProductSalesResource
- SavingsTransaction
- Illuminate\Database\Eloquent\Factories\HasFactory
- PetugasPanelProvider.php
- Filament\Resources\Pages\EditRecord
- ShuReport
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
- SavingsTypeResource
- Loan
- PermissionResource
- UserRoleResource
- DataChangeLog
- AuditTrailService
- Filament\Infolists\Infolist
- Purchase
- StockMovementLog
- UserResource
- StockMovementLogResource
- StockMovementLogResource
- Illuminate\Database\Eloquent\Model
- ActivityLog
- AuthLogResource
- AppServiceProvider.php
- Filament\Widgets\ChartWidget
- LoanTypeResource
- Roles
- RecentActivitiesWidget
- Permissions
- Illuminate\Database\Eloquent\Relations\BelongsTo
- LoanPayment
- ProductSalesResource
- AuthLog
- ActivityLogResource
- Filament\Resources\Pages\ViewRecord
- DataChangeLogResource
- PengajuanResource
- SavingResource
- Illuminate\Database\Eloquent\Relations\HasMany
- SystemSetting
- RoleResource
- .subscribe
- LoanResource
- SavingResource
- LoanType
- ListProducts
- SendLowStockNotificationJob
- LoanCalculator
- EditLoan
- ReportExportService.php
- IncomeReport
- CreateLoan
- CreateProductSales.php
- Dashboard.php
- User.php
- SystemSettingResource
- LogoutResponse
- RekapPenjualan
- Resources/ProductSalesResource/Pages/ListProductSales.php
- UserRoles
- EventServiceProvider
- Notification
- SavingsReport
- ViewSaving.php

## God Nodes (most connected - your core abstractions)
1. `User` - 72 edges
2. `Product` - 61 edges
3. `Sale` - 51 edges
4. `Loan` - 46 edges
5. `Purchase` - 39 edges
6. `AuditTrailService` - 34 edges
7. `SavingsTransaction` - 28 edges
8. `InventoryReport` - 27 edges
9. `StockMovementLog` - 27 edges
10. `ProductSalesResource` - 27 edges

## Surprising Connections (you probably didn't know these)
- `Customer` --inherits--> `User`  [EXTRACTED]
  Models/Customer.php → Models/User.php
- `SalePrintController` --inherits--> `Controller`  [EXTRACTED]
  Http/Controllers/SalePrintController.php → Http/Controllers/Controller.php
- `MemberApiController` --inherits--> `Controller`  [EXTRACTED]
  Http/Controllers/Api/MemberApiController.php → Http/Controllers/Controller.php
- `LoginController` --inherits--> `Controller`  [EXTRACTED]
  Http/Controllers/Auth/LoginController.php → Http/Controllers/Controller.php
- `BackupDownloadController` --inherits--> `Controller`  [EXTRACTED]
  Http/Controllers/BackupDownloadController.php → Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (80 total, 35 thin omitted)

### Community 0 - "Maatwebsite\Excel\Concerns\WithStyles"
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

### Community 6 - "ProductSalesResource"
Cohesion: 0.06
Nodes (6): Filament\Forms\Components\Section, Filament\Forms\Get, Filament\Forms\Set, ProductPurchaseResource, ProductSalesResource, Section

### Community 7 - "SavingsTransaction"
Cohesion: 0.05
Nodes (20): Closure, RekapKeuanganStats, MemberApiController, LoginController, BackupDownloadController, Controller, MemberCardController, SavingsReceiptController (+12 more)

### Community 8 - "Illuminate\Database\Eloquent\Factories\HasFactory"
Cohesion: 0.23
Nodes (5): Illuminate\Database\Eloquent\Factories\HasFactory, Cooperation, Customer, SavingsType, Supplier

### Community 9 - "PetugasPanelProvider.php"
Cohesion: 0.18
Nodes (21): Filament\Http\Middleware\Authenticate, Filament\Http\Middleware\AuthenticateSession, Filament\Http\Middleware\DisableBladeIconComponents, Filament\Http\Middleware\DispatchServingFilamentEvent, Filament\Pages, Filament\Panel, Filament\PanelProvider, Filament\Support\Colors\Color (+13 more)

### Community 10 - "Filament\Resources\Pages\EditRecord"
Cohesion: 0.08
Nodes (8): Filament\Resources\Pages\EditRecord, EditProductCategory, EditProduct, EditProductSales, EditRole, EditSaving, EditStockMovementLog, EditSystemSetting

### Community 11 - "ShuReport"
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
Cohesion: 0.18
Nodes (4): FinancialReport, Illuminate\Database\Eloquent\Relations\MorphTo, CashFlow, TransactionSummary

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

### Community 23 - "SavingsTypeResource"
Cohesion: 0.12
Nodes (3): EditSavingsType, ListSavingsTypes, SavingsTypeResource

### Community 25 - "PermissionResource"
Cohesion: 0.12
Nodes (3): EditPermission, ListPermissions, PermissionResource

### Community 26 - "UserRoleResource"
Cohesion: 0.12
Nodes (3): EditUserRole, ListUserRoles, UserRoleResource

### Community 27 - "DataChangeLog"
Cohesion: 0.11
Nodes (3): Illuminate\Support\Facades\Config, Illuminate\Support\Facades\Request, DataChangeLog

### Community 29 - "Filament\Infolists\Infolist"
Cohesion: 0.17
Nodes (5): Filament\Infolists, Filament\Infolists\Infolist, ViewLoan, ViewLoan, ViewPengajuan

### Community 30 - "Purchase"
Cohesion: 0.16
Nodes (3): CreateProductPurchase, Purchase, PurchaseObserver

### Community 32 - "UserResource"
Cohesion: 0.16
Nodes (5): Filament\Actions\Action, Filament\Forms\Components\CheckboxList, EditUser, ListUsers, UserResource

### Community 33 - "StockMovementLogResource"
Cohesion: 0.07
Nodes (3): ProductResource, ViewStockMovementLog, StockMovementLogResource

### Community 35 - "Illuminate\Database\Eloquent\Model"
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

### Community 42 - "RecentActivitiesWidget"
Cohesion: 0.19
Nodes (3): RecentActivitiesWidget, RecentLoanRequests, Filament\Widgets\TableWidget

### Community 43 - "Permissions"
Cohesion: 0.21
Nodes (3): Illuminate\Database\Eloquent\Relations\BelongsToMany, Permissions, PermissionPolicy

### Community 44 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.15
Nodes (5): Illuminate\Database\Eloquent\Relations\BelongsTo, Expense, Report, StockAdjusmentDetail, UserRole

### Community 49 - "Filament\Resources\Pages\ViewRecord"
Cohesion: 0.19
Nodes (5): ViewActivityLog, ViewProductSales, ViewAuthLog, ViewDataChangeLog, Filament\Resources\Pages\ViewRecord

### Community 54 - "SystemSetting"
Cohesion: 0.17
Nodes (5): Illuminate\Foundation\Support\Providers\AuthServiceProvider, Illuminate\Support\Facades\Gate, SystemSetting, SystemSettingPolicy, AuthServiceProvider

### Community 56 - ".subscribe"
Cohesion: 0.25
Nodes (7): Failed, Logout, Lockout, Login, PasswordReset, Registered, Verified

### Community 61 - "SendLowStockNotificationJob"
Cohesion: 0.36
Nodes (6): Illuminate\Bus\Queueable, Illuminate\Contracts\Queue\ShouldQueue, Illuminate\Foundation\Bus\Dispatchable, Illuminate\Queue\InteractsWithQueue, Illuminate\Queue\SerializesModels, SendLowStockNotificationJob

### Community 68 - "Dashboard.php"
Cohesion: 0.40
Nodes (3): Filament\Pages\Concerns\HasWidgets, Dashboard, Filament\Widgets\AccountWidget

### Community 69 - "User.php"
Cohesion: 0.29
Nodes (5): Filament\Facades\Filament, Filament\Models\Contracts\FilamentUser, Illuminate\Http\Exceptions\HttpResponseException, Illuminate\Notifications\Notifiable, Laravel\Sanctum\HasApiTokens

### Community 71 - "LogoutResponse"
Cohesion: 0.50
Nodes (3): Filament\Http\Responses\Auth\Contracts\LogoutResponse, LogoutResponse, Illuminate\Http\RedirectResponse

### Community 78 - "SavingsReport"
Cohesion: 0.33
Nodes (3): Filament\Forms\Contracts\HasForms, SavingsReport, Filament\Tables\Contracts\HasTable

## Knowledge Gaps
- **35 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `UserResource`, `AppServiceProvider.php`, `Filament\Tables\Table`, `ReportDashboard`, `ActivityLog`, `ProductSalesResource`, `Filament\Widgets\ChartWidget`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `SavingsTransaction`, `User.php`, `ShuReport`, `Permissions`, `Illuminate\Support\Facades\Auth`, `SavingsReport`, `Resources/LoanResource.php`, `Roles`, `SystemSetting`, `SendLowStockNotificationJob`?**
  _High betweenness centrality (0.111) - this node is a cross-community bridge._
- **Why does `Product` connect `Product` to `Maatwebsite\Excel\Concerns\WithStyles`, `Filament\Tables\Table`, `ReportDashboard`, `ProductSalesResource`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `Illuminate\Support\Facades\Auth`, `Resources/LoanResource.php`, `Sale`, `InventoryReport`, `AuditTrailPage.php`, `Purchase`, `StockMovementLog`, `Illuminate\Database\Eloquent\Model`, `ActivityLog`, `AppServiceProvider.php`, `ListProducts`, `SendLowStockNotificationJob`, `ReportExportService.php`, `CreateProductSales.php`?**
  _High betweenness centrality (0.063) - this node is a cross-community bridge._
- **Why does `ProductResource` connect `StockMovementLogResource` to `Filament\Tables\Table`, `Filament\Actions`, `Filament\Resources\Pages\EditRecord`, `Resources/LoanResource.php`, `Product`?**
  _High betweenness centrality (0.047) - this node is a cross-community bridge._
- **Should `Maatwebsite\Excel\Concerns\WithStyles` be split into smaller, more focused modules?**
  _Cohesion score 0.08283730158730158 - nodes in this community are weakly interconnected._
- **Should `Filament\Tables\Table` be split into smaller, more focused modules?**
  _Cohesion score 0.13530655391120508 - nodes in this community are weakly interconnected._
- **Should `Filament\Resources\Pages\ListRecords` be split into smaller, more focused modules?**
  _Cohesion score 0.05187074829931973 - nodes in this community are weakly interconnected._
- **Should `Filament\Actions` be split into smaller, more focused modules?**
  _Cohesion score 0.06659619450317125 - nodes in this community are weakly interconnected._