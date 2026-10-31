<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $fillable = [
        'booking_id',
        'refund_code',
        'reason',
        'amount',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'bank_name',
        'bank_account',
        'account_holder',
        'transferred_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'bank_account' => 'encrypted',
            'account_holder' => 'encrypted',
            'reviewed_at' => 'datetime',
            'transferred_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
