<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarModelOptionalRequest;
use App\Models\CarModel;
use App\Models\Optional;
use Illuminate\Http\Request;

class CarModelOptionalController extends Controller
{
    public function index()
    {
        $associations = CarModel::query()
            ->with('optionals')
            ->get()
            ->flatMap(function (CarModel $carModel) {
                return $carModel->optionals->map(function (Optional $optional) use ($carModel) {
                    return [
                        'car_model_id' => $carModel->id,
                        'car_model_name' => $carModel->name,
                        'optional_id' => $optional->id,
                        'optional_name' => $optional->name,
                    ];
                });
            })
            ->values();

        return response()->json($associations);
    }

    public function show(CarModel $carModel, Optional $optional)
    {
        $exists = $carModel->optionals()->where('optionals.id', $optional->id)->exists();

        if (! $exists) {
            return response()->json([
                'message' => 'Associazione optional-modello non trovata.',
            ], 404);
        }

        return response()->json([
            'car_model_id' => $carModel->id,
            'car_model_name' => $carModel->name,
            'optional_id' => $optional->id,
            'optional_name' => $optional->name,
        ]);
    }

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

    public function update(Request $request, CarModel $carModel, Optional $optional)
    {
        $data = $request->validate([
            'optional_id' => ['required', 'integer', 'exists:optionals,id'],
        ]);

        $exists = $carModel->optionals()->where('optionals.id', $optional->id)->exists();

        if (! $exists) {
            return response()->json([
                'message' => 'Associazione optional-modello non trovata.',
            ], 404);
        }

        $newOptionalId = (int) $data['optional_id'];

        if ($newOptionalId === $optional->id) {
            return response()->json([
                'message' => 'Associazione aggiornata con successo!',
                'data' => [
                    'car_model_id' => $carModel->id,
                    'optional_id' => $optional->id,
                ],
            ], 200);
        }

        $alreadyLinked = $carModel->optionals()->where('optionals.id', $newOptionalId)->exists();

        if ($alreadyLinked) {
            return response()->json([
                'message' => 'Il nuovo optional selezionato è già associato a questo modello.',
            ], 422);
        }

        $carModel->optionals()->detach($optional->id);
        $carModel->optionals()->attach($newOptionalId);

        return response()->json([
            'message' => 'Associazione aggiornata con successo!',
            'data' => [
                'car_model_id' => $carModel->id,
                'optional_id' => $newOptionalId,
            ],
        ], 200);
    }

    public function destroy(CarModel $carModel, Optional $optional)
    {
        $exists = $carModel->optionals()->where('optionals.id', $optional->id)->exists();

        if (! $exists) {
            return response()->json([
                'message' => 'Associazione optional-modello non trovata.',
            ], 404);
        }

        $carModel->optionals()->detach($optional->id);

        return response()->json([
            'message' => 'Associazione optional-modello eliminata con successo!',
        ], 200);
    }
}
