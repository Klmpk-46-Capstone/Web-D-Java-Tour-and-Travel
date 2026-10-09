<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image_path',
        'description',
        'seats',
        'luggage_capacity',
        'transmission',
        'price_per_day',
        'includes_driver',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'seats' => 'integer',
            'luggage_capacity' => 'integer',
            'price_per_day' => 'integer',
            'includes_driver' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
