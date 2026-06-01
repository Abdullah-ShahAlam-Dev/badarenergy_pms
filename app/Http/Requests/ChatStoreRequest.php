<?php

namespace App\Http\Requests;

use App\Http\Requests\CoreRequest;

class ChatStoreRequest extends CoreRequest
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
        $rules = [
            'message' => 'required',
        ];

        if (!$this->has('message_group_id') || $this->message_group_id == '') {
            $rules['user_id'] = 'required_if:user_type,employee';
            $rules['client_id'] = 'required_if:user_type,client';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'user_id.required_if' => 'Select a user to send the message',
            'client_id.required_if' => 'Select a client to send the message',
        ];
    }

}
