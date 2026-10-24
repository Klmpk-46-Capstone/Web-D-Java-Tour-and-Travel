<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'transaction_code',
        'amount',
        'payment_method',
        'payment_channel',
        'paid_at',
        'status',
        'receipt_path',

        // Data integrasi Xendit
        'xendit_invoice_id',
        'payment_url',
        'external_id',
        'xendit_payment_request_id',
        'payment_stage',
        'payment_instruction',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'integer',
        'paid_at' => 'datetime',
        'payment_instruction' => 'array',
    ];

    /**
     * Get the booking that owns the transaction.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getServiceTypeAttribute(): string
    {
        if ($this->booking?->vehicle_id || $this->booking?->selected_armada) {
            return 'armada';
        }
        return 'paket';
    }

    public function getServiceNameAttribute(): string
    {
        if ($this->booking?->package) {
            return $this->booking->package->name;
        }
        if ($this->booking?->vehicle) {
            return $this->booking->vehicle->name;
        }
        if ($this->booking?->selected_armada) {
            return $this->booking->selected_armada;
        }
        return 'Layanan Tour & Travel';
    }

    public function getServiceDetailAttribute(): string
    {
        if ($this->booking?->notes) {
            return $this->booking->notes;
        }
        if ($this->booking?->pickup_location) {
            return $this->booking->pickup_location;
        }
        if ($this->booking?->pax) {
            return 'Pax: ' . $this->booking->pax . ' Orang';
        }
        return '-';
    }
}
