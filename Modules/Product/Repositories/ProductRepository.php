<?php

namespace Modules\Product\Repositories;

use Modules\Product\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class ProductRepository
{
    private const CACHE_TTL = 600;

    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        return $this->applyFilters($filters)
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): Product
    {
        $cacheKey = "product:{$id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            return Product::query()->with('category')->findOrFail($id);
        });
    }

    public function create(array $data): Product
    {
        $product = Product::query()->create($data);

        $this->forgetProductCache($product->id);

        return $product;
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        $this->forgetProductCache($product->id);

        return $product->refresh();
    }

    public function delete(Product $product): bool
    {
        $id = $product->id;
        $result = $product->delete();

        $this->forgetProductCache($id);

        return $result;
    }

    public function applyFilters(array $filters): Builder
    {
        $query = Product::query()->with('category');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }

    protected function forgetProductCache(int $productId): void
    {
        Cache::forget("product:{$productId}");
    }
}
