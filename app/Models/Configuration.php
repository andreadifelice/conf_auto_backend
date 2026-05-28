<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    protected $fillable = ['user_id', 'car_model_id', 'engine_id', 'total_price', 'status'];

    protected function user(){
        return $this->belongsTo(User::class);
    }

    protected function carModel(){
        return $this->belongsTo(CarModel::class);
    }

    protected function engine(){
        return $this->belongsTo(Engine::class);
    }

    protected function optionals(){
        return $this->belongsTo(Optional::class, 'configuration_optional');
    }
}
