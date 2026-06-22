<?php

namespace App\Http\Requests\Admin\Client;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class UpdateClientRequest extends CoreRequest
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
            'email' => 'nullable|email|required_if:login,enable|unique:users,email,'.$this->route('client').',id,company_id,' . company()->id,
            'slack_username' => 'nullable|unique',
            'name'  => 'required',
            'website' => 'nullable|url',
            'country' => 'required_with:mobile',
            'password' => 'nullable|min:8',
            'mobile' => 'nullable|numeric',
            'dealer_code' => 'required|max:20|unique:client_details,dealer_code,' . $this->route('client') . ',user_id,company_id,' . company()->id,
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_days' => 'nullable|integer|min:0',
            'dealer_tier' => 'required|in:Tier A,Tier B,Tier C'
        ];

        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

}
