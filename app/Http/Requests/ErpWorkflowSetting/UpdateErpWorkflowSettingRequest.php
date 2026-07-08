<?php

namespace App\Http\Requests\ErpWorkflowSetting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateErpWorkflowSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return user()->permission('manage_erp_workflow_settings') === 'all' || in_array('admin', user_roles());
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'stock_out_trigger' => 'required|in:invoice_approval,delivery_order_dispatch',
            'serial_prefix' => 'required|string|max:10',
            'serial_digit_length' => 'required|integer|min:3|max:15',
            'serial_include_year' => 'required|boolean',
            'approvals_delivery_order' => 'required|boolean',
            'barcode_type' => 'required|in:code128,qrcode,pdf417',
            'barcode_terms_url' => 'required|url|max:255',
            'shipment_prefix' => 'required|string|max:10',
            'shipment_digit_length' => 'required|integer|min:3|max:15',
            'shipment_include_year' => 'required|boolean',
            'allow_manual_shipment_number' => 'required|boolean',
            'intake_prefix' => 'required|string|max:10',
            'intake_digit_length' => 'required|integer|min:3|max:15',
            'intake_include_year' => 'required|boolean',
            'approvals_stock_intake' => 'required|boolean',
        ];
    }
}
