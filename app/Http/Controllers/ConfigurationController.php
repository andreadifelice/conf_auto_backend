<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfigurationRequest;
use App\Models\CarModel;
use App\Models\Configuration;
use App\Models\Engine;
use App\Models\Optional;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConfigurationController extends Controller
{
    public function index()
    {
        return Auth::user()->configurations()->with(['carModel', 'engine', 'color', 'optionals'])->get();
    }

    public function store(ConfigurationRequest $request)
    {
        $data = $request->validated();

        try {
            $configuration = DB::transaction(function () use ($data) {

                $car = CarModel::findOrFail($data['car_model_id']);
                $engine = Engine::findOrFail($data['engine_id']);
                $color = $car->colors()->where('colors.id', $data['color_id'])->firstOrFail();

                $optionalsPrice = 0;
                if (! empty($data['optional_id'])) {
                    $optionalsPrice = Optional::whereIn('id', $data['optional_id'])->sum('price');
                }

                $colorSurcharge = $color->pivot->price_surcharge;
                $totalPrice = $car->base_price + $engine->additional_price + $colorSurcharge + $optionalsPrice;

                $config = Configuration::create([
                    'user_id' => Auth::id(),
                    'car_model_id' => $car->id,
                    'engine_id' => $engine->id,
                    'color_id' => $color->id,
                    'total_price' => $totalPrice,
                    'status' => 'saved',
                ]);

                if (! empty($data['optional_id'])) {
                    $config->optionals()->attach($data['optional_id']);
                }

                return $config->load('carModel', 'engine', 'color', 'optionals');
            });

            return response()->json([
                'message' => 'Configurazione salvata con successo!',
                'data' => $configuration,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il salvataggio della configurazione.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function downloadReceipt($id)
    {
        $configuration = Configuration::with(['carModel', 'engine', 'color', 'optionals'])->findOrFail($id);

        if ($configuration->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Non sei autorizzato a scaricare questo preventivo.',
            ], 403);
        }

        $pdf = Pdf::loadView('receipts.configuration', [
            'configuration' => $configuration,
        ]);

        return $pdf->download('Preventivo_Auto_'.$configuration->id.'.pdf');
    }
}
