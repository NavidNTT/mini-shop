<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'title' => ['sometimes','string','max:255'],

            'description' => ['nullable','string'],

            'price' => ['sometimes','numeric','min:0'],

            'stock' => ['sometimes','integer','min:0'],

            'is_active' => ['nullable','boolean']

        ];
    }

}
