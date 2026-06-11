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

    public function getProducts(int $perPage = 10)
    {
        return $this->productRepository->paginate($perPage);
    }

    public function getProduct(int $id): Product
    {
        return $this->productRepository->findById($id);
    }

    public function createProduct(array $data): Product
    {

        $data['slug'] = Str::slug($data['title']);

        return $this->productRepository->create($data);

    }

    public function updateProduct(int $id, array $data): Product
    {

        $product = $this->productRepository->findById($id);

        if(isset($data['title'])){
            $data['slug'] = Str::slug($data['title']);
        }

        return $this->productRepository->update($product,$data);

    }

    public function deleteProduct(int $id): bool
    {

        $product = $this->productRepository->findById($id);

        return $this->productRepository->delete($product);

    }

}
