<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Engine extends Model
{
    protected $fillable = [
        'name', 
        'fuel_type', 
        'horse_power', 
        'additional_price'
    ];

    public function carModels(): BelongsToMany
    {
        return $this->belongsToMany(CarModel::class);
    }
}
