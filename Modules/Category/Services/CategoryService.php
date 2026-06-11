<?php

namespace Modules\Category\Services;

use Modules\Category\Repositories\CategoryRepository;
use Illuminate\Support\Str;

class CategoryService
{
    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

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
        return $this->categoryRepository->create($data);
    }

    public function updateCategory($id, array $data)
    {
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        return $this->categoryRepository->update($id, $data);
    }

    public function deleteCategory($id)
    {
        return $this->categoryRepository->delete($id);
    }
}