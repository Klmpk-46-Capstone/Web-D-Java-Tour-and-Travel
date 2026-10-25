<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    /**
     * Status booking yang masih menahan kursi trip.
     */
    public const SEAT_HOLDING_STATUSES = ['pending', 'confirmed', 'completed', 'refund_requested'];

    protected $fillable = [
        'booking_code',
        'user_id',
        'trip_id',
        'package_id',

        // Data Armada
        'vehicle_id',
        'rental_date',
        'duration',
        'rental_option',
        'rate',
        'down_payment',
        'delivery_method',
        'identity_file_path',

        // Data customer dan booking
        'customer_name',
        'customer_email',
        'customer_phone',
        'pax',
        'selected_armada',
        'pickup_location',
        'pickup_details',
        'total_price',
        'payment_method',
        'payment_status',
        'booking_status',
        'notes',
        'checked_in_at',
        'checked_in_by',
    ];

    protected $casts = [
        'pax' => 'integer',
        'total_price' => 'integer',
        'down_payment' => 'integer',
        'rate' => 'integer',
        'duration' => 'integer',
        'rental_date' => 'date',
        'checked_in_at' => 'datetime',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function refund(): HasOne
    {
        return $this->hasOne(Refund::class)->latestOfMany();
    }

    public function getIsCheckedInAttribute(): bool
    {
        return $this->checked_in_at !== null;
    }

    /**
     * Check-in hanya untuk booking yang sudah terkonfirmasi dan sudah membayar (DP/lunas).
     */
    public function getCanCheckInAttribute(): bool
    {
        return in_array($this->booking_status, ['confirmed', 'completed'], true)
            && in_array($this->payment_status, ['partial', 'paid'], true);
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }
}
