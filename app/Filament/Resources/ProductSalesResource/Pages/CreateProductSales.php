<?php

namespace App\Filament\Resources\ProductSalesResource\Pages;

use App\Filament\Resources\ProductSalesResource;
use App\Models\Sale;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CreateProductSales extends CreateRecord
{
    protected static string $resource = ProductSalesResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->validateStock($data);

        $today = now()->format('Ymd');
        $lastSale = Sale::whereDate('created_at', now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();
        $sequence = 1;
        if ($lastSale && str_contains($lastSale->sale_number, $today)) {
            $lastSequence = (int) substr($lastSale->sale_number, -3);
            $sequence = $lastSequence + 1;
        }
        $data['sale_number'] = 'SL-' . $today . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
        $data['processed_by'] = Auth::id();

        $data['subtotal'] = $data['subtotal'] ?? 0;
        $data['tax_amount'] = $data['tax_amount'] ?? 0;
        $data['discount_amount'] = $data['discount_amount'] ?? 0;
        $data['total_amount'] = $data['total_amount'] ?? 0;

        return $data;
    }

    private function validateStock(array $data): void
    {
        if (!isset($data['details'])) {
            return;
        }

        $insufficientStock = [];
        foreach ($data['details'] as $detail) {
            if (!isset($detail['product_id']) || !isset($detail['quantity'])) {
                continue;
            }

            $product = Product::find($detail['product_id']);
            if ($product && $detail['quantity'] > $product->current_stock) {
                $insufficientStock[] = "{$product->name} (stok: {$product->current_stock}, diminta: {$detail['quantity']})";
            }
        }

        if (!empty($insufficientStock)) {
            Notification::make()
                ->danger()
                ->title('Stok tidak mencukupi!')
                ->body('Produk berikut stoknya tidak mencukupi: ' . implode(', ', $insufficientStock))
                ->persistent()
                ->send();

            $this->halt();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Tambah Penjualan Produk';
    }
}

