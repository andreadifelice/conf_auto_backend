<?php

namespace Database\Seeders;

use App\Models\CarModel;
use App\Models\Engine;
use App\Models\Optional;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crea i Modelli d'auto
        $citycar = CarModel::create(['name' => 'Citycar Elettrica', 'base_price' => 20000.00]);
        $suv = CarModel::create(['name' => 'SUV Familiare', 'base_price' => 45000.00]);

        // 2. Crea i Motori
        $benzina = Engine::create(['name' => '1.2 Benzina', 'fuel_type' => 'benzina', 'horse_power' => 80, 'additional_price' => 0]);
        $elettrico = Engine::create(['name' => 'Elettrico 204 CV', 'fuel_type' => 'elettrico', 'horse_power' => 204, 'additional_price' => 8000.00]);

        // Associa i motori ai modelli (Tabella pivot car_model_engine)
        $suv->engines()->attach([$benzina->id, $elettrico->id]);
        $citycar->engines()->attach([$elettrico->id]); // La citycar è solo elettrica!

        // 3. Crea gli Optional
        $cerchi19 = Optional::create(['name' => 'Cerchi in lega 19"', 'category' => 'esterni', 'price' => 1200.00]);
        $sediliPelle = Optional::create(['name' => 'Sedili in Pelle', 'category' => 'interni', 'price' => 1500.00]);
        $sediliSportivi = Optional::create(['name' => 'Sedili Sportivi', 'category' => 'interni', 'price' => 800.00]);
        $packTech = Optional::create(['name' => 'Pacchetto Tech Avanzato', 'category' => 'tecnologia', 'price' => 2000.00]);
        $schermoGrande = Optional::create(['name' => 'Display Infotainment 12"', 'category' => 'tecnologia', 'price' => 500.00]);

        // Associa gli optional ai modelli d'auto (Tabella pivot car_model_optional)
        $suv->optionals()->attach([$cerchi19->id, $sediliPelle->id, $sediliSportivi->id, $packTech->id, $schermoGrande->id]);
        $citycar->optionals()->attach([$sediliSportivi->id, $schermoGrande->id]); // La citycar non può avere i cerchi da 19" o la pelle

        // 4. Definisci le regole di COMPATIBILITÀ (Tabella pivot optional_compatibilities)
        
        // REQUISITO: Il Pacchetto Tech richiede obbligatoriamente il Display da 12"
        $packTech->requires()->attach($schermoGrande->id);

        // ESCLUSIONE MUTUA: Se scegli i Sedili in Pelle, escludi i Sedili Sportivi (e viceversa)
        $sediliPelle->excludes()->attach($sediliSportivi->id);
        $sediliSportivi->excludes()->attach($sediliPelle->id);
    }
}
