<?php

namespace Modules\Category\Repositories;

use Modules\Category\Models\Category;

class CategoryRepository
{
    public function all()
    {
        return Category::with('children')->get();
    }

    public function find($id)
    {
        return Category::findOrFail($id);
    }

    public function create(array $data)
    {
        return Category::create($data);
    }

    public function update($id, array $data)
    {
        $category = $this->find($id);
        $category->update($data);
        return $category;
    }

    public function delete($id)
    {
        $category = $this->find($id);
        return $category->delete();
    }
}