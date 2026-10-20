<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'batch_name',
        'departure_date',
        'return_date',
        'quota',
        'booked_seats',
        'status',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'quota' => 'integer',
        'booked_seats' => 'integer',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function getRemainingSeatsAttribute(): int
    {
        return max(0, $this->quota - $this->booked_seats);
    }

    public function getIsFullAttribute(): bool
    {
        return $this->remaining_seats <= 0;
    }

    public function getPercentageBookedAttribute(): int
    {
        if ($this->quota <= 0) {
            return 100;
        }
        return (int) min(100, round(($this->booked_seats / $this->quota) * 100));
    }
}
