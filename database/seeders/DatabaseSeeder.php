<?php

namespace Database\Seeders;

use App\Models\CarModel;
use App\Models\Category;
use App\Models\Color;
use App\Models\Engine;
use App\Models\Optional;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $citycarCategory = Category::create(['name' => 'Citycar', 'slug' => 'citycar']);
        $suvCategory = Category::create(['name' => 'SUV', 'slug' => 'suv']);

        $citycar = CarModel::create([
            'category_id' => $citycarCategory->id,
            'name' => 'Citycar Elettrica',
            'model' => 'Urban E',
            'year' => 2026,
            'description' => 'Compatta elettrica ideale per la città, agile e silenziosa.',
            'image_url' => null,
            'base_price' => 20000.00,
            'is_active' => true,
        ]);

        $suv = CarModel::create([
            'category_id' => $suvCategory->id,
            'name' => 'SUV Familiare',
            'model' => 'Family X',
            'year' => 2026,
            'description' => 'SUV spazioso con motorizzazioni benzina ed elettrica per tutta la famiglia.',
            'image_url' => null,
            'base_price' => 45000.00,
            'is_active' => true,
        ]);

        $benzina = Engine::create([
            'name' => '1.2 Benzina',
            'fuel_type' => 'benzina',
            'horse_power' => 80,
            'additional_price' => 0,
        ]);

        $elettrico = Engine::create([
            'name' => 'Elettrico 204 CV',
            'fuel_type' => 'elettrico',
            'horse_power' => 204,
            'additional_price' => 8000.00,
        ]);

        $suv->engines()->attach([$benzina->id, $elettrico->id]);
        $citycar->engines()->attach([$elettrico->id]);

        $cerchi19 = Optional::create([
            'name' => 'Cerchi in lega 19"',
            'category' => 'esterni',
            'price' => 1200.00,
        ]);

        $sediliPelle = Optional::create([
            'name' => 'Sedili in Pelle',
            'category' => 'interni',
            'price' => 1500.00,
        ]);

        $sediliSportivi = Optional::create([
            'name' => 'Sedili Sportivi',
            'category' => 'interni',
            'price' => 800.00,
        ]);

        $packTech = Optional::create([
            'name' => 'Pacchetto Tech Avanzato',
            'category' => 'tecnologia',
            'price' => 2000.00,
        ]);

        $schermoGrande = Optional::create([
            'name' => 'Display Infotainment 12"',
            'category' => 'tecnologia',
            'price' => 500.00,
        ]);

        $suv->optionals()->attach([
            $cerchi19->id,
            $sediliPelle->id,
            $sediliSportivi->id,
            $packTech->id,
            $schermoGrande->id,
        ]);

        $citycar->optionals()->attach([
            $sediliSportivi->id,
            $schermoGrande->id,
        ]);

        $packTech->requires()->attach($schermoGrande->id);

        $sediliPelle->excludes()->attach($sediliSportivi->id);
        $sediliSportivi->excludes()->attach($sediliPelle->id);

        $bianco = Color::create(['name' => 'Bianco Ghiaccio', 'hex_code' => '#F5F5F5']);
        $nero = Color::create(['name' => 'Nero Opaco', 'hex_code' => '#1A1A1A']);
        $grigio = Color::create(['name' => 'Grigio Metallizzato', 'hex_code' => '#808080']);
        $rosso = Color::create(['name' => 'Rosso Racing', 'hex_code' => '#CC0000']);
        $blu = Color::create(['name' => 'Blu Notte', 'hex_code' => '#003366']);

        $suv->colors()->attach([
            $bianco->id => ['price_surcharge' => 0],
            $nero->id => ['price_surcharge' => 500],
            $grigio->id => ['price_surcharge' => 300],
            $rosso->id => ['price_surcharge' => 800],
            $blu->id => ['price_surcharge' => 600],
        ]);

        $citycar->colors()->attach([
            $bianco->id => ['price_surcharge' => 0],
            $nero->id => ['price_surcharge' => 400],
            $grigio->id => ['price_surcharge' => 250],
            $rosso->id => ['price_surcharge' => 600],
        ]);
    }
}
