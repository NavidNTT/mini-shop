<?php

namespace Modules\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Category\Models\Category;
use Modules\Product\Models\Product;

class ProductDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $mobileCategory = Category::where('slug', 'mobile')->first();
        $clothingCategory = Category::where('slug', 'clothing')->first();

        Product::create([
            'title' => 'گوشی نمونه A',
            'slug' => 'sample-phone-a',
            'description' => 'یک گوشی نمونه برای تست API',
            'price' => 15000000,
            'stock' => 25,
            'is_active' => true,
            'category_id' => $mobileCategory?->id,
        ]);

        Product::create([
            'title' => 'گوشی نمونه B',
            'slug' => 'sample-phone-b',
            'description' => 'گوشی دوم برای تست فیلتر و جستجو',
            'price' => 22000000,
            'stock' => 10,
            'is_active' => true,
            'category_id' => $mobileCategory?->id,
        ]);

        Product::create([
            'title' => 'تی‌شرت نمونه',
            'slug' => 'sample-tshirt',
            'description' => 'محصول غیرفعال برای تست سبد خرید',
            'price' => 350000,
            'stock' => 50,
            'is_active' => false,
            'category_id' => $clothingCategory?->id,
        ]);

        Product::create([
            'title' => 'هدفون بی‌سیم',
            'slug' => 'wireless-headphones',
            'description' => 'هدفون با موجودی محدود',
            'price' => 2800000,
            'stock' => 3,
            'is_active' => true,
            'category_id' => Category::where('slug', 'electronics')->value('id'),
        ]);
    }
}
