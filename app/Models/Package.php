<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Package extends Model
{
    use HasFactory;

    /**
     * Master pilihan kategori wisata utama.
     */
    public const MASTER_CATEGORIES = [
        'Wisata Bahari & Island Hopping',
        'Mountain Trekking & Sunrise',
        'Cultural, Heritage & City Tour',
    ];

    protected $fillable = [
        'name',
        'slug',
        'category',
        'route_type',
        'duration',
        'price',
        'image_url',
        'badge',
        'rating',
        'total_reviews',
        'description',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'price' => 'integer',
        'rating' => 'float',
        'total_reviews' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($package) {
            if (empty($package->slug)) {
                $package->slug = Str::slug($package->name) . '-' . Str::random(5);
            }
        });
    }

    public function itineraries(): HasMany
    {
        return $this->hasMany(Itinerary::class)->orderBy('day_number');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class)->orderBy('departure_date');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true)->where('is_hidden', false);
    }

    public function allReviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Mengambil daftar master kategori wisata yang dikelola dan terdaftar di database.
     * Digunakan secara sinkron oleh panel Admin (tambah/edit paket) dan filter katalog Customer.
     *
     * @return array<int, string>
     */
    public static function getAvailableCategories(): array
    {
        $dbCategories = static::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->all();

        return array_values(array_unique(array_merge(self::MASTER_CATEGORIES, $dbCategories)));
    }

    /**
     * Menghitung format teks durasi perjalanan otomatis berdasarkan jumlah hari itinerary.
     * Aturan:
     * - 1 Hari: "1 Hari" (One-Day Trip tanpa malam)
     * - 2 Hari: "2 Hari 1 Malam"
     * - 3 Hari: "3 Hari 2 Malam"
     * - N Hari: "N Hari (N - 1) Malam"
     */
    public static function calculateDurationFromDays(int $days): string
    {
        if ($days <= 1) {
            return '1 Hari';
        }

        $nights = $days - 1;
        return "{$days} Hari {$nights} Malam";
    }

    /**
     * Menghitung durasi perjalanan dalam jumlah hari.
     * Contoh: "3 Hari 2 Malam" => 3, "2 Hari 1 Malam" => 2, "Midnight (1 Hari)" => 1.
     */
    public function durationInDays(): int
    {
        if (!empty($this->duration)) {
            if (preg_match('/(\d+)\s*(?:hari|day|d)/i', $this->duration, $matches)) {
                return max(1, (int) $matches[1]);
            }
        }

        $itineraryCount = $this->relationLoaded('itineraries')
            ? $this->itineraries->count()
            : $this->itineraries()->count();

        if ($itineraryCount > 0) {
            return $itineraryCount;
        }

        return 1;
    }
}
