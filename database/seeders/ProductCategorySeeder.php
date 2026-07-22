<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Sembako', 'code' => 'CAT-SEMBAKO', 'description' => 'Kebutuhan pokok', 'is_active' => true],
            ['name' => 'Makanan', 'code' => 'CAT-FOOD', 'description' => 'Produk makanan kemasan', 'is_active' => true],
            ['name' => 'Minuman', 'code' => 'CAT-DRINK', 'description' => 'Produk minuman kemasan', 'is_active' => true],
            ['name' => 'ATK', 'code' => 'CAT-ATK', 'description' => 'Alat tulis kantor', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            ProductCategory::updateOrCreate(
                ['code' => $category['code']],
                array_merge($category, ['cooperation_id' => 1])
            );
        }
    }
}



