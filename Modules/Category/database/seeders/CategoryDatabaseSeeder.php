<?php

namespace Modules\Category\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Category\Models\Category;

class CategoryDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $electronics = Category::create([
            'name' => 'الکترونیک',
            'slug' => 'electronics',
        ]);

        $phones = Category::create([
            'name' => 'موبایل',
            'slug' => 'mobile',
            'parent_id' => $electronics->id,
        ]);

        Category::create([
            'name' => 'لباس',
            'slug' => 'clothing',
        ]);

        Category::create([
            'name' => 'کتاب',
            'slug' => 'books',
        ]);

        // Keep reference for product seeder via slug lookup
        unset($phones);
    }
}
