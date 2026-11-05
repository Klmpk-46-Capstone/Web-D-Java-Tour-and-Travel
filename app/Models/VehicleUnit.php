<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleUnit extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_MAINTENANCE = 'maintenance';

    public const STATUS_LABELS = [
        self::STATUS_ACTIVE => 'Aktif',
        self::STATUS_MAINTENANCE => 'Servis',
    ];

    protected $fillable = [
        'vehicle_id',
        'plate_number',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'vehicle_id' => 'integer',
        ];
    }

    /**
     * Normalisasi plat sebelum disimpan agar pencarian dan validasi unik konsisten.
     */
    public function setPlateNumberAttribute(mixed $value): void
    {
        $normalized = strtoupper((string) preg_replace('/\s+/', ' ', trim((string) $value)));

        $this->attributes['plate_number'] = $normalized;
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
