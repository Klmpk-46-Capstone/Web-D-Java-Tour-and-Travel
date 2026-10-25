<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Vehicle extends Model
{
    /**
     * Kategori armada yang dipakai pada filter katalog.
     */
    public const CATEGORIES = [
        'mpv' => 'MPV Keluarga',
        'premium' => 'Premium & Hybrid',
        'minibus' => 'Minibus Rombongan',
    ];

    public const CATEGORIES_EN = [
        'mpv' => 'Family MPV',
        'premium' => 'Premium & Hybrid',
        'minibus' => 'Group Minibus',
    ];

    /**
     * Label kategori sesuai bahasa aktif.
     *
     * @return array<string, string>
     */
    public static function categoryOptions(): array
    {
        return app()->getLocale() === 'en'
            ? self::CATEGORIES_EN
            : self::CATEGORIES;
    }

    protected $fillable = [
        'name',
        'slug',
        'category',
        'image_path',
        'description',
        'badge',
        'seats',
        'luggage_capacity',
        'transmission',
        'price_per_day',
        'price_with_driver',
        'price_without_driver',
        'includes_driver',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'seats' => 'integer',
            'luggage_capacity' => 'integer',
            'price_per_day' => 'integer',
            'price_with_driver' => 'integer',
            'price_without_driver' => 'integer',
            'includes_driver' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Resolve the vehicle photo: absolute URLs are used as-is,
     * relative paths are served from the public storage disk.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $path = $this->image_path;

            if (blank($path)) {
                return null;
            }

            if (Str::startsWith($path, ['http://', 'https://'])) {
                return $path;
            }

            return Storage::disk('public')->url(ltrim($path, '/'));
        });
    }

    protected function categoryLabel(): Attribute
    {
        return Attribute::get(
            fn (): string => self::categoryOptions()[$this->category] ?? (app()->getLocale() === 'en' ? 'Vehicle' : 'Armada')
        );
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->formatRupiah((float) $this->price_per_day);
    }

    /**
     * Harga terendah dari seluruh opsi sewa, dipakai untuk label "mulai dari".
     */
    public function getLowestPriceAttribute(): ?int
    {
        $prices = collect([
            $this->price_with_driver,
            $this->price_without_driver,
            $this->price_per_day,
        ])->filter(fn ($price) => $price !== null);

        return $prices->isEmpty() ? null : (int) $prices->min();
    }

    public function getFormattedLowestPriceAttribute(): ?string
    {
        return $this->lowest_price !== null
            ? $this->formatRupiah((float) $this->lowest_price)
            : null;
    }

    /**
     * Opsi sewa yang tersedia untuk unit ini (dengan / tanpa supir).
     * Harga fallback memakai price_per_day agar data lama tetap tampil.
     *
     * @return array<int, array{key: string, label: string, price: int, description: string}>
     */
    public function rentalOptions(): array
    {
        $withDriver = $this->price_with_driver
            ?? ($this->includes_driver ? $this->price_per_day : null);

        $withoutDriver = $this->price_without_driver;

        $options = [];

        $isEnglish = app()->getLocale() === 'en';

        if ($withDriver) {
            $options[] = [
                'key' => 'with_driver',
                'label' => $isEnglish ? 'With Driver' : 'Dengan Supir',
                'price' => (int) $withDriver,
                'description' => $isEnglish
                    ? 'Includes a professional driver for your entire travel route.'
                    : 'Termasuk driver profesional untuk seluruh rute perjalanan Anda.',
            ];
        }

        if ($withoutDriver) {
            $options[] = [
                'key' => 'without_driver',
                'label' => $isEnglish ? 'Without Driver (Self-Drive)' : 'Tanpa Supir (Lepas Kunci)',
                'price' => (int) $withoutDriver,
                'description' => $isEnglish
                    ? 'Vehicle rental only. A valid driver’s license must be shown at handover.'
                    : 'Sewa unit saja. Wajib menunjukkan SIM A yang masih berlaku saat serah terima.',
            ];
        }

        if ($options === [] && $this->price_per_day) {
            $options[] = [
                'key' => 'daily',
                'label' => $isEnglish ? 'Daily Rental' : 'Sewa Harian',
                'price' => (int) $this->price_per_day,
                'description' => $isEnglish
                    ? 'Daily rental rate for the vehicle unit.'
                    : 'Tarif sewa harian unit armada.',
            ];
        }

        return $options;
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)
            ->where('is_approved', true)
            ->where('is_hidden', false);
    }

    /**
     * Format nominal rupiah sesuai bahasa aktif.
     */
    private function formatRupiah(float $amount): string
    {
        return app()->getLocale() === 'en'
            ? 'Rp ' . number_format($amount, 0, '.', ',')
            : 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
