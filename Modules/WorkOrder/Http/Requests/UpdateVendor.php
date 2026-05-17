<?php

namespace Modules\WorkOrder\Http\Requests;

use App\Http\Requests\CoreRequest;

class UpdateVendor extends CoreRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('vendor'); // Assuming {vendor} is the parameter name
        return [
            'vendor_name' => 'required|string|max:255',
            'email'       => 'nullable|email|unique:vendors,email,' . $id . '|max:255',
            'mobile'      => 'nullable|string|max:30',
        ];
    }
}
