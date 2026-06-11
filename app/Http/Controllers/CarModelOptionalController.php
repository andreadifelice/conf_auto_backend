<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarModelOptionalRequest;
use App\Models\CarModel;

class CarModelOptionalController extends Controller
{
    public function store(CarModelOptionalRequest $request)
    {
        $data = $request->validated();

        try {
            $carModel = CarModel::findOrFail($data['car_model_id']);
            $requestedIds = array_values(array_unique(array_map('intval', $data['optional_ids'])));
            $existingIds = $carModel->optionals()
                ->get()
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
            $newIds = array_values(array_diff($requestedIds, $existingIds));
            $skippedIds = array_values(array_intersect($requestedIds, $existingIds));

            if (empty($newIds)) {
                return response()->json([
                    'message' => 'Gli optional selezionati sono già associati a questo modello.',
                ], 422);
            }

            $carModel->optionals()->attach($newIds);

            return response()->json([
                'message' => count($newIds) === 1
                    ? 'Optional associato al modello con successo!'
                    : count($newIds) . ' optional associati al modello con successo!',
                'data' => [
                    'car_model_id' => $carModel->id,
                    'attached_optional_ids' => $newIds,
                    'skipped_optional_ids' => $skippedIds,
                ],
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Errore durante l\'associazione optional-modello.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
