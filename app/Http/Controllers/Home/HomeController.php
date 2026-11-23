<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HomeController extends Controller
{

    /**
     * Destinasi tetap untuk dropdown pencarian paket wisata.
     * Nilai = label; alias dipakai untuk mencocokkan penulisan lain di data paket.
     */
    private const DESTINATION_OPTIONS = [
        'Dieng' => ['Dieng'],
        'Bromo' => ['Bromo'],
        'Karimunjawa' => ['Karimunjawa', 'Karimun Jawa'],
        'Kawah Ijen' => ['Kawah Ijen', 'Ijen'],
        'Jogja' => ['Jogja', 'Yogyakarta', 'Jogjakarta'],
        'Borobudur' => ['Borobudur'],
    ];

    public function index(Request $request): View
    {
        $destinationOptions = array_keys(self::DESTINATION_OPTIONS);

        $filters = $request->validate([
            'search' => ['nullable', 'boolean'],
            'mode' => ['nullable', 'in:paket,mobil'],
            'pickup' => ['nullable', 'string', 'max:150'],
            'destination' => ['nullable', 'string', Rule::in($destinationOptions)],
            'transmission' => ['nullable', 'in:manual,automatic'],
            'departure_date' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
            'passengers' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ], $this->catalogValidationMessages());

        $searchActive = $request->boolean('search');
        $mode = $filters['mode'] ?? 'paket';
        $pickup = trim((string) ($filters['pickup'] ?? ''));
        $selectedDestination = $filters['destination'] ?? null;
        $transmission = $filters['transmission'] ?? null;
        $departureDate = $filters['departure_date'] ?? null;
        $passengers = (int) ($filters['passengers'] ?? 4);

        $applyPackageFilters = function ($query) use (
            $searchActive,
            $mode,
            $pickup,
            $selectedDestination,
            $departureDate
        ) {
            if (! $searchActive || $mode !== 'paket') {
                return $query;
            }

            if ($pickup !== '') {
                $keyword = '%' . $pickup . '%';

                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('name', 'like', $keyword)
                        ->orWhere('category', 'like', $keyword)
                        ->orWhere('badge', 'like', $keyword)
                        ->orWhere('description', 'like', $keyword);
                });
            }

            if (filled($selectedDestination)) {
                $aliases = self::DESTINATION_OPTIONS[$selectedDestination] ?? [$selectedDestination];

                $query->where(function ($subQuery) use ($aliases) {
                    foreach ($aliases as $alias) {
                        $keyword = '%' . $alias . '%';

                        $subQuery->orWhere('name', 'like', $keyword)
                            ->orWhere('category', 'like', $keyword)
                            ->orWhere('description', 'like', $keyword);
                    }
                });
            }

            if (filled($departureDate)) {
                $query->whereHas('trips', function ($tripQuery) use ($departureDate) {
                    $tripQuery->whereDate('departure_date', $departureDate)
                        ->whereIn('status', ['available', 'open', 'almost_full'])
                        ->where(function ($vq) {
                            $vq->whereNull('vehicle_id')
                                ->orWhereHas('vehicle', fn ($vehicleQuery) => $vehicleQuery->whereHas('activeUnits'));
                        });
                });
            }

            return $query;
        };

        // 1. Featured Packages (Rekomendasi Utama) - Data-driven from CMS toggle
        $featuredPackages = $applyPackageFilters(
            Package::with([
                'trips' => fn ($tripQuery) => $tripQuery
                    ->where('status', '!=', 'cancelled')
                    ->whereDate('departure_date', '>=', now()->toDateString())
                    ->where(function ($vq) {
                        $vq->whereNull('vehicle_id')
                            ->orWhereHas('vehicle', fn ($vehicleQuery) => $vehicleQuery->whereHas('activeUnits'));
                    }),
                'itineraries',
                'reviews',
            ])
                ->where('is_active', true)
                ->visibleToCustomer()
                ->where('is_featured', true)
                ->latest('id')
        )->get();

        // 1b. Active Packages for Interactive Destination Map
        $activeMapPackages = Package::query()
            ->with([
                'trips' => fn ($tripQuery) => $tripQuery
                    ->where('status', '!=', 'cancelled')
                    ->whereDate('departure_date', '>=', now()->toDateString()),
            ])
            ->where('is_active', true)
            ->visibleToCustomer()
            ->latest('id')
            ->get();

        $mapDestinations = [
            'bromo' => [
                'id' => 'bromo',
                'keyword' => 'Bromo',
                'name' => 'Gunung Bromo',
                'name_en' => 'Mount Bromo',
                'area' => 'Bromo Tengger Semeru',
                'area_en' => 'Bromo Tengger Semeru',
                'badge' => 'Mountain & Sunrise',
                'badge_en' => 'Mountain & Sunrise',
                'description' => 'Kawasan kaldera Bromo dengan lautan pasir legendaris, kawah aktif, dan panorama golden sunrise terbaik di Jawa Timur.',
                'description_en' => 'The Bromo caldera area featuring the legendary sea of sand, active crater, and the best golden sunrise view in East Java.',
                'x' => 53,
                'y' => 53.5,
                'label_pos' => 'top',
                'packages' => $activeMapPackages->filter(fn ($pkg) => $pkg->matchesMapDestination('bromo'))->values(),
            ],
            'tumpak_sewu' => [
                'id' => 'tumpak_sewu',
                'keyword' => 'Tumpak Sewu',
                'name' => 'Air Terjun Tumpak Sewu',
                'name_en' => 'Tumpak Sewu Waterfall',
                'area' => 'Pronojiwo, Lumajang',
                'area_en' => 'Pronojiwo, Lumajang',
                'badge' => 'Air Terjun Eksotis',
                'badge_en' => 'Exotic Waterfall',
                'description' => 'Air terjun tirai megah setinggi 120 meter di perbatasan Malang-Lumajang yang sering dijuluki Niagara-nya Indonesia.',
                'description_en' => 'A magnificent 120-meter curtain waterfall on the border of Malang and Lumajang, often dubbed the Niagara of Indonesia.',
                'x' => 55,
                'y' => 70.5,
                'label_pos' => 'top',
                'packages' => $activeMapPackages->filter(fn ($pkg) => $pkg->matchesMapDestination('tumpak_sewu'))->values(),
            ],
            'ijen' => [
                'id' => 'ijen',
                'keyword' => 'Ijen',
                'name' => 'Kawah Ijen',
                'name_en' => 'Ijen Crater',
                'area' => 'Banyuwangi & Bondowoso',
                'area_en' => 'Banyuwangi & Bondowoso',
                'badge' => 'Blue Fire Phenomenon',
                'badge_en' => 'Blue Fire Phenomenon',
                'description' => 'Fenomena kawah asam terbesar di dunia dengan pancaran Blue Fire magis di tengah malam dan panorama danau toska alami.',
                'description_en' => 'The world\'s largest acidic crater lake phenomenon featuring magical blue fire at midnight and panoramic turquoise waters.',
                'x' => 85.2,
                'y' => 67.5,
                'label_pos' => 'right',
                'packages' => $activeMapPackages->filter(fn ($pkg) => $pkg->matchesMapDestination('ijen'))->values(),
            ],
        ];

        // 2. Paket yang tampil pada section destinasi / hasil pencarian paket.
        $destinations = $applyPackageFilters(
            Package::query()
                ->where('is_active', true)
                ->visibleToCustomer()
                ->orderByDesc('is_featured')
                ->latest('id')
        )->limit(8)->get();

        // 3. Armada: kapasitas kursi dan kata kunci nama/kategori/deskripsi.
        $vehicles = Vehicle::query()
            ->where('is_active', true)
            ->whereHas('activeUnits')
            ->when(
                $searchActive && $mode === 'mobil' && $pickup !== '',
                function ($query) use ($pickup) {
                    $keyword = '%' . $pickup . '%';

                    $query->where(function ($subQuery) use ($keyword) {
                        $subQuery->where('name', 'like', $keyword)
                            ->orWhere('category', 'like', $keyword)
                            ->orWhere('description', 'like', $keyword);
                    });
                }
            )
            ->when(
                $searchActive && $mode === 'mobil' && $transmission === 'automatic',
                fn ($query) => $query->where('transmission', 'like', '%matic%')
            )
            ->when(
                $searchActive && $mode === 'mobil' && $transmission === 'manual',
                fn ($query) => $query->where('transmission', 'like', '%manual%')
            )
            ->when(
                $searchActive && $mode === 'mobil' && $passengers > 0,
                fn ($query) => $query->where('seats', '>=', $passengers)
            )
            ->orderBy('price_per_day')
            ->limit(6)
            ->get();

        $resultCount = $searchActive
            ? ($mode === 'paket' ? $destinations->count() : $vehicles->count())
            : 0;

        return view($this->dashboardView('beranda.index'), [
            'featuredPackages' => $featuredPackages,
            'mapDestinations' => $mapDestinations,
            'destinations' => $destinations,
            'destinationOptions' => $destinationOptions,
            'vehicles' => $vehicles,
            'searchActive' => $searchActive,
            'mode' => $mode,
            'pickup' => $pickup,
            'departureDate' => $departureDate,
            'passengers' => $passengers,
            'selectedDestination' => $selectedDestination,
            'transmission' => $transmission,
            'resultCount' => $resultCount,
        ]);
    }

    // Customer Package Catalog
}
