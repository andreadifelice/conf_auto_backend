<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarModel extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'model',
        'year',
        'description',
        'base_price',
        'is_active',
    ];

    public function engines(): BelongsToMany
    {
        return $this->belongsToMany(Engine::class, 'car_model_engine');
    }

    public function optionals(): BelongsToMany
    {
        return $this->belongsToMany(Optional::class, 'car_model_optional');
    }

    public function colors(): BelongsToMany
    {
        return $this->belongsToMany(Color::class, 'car_color')
            ->withPivot('price_surcharge')
            ->withTimestamps();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(CarModelImage::class)->orderBy('sort_order');
    }

    public function configurations(): HasMany
    {
        return $this->hasMany(Configuration::class);
    }
}
