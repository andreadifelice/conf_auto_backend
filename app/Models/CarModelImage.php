<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CarModelImage extends Model
{
    protected $fillable = [
        'car_model_id',
        'path',
        'sort_order',
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'url',
    ];

    public function carModel(): BelongsTo
    {
        return $this->belongsTo(CarModel::class);
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk('public')->url($this->path));
    }

    protected static function booted(): void
    {
        static::deleting(function (CarModelImage $image): void {
            Storage::disk('public')->delete($image->path);
        });
    }
}
