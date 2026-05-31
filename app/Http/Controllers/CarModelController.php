<?php

namespace App\Http\Controllers;

use App\Models\CarModel;
use Illuminate\Http\JsonResponse;

class CarModelController extends Controller
{
    /* mostra tutte le auto disponibili */
    public function index()
    {
        $models = CarModel::query()->where('is_active', true)->get();
        return response()->json($models);
    }


    /* Mostra tutti i dettagli di una singola auto */
    public function show($id)
    {
        $carmodel = CarModel::with([
            'engines',
            'optionals.requires',
            'optionals.excludes'
        ])->where('is_active', true)->findOrFail($id);
        return response()->json($carmodel);
    }
}
