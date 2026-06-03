<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Engine extends Model
{
    protected $fillable = [
        'name', 
        'fuel_type', 
        'horse_power', 
        'additional_price'
    ];

    protected function carModels(){
        return $this->belongsToMany(CarModel::class);
    }
}
