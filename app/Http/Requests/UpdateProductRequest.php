<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'kasir'], true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $product = $this->route('product');
        $id = is_object($product) ? $product->id : $product;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('products', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:150'], 'wood_type' => ['required', 'string', 'max:100'],
            'size' => ['nullable', 'string', 'max:100'], 'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'], 'thickness' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:30'], 'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'numeric', 'min:0'], 'minimum_stock' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'], 'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
