<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class StoreProductRequest extends CoreRequest
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
                })
            ],
            'price' => 'required|numeric',
            'downloadable_file' => 'required_if:downloadable,true|file',
            'product_code' => 'nullable|string',
            'voltage' => 'nullable|string',
            'capacity' => 'nullable|string',
            'barcode' => 'nullable|string',
            'type' => 'nullable|in:imported,assembled',
            'is_serialized' => 'nullable|boolean',
        ];

        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    public function messages()
    {
        return [
            'downloadable_file.required_if' => __('validation.required', ['attribute' => __('app.downloadableFile')]),
        ];
    }

    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

}
