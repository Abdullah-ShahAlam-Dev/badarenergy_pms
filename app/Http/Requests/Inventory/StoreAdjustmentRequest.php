<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdjustmentRequest extends FormRequest
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
            'product_id'   => 'required|integer|exists:products,id',
            'warehouse_id' => 'required|integer|exists:warehouses,id',
            'quantity'     => 'required|numeric|min:0.01',
            'type'         => 'required|in:in,out',
            'category'     => 'required|in:available,faulty,in_transit',
            'remarks'      => 'nullable|string|max:1000',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'product_id.required'   => __('validation.required', ['attribute' => __('modules.inventory.product')]),
            'product_id.exists'     => __('validation.exists', ['attribute' => __('modules.inventory.product')]),
            'warehouse_id.required' => __('validation.required', ['attribute' => __('modules.inventory.warehouse')]),
            'warehouse_id.exists'   => __('validation.exists', ['attribute' => __('modules.inventory.warehouse')]),
            'quantity.required'     => __('validation.required', ['attribute' => __('modules.inventory.quantity')]),
            'quantity.numeric'      => __('validation.numeric', ['attribute' => __('modules.inventory.quantity')]),
            'quantity.min'          => __('validation.min.numeric', ['attribute' => __('modules.inventory.quantity'), 'min' => 0.01]),
            'type.required'         => __('validation.required', ['attribute' => __('modules.inventory.type')]),
            'type.in'               => __('validation.in', ['attribute' => __('modules.inventory.type')]),
        ];
    }
}
