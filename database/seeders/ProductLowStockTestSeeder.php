<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Cooperation;
use Illuminate\Database\Seeder;

class ProductLowStockTestSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Ambil cooperation pertama atau buat jika belum ada
        $cooperation = Cooperation::first();
        if (!$cooperation) {
            $this->command->error('No cooperation found. Please create a cooperation first.');
            return;
        }

        // Ambil kategori pertama atau buat jika belum ada
        $category = ProductCategory::first();
        if (!$category) {
            $category = ProductCategory::create([
                'cooperation_id' => $cooperation->id,
                'name' => 'Test Category',
                'description' => 'Category for testing low stock alerts'
            ]);
        }

        $testProducts = [
            [
                'name' => 'Produk Stok Habis',
                'code' => 'TST001',
                'unit' => 'pcs',
                'purchase_price' => 10000,
                'selling_price' => 15000,
                'min_stock' => 10,
                'current_stock' => 0, // Stok habis
                'description' => 'Produk untuk testing alert stok habis'
            ],
            [
                'name' => 'Produk Stok Rendah 1',
                'code' => 'TST002', 
                'unit' => 'pcs',
                'purchase_price' => 5000,
                'selling_price' => 8000,
                'min_stock' => 20,
                'current_stock' => 5, // Stok rendah
                'description' => 'Produk untuk testing alert stok rendah'
            ],
            [
                'name' => 'Produk Stok Rendah 2',
                'code' => 'TST003',
                'unit' => 'box',
                'purchase_price' => 25000,
                'selling_price' => 35000,
                'min_stock' => 15,
                'current_stock' => 3, // Stok rendah
                'description' => 'Produk untuk testing alert stok rendah'
            ],
            [
                'name' => 'Produk Stok Normal',
                'code' => 'TST004',
                'unit' => 'pcs',
                'purchase_price' => 12000,
                'selling_price' => 18000,
                'min_stock' => 10,
                'current_stock' => 50, // Stok normal
                'description' => 'Produk dengan stok normal'
            ],
        ];

        foreach ($testProducts as $productData) {
            Product::updateOrCreate(
                ['code' => $productData['code']],
                array_merge($productData, [
                    'cooperation_id' => $cooperation->id,
                    'product_category_id' => $category->id,
                    'is_active' => true,
                ])
            );
        }

        $this->command->info('Test products for low stock alerts created successfully!');
        $this->command->info('Products created:');
        $this->command->line('- Produk Stok Habis (0/10)');
        $this->command->line('- Produk Stok Rendah 1 (5/20)');
        $this->command->line('- Produk Stok Rendah 2 (3/15)');
        $this->command->line('- Produk Stok Normal (50/10)');
    }
}
