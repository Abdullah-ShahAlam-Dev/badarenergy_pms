<?php

namespace App\Http\Requests\Distributor;

use App\Http\Requests\CoreRequest;

class StoreDistributorRequest extends CoreRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,deactive',
        ];
    }
}
