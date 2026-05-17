<?php

namespace App\Http\Requests\Attendance;

use App\Http\Requests\CoreRequest;

class StoreBulkAttendance extends CoreRequest
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
            'year' => 'required_if:mark_attendance_by,month',
            'month' => 'required_if:mark_attendance_by,month',
            'multi_date' => 'required_if:mark_attendance_by,date',
            'user_id.0' => 'required',
        ];

        if ($this->mark_as == 'leave') {
            $rules['leave_type_id'] = 'required';
            $rules['leave_duration'] = 'required';
        } else {
            $rules['clock_in_time'] = 'required';
            $rules['clock_out_time'] = 'required';
            $rules['working_from']  = 'required_if:work_from_type,==,other';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'user_id.0.required' => __('messages.atleastOneValidation')
        ];
    }

}
