<?php

namespace App\Http\Requests\Developer;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectDocumentRequest extends FormRequest
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
            'documents' => ['required', 'array', 'min:1', 'max:10'],
            'documents.*.document_type' => [
                'required',
                'string',
                'in:loan_drawings,loan_cost_calculation,loan_photos,loan_land_title,loan_bank_statement,loan_revenue_evidence',
            ],
            'documents.*.title' => ['required', 'string', 'max:255'],
            'documents.*.file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,xlsx,xls', 'max:10240'], // 10MB
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'documents.required' => 'At least one document is required.',
            'documents.*.document_type.in' => 'Invalid document type. Allowed types: loan_drawings, loan_cost_calculation, loan_photos, loan_land_title, loan_bank_statement, loan_revenue_evidence.',
            'documents.*.file.max' => 'Each file must not be larger than 10MB.',
            'documents.*.file.mimes' => 'Each file must be a PDF, JPG, PNG, or Excel file.',
        ];
    }
}
