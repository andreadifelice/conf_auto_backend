<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarModelRequest;
use App\Models\CarModel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CarModelController extends Controller
{
    public function index()
    {
        $models = CarModel::query()
            ->with('images')
            ->where('is_active', true)
            ->get();

        return response()->json($models);
    }

    public function store(CarModelRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $carModel = CarModel::create([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'model' => $data['model'],
                'year' => $data['year'],
                'description' => $data['description'] ?? null,
                'base_price' => $data['base_price'],
                'is_active' => $request->boolean('is_active', true),
            ]);

            $this->storeImages($carModel, $data['images'] ?? []);

            DB::commit();

            $carModel->load('images');

            return response()->json([
                'message' => 'Modello di auto inserito con successo nel catalogo!',
                'data' => $carModel,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Errore durante l\'inserimento del modello di auto.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(CarModelRequest $request, CarModel $carModel)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $carModel->update([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'model' => $data['model'],
                'year' => $data['year'],
                'description' => $data['description'] ?? null,
                'base_price' => $data['base_price'],
                'is_active' => $request->boolean('is_active', $carModel->is_active),
            ]);

            if (! empty($data['images'])) {
                $this->storeImages($carModel, $data['images'], $carModel->images()->count());
            }

            DB::commit();

            $carModel->load('images');

            return response()->json([
                'message' => 'Modello di auto aggiornato con successo!',
                'data' => $carModel,
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Errore durante l\'aggiornamento del modello di auto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(CarModel $carModel)
    {
        try {
            DB::beginTransaction();

            $carModel->engines()->detach();
            $carModel->optionals()->detach();
            $carModel->colors()->detach();
            $carModel->images()->delete();
            CarModel::query()->whereKey($carModel->id)->delete();

            DB::commit();

            return response()->json([
                'message' => 'Modello di auto eliminato con successo!',
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Errore durante l\'eliminazione del modello di auto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /* Mostra tutti i dettagli di una singola auto */
    public function show($id)
    {
        $carmodel = CarModel::with([
            'images',
            'engines',
            'colors',
            'optionals.requires',
            'optionals.excludes',
        ])->where('is_active', true)->findOrFail($id);

        return response()->json($carmodel);
    }

    /**
     * @param  list<UploadedFile>  $images
     */
    private function storeImages(CarModel $carModel, array $images, int $sortOffset = 0): void
    {
        foreach ($images as $index => $image) {
            $path = $image->store('car-models', 'public');

            $carModel->images()->create([
                'path' => $path,
                'sort_order' => $sortOffset + $index,
            ]);
        }
    }
}
