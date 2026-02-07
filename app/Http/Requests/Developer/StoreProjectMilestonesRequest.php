<?php

namespace App\Http\Requests\Developer;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectMilestonesRequest extends FormRequest
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
            'milestones' => ['required', 'array', 'min:1'],
            'milestones.*.title' => ['required', 'string', 'max:255'],
            'milestones.*.description' => ['nullable', 'string', 'max:1000'],
            'milestones.*.amount' => ['required', 'numeric', 'min:0.01'],
            'milestones.*.due_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'milestones.required' => 'At least one milestone is required.',
            'milestones.min' => 'At least one milestone is required.',
            'milestones.*.title.required' => 'Each milestone must have a title.',
            'milestones.*.title.max' => 'Milestone title must not exceed 255 characters.',
            'milestones.*.description.max' => 'Milestone description must not exceed 1000 characters.',
            'milestones.*.amount.required' => 'Each milestone must have an amount.',
            'milestones.*.amount.numeric' => 'Milestone amount must be a number.',
            'milestones.*.amount.min' => 'Milestone amount must be at least 0.01.',
            'milestones.*.due_date.date' => 'Invalid due date format.',
            'milestones.*.due_date.after_or_equal' => 'Due date must be today or in the future.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'milestones.*.title' => 'milestone title',
            'milestones.*.description' => 'milestone description',
            'milestones.*.amount' => 'milestone amount',
            'milestones.*.due_date' => 'milestone due date',
        ];
    }
}
