<?php

namespace App\Http\Requests\Developer;

use App\Enums\DocumentTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreKybDocumentRequest extends FormRequest
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
            'documents' => ['required', 'array', 'min:1', 'max:3'],
            'documents.*.document_type' => [
                'required',
                'string',
                'in:kyb_certificate,kyb_id,kyb_address_proof',
            ],
            'documents.*.title' => ['required', 'string', 'max:255'],
            'documents.*.file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // 10MB
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'documents.required' => 'At least one document is required.',
            'documents.*.document_type.in' => 'Invalid document type. Allowed types: kyb_certificate, kyb_id, kyb_address_proof.',
            'documents.*.file.max' => 'Each file must not be larger than 10MB.',
            'documents.*.file.mimes' => 'Each file must be a PDF, JPG, or PNG.',
        ];
    }
}
