<?php

namespace App\Http\Requests\Lender;

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
        return [
            'action' => ['required', 'string', 'in:sign'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'action.required' => 'Action is required.',
            'action.in' => 'Invalid action. Allowed: sign.',
        ];
    }
}
