<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarColorRequest;
use App\Models\CarModel;
use App\Models\Color;
use Illuminate\Http\Request;

class CarColorController extends Controller
{
    public function index()
    {
        $associations = CarModel::query()
            ->with('colors')
            ->get()
            ->flatMap(function (CarModel $carModel) {
                return $carModel->colors->map(function (Color $color) use ($carModel) {
                    return [
                        'id' => $color->pivot->id,
                        'car_model_id' => $carModel->id,
                        'car_model_name' => $carModel->name,
                        'color_id' => $color->id,
                        'color_name' => $color->name,
                        'price_surcharge' => (int) $color->pivot->price_surcharge,
                        'created_at' => $color->pivot->created_at,
                    ];
                });
            })
            ->sortByDesc('created_at')
            ->values();

        return response()->json($associations);
    }

    public function show(CarModel $carModel, Color $color)
    {
        $pivot = $carModel->colors()->where('colors.id', $color->id)->first();

        if (! $pivot) {
            return response()->json([
                'message' => 'Associazione colore-modello non trovata.',
            ], 404);
        }

        return response()->json([
            'car_model_id' => $carModel->id,
            'car_model_name' => $carModel->name,
            'color_id' => $color->id,
            'color_name' => $color->name,
            'price_surcharge' => (int) $pivot->pivot->price_surcharge,
        ]);
    }

    public function store(CarColorRequest $request)
    {
        $data = $request->validated();

        try {
            $carModel = CarModel::findOrFail($data['car_model_id']);
            $colorId = (int) $data['color_id'];
            $priceSurcharge = (int) round($data['price_surcharge']);

            $alreadyLinked = $carModel->colors()
                ->where('color_id', $colorId)
                ->exists();

            if ($alreadyLinked) {
                $carModel->colors()->updateExistingPivot($colorId, [
                    'price_surcharge' => $priceSurcharge,
                ]);

                return response()->json([
                    'message' => 'Supplemento del colore aggiornato con successo!',
                    'data' => [
                        'car_model_id' => $carModel->id,
                        'color_id' => $colorId,
                        'price_surcharge' => $priceSurcharge,
                    ],
                ], 200);
            }

            $carModel->colors()->attach($colorId, [
                'price_surcharge' => $priceSurcharge,
            ]);

            return response()->json([
                'message' => 'Colore associato al modello con successo!',
                'data' => [
                    'car_model_id' => $carModel->id,
                    'color_id' => $colorId,
                    'price_surcharge' => $priceSurcharge,
                ],
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Errore durante l\'associazione colore-modello.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, CarModel $carModel, Color $color)
    {
        $data = $request->validate([
            'color_id' => ['nullable', 'integer', 'exists:colors,id'],
            'price_surcharge' => ['nullable', 'numeric', 'min:0'],
        ]);

        $existing = $carModel->colors()->where('colors.id', $color->id)->first();

        if (! $existing) {
            return response()->json([
                'message' => 'Associazione colore-modello non trovata.',
            ], 404);
        }

        $targetColorId = isset($data['color_id']) ? (int) $data['color_id'] : $color->id;
        $targetPrice = isset($data['price_surcharge'])
            ? (int) round($data['price_surcharge'])
            : (int) $existing->pivot->price_surcharge;

        if ($targetColorId !== $color->id) {
            $alreadyLinked = $carModel->colors()->where('colors.id', $targetColorId)->exists();

            if ($alreadyLinked) {
                return response()->json([
                    'message' => 'Il colore selezionato è già associato a questo modello.',
                ], 422);
            }

            $carModel->colors()->detach($color->id);
            $carModel->colors()->attach($targetColorId, [
                'price_surcharge' => $targetPrice,
            ]);
        } else {
            $carModel->colors()->updateExistingPivot($targetColorId, [
                'price_surcharge' => $targetPrice,
            ]);
        }

        return response()->json([
            'message' => 'Associazione colore-modello aggiornata con successo!',
            'data' => [
                'car_model_id' => $carModel->id,
                'color_id' => $targetColorId,
                'price_surcharge' => $targetPrice,
            ],
        ], 200);
    }

    public function destroy(CarModel $carModel, Color $color)
    {
        $exists = $carModel->colors()->where('colors.id', $color->id)->exists();

        if (! $exists) {
            return response()->json([
                'message' => 'Associazione colore-modello non trovata.',
            ], 404);
        }

        $carModel->colors()->detach($color->id);

        return response()->json([
            'message' => 'Associazione colore-modello eliminata con successo!',
        ], 200);
    }
}
