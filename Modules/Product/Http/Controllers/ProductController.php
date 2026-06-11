<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Product\Services\ProductService;
use Modules\Product\Http\Requests\StoreProductRequest;
use Modules\Product\Http\Requests\UpdateProductRequest;

class ProductController extends Controller
{

    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(Request $request): JsonResponse
    {

        $perPage = (int)$request->get('per_page',10);

        $products = $this->productService->getProducts($perPage);

        return response()->json($products);

    }

    public function show(int $id): JsonResponse
    {

        $product = $this->productService->getProduct($id);

        return response()->json($product);

    }

    public function store(StoreProductRequest $request): JsonResponse
    {

        $product = $this->productService->createProduct($request->validated());

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product
        ],201);

    }

    public function update(UpdateProductRequest $request,int $id): JsonResponse
    {

        $product = $this->productService->updateProduct($id,$request->validated());

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product
        ]);

    }

    public function destroy(int $id): JsonResponse
    {

        $this->productService->deleteProduct($id);

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);

    }

}
