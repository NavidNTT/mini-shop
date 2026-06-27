<?php

namespace Modules\Category\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Category\Models\Category;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $category = Category::query()->find($this->route('id'));

        return $category && $this->user()?->can('update', $category);
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,'.$this->route('id'),
            'parent_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
        ];
    }
}
