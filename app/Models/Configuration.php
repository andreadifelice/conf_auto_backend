<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Configuration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'car_model_id',
        'engine_id',
        'total_price',
        'status'
    ];

    public function optionals(): BelongsToMany
    {
        return $this->belongsToMany(Optional::class, 'configuration_optional');
    }

    public function carModel(): BelongsTo
    {
        return $this->belongsTo(CarModel::class, 'car_model_id');
    }

    public function engine(): BelongsTo
    {
        return $this->belongsTo(Engine::class, 'engine_id');
    }
}