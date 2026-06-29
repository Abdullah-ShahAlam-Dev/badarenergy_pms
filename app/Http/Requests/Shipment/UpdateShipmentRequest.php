<?php

namespace App\Http\Requests\Shipment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return user()->permission('edit_shipments') === 'all' || in_array('admin', user_roles());
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $companyId = company() ? company()->id : 1;
        $shipmentId = $this->route('shipment');
        $allowManual = \App\Facades\WorkflowConfig::get('inventory', 'allow_manual_shipment_number', false, $companyId);

        $rules = [
            'container_number' => 'nullable|string|max:50',
            'bill_of_lading' => 'nullable|string|max:100',
            'manufacturing_ref' => 'nullable|string|max:100',
            'port_of_origin' => 'required|string|max:100',
            'port_of_discharge' => 'required|string|max:100',
            'eta' => 'required|date',
            'arrival_date' => 'nullable|date|after_or_equal:eta',
            'status' => 'required|in:in_transit,port_customs,warehouse_receiving,completed,cancelled',
            'remarks' => 'nullable|string|max:1000',
        ];

        if ($allowManual) {
            $rules['shipment_number'] = 'required|string|max:50|unique:shipments,shipment_number,' . $shipmentId . ',id,company_id,' . $companyId;
        } else {
            $rules['shipment_number'] = 'nullable|string';
        }

        return $rules;
    }

    /**
     * Validate status transition rules.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $shipmentId = $this->route('shipment');
            if ($shipmentId) {
                $shipment = \App\Models\Shipment::find($shipmentId);
                if ($shipment && $this->has('status')) {
                    $oldStatus = $shipment->status;
                    $newStatus = $this->status;

                    if ($oldStatus !== $newStatus) {
                        $allowed = false;
                        if ($newStatus === 'cancelled') {
                            $allowed = true;
                        } elseif ($oldStatus === 'in_transit' && $newStatus === 'port_customs') {
                            $allowed = true;
                        } elseif ($oldStatus === 'port_customs' && $newStatus === 'warehouse_receiving') {
                            $allowed = true;
                        } elseif ($oldStatus === 'warehouse_receiving' && $newStatus === 'completed') {
                            $allowed = true;
                        }

                        if (!$allowed) {
                            $validator->errors()->add('status', 'Invalid status transition from ' . ucwords(str_replace('_', ' ', $oldStatus)) . ' to ' . ucwords(str_replace('_', ' ', $newStatus)) . '.');
                        }
                    }
                }
            }
        });
    }
}
