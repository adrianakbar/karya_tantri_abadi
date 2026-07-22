<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransactionSummary extends Model
{
    // This model is not backed by a physical table, it's a virtual table for the union query
    protected $table = null; 
    protected $primaryKey = 'id'; // Needed for Filament's table
    public $incrementing = false; // Not auto-incrementing as IDs come from original tables
    public $timestamps = false;

    // These attributes are selected in the union query
    protected $fillable = [
        'transaction_date',
        'description',
        'type',
        'amount',
        'category',
        'reference_type',
        'balance_after',
        'id',
        'sort_date' // For sorting
    ];

    /**
     * Scope a query to include transactions for the authenticated user's cooperation.
     * This constructs a UNION ALL query across various transaction tables, returning an Eloquent Builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCooperation(\Illuminate\Database\Eloquent\Builder $query)
    {
        $cooperationId = Auth::user()->cooperation_id;

        // Sales (inflow)
        $salesQuery = Sale::query()
            ->where('cooperation_id', $cooperationId)
            ->where('status', 'completed')
            ->select([
                'sale_date as transaction_date',
                DB::raw("CONCAT('Penjualan Produk - ', sale_number) as description"),
                DB::raw("'inflow' as type"),
                'total_amount as amount',
                DB::raw("'Penjualan Produk' as category"),
                DB::raw("'Sale' as reference_type"),
                'id as transaction_id',
                'sale_date as sort_date'
            ])->addSelect(DB::raw('total_amount as net_amount'));

        // Savings (inflow)
        $savingsQuery = SavingsTransaction::query()
            ->where('cooperation_id', $cooperationId)
            ->where('status', 'completed')
            ->select([
                'transaction_date',
                DB::raw("CONCAT('Tabungan Anggota - ', transaction_number) as description"),
                DB::raw("'inflow' as type"),
                'amount',
                DB::raw("'Tabungan Anggota' as category"),
                DB::raw("'SavingsTransaction' as reference_type"),
                'id as transaction_id',
                'transaction_date as sort_date'
            ])->addSelect(DB::raw('amount as net_amount'));

        // Loan Payments (inflow)
        $loanPaymentsQuery = LoanPayment::query()
            ->whereHas('loan', fn ($q) => $q->where('cooperation_id', $cooperationId))
            ->where('status', 'paid')
            ->select([
                'payment_date as transaction_date',
                DB::raw("CONCAT('Cicilan Pinjaman - ', payment_number) as description"),
                DB::raw("'inflow' as type"),
                'total_amount as amount',
                DB::raw("'Cicilan Pinjaman' as category"),
                DB::raw("'LoanPayment' as reference_type"),
                'id as transaction_id',
                'payment_date as sort_date'
            ])->addSelect(DB::raw('total_amount as net_amount'));

        // Purchases (outflow)
        $purchasesQuery = Purchase::query()
            ->where('cooperation_id', $cooperationId)
            ->select([
                'purchase_date as transaction_date',
                DB::raw("CONCAT('Pembelian/Restok - ', purchase_number) as description"),
                DB::raw("'outflow' as type"),
                'total_amount as amount',
                DB::raw("'Pembelian/Restok' as category"),
                DB::raw("'Purchase' as reference_type"),
                'id as transaction_id',
                'purchase_date as sort_date'
            ])->addSelect(DB::raw('-total_amount as net_amount'));

        // Expenses (outflow) - Gaji Karyawan
        $salariesQuery = Expense::query()
            ->where('expenses.cooperation_id', $cooperationId)
            ->where('status', 'approved')
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->where('expense_categories.name', 'Gaji Karyawan')
            ->select([
                'expenses.expense_date as transaction_date',
                DB::raw("CONCAT(expense_categories.name, ' - ', expenses.notes) as description"),
                DB::raw("'outflow' as type"),
                'expenses.amount',
                'expense_categories.name as category',
                DB::raw("'Expense' as reference_type"),
                'expenses.id as transaction_id',
                'expenses.expense_date as sort_date'
            ])->addSelect(DB::raw('-amount as net_amount'));

        // Expenses (outflow) - Other Expenses
        $otherExpensesQuery = Expense::query()
            ->where('expenses.cooperation_id', $cooperationId)
            ->where('status', 'approved')
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->where('expense_categories.name', '!=', 'Gaji Karyawan')
            ->select([
                'expenses.expense_date as transaction_date',
                DB::raw("CONCAT(expense_categories.name, ' - ', expenses.notes) as description"),
                DB::raw("'outflow' as type"),
                'expenses.amount',
                'expense_categories.name as category',
                DB::raw("'Expense' as reference_type"),
                'expenses.id as transaction_id',
                'expenses.expense_date as sort_date'
            ])->addSelect(DB::raw('-amount as net_amount'));

        // Loan Disbursements (outflow)
        $loanDisbursementsQuery = Loan::query()
            ->where('cooperation_id', $cooperationId)
            ->where('status', 'active') // Assuming active means disbursed
            ->select([
                'disbursement_date as transaction_date',
                DB::raw("CONCAT('Pencairan Pinjaman - ', loan_number) as description"),
                DB::raw("'outflow' as type"),
                'principal_amount as amount',
                DB::raw("'Pencairan Pinjaman' as category"),
                DB::raw("'Loan' as reference_type"),
                'id as transaction_id',
                'disbursement_date as sort_date'
            ])->addSelect(DB::raw('-principal_amount as net_amount'));

        // Combine all queries using unionAll
        $combinedQuery = $salesQuery
            ->unionAll($savingsQuery)
            ->unionAll($loanPaymentsQuery)
            ->unionAll($purchasesQuery)
            ->unionAll($salariesQuery)
            ->unionAll($otherExpensesQuery)
            ->unionAll($loanDisbursementsQuery);
        
        // Wrap the combined query in a subquery to calculate running balance using window function
        $sub = DB::query()->fromSub($combinedQuery, 'transactions_union')
            ->select([
                'transaction_date',
                'description',
                'type',
                'amount',
                'category',
                'reference_type',
                'transaction_id as id', // Alias transaction_id back to id for Filament
                'sort_date',
                DB::raw('SUM(net_amount) OVER (ORDER BY sort_date ASC, transaction_id ASC) as balance_after')
            ]);

        // Final query from the subquery with running balance
        return $query->fromSub($sub, 'combined_transactions');
    }

    /**
     * Override the newEloquentBuilder method to return a custom builder if needed,
     * but for fromSub, the base Builder should be sufficient.
     */
    public function newEloquentBuilder($query)
    {
        return new \Illuminate\Database\Eloquent\Builder($query);
    }
}
