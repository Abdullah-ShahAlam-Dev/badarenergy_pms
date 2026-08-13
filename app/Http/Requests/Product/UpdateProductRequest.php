<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class UpdateProductRequest extends CoreRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => [
                'required',
                \Illuminate\Validation\Rule::unique('products')->where(function ($query) {
                    return $query->where('company_id', company()->id);
                })->ignore($this->route('product'))
            ],
            'price' => 'required|numeric',
            'downloadable_file' => 'nullable|file',
            'product_code' => 'nullable|string',
            'voltage' => 'nullable|string',
            'capacity' => 'nullable|string',
            'barcode' => 'nullable|string',
            'type' => 'nullable|string',
            'is_serialized' => 'nullable|boolean',
            'origin_type' => 'nullable|string',
            'product_classification' => 'nullable|string',
            'product_source' => 'nullable|string',
        ];

        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    protected function prepareForValidation()
    {
        if ($this->has('product_source')) {
            $this->merge([
                'is_serialized' => $this->product_source === 'badar_energy' ? 1 : 0,
            ]);
        }
    }

    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

}
