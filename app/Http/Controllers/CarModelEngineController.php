<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarModelEngineRequest;
use App\Models\CarModel;

class CarModelEngineController extends Controller
{
    public function store(CarModelEngineRequest $request)
    {
        $data = $request->validated();

        try {
            $carModel = CarModel::findOrFail($data['car_model_id']);

            if ($carModel->engines()->where('engine_id', $data['engine_id'])->exists()) {
                return response()->json([
                    'message' => 'Questo motore è già associato al modello selezionato.',
                ], 422);
            }

            $carModel->engines()->attach($data['engine_id']);

            return response()->json([
                'message' => 'Motore associato al modello con successo!',
                'data' => [
                    'car_model_id' => $carModel->id,
                    'engine_id' => $data['engine_id'],
                ],
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Errore durante l\'associazione motore-modello.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
