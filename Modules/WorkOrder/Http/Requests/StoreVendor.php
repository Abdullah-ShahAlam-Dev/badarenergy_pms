<?php

namespace Modules\WorkOrder\Http\Requests;

use App\Http\Requests\CoreRequest;

class StoreVendor extends CoreRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'vendor_name' => 'required|string|max:255',
            'email'       => 'nullable|email|unique:vendors,email|max:255',
            'mobile'      => 'nullable|string|max:30',
        ];
    }
}
