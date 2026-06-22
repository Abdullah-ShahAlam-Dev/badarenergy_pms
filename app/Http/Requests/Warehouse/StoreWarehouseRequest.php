<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'    => 'required|string|max:255',
            'code'    => 'required|string|max:50|unique:warehouses,code',
            'type'    => 'required|in:warehouse,outlet,assembly,transit',
            'address' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required'    => __('validation.required', ['attribute' => __('modules.warehouse.name')]),
            'code.required'    => __('validation.required', ['attribute' => __('modules.warehouse.code')]),
            'code.unique'      => __('validation.unique', ['attribute' => __('modules.warehouse.code')]),
            'type.required'    => __('validation.required', ['attribute' => __('modules.warehouse.type')]),
            'type.in'          => __('validation.in', ['attribute' => __('modules.warehouse.type')]),
        ];
    }
}
