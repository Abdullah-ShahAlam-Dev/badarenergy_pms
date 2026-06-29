<?php

namespace App\Http\Requests\Admin\Client;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class StoreClientRequest extends CoreRequest
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
            'name' => 'required',
            'email' => 'nullable|email|required_if:login,enable|email:rfc|unique:users,email,null,id,company_id,' . company()->id,
            'password' => 'nullable|required_if:login,enable|min:8',
            'slack_username' => 'nullable',
            'website' => 'nullable|url',
            'country' => 'required_with:mobile',
            'mobile' => 'nullable|numeric',
            'dealer_code' => 'required|max:20|unique:client_details,dealer_code,null,id,company_id,' . company()->id,
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0',
            'dealer_tier' => 'required|in:Tier A,Tier B,Tier C',
            'dealer_category' => 'required|in:distributor,dealer,end_customer',
            'salesperson_id' => 'required|exists:users,id',
            'city' => 'required|string|max:100',
            'address' => 'required|string|max:500',
            'area' => 'required|string|max:100'
        ];

        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    public function messages()
    {
        return [
            'website.url' => 'The website format is invalid. Add https:// or http to url'
        ];
    }

    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

}
