<?php

namespace App\Http\Requests\Developer;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photos' => ['required', 'array', 'min:1', 'max:10'],
            'photos.*.file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'], // 5MB
            'photos.*.title' => ['nullable', 'string', 'max:255'],
            'photos.*.is_featured' => ['nullable'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        // Convert is_featured string values to boolean
        if (isset($validated['photos'])) {
            foreach ($validated['photos'] as $index => $photo) {
                if (isset($photo['is_featured'])) {
                    $validated['photos'][$index]['is_featured'] = filter_var($photo['is_featured'], FILTER_VALIDATE_BOOLEAN);
                } else {
                    $validated['photos'][$index]['is_featured'] = false;
                }
            }
        }

        // Return specific key if requested
        if ($key !== null) {
            return data_get($validated, $key, $default);
        }

        return $validated;
    }

    public function messages(): array
    {
        return [
            'photos.required' => 'At least one photo is required.',
            'photos.max' => 'Maximum 10 photos can be uploaded at once.',
            'photos.*.file.max' => 'Each photo must not be larger than 5MB.',
            'photos.*.file.mimes' => 'Each photo must be a JPG, PNG, or WebP image.',
        ];
    }
}
