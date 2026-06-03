<?php

namespace App\Http\Controllers;

use App\Http\Requests\EngineRequest;
use App\Models\Engine;

class EngineController extends Controller
{
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
            $engine->delete();
            return response()->json([
                'message' => 'Modello di auto eliminato con successo!'
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Modello di auto eliminato con successo!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
