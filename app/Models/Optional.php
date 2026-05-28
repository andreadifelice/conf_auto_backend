<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Optional extends Model
{
    protected $fillable = ['name', 'category', 'price'];

    public function requires(): BelongsToMany
    {
        return $this->belongsToMany(
            Optional::class, 
            'optional_compatibilities', 
            'optional_id', 
            'requires_optional_id'
        );
    }

    public function excludes(): BelongsToMany
    {
        return $this->belongsToMany(
            Optional::class, 
            'optional_compatibilities', 
            'optional_id', 
            'excludes_optional_id'
        );
    }

    public function carModels(): BelongsToMany
    {
        return $this->belongsToMany(CarModel::class, 'car_model_optional');
    }
}
