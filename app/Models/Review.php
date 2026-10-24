<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'booking_id',
        'package_id',
        'user_id',
        'customer_name',
        'rating',
        'comment',
        'is_approved',
        'is_hidden',
        'service_ratings',
        'experience_tags',
        'photos',
        'is_public',
        'identity_verified',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'service_ratings' => 'array',
            'experience_tags' => 'array',
            'photos' => 'array',
            'is_approved' => 'boolean',
            'is_hidden' => 'boolean',
            'is_public' => 'boolean',
            'identity_verified' => 'boolean',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
