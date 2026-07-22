<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultCategory = ProductCategory::first() ?? ProductCategory::create([
            'cooperation_id' => 1,
            'name' => 'Umum',
            'code' => 'CAT-GENERAL',
            'description' => 'Kategori umum',
            'is_active' => true,
        ]);

        $products = [
            [
                'name' => 'Beras Medium 5kg',
                'code' => 'PRD-BERAS-5KG',
                'barcode' => '899000000001',
                'description' => 'Beras kualitas medium kemasan 5kg',
                'unit' => 'sak',
                'purchase_price' => 65000,
                'selling_price' => 75000,
                'min_stock' => 5,
                'current_stock' => 20,
                'image_url' => null,
                'is_active' => true,
                'product_category_id' => $defaultCategory->id,
            ],
            [
                'name' => 'Minyak Goreng 1L',
                'code' => 'PRD-MINYAK-1L',
                'barcode' => '899000000002',
                'description' => 'Minyak goreng kemasan 1 liter',
                'unit' => 'botol',
                'purchase_price' => 14000,
                'selling_price' => 17000,
                'min_stock' => 10,
                'current_stock' => 50,
                'image_url' => null,
                'is_active' => true,
                'product_category_id' => $defaultCategory->id,
            ],
            [
                'name' => 'Gula Pasir 1kg',
                'code' => 'PRD-GULA-1KG',
                'barcode' => '899000000003',
                'description' => 'Gula pasir kemasan 1 kg',
                'unit' => 'pak',
                'purchase_price' => 12000,
                'selling_price' => 15000,
                'min_stock' => 10,
                'current_stock' => 40,
                'image_url' => null,
                'is_active' => true,
                'product_category_id' => $defaultCategory->id,
            ],
            [
                'name' => 'Pulpen Biru',
                'code' => 'PRD-PULPEN-BIRU',
                'barcode' => '899000000004',
                'description' => 'Pulpen warna biru',
                'unit' => 'pcs',
                'purchase_price' => 2500,
                'selling_price' => 4000,
                'min_stock' => 50,
                'current_stock' => 200,
                'image_url' => null,
                'is_active' => true,
                'product_category_id' => $defaultCategory->id,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['code' => $product['code']],
                array_merge($product, ['cooperation_id' => 1])
            );
        }
    }
}



