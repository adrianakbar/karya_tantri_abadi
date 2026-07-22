<?php

namespace App\Observers;

use App\Services\AuditTrailService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditTrailObserver
{
    /**
     * Models that should be excluded from audit logging
     */
    protected array $excludedModels = [
        'App\Models\ActivityLog',
        'App\Models\AuthLog',
        'App\Models\DataChangeLog',
        'App\Models\StockMovementLog', // Already has specific logging
    ];

    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        if ($this->shouldLog($model)) {
            AuditTrailService::logDataChange($model, 'create');
            
            // Log specific activity based on model type
            $this->logSpecificActivity($model, 'created');
        }
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        if ($this->shouldLog($model)) {
            AuditTrailService::logDataChange($model, 'update');
            
            // Log specific activity based on model type
            $this->logSpecificActivity($model, 'updated');
        }
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        if ($this->shouldLog($model)) {
            AuditTrailService::logDataChange($model, 'delete');
            
            // Log specific activity based on model type
            $this->logSpecificActivity($model, 'deleted');
        }
    }

    /**
     * Handle the Model "restored" event.
     */
    public function restored(Model $model): void
    {
        if ($this->shouldLog($model)) {
            AuditTrailService::logActivity(
                'restore',
                $this->getModuleName($model),
                "Restored {$this->getModelDisplayName($model)} (ID: {$model->getKey()})"
            );
        }
    }

    /**
     * Handle the Model "force deleted" event.
     */
    public function forceDeleted(Model $model): void
    {
        if ($this->shouldLog($model)) {
            AuditTrailService::logActivity(
                'force_delete',
                $this->getModuleName($model),
                "Permanently deleted {$this->getModelDisplayName($model)} (ID: {$model->getKey()})"
            );
        }
    }

    /**
     * Determine if the model should be logged
     */
    protected function shouldLog(Model $model): bool
    {
        // Don't log if no user is authenticated (e.g., seeders, console commands)
        if (!Auth::check()) {
            return false;
        }

        // Don't log excluded models
        return !in_array(get_class($model), $this->excludedModels);
    }

    /**
     * Log specific activity based on model type
     */
    protected function logSpecificActivity(Model $model, string $action): void
    {
        $modelName = $this->getModelDisplayName($model);
        $module = $this->getModuleName($model);
        $description = ucfirst($action) . " {$modelName}";

        // Add specific details based on model type
        if (method_exists($model, 'name') && $model->name) {
            $description .= ": {$model->name}";
        } elseif (method_exists($model, 'title') && $model->title) {
            $description .= ": {$model->title}";
        } else {
            $description .= " (ID: {$model->getKey()})";
        }

        // Add amount for financial models
        if (method_exists($model, 'amount') && $model->amount) {
            $description .= " - Amount: " . number_format($model->amount, 0, ',', '.');
        }

        AuditTrailService::logActivity($action, $module, $description);
    }

    /**
     * Get module name based on model class
     */
    protected function getModuleName(Model $model): string
    {
        $className = class_basename($model);
        
        $modules = [
            'User' => 'user_management',
            'Product' => 'inventory',
            'Sale' => 'sales',
            'Purchase' => 'purchases',
            'Loan' => 'loans',
            'SavingsTransaction' => 'savings',
            'CashFlow' => 'cash_flow',
            'Expense' => 'expenses',
            'ExpenseCategory' => 'expenses',
            'Cooperation' => 'cooperation',
        ];

        return $modules[$className] ?? strtolower($className);
    }

    /**
     * Get human-readable model display name
     */
    protected function getModelDisplayName(Model $model): string
    {
        $className = class_basename($model);
        
        $displayNames = [
            'User' => 'User',
            'Product' => 'Product',
            'Sale' => 'Sale',
            'Purchase' => 'Purchase',
            'Loan' => 'Loan',
            'SavingsTransaction' => 'Savings Transaction',
            'CashFlow' => 'Cash Flow',
            'Expense' => 'Expense',
            'ExpenseCategory' => 'Expense Category',
            'Cooperation' => 'Cooperation',
        ];

        return $displayNames[$className] ?? $className;
    }
}
