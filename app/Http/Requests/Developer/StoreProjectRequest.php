<?php

namespace App\Http\Requests\Developer;

use App\Enums\KybStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if developer's KYB is approved
        $developerProfile = $this->user()->developerProfile;

        return $developerProfile && $developerProfile->kyb_status === KybStatusEnum::APPROVED;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'project_type' => ['required', 'string', 'in:residential,commercial,mixed_use,industrial,land'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'loan_amount' => ['required', 'numeric', 'min:1000'],
            'min_investment' => ['required', 'numeric', 'min:100'],
            'currency' => ['required', 'string', 'max:100'],
            'construction_start_date' => ['nullable', 'date'],
            'construction_end_date' => ['nullable', 'date', 'after_or_equal:construction_start_date'],
        ];
    }

    /**
     * Get the error messages for authorization failure.
     */
    protected function failedAuthorization(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'You must complete KYB verification before creating projects.',
        ], 403);
    }
}
