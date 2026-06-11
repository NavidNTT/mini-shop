<?php

namespace Modules\Category\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $this->route('category'),
            'parent_id' => 'nullable|exists:categories,id',
        ];
    }
}
