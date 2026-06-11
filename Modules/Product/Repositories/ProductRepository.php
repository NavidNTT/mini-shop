<?php

namespace Modules\Product\Repositories;

use Modules\Product\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository
{

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Product::query()
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): Product
    {
        return Product::query()->findOrFail($id);
    }

    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->refresh();
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }

}
