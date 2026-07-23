<?php

namespace App\Http\Requests\Orders;

use App\Http\Requests\CoreRequest;

class UpdateOrder extends CoreRequest
{

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
        $this->has('show_shipping_address') ? $this->request->add(['show_shipping_address' => 'yes']) : $this->request->add(['show_shipping_address' => 'no']);

        $rules = [
            'sub_total' => 'required',
            'total' => 'required',
            'customer_type' => 'required|in:dealer,distributor,end_to_end,care_of',
        ];

        if (request('customer_type') === 'end_to_end') {
            $rules['custom_customer_name'] = 'required|string|max:255';
        } elseif (request('customer_type') === 'care_of') {
            $rules['care_of_id'] = 'required';
        } elseif (request('customer_type') === 'distributor') {
            $rules['distributor_id'] = 'required';
        } else {
            $rules['client_id'] = 'required';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'client_id.required' => __('modules.projects.selectClient'),
            'distributor_id.required' => 'Please select a Distributor.',
            'custom_customer_name.required' => 'Customer name is required.',
            'care_of_id.required' => 'Please select a Care of employee.'
        ];
    }

}
