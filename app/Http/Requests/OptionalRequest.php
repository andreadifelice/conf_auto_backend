<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class OptionalRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'car_models' => ['nullable', 'array'],
            'car_models.*' => ['integer', 'exists:car_models,id'],
            'requires' => ['nullable', 'array'],
            'requires.*' => ['integer', 'exists:optionals,id'],
            'excludes'   => ['nullable', 'array'],
            'excludes.*' => ['integer', 'exists:optionals,id'],
        ];
    }
}
