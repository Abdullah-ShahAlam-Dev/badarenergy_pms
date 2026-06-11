<?php

namespace Modules\CRMEmail\Http\Requests;

use App\Http\Requests\CoreRequest;

class UpdateEmailSegment extends CoreRequest
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
        return [
            'name' => 'required|string|max:255',
            'sources' => 'required|array|min:1',
            'sources.*' => 'required|in:clients,leads,contacts',
            'criteria' => 'required|array',
        ];
    }
}
