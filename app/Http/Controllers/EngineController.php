<?php

namespace App\Http\Controllers;

use App\Http\Requests\EngineRequest;
use App\Models\Engine;
use Illuminate\Support\Facades\DB;

class EngineController extends Controller
{
    public function index()
    {
        $engines = Engine::query()->orderBy('name', 'asc')->get();

        return response()->json($engines);
    }

    public function store(EngineRequest $request)
    {
        $data = $request->validated();
        try {
            $engine = Engine::create([
                'name' => $data['name'],
                'fuel_type' => $data['fuel_type'],
                'horse_power' => $data['horse_power'],
                'additional_price' => $data['additional_price'],
            ]);

            return response()->json([
                'message' => 'Motore auto creato con successo!',
                'data' => $engine
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Errore durante la creazione del motore dell\'auto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(EngineRequest $request, Engine $engine)
    {
        $data = $request->validated();

        try {
            $engine->update($data);

            return response()->json([
                'message' => 'Motore di auto aggiornato con successo!',
                'data' => $engine
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Errore durante l\'aggiornamento del motore di auto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Engine $engine)
    {
        try {
            DB::beginTransaction();

            $engine->carModels()->detach();
            Engine::query()->whereKey($engine->id)->delete();

            DB::commit();

            return response()->json([
                'message' => 'Motore auto eliminato con successo!',
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Errore durante l\'eliminazione del motore dell\'auto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
