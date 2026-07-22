<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\User;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\SavingsTransaction;
use App\Models\Loan;
use App\Models\Product;
use App\Models\ShuDistribution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Dashboard Laporan';
    protected static ?string $navigationGroup = 'Laporan';
    protected static string $view = 'filament.pages.report-dashboard';
    protected static ?string $title = 'Dashboard Laporan Karya Tantri Abadi';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return false;
    }

    public static function getNavigationGroup(): ?string
    {
        $panelId = \Filament\Facades\Filament::getCurrentPanel()?->getId();
        return $panelId === 'spv' ? null : 'Laporan';
    }

    protected function getCooperationId(): ?int
    {
        return Auth::user()?->cooperation_id;
    }

    public function getMonthlyRevenue(): float
    {
        $cooperationId = $this->getCooperationId();
        if (!$cooperationId) return 0;

        return Sale::where('cooperation_id', $cooperationId)
            ->whereMonth('sale_date', Carbon::now()->month)
            ->whereYear('sale_date', Carbon::now()->year)
            ->sum('total_amount');
    }

    public function getMonthlyExpenses(): float
    {
        $cooperationId = $this->getCooperationId();
        if (!$cooperationId) return 0;

        $purchases = Purchase::where('cooperation_id', $cooperationId)
            ->whereMonth('purchase_date', Carbon::now()->month)
            ->whereYear('purchase_date', Carbon::now()->year)
            ->sum('total_amount');

        $expenses = Expense::where('cooperation_id', $cooperationId)
            ->whereMonth('expense_date', Carbon::now()->month)
            ->whereYear('expense_date', Carbon::now()->year)
            ->sum('amount');

        return $purchases + $expenses;
    }

    public function getTotalMembers(): int
    {
        $cooperationId = $this->getCooperationId();
        if (!$cooperationId) return 0;

        return User::where('cooperation_id', $cooperationId)->count();
    }

    public function getTotalSavings(): float
    {
        $cooperationId = $this->getCooperationId();
        if (!$cooperationId) return 0;

        return SavingsTransaction::where('cooperation_id', $cooperationId)
            ->where('status', 'completed')
            ->sum('amount');
    }

    public function getActiveLoansCount(): int
    {
        $cooperationId = $this->getCooperationId();
        if (!$cooperationId) return 0;

        return Loan::where('cooperation_id', $cooperationId)
            ->where('status', 'active')
            ->count();
    }

    public function getTotalOutstandingLoans(): float
    {
        $cooperationId = $this->getCooperationId();
        if (!$cooperationId) return 0;

        return Loan::where('cooperation_id', $cooperationId)
            ->where('status', 'active')
            ->sum('principal_amount');
    }

    public function getLastYearShu(): float
    {
        $cooperationId = $this->getCooperationId();
        if (!$cooperationId) return 0;

        return ShuDistribution::where('cooperation_id', $cooperationId)
            ->where('year', Carbon::now()->year - 1)
            ->value('total_shu') ?? 0;
    }

    public function getProjectedShu(): float
    {
        $cooperationId = $this->getCooperationId();
        if (!$cooperationId) return 0;

        $revenue = Sale::where('cooperation_id', $cooperationId)
            ->whereYear('sale_date', Carbon::now()->year)
            ->sum('total_amount');

        $purchases = Purchase::where('cooperation_id', $cooperationId)
            ->whereYear('purchase_date', Carbon::now()->year)
            ->sum('total_amount');

        $expenses = Expense::where('cooperation_id', $cooperationId)
            ->whereYear('expense_date', Carbon::now()->year)
            ->sum('amount');

        return $revenue - ($purchases + $expenses);
    }

    public function getLowStockCount(): int
    {
        $cooperationId = $this->getCooperationId();
        if (!$cooperationId) return 0;

        return Product::where('cooperation_id', $cooperationId)
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->count();
    }

    public function getLowStockProducts(): Collection
    {
        $cooperationId = $this->getCooperationId();
        if (!$cooperationId) return collect();

        return Product::where('cooperation_id', $cooperationId)
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->get();
    }

    public function getRecentTransactions(): Collection
    {
        $cooperationId = $this->getCooperationId();
        if (!$cooperationId) return collect();

        $sales = Sale::where('cooperation_id', $cooperationId)
            ->latest('sale_date')
            ->limit(5)
            ->get()
            ->map(fn($s) => [
                'number' => $s->sale_number,
                'date' => Carbon::parse($s->sale_date),
                'amount' => $s->total_amount,
                'type' => 'Penjualan',
            ]);

        $purchases = Purchase::where('cooperation_id', $cooperationId)
            ->latest('purchase_date')
            ->limit(5)
            ->get()
            ->map(fn($p) => [
                'number' => $p->purchase_number,
                'date' => Carbon::parse($p->purchase_date),
                'amount' => $p->total_amount,
                'type' => 'Pembelian',
            ]);

        $savings = SavingsTransaction::where('cooperation_id', $cooperationId)
            ->latest('transaction_date')
            ->limit(5)
            ->get()
            ->map(fn($st) => [
                'number' => $st->transaction_number,
                'date' => Carbon::parse($st->transaction_date),
                'amount' => $st->amount,
                'type' => 'Simpanan',
            ]);

        return $sales->concat($purchases)->concat($savings)
            ->sortByDesc(fn($t) => $t['date']->timestamp)
            ->take(10);
    }
}
