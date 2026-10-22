<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
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
            fn (): string => self::CATEGORIES[$this->category] ?? 'Armada'
        );
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->price_per_day, 0, ',', '.');
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
            ? 'Rp ' . number_format((float) $this->lowest_price, 0, ',', '.')
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

        if ($withDriver) {
            $options[] = [
                'key' => 'with_driver',
                'label' => 'Dengan Supir',
                'price' => (int) $withDriver,
                'description' => 'Termasuk driver profesional untuk seluruh rute perjalanan Anda.',
            ];
        }

        if ($withoutDriver) {
            $options[] = [
                'key' => 'without_driver',
                'label' => 'Tanpa Supir (Lepas Kunci)',
                'price' => (int) $withoutDriver,
                'description' => 'Sewa unit saja. Wajib menunjukkan SIM A yang masih berlaku saat serah terima.',
            ];
        }

        if ($options === [] && $this->price_per_day) {
            $options[] = [
                'key' => 'daily',
                'label' => 'Sewa Harian',
                'price' => (int) $this->price_per_day,
                'description' => 'Tarif sewa harian unit armada.',
            ];
        }

        return $options;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
