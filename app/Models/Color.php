<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Color extends Model
{
    protected $fillable = [
        'name',
        'hex_code',
    ];

    public function carModels(): BelongsToMany
    {
        return $this->belongsToMany(CarModel::class, 'car_color')
            ->withPivot('price_surcharge')
            ->withTimestamps();
    }
}
