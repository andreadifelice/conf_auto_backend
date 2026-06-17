<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarModelEngineRequest;
use App\Models\CarModel;
use App\Models\Engine;
use Illuminate\Http\Request;

class CarModelEngineController extends Controller
{
    public function index()
    {
        $associations = CarModel::query()
            ->with('engines')
            ->get()
            ->flatMap(function (CarModel $carModel) {
                return $carModel->engines->map(function (Engine $engine) use ($carModel) {
                    return [
                        'id' => $engine->pivot->id,
                        'car_model_id' => $carModel->id,
                        'car_model_name' => $carModel->name,
                        'engine_id' => $engine->id,
                        'engine_name' => $engine->name,
                        'created_at' => $engine->pivot->created_at,
                    ];
                });
            })
            ->sortByDesc('created_at')
            ->values();

        return response()->json($associations);
    }

    public function show(CarModel $carModel, Engine $engine)
    {
        $exists = $carModel->engines()->where('engines.id', $engine->id)->exists();

        if (! $exists) {
            return response()->json([
                'message' => 'Associazione motore-modello non trovata.',
            ], 404);
        }

        return response()->json([
            'car_model_id' => $carModel->id,
            'car_model_name' => $carModel->name,
            'engine_id' => $engine->id,
            'engine_name' => $engine->name,
        ]);
    }

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

    public function update(Request $request, CarModel $carModel, Engine $engine)
    {
        $data = $request->validate([
            'engine_id' => ['required', 'integer', 'exists:engines,id'],
        ]);

        $exists = $carModel->engines()->where('engines.id', $engine->id)->exists();

        if (! $exists) {
            return response()->json([
                'message' => 'Associazione motore-modello non trovata.',
            ], 404);
        }

        $newEngineId = (int) $data['engine_id'];

        if ($newEngineId === $engine->id) {
            return response()->json([
                'message' => 'Associazione aggiornata con successo!',
                'data' => [
                    'car_model_id' => $carModel->id,
                    'engine_id' => $engine->id,
                ],
            ], 200);
        }

        $alreadyLinked = $carModel->engines()->where('engines.id', $newEngineId)->exists();

        if ($alreadyLinked) {
            return response()->json([
                'message' => 'Il nuovo motore selezionato è già associato a questo modello.',
            ], 422);
        }

        $carModel->engines()->detach($engine->id);
        $carModel->engines()->attach($newEngineId);

        return response()->json([
            'message' => 'Associazione aggiornata con successo!',
            'data' => [
                'car_model_id' => $carModel->id,
                'engine_id' => $newEngineId,
            ],
        ], 200);
    }

    public function destroy(CarModel $carModel, Engine $engine)
    {
        $exists = $carModel->engines()->where('engines.id', $engine->id)->exists();

        if (! $exists) {
            return response()->json([
                'message' => 'Associazione motore-modello non trovata.',
            ], 404);
        }

        $carModel->engines()->detach($engine->id);

        return response()->json([
            'message' => 'Associazione motore-modello eliminata con successo!',
        ], 200);
    }
}
