<?php

namespace Modules\Product\Services;

use Illuminate\Support\Str;
use Modules\Product\Models\Product;
use Modules\Product\Repositories\ProductRepository;

class ProductService
{
    public function __construct(
        protected ProductRepository $productRepository
    ) {}

    public function getProducts(int $perPage = 10, array $filters = [])
    {
        return $this->productRepository->paginate($perPage, $filters);
    }

    public function getProduct(int $id): Product
    {
        return $this->productRepository->findById($id);
    }

    public function createProduct(array $data): Product
    {
        $data['slug'] = $this->generateUniqueSlug($data['title']);

        return $this->productRepository->create($data);
    }

    public function updateProduct(int $id, array $data): Product
    {
        $product = $this->productRepository->findById($id);

        if (isset($data['title'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title'], $product->id);
        }

        return $this->productRepository->update($product, $data);
    }

    public function deleteProduct(int $id): bool
    {
        $product = $this->productRepository->findById($id);

        return $this->productRepository->delete($product);
    }

    protected function generateUniqueSlug(string $title, ?int $exceptId = null): string
    {
        $baseSlug = Str::slug($title) ?: 'product';
        $slug = $baseSlug;
        $counter = 1;

        while ($this->slugExists($slug, $exceptId)) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, ?int $exceptId = null): bool
    {
        return Product::query()
            ->where('slug', $slug)
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->exists();
    }
}
