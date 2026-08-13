<?php

namespace App\Http\Requests\StockIntake;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockIntakeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return user()->permission('add_inventory') === 'all' || in_array('admin', user_roles());
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'warehouse_id' => 'required|exists:warehouses,id',
            'intake_date' => 'required|date',
            'shipment_id' => 'nullable|string',
            'container_number' => 'nullable|string|max:100',
            'bill_of_lading' => 'nullable|string|max:100',
            'manufacturing_ref' => 'nullable|string|max:100',
            'port_of_origin' => 'nullable|string|max:100',
            'port_of_discharge' => 'nullable|string|max:100',
            'eta' => 'nullable|string',
            'shipment_status' => 'nullable|string',
            'shipment_remarks' => 'nullable|string|max:1000',
            'remarks' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_declared' => 'required|numeric|min:0.01',
            'items.*.quantity_received' => 'required|numeric|min:0',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.batch_id' => 'nullable|exists:product_batches,id',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'items.*.quantity_received.lte' => 'Received quantity cannot exceed declared quantity.',
        ];
    }
}
