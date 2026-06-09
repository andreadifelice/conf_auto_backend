<?php

namespace App\Http\Controllers;

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
}
