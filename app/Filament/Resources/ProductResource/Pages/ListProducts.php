<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            // ProductStockOverview::class, // Temporary disable widget
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Data')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTitle(): string
    {
        return 'Daftar Produk';
    }

    protected function getTableDescription(): ?string
    {
        $user = Auth::user();
        if (!$user || !$user->cooperation_id) {
            return null;
        }

        try {
            $totalProducts = Product::where('cooperation_id', $user->cooperation_id)->count();
            $lowStock = Product::where('cooperation_id', $user->cooperation_id)
                ->whereColumn('current_stock', '<', 'min_stock')
                ->where('current_stock', '>', 0)
                ->count();
            $outOfStock = Product::where('cooperation_id', $user->cooperation_id)
                ->where('current_stock', '<=', 0)
                ->count();
            $normalStock = $totalProducts - $lowStock - $outOfStock;

            $description = "📊 Total: {$totalProducts} produk | ";
            $description .= "✅ Normal: {$normalStock} | ";
            
            if ($lowStock > 0) {
                $description .= "⚠️ Stok Rendah: {$lowStock} | ";
            }
            
            if ($outOfStock > 0) {
                $description .= "🚨 Stok Habis: {$outOfStock}";
            } else {
                $description = rtrim($description, ' | ');
            }

            return $description;
        } catch (\Exception $e) {
            return "Error memuat statistik stok";
        }
    }

    public function mount(): void
    {
        parent::mount();
        $this->checkLowStockAlert();
    }

    protected function checkLowStockAlert(): void
    {
        $user = Auth::user();
        if (!$user || !$user->cooperation_id) {
            return;
        }

        try {
            $lowStockProducts = Product::where('cooperation_id', $user->cooperation_id)
                ->whereColumn('current_stock', '<', 'min_stock')
                ->where('current_stock', '>', 0)
                ->count();

            $outOfStockProducts = Product::where('cooperation_id', $user->cooperation_id)
                ->where('current_stock', '<=', 0)
                ->count();

            if ($outOfStockProducts > 0) {
                Notification::make()
                    ->warning()
                    ->title('Peringatan Stok Habis!')
                    ->body("Terdapat {$outOfStockProducts} produk yang stoknya habis. Segera lakukan pengadaan ulang.")
                    ->persistent()
                    ->send();
            }

            if ($lowStockProducts > 0) {
                Notification::make()
                    ->warning()
                    ->title('Peringatan Stok Rendah!')
                    ->body("Terdapat {$lowStockProducts} produk yang stoknya di bawah minimum. Pertimbangkan untuk menambah stok.")
                    ->persistent()
                    ->send();
            }
        } catch (\Exception $e) {
            Log::error('Error checking low stock alert: ' . $e->getMessage());
        }
    }
}
