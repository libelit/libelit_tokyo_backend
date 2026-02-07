<?php

namespace App\Http\Requests\Developer;

use Illuminate\Foundation\Http\FormRequest;

class CompleteMilestoneRequest extends FormRequest
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
            'proofs' => ['required', 'array', 'min:1', 'max:10'],
            'proofs.*.proof_type' => [
                'required',
                'string',
                'in:photo,invoice,inspection_report,bank_statement,other',
            ],
            'proofs.*.title' => ['required', 'string', 'max:255'],
            'proofs.*.description' => ['nullable', 'string', 'max:1000'],
            'proofs.*.file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'proofs.required' => 'At least one proof is required to complete the milestone.',
            'proofs.*.proof_type.in' => 'Invalid proof type. Allowed types: photo, invoice, inspection_report, bank_statement, other.',
            'proofs.*.file.max' => 'Each file must not be larger than 10MB.',
            'proofs.*.file.mimes' => 'Each file must be a PDF, JPG, PNG, DOC, or DOCX file.',
        ];
    }
}
