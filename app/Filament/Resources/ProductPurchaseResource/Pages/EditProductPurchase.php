<?php

namespace App\Filament\Resources\ProductPurchaseResource\Pages;

use App\Filament\Resources\ProductPurchaseResource;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductPurchase extends EditRecord
{
    protected static string $resource = ProductPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
             Actions\DeleteAction::make()
                ->label('Hapus Data')
                ->icon('heroicon-o-trash')
                ->color('danger'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $purchase = $this->record;

        // cari expense yang sudah dibuat sebelumnya
        $expense = Expense::where('receipt_number', $purchase->invoice_number)->first();

        if ($expense) {
            // update expense yang sudah ada
            $expense->update([
                'amount'       => $purchase->grand_total,
                'recipient'    => $purchase->supplier_name ?? 'Unknown',
                'notes'        => "Update otomatis dari pembelian {$purchase->purchase_number}",
            ]);
        } else {
            // kalau belum ada, bikin baru
            $category = ExpenseCategory::getDefaultPurchaseCategory($purchase->cooperation_id ?? 1);

            Expense::create([
                'cooperation_id'      => $purchase->cooperation_id ?? 1,
                'expense_category_id' => $category->id,
                'amount'              => $purchase->grand_total,
                'expense_date'        => now(),
                'receipt_number'      => $purchase->invoice_number,
                'recipient'           => $purchase->supplier_name ?? 'Unknown',
                'processed_by'        => $purchase->processed_by,
                'status'              => 'pending',
                'notes'               => "Otomatis dari pembelian {$purchase->purchase_number}",
            ]);
        }
    }
}
