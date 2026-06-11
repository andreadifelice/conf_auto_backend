<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarColorRequest;
use App\Models\CarModel;

class CarColorController extends Controller
{
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
}
