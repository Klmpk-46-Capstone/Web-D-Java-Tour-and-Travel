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
        'vehicle_id',
        'vehicle_unit_id',
        'driver_name',
        'meeting_point',
        'departure_time',
    ];

    public const STATUS_LABELS = [
        'available' => 'Tersedia',
        'almost_full' => 'Hampir Penuh',
        'full' => 'Penuh',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
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

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function vehicleUnit(): BelongsTo
    {
        return $this->belongsTo(VehicleUnit::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst((string) $this->status);
    }

    /**
     * Jumlah hari trip (inklusif) berdasarkan tanggal berangkat & pulang.
     */
    public function getDurationDaysAttribute(): int
    {
        if (! $this->departure_date || ! $this->return_date) {
            return 1;
        }

        return (int) $this->departure_date->diffInDays($this->return_date) + 1;
    }

    /**
     * Total kursi yang masih ditahan oleh booking aktif (bukan batal/refund).
     */
    public function activeSeatCount(): int
    {
        return (int) $this->bookings()
            ->whereIn('booking_status', Booking::SEAT_HOLDING_STATUSES)
            ->sum('pax');
    }

    /**
     * Status otomatis berdasarkan okupansi. Status final (selesai/batal)
     * tidak ditimpa agar keputusan operasional tetap dihormati.
     */
    public function resolveStatus(?string $manual = null): string
    {
        $status = $manual ?? $this->status;

        if (in_array($status, ['completed', 'cancelled'], true)) {
            return $status;
        }

        if ($this->quota > 0 && $this->booked_seats >= $this->quota) {
            return 'full';
        }

        if ($this->quota > 0 && ($this->booked_seats / $this->quota) >= 0.8) {
            return 'almost_full';
        }

        return 'available';
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

    /**
     * Mengambil daftar pilihan titik kumpul/penjemputan otomatis berdasarkan data admin.
     *
     * @return array<int, string>
     */
    public function getPickupOptions(): array
    {
        $options = [];

        // 1. Dari meeting_point trip ini
        if (!empty($this->meeting_point)) {
            $split = preg_split('/[,\r\n;|]+/', (string) $this->meeting_point, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($split as $item) {
                $trimmed = trim($item);
                if ($trimmed !== '') {
                    $options[] = $trimmed;
                }
            }
        }

        // 2. Dari trip lain pada paket yang sama
        if ($this->package_id) {
            $otherPoints = static::where('package_id', $this->package_id)
                ->whereNotNull('meeting_point')
                ->where('meeting_point', '!=', '')
                ->pluck('meeting_point');

            foreach ($otherPoints as $raw) {
                $split = preg_split('/[,\r\n;|]+/', (string) $raw, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($split as $item) {
                    $trimmed = trim($item);
                    if ($trimmed !== '') {
                        $options[] = $trimmed;
                    }
                }
            }
        }

        $options = array_values(array_unique($options));

        if (empty($options)) {
            $options = ['Titik Kumpul Utama Sesuai Konfirmasi Admin'];
        }

        return $options;
    }
}
