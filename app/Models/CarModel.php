<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CarModel extends Model
{
    protected $fillable = ['name', 'description', 'base_price', 'is_active'];

    public function engines(): BelongsToMany
    {
        return $this->belongsToMany(Engine::class, 'car_model_engine');
    }

    public function optionals(): BelongsToMany
    {
        return $this->belongsToMany(Optional::class, 'car_model_optional');
    }

    protected function configurations(){
        return $this->hasMany(Configuration::class);
    }
}
