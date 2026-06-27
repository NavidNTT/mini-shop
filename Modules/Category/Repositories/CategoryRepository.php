<?php

namespace Modules\Category\Repositories;

use Modules\Category\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryRepository
{
    private const CACHE_TTL = 3600;

    public function all()
    {
        return Cache::remember('categories:all', self::CACHE_TTL, function () {
            return Category::with('children')->get();
        });
    }

    public function find($id)
    {
        $cacheKey = "category:{$id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            return Category::findOrFail($id);
        });
    }

    public function create(array $data)
    {
        $category = Category::create($data);

        Cache::forget('categories:all');

        return $category;
    }

    public function update($id, array $data)
    {
        $category = $this->find($id);
        $category->update($data);

        Cache::forget('categories:all');
        Cache::forget("category:{$id}");

        return $category;
    }

    public function delete($id)
    {
        $category = $this->find($id);
        $result = $category->delete();

        Cache::forget('categories:all');
        Cache::forget("category:{$id}");

        return $result;
    }
}
