<?php

namespace App\Http\Controllers;

use App\Http\Requests\OptionalRequest;
use App\Models\Optional;
use Illuminate\Support\Facades\DB;

class OptionalController extends Controller
{
    public function index()
    {
        $optionals = Optional::query()->orderBy('name')->get();

        return response()->json($optionals);
    }

    public function show(Optional $optional)
    {
        $optional->load(['requires', 'excludes']);

        return response()->json($optional);
    }

    public function store(OptionalRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();
            $optional = Optional::create([
                'name' => $data['name'],
                'category' => $data['category'],
                'price' => $data['price']
            ]);

            if (!empty($data['car_models'])) {
                $optional->carModels()->sync($data['car_models']);
            }

            if (!empty($data['requires'])) {
                $optional->requires()->sync($data['requires']);
            }

            if (!empty($data['excludes'])) {
                $optional->excludes()->sync($data['excludes']);
            }
            DB::commit();

            $optional->load(['carModels', 'requires', 'excludes']);

            return response()->json([
                'message' => 'Optional creato e configurato con successo!',
                'data'    => $optional
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Errore durante la creazione dell\'optional',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(OptionalRequest $request, Optional $optional)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $optional->update($data);

            $carModels = $data['car_models'] ?? [];
            $optional->carModels()->sync($carModels);

            $requires = $data['requires'] ?? [];
            $optional->requires()->sync($requires);

            $excludes = $data['excludes'] ?? [];
            $optional->excludes()->sync($excludes);

            DB::commit();

            $optional->load(['carModels', 'requires', 'excludes']);

            return response()->json([
                'message' => 'Optional aggiornato con successo!',
                'data' => $optional
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Errore durante l\'aggiornamento dell\'optional',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Optional $optional)
    {
        try {
            $optional->carModels()->detach();
            $optional->requires()->detach();
            $optional->excludes()->detach();

            $optional->delete();

            return response()->json([
                'message' => 'Optional eliminato con successo!'
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Errore durante l\'eliminazione dell\'optional',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
