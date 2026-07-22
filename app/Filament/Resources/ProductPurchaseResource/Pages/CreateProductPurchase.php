<?php

namespace App\Filament\Resources\ProductPurchaseResource\Pages;

use App\Filament\Resources\ProductPurchaseResource;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProductPurchase extends CreateRecord
{
    protected static string $resource = ProductPurchaseResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $today = now()->format('Ymd');
        $lastPurchase = Purchase::whereDate('created_at', today())->count();
        $sequence = str_pad($lastPurchase + 1, 4, '0', STR_PAD_LEFT);
        $data['purchase_number'] = "PO/{$today}/{$sequence}";

        $data['invoice_number'] = $data['invoice_number'] ?? "unknown";

        $data['processed_by'] = Auth::id() ?? 1;

        $data['total_amount'] = $data['total_amount'] ?? 0;
        $data['tax_amount'] = $data['tax_amount'] ?? 0;
        $data['discount_amount'] = $data['discount_amount'] ?? 0;
        $data['grand_total'] = $data['grand_total'] ?? 0;

        return $data;
    }

     protected function afterCreate(): void
    {
        $purchase = $this->record;

        $category = \App\Models\ExpenseCategory::getDefaultPurchaseCategory($purchase->cooperation_id ?? 1);

        Expense::create([
            'cooperation_id'      => $purchase->cooperation_id ?? 1,
            'expense_category_id' => $category->id,
            'amount'              => $purchase->grand_total,
            'expense_date'        => now(),
            'receipt_number'      => $purchase->invoice_number,
            'recipient'           => $purchase->supplier_name ?? 'Unknown', // kalau ada field supplier
            'processed_by'        => $purchase->processed_by,
            'status'              => 'pending',
            'notes'               => "Otomatis dari pembelian {$purchase->purchase_number}",
        ]);
    }

    public function getTitle(): string
    {
        return 'Tambah Pembelian Produk';
    }
}
