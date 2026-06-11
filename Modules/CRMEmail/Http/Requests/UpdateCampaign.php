<?php

namespace Modules\CRMEmail\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCampaign extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'template_id'  => ['required', 'integer', 'exists:crm_email_marketing_templates,id'],
            'segment_id'   => ['required', 'integer', 'exists:crm_email_segments,id'],
            'email_body'   => ['nullable', 'string'],
            'scheduled_at' => [
                'nullable',
                'date_format:"' . company()->date_format . '"',
                'after_or_equal:' . now(company()->timezone)->format(company()->date_format)
            ],
        ];
    }

    /**
     * Custom error messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required'        => 'Campaign name is required.',
            'template_id.required' => 'Please select an email template.',
            'template_id.exists'   => 'The selected template does not exist.',
            'segment_id.required'  => 'Please select a recipient segment.',
            'segment_id.exists'    => 'The selected segment does not exist.',
            'scheduled_at.after_or_equal' => 'Scheduled date must be today or in the future.',
        ];
    }
}
