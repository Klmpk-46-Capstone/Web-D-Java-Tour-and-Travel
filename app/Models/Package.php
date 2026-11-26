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

    /**
     * Master pilihan tipe rute wilayah yang selaras dengan titik peta Jawa Timur.
     */
    public const ROUTE_TYPES = [
        'bromo' => 'Gunung Bromo (Kawasan Bromo Tengger Semeru)',
        'tumpak_sewu' => 'Air Terjun Tumpak Sewu (Lumajang / Pronojiwo)',
        'ijen' => 'Kawah Ijen (Banyuwangi / Blue Fire)',
        'bahari' => 'Bahari (Karimunjawa / Jepara)',
        'gunung' => 'Gunung / Dataran Tinggi Lainnya (Dieng / dsb)',
        'heritage' => 'Heritage & Budaya (Jogja / Borobudur)',
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

    /**
     * Paket yang masih layak tampil ke customer.
     *
     * Paket tanpa jadwal mendatang tetap tampil sebagai informasi paket.
     * Paket yang memiliki jadwal mendatang tetap tampil (baik armada belum ditentukan
     * maupun armada yang telah memiliki unit operasional aktif).
     */
    public function scopeVisibleToCustomer($query)
    {
        $today = now()->toDateString();

        return $query->where(function ($subQuery) use ($today) {
            $subQuery->whereDoesntHave('trips', function ($tripQuery) use ($today) {
                $tripQuery->where('status', '!=', 'cancelled')
                    ->whereDate('departure_date', '>=', $today);
            })->orWhereHas('trips', function ($tripQuery) use ($today) {
                $tripQuery->where('status', '!=', 'cancelled')
                    ->whereDate('departure_date', '>=', $today)
                    ->where(function ($vq) {
                        $vq->whereNull('vehicle_id')
                            ->orWhereHas('vehicle', fn ($vehicleQuery) => $vehicleQuery->whereHas('activeUnits'));
                    });
            });
        });
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

    /**
     * Label rute wilayah yang mudah dibaca.
     */
    public function getRouteTypeLabelAttribute(): string
    {
        return self::ROUTE_TYPES[$this->route_type] ?? ucfirst((string) $this->route_type);
    }

    /**
     * Memeriksa apakah paket wisata ini cocok dengan salah satu destinasi peta Jawa Timur:
     * 'bromo', 'tumpak_sewu', 'ijen'.
     *
     * Prioritas kecocokan:
     * 1. route_type eksplisit yang dipilih admin ('bromo', 'tumpak_sewu', 'ijen')
     * 2. Kompatibilitas data lama/seeder: route_type 'gunung' yang mengandung kata kunci pada teks
     * 3. Kata kunci destinasi pada nama, kategori, deskripsi, atau slug paket
     */
    public function matchesMapDestination(string $destinationKey): bool
    {
        $key = strtolower(trim($destinationKey));
        $route = strtolower((string) $this->route_type);
        $text = strtolower($this->name . ' ' . $this->category . ' ' . $this->description . ' ' . $this->slug);

        return match ($key) {
            'bromo' => $route === 'bromo'
                || in_array($route, ['gunung_bromo', 'gunung-bromo'])
                || ($route === 'gunung' && str_contains($text, 'bromo'))
                || str_contains($text, 'bromo'),

            'tumpak_sewu' => $route === 'tumpak_sewu'
                || in_array($route, ['tumpaksewu', 'air_terjun', 'air-terjun', 'air_terjun_tumpak_sewu'])
                || str_contains($text, 'tumpak sewu')
                || str_contains($text, 'tumpaksewu')
                || str_contains($text, 'sewu'),

            'ijen' => $route === 'ijen'
                || in_array($route, ['kawah_ijen', 'kawah-ijen'])
                || ($route === 'gunung' && str_contains($text, 'ijen'))
                || str_contains($text, 'ijen'),

            default => false,
        };
    }
}
