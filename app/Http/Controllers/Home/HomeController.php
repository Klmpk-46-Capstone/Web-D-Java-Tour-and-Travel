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
                        ->whereNotNull('vehicle_id')
                        ->whereHas('vehicle', fn ($vehicleQuery) => $vehicleQuery->whereHas('activeUnits'));
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
                    ->whereNotNull('vehicle_id')
                    ->whereHas('vehicle', fn ($vehicleQuery) => $vehicleQuery->whereHas('activeUnits')),
                'itineraries',
                'reviews',
            ])
                ->where('is_active', true)
                ->visibleToCustomer()
                ->where('is_featured', true)
                ->latest('id')
        )->get();

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
