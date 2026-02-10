<?php

namespace App\Http\Requests\Lender;

use Illuminate\Foundation\Http\FormRequest;

class UploadPaymentProofRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'proofs' => ['required', 'array', 'min:1', 'max:5'],
            'proofs.*.title' => ['required', 'string', 'max:255'],
            'proofs.*.file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'proofs.required' => 'At least one payment proof is required.',
            'proofs.max' => 'You can upload a maximum of 5 payment proofs.',
            'proofs.*.title.required' => 'Each payment proof must have a title.',
            'proofs.*.file.required' => 'Each payment proof must have a file.',
            'proofs.*.file.mimes' => 'Payment proof files must be PDF, JPG, JPEG, or PNG.',
            'proofs.*.file.max' => 'Each payment proof file must not exceed 10MB.',
        ];
    }
}
