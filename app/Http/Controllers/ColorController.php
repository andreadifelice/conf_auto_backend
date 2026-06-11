<?php

namespace App\Http\Controllers;

use App\Http\Requests\ColorRequest;
use App\Models\Color;
use Illuminate\Http\JsonResponse;

class ColorController extends Controller
{
    public function index(): JsonResponse
    {
        $colors = Color::query()->orderBy('name')->get();

        return response()->json($colors);
    }

    public function show(Color $color): JsonResponse
    {
        $color->load([
            'carModels' => fn ($query) => $query->where('is_active', true),
        ]);

        return response()->json($color);
    }

    public function store(ColorRequest $request)
    {
        $data = $request->validated();
        try {
            $engine = Color::create([
                'name' => $data['name'],
                'hex_code' => $data['hex_code'],
            ]);

            return response()->json([
                'message' => 'Colore auto creato con successo!',
                'data' => $engine
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Errore durante la creazione del colore dell\'auto',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
