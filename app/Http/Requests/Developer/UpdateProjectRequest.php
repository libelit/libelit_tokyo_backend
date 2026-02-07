<?php

namespace App\Http\Requests\Developer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string', 'max:5000'],
            'project_type' => ['sometimes', 'string', 'in:residential,commercial,mixed_use,industrial,land'],
            'address' => ['sometimes', 'string', 'max:500'],
            'city' => ['sometimes', 'string', 'max:100'],
            'country' => ['sometimes', 'string', 'max:100'],
            'funding_goal' => ['sometimes', 'numeric', 'min:1000'],
            'min_investment' => ['sometimes', 'numeric', 'min:100'],
            'expected_return' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'loan_term_months' => ['sometimes', 'integer', 'min:1', 'max:120'],
            'ltv_ratio' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
