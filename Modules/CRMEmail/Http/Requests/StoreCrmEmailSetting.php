<?php

namespace Modules\CRMEmail\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCrmEmailSetting extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'from_name'           => 'nullable|string|max:255',
            'from_email'          => 'nullable|email|max:255',
            'throttle_per_minute' => 'required|integer|min:1|max:1000',
            'track_opens'         => 'nullable|in:yes,no',
            'track_clicks'        => 'nullable|in:yes,no',
            'unsubscribe_footer'  => 'nullable|in:yes,no',
            'footer_text'         => 'nullable|string|max:2000',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'throttle_per_minute.required' => 'Throttle (emails per minute) is required.',
            'throttle_per_minute.min'      => 'Throttle must be at least 1 email per minute.',
            'throttle_per_minute.max'      => 'Throttle cannot exceed 1000 emails per minute.',
            'from_email.email'             => 'The default sender email must be a valid email address.',
        ];
    }
}
