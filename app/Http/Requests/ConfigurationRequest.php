<?php

namespace App\Http\Requests;

use App\Models\CarModel;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ConfigurationRequest extends FormRequest
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
            'car_model_id' => ['required', 'exists:car_models,id'],
            'engine_id' => ['required', 'exists:engines,id'],
            'color_id' => [
                'required',
                'integer',
                'exists:colors,id',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $isAvailable = CarModel::query()
                        ->whereKey($this->input('car_model_id'))
                        ->whereHas('colors', fn ($query) => $query->where('colors.id', $value))
                        ->exists();

                    if (! $isAvailable) {
                        $fail('Il colore selezionato non è disponibile per questo modello.');
                    }
                },
            ],
            'optional_id' => ['array'],
            'optional_id.*' => ['exists:optionals,id'],
        ];
    }
}
