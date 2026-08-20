# Graph Report - app  (2026-08-20)

## Corpus Check
- 191 files · ~43,221 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1323 nodes · 3044 edges · 68 communities (41 shown, 27 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 61 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `c01f736b`
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
- Illuminate\Http\Request
- Illuminate\Database\Eloquent\Factories\HasFactory
- PetugasPanelProvider.php
- Filament\Resources\Pages\EditRecord
- ShuReport
- AuthenticationListener
- Illuminate\Support\Facades\Auth
- Resources/LoanResource.php
- ProductPurchaseResource
- ProductCategoryResource
- LoanResource
- Sale
- InventoryReport
- Product
- FinancialReport
- Expense
- SavingsTypeResource
- Loan
- PermissionResource
- UserRoleResource
- DataChangeLog
- AuditTrailService
- Filament\Resources\Pages\ViewRecord
- Purchase
- StockMovementLog
- UserResource
- StockMovementLogResource
- StockMovementLogResource
- Illuminate\Database\Eloquent\Model
- AuthLog
- AuthLogResource
- AppServiceProvider.php
- Filament\Widgets\ChartWidget
- LoanTypeResource
- Illuminate\Auth\Events\Logout
- RecentActivitiesWidget
- PaymentsRelationManager
- Illuminate\Database\Eloquent\Relations\BelongsTo
- ListRoles.php
- ProductSalesResource
- ActivityLogResource
- ViewProductSales.php
- DataChangeLogResource
- PengajuanResource
- SavingsTransaction
- ShuDistribution
- RoleResource
- .subscribe
- LoanResource
- SavingResource
- ListProducts.php
- LoanCalculator
- ReportExportService.php
- CreateLoan
- Dashboard.php
- SystemSettingResource
- LogoutResponse
- UserRoles
- EventServiceProvider
- Notification

## God Nodes (most connected - your core abstractions)
1. `User` - 72 edges
2. `Product` - 61 edges
3. `Sale` - 51 edges
4. `Loan` - 46 edges
5. `Purchase` - 39 edges
6. `AuditTrailService` - 34 edges
7. `SavingsTransaction` - 30 edges
8. `InventoryReport` - 27 edges
9. `ProductSalesResource` - 27 edges
10. `StockMovementLog` - 27 edges

## Surprising Connections (you probably didn't know these)
- `MemberCardController` --inherits--> `Controller`  [EXTRACTED]
  Http/Controllers/MemberCardController.php → Http/Controllers/Controller.php
- `SalePrintController` --inherits--> `Controller`  [EXTRACTED]
  Http/Controllers/SalePrintController.php → Http/Controllers/Controller.php
- `Customer` --inherits--> `User`  [EXTRACTED]
  Models/Customer.php → Models/User.php
- `MemberApiController` --inherits--> `Controller`  [EXTRACTED]
  Http/Controllers/Api/MemberApiController.php → Http/Controllers/Controller.php
- `LoginController` --inherits--> `Controller`  [EXTRACTED]
  Http/Controllers/Auth/LoginController.php → Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (68 total, 27 thin omitted)

### Community 0 - "Maatwebsite\Excel\Concerns\WithStyles"
Cohesion: 0.08
Nodes (18): FinancialReportExport, IncomeReportExport, InventoryReportExport, LoanPaymentExport, LoanReportExport, ReportExport, SavingsReportExport, ShuCalculationExport (+10 more)

### Community 1 - "User"
Cohesion: 0.06
Nodes (19): Filament\Facades\Filament, Filament\Models\Contracts\FilamentUser, MemberCardController, Illuminate\Database\Eloquent\Relations\BelongsToMany, Illuminate\Foundation\Auth\User, Illuminate\Foundation\Support\Providers\AuthServiceProvider, Illuminate\Http\Exceptions\HttpResponseException, Illuminate\Notifications\Notifiable (+11 more)

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
Nodes (14): Filament\Actions, CreateLoanType, Filament\Resources\Pages\CreateRecord, CreatePermission, CreateProductCategory, CreateProduct, CreateRole, CreateSaving (+6 more)

### Community 6 - "ProductSalesResource"
Cohesion: 0.11
Nodes (3): Filament\Forms\Components\Section, ProductSalesResource, Section

### Community 7 - "Illuminate\Http\Request"
Cohesion: 0.08
Nodes (16): Closure, MemberApiController, LoginController, BackupDownloadController, Controller, SavingsReceiptController, LogUserActivity, Illuminate\Contracts\View\View (+8 more)

### Community 8 - "Illuminate\Database\Eloquent\Factories\HasFactory"
Cohesion: 0.14
Nodes (7): Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Support\Str, Cooperation, Customer, LoanType, ProductCategory, Supplier

### Community 9 - "PetugasPanelProvider.php"
Cohesion: 0.18
Nodes (21): Filament\Http\Middleware\Authenticate, Filament\Http\Middleware\AuthenticateSession, Filament\Http\Middleware\DisableBladeIconComponents, Filament\Http\Middleware\DispatchServingFilamentEvent, Filament\Pages, Filament\Panel, Filament\PanelProvider, Filament\Support\Colors\Color (+13 more)

### Community 10 - "Filament\Resources\Pages\EditRecord"
Cohesion: 0.07
Nodes (9): EditLoanType, Filament\Resources\Pages\EditRecord, EditProduct, EditProductSales, EditRole, EditSaving, EditStockMovementLog, EditSystemSetting (+1 more)

### Community 11 - "ShuReport"
Cohesion: 0.07
Nodes (5): Filament\Forms\Contracts\HasForms, IncomeReport, LoanReport, ShuReport, Filament\Tables\Contracts\HasTable

### Community 12 - "AuthenticationListener"
Cohesion: 0.25
Nodes (7): Illuminate\Auth\Events\Failed, Illuminate\Auth\Events\Lockout, Illuminate\Auth\Events\Login, Illuminate\Auth\Events\PasswordReset, Illuminate\Auth\Events\Registered, Illuminate\Auth\Events\Verified, AuthenticationListener

### Community 13 - "Illuminate\Support\Facades\Auth"
Cohesion: 0.22
Nodes (16): Barryvdh\DomPDF\Facade\Pdf, Carbon\Carbon, Filament\Forms\Components\DatePicker, Filament\Forms\Components\Select, Filament\Forms\Concerns\InteractsWithForms, Filament\Notifications\Notification, Filament\Pages\Page, Pengaturan (+8 more)

### Community 14 - "Resources/LoanResource.php"
Cohesion: 0.13
Nodes (19): App\Filament\Resources\LoanResource\Pages, App\Filament\Resources\Petugas\PengajuanResource\Pages, App\Filament\Resources\ProductPurchaseResource\Pages, App\Filament\Resources\ProductResource\Pages, App\Filament\Resources\ProductSalesResource\Pages, App\Filament\Resources\UserResource\Pages, App\Filament\Resources\UserResource\RelationManagers, Filament\Forms\Components\FileUpload (+11 more)

### Community 15 - "ProductPurchaseResource"
Cohesion: 0.12
Nodes (3): Filament\Forms\Get, Filament\Forms\Set, ProductPurchaseResource

### Community 16 - "ProductCategoryResource"
Cohesion: 0.09
Nodes (3): EditProductCategory, ListProductCategories, ProductCategoryResource

### Community 18 - "Sale"
Cohesion: 0.13
Nodes (4): CreateProductSales, SalePrintController, Sale, SaleObserver

### Community 19 - "InventoryReport"
Cohesion: 0.11
Nodes (3): InventoryReport, SaleDetail, SaleDetailObserver

### Community 20 - "Product"
Cohesion: 0.13
Nodes (12): CheckLowStockCommand, GenerateAuditTestData, Illuminate\Bus\Queueable, Illuminate\Console\Command, Illuminate\Contracts\Queue\ShouldQueue, Illuminate\Foundation\Bus\Dispatchable, Illuminate\Queue\InteractsWithQueue, Illuminate\Queue\SerializesModels (+4 more)

### Community 21 - "FinancialReport"
Cohesion: 0.05
Nodes (13): AuditTrailPage, FinancialReport, StockOverviewWidget, StockMovementStatsWidget, AuditTrailStatsWidget, InventoryOverview, RekapKeuanganStats, RekapPinjamanStats (+5 more)

### Community 22 - "Expense"
Cohesion: 0.11
Nodes (4): CreateProductPurchase, EditProductPurchase, Expense, ExpenseCategory

### Community 23 - "SavingsTypeResource"
Cohesion: 0.12
Nodes (3): EditSavingsType, ListSavingsTypes, SavingsTypeResource

### Community 24 - "Loan"
Cohesion: 0.12
Nodes (3): CreateLoan, Loan, LoanService

### Community 25 - "PermissionResource"
Cohesion: 0.12
Nodes (3): EditPermission, ListPermissions, PermissionResource

### Community 29 - "Filament\Resources\Pages\ViewRecord"
Cohesion: 0.13
Nodes (9): Filament\Infolists, Filament\Infolists\Infolist, ViewActivityLog, ViewLoan, ViewSaving, ViewAuthLog, ViewDataChangeLog, Filament\Resources\Pages\ViewRecord (+1 more)

### Community 32 - "UserResource"
Cohesion: 0.16
Nodes (5): Filament\Actions\Action, Filament\Forms\Components\CheckboxList, EditUser, ListUsers, UserResource

### Community 33 - "StockMovementLogResource"
Cohesion: 0.07
Nodes (3): ProductResource, ViewStockMovementLog, StockMovementLogResource

### Community 35 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.30
Nodes (4): Illuminate\Database\Eloquent\Model, RolePermissions, StockAdjusmentDetail, AuditTrailObserver

### Community 36 - "AuthLog"
Cohesion: 0.08
Nodes (5): CleanupAuditLogs, Illuminate\Support\Facades\Config, Illuminate\Support\Facades\Request, ActivityLog, AuthLog

### Community 38 - "AppServiceProvider.php"
Cohesion: 0.24
Nodes (4): Illuminate\Support\ServiceProvider, PurchaseDetail, PurchaseDetailObserver, AppServiceProvider

### Community 39 - "Filament\Widgets\ChartWidget"
Cohesion: 0.09
Nodes (6): Filament\Widgets\ChartWidget, RekapAnggota, RekapAnggotaBaru, RekapPenjualan, RekapPinjaman, RekapSimpanan

### Community 42 - "RecentActivitiesWidget"
Cohesion: 0.19
Nodes (3): RecentActivitiesWidget, RecentLoanRequests, Filament\Widgets\TableWidget

### Community 44 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.12
Nodes (5): Illuminate\Database\Eloquent\Relations\BelongsTo, LoanPayment, Report, ShuMemberShare, UserRole

### Community 52 - "SavingsTransaction"
Cohesion: 0.11
Nodes (3): SavingResource, SavingsTransaction, SavingsType

### Community 53 - "ShuDistribution"
Cohesion: 0.13
Nodes (3): Illuminate\Database\Eloquent\Relations\HasMany, ShuDistribution, StockAdjusment

### Community 56 - ".subscribe"
Cohesion: 0.25
Nodes (7): Failed, Logout, Lockout, Login, PasswordReset, Registered, Verified

### Community 62 - "LoanCalculator"
Cohesion: 0.12
Nodes (3): ViewLoan, CreatePengajuan, LoanCalculator

### Community 71 - "LogoutResponse"
Cohesion: 0.50
Nodes (3): Filament\Http\Responses\Auth\Contracts\LogoutResponse, LogoutResponse, Illuminate\Http\RedirectResponse

### Community 77 - "Notification"
Cohesion: 0.18
Nodes (3): BackupManagement, Illuminate\Database\Eloquent\Relations\MorphTo, Notification

## Knowledge Gaps
- **1 isolated node(s):** `Dashboard`
  These have ≤1 connection - possible missing edges or undocumented components.
- **27 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `UserResource`, `Filament\Tables\Table`, `ReportDashboard`, `ProductSalesResource`, `Filament\Widgets\ChartWidget`, `Illuminate\Http\Request`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `AppServiceProvider.php`, `ShuReport`, `Illuminate\Support\Facades\Auth`, `Resources/LoanResource.php`, `Product`, `SavingsTransaction`, `Expense`?**
  _High betweenness centrality (0.121) - this node is a cross-community bridge._
- **Why does `Product` connect `Product` to `Maatwebsite\Excel\Concerns\WithStyles`, `ReportExportService.php`, `Filament\Tables\Table`, `Illuminate\Database\Eloquent\Model`, `ReportDashboard`, `ProductSalesResource`, `AppServiceProvider.php`, `Illuminate\Database\Eloquent\Factories\HasFactory`, `Illuminate\Support\Facades\Auth`, `Resources/LoanResource.php`, `ProductPurchaseResource`, `Sale`, `InventoryReport`, `FinancialReport`, `Expense`, `ListProducts.php`, `Purchase`, `StockMovementLog`?**
  _High betweenness centrality (0.098) - this node is a cross-community bridge._
- **Why does `PengajuanResource` connect `PengajuanResource` to `Filament\Tables\Table`, `Filament\Resources\Pages\ListRecords`, `PetugasPanelProvider.php`, `Resources/LoanResource.php`, `Filament\Resources\Pages\ViewRecord`, `LoanCalculator`?**
  _High betweenness centrality (0.050) - this node is a cross-community bridge._
- **What connects `Dashboard` to the rest of the system?**
  _1 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Maatwebsite\Excel\Concerns\WithStyles` be split into smaller, more focused modules?**
  _Cohesion score 0.08283730158730158 - nodes in this community are weakly interconnected._
- **Should `User` be split into smaller, more focused modules?**
  _Cohesion score 0.055811571940604196 - nodes in this community are weakly interconnected._
- **Should `Filament\Tables\Table` be split into smaller, more focused modules?**
  _Cohesion score 0.13530655391120508 - nodes in this community are weakly interconnected._