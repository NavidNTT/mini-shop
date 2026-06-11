<?php

namespace Modules\Category\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Category\Services\CategoryService;
use Modules\Category\Http\Requests\StoreCategoryRequest;
use Modules\Category\Http\Requests\UpdateCategoryRequest;
use Modules\Category\Transformers\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        $categories = $this->categoryService->getAllCategories();
        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->createCategory($request->validated());
        return response()->json(new CategoryResource($category), 201);
    }

    public function show($id)
    {
        $category = $this->categoryService->getCategoryById($id);
        return new CategoryResource($category);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $category = $this->categoryService->updateCategory($id, $request->all());
        return response()->json(new CategoryResource($category));
    }

    public function destroy($id): JsonResponse
    {
        $this->categoryService->deleteCategory($id);
        return response()->json(['message' => 'Category deleted successfully.'], 204);
    }
}