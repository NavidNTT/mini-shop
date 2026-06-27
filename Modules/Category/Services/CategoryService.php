<?php

namespace Modules\Category\Services;

use Modules\Category\Exceptions\CategoryDeleteException;
use Modules\Category\Exceptions\InvalidCategoryHierarchyException;
use Modules\Category\Models\Category;
use Modules\Category\Repositories\CategoryRepository;
use Illuminate\Support\Str;

class CategoryService
{
    public function __construct(
        protected CategoryRepository $categoryRepository
    ) {}

    public function getAllCategories()
    {
        return $this->categoryRepository->all();
    }

    public function getCategoryById($id)
    {
        return $this->categoryRepository->find($id);
    }

    public function createCategory(array $data)
    {
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $this->validateParentId($data['parent_id'] ?? null);

        return $this->categoryRepository->create($data);
    }

    public function updateCategory($id, array $data)
    {
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if (array_key_exists('parent_id', $data)) {
            $this->validateParentId($data['parent_id'], (int) $id);
        }

        return $this->categoryRepository->update($id, $data);
    }

    public function deleteCategory($id)
    {
        $category = $this->categoryRepository->find($id);

        if ($category->products()->exists()) {
            throw CategoryDeleteException::hasProducts();
        }

        if ($category->children()->exists()) {
            throw CategoryDeleteException::hasChildren();
        }

        return $this->categoryRepository->delete($id);
    }

    protected function validateParentId(?int $parentId, ?int $categoryId = null): void
    {
        if ($parentId === null) {
            return;
        }

        if ($categoryId !== null && $parentId === $categoryId) {
            throw new InvalidCategoryHierarchyException('دسته‌بندی نمی‌تواند والد خودش باشد.');
        }

        $parent = Category::query()->find($parentId);

        if (!$parent) {
            throw new InvalidCategoryHierarchyException('دسته‌بندی والد یافت نشد.');
        }

        if ($categoryId === null) {
            return;
        }

        $current = $parent;

        while ($current) {
            if ($current->id === $categoryId) {
                throw new InvalidCategoryHierarchyException('انتخاب این والد باعث ایجاد حلقه در سلسله‌مراتب می‌شود.');
            }

            $current = $current->parent;
        }
    }
}
