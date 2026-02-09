<?php

namespace App\Http\Requests\Lender;

use App\Enums\KybStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

class StoreLoanProposalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $lenderProfile = $this->user()->lenderProfile;

        return $lenderProfile && $lenderProfile->kyb_status === KybStatusEnum::APPROVED;
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): JsonResponse
    {
        abort(response()->json([
            'success' => false,
            'message' => 'You must complete KYB verification before submitting loan proposals.',
        ], 403));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'loan_amount_offered' => ['required', 'numeric', 'min:1000'],
            'currency' => ['required', 'string', 'size:3'],
            'interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'loan_maturity_date' => ['required', 'date', 'after:today'],
            'security_packages' => ['required', 'array', 'min:1'],
            'security_packages.*' => ['required', 'string', 'in:mortgage,spv_charge,guarantees'],
            'max_ltv_accepted' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'bid_expiry_date' => ['required', 'date', 'after:today'],
            'additional_conditions' => ['nullable', 'string', 'max:5000'],
            'loan_term_agreement' => ['required', 'file', 'mimes:pdf', 'max:10240'], // 10MB max
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'Project is required.',
            'project_id.exists' => 'Project not found.',
            'loan_amount_offered.required' => 'Loan amount is required.',
            'loan_amount_offered.min' => 'Loan amount must be at least 1000.',
            'interest_rate.required' => 'Interest rate is required.',
            'interest_rate.max' => 'Interest rate cannot exceed 100%.',
            'loan_maturity_date.required' => 'Loan maturity date is required.',
            'loan_maturity_date.after' => 'Loan maturity date must be in the future.',
            'security_packages.required' => 'At least one security package is required.',
            'security_packages.*.in' => 'Invalid security package. Allowed: mortgage, spv_charge, guarantees.',
            'bid_expiry_date.required' => 'Bid expiry date is required.',
            'bid_expiry_date.after' => 'Bid expiry date must be in the future.',
            'loan_term_agreement.required' => 'Loan term agreement document is required.',
            'loan_term_agreement.file' => 'Loan term agreement must be a file.',
            'loan_term_agreement.mimes' => 'Loan term agreement must be a PDF file.',
            'loan_term_agreement.max' => 'Loan term agreement must not exceed 10MB.',
        ];
    }
}
