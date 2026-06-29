<?php

namespace App\Http\Requests\Ledger;

use App\Http\Requests\CoreRequest;

class StoreAdjustmentRequest extends CoreRequest
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
            'dealer_id' => 'required|exists:users,id',
            'type' => 'required|in:opening_balance,adjustment,write_off',
            'amount' => 'required|numeric|min:0.01',
            'entry_type' => 'required|in:debit,credit',
            'date' => 'required|date',
            'remarks' => 'nullable|string',
        ];
    }
}
