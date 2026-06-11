<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarModelRequest;
use App\Models\CarModel;
use Illuminate\Http\JsonResponse;

class CarModelController extends Controller
{
    public function index()
    {
        $models = CarModel::query()->where('is_active', true)->get();
        return response()->json($models);
    }

    public function store(CarModelRequest $request)
    {
        $data = $request->validated();
        try {
            $data['is_active'] = $request->boolean('is_active', true);

            $carModel = CarModel::create([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'model' => $data['model'],
                'year' => $data['year'],
                'description' => $data['description'] ?? null,
                'image_url' => $data['image_url'] ?? null,
                'base_price' => $data['base_price'],
                'is_active' => $data['is_active'],
            ]);
            return response()->json([
                'message' => 'Modello di auto inserito con successo nel catalogo!',
                'data'    => $carModel
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Errore durante l\'inserimento del modello di auto.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function update(CarModelRequest $request, CarModel $carModel)
    {
        $data = $request->validated();

        try {
            $data['is_active'] = $request->boolean('is_active', $carModel->is_active);

            $carModel->update($data);

            return response()->json([
                'message' => 'Modello di auto aggiornato con successo!',
                'data' => $carModel
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Errore durante l\'aggiornamento del modello di auto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(CarModel $carModel)
    {
        try {
            $carModel->delete();

            return response()->json([
                'message' => 'Modello di auto eliminato con successo!'
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Errore durante l\'eliminazione del modello di auto',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /* Mostra tutti i dettagli di una singola auto */
    public function show($id)
    {
        $carmodel = CarModel::with([
            'engines',
            'colors',
            'optionals.requires',
            'optionals.excludes',
        ])->where('is_active', true)->findOrFail($id);
        return response()->json($carmodel);
    }
}
