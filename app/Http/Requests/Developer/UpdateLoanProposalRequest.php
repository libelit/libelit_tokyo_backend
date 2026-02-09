<?php

namespace App\Http\Requests\Developer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLoanProposalRequest extends FormRequest
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
        $rules = [
            'action' => ['required', 'string', 'in:start_review,accept,reject,sign'],
        ];

        // rejection_reason is required only for reject action
        if ($this->input('action') === 'reject') {
            $rules['rejection_reason'] = ['required', 'string', 'max:2000'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'action.required' => 'Action is required.',
            'action.in' => 'Invalid action. Allowed: start_review, accept, reject, sign.',
            'rejection_reason.required' => 'Please provide a reason for rejecting this proposal.',
            'rejection_reason.max' => 'Rejection reason cannot exceed 2000 characters.',
        ];
    }
}
