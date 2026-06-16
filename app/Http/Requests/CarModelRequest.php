<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CarModelRequest extends FormRequest
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
        $carModel = $this->route('carModel');

        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:200'],
            'model' => ['required', 'string', 'max:200'],
            'year' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 2)],
            'description' => ['nullable', 'string', 'max:500'],
            'images' => [
                'nullable',
                'array',
                function (string $attribute, mixed $value, \Closure $fail) use ($carModel): void {
                    $newImagesCount = is_array($value) ? count($value) : 0;

                    $existingImagesCount = $carModel ? $carModel->images()->count() : 0;

                    $totalImages = $existingImagesCount + $newImagesCount;

                    if ($totalImages > 12) {
                        $fail("Il modello non può avere più di 12 immagini in totale. Attualmente ne ha {$existingImagesCount} e stai provando ad aggiungerne altre {$newImagesCount}.");
                    }
                }
            ],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'images.*.max' => 'Ogni immagine non può superare i 5 MB.',
            'images.*.image' => 'Ogni file caricato deve essere un\'immagine valida.',
            'images.*.mimes' => 'Le immagini devono essere in formato JPEG, PNG, JPG o WEBP.',
        ];
    }
}
