<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProductCategory;
use App\Support\InputLimits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:' . InputLimits::SHORT_TEXT],
            'category' => ['required', Rule::enum(ProductCategory::class)],
            'volume' => ['required', 'string', 'max:' . InputLimits::SHORT_TEXT],
            'price' => ['required', 'integer', 'min:0'],
            'tax_rate' => [
                'required',
                'integer',
                Rule::in(array_keys(config('shop.tax.rates'))),
            ],
            'is_active' => ['required', 'boolean'],
            'is_standard' => ['boolean'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . config('shop.products.image_max_kb')],
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['remove_image'] = ['nullable', 'boolean'];
        }

        return $rules;
    }

    public function productAttributes(): array
    {
        return $this->safe()->except(['image', 'remove_image']) + [
            'is_standard' => false,
            'description' => null,
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => '商品名',
            'tax_rate' => '消費税率',
        ];
    }
}
