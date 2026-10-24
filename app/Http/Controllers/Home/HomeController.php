<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'boolean'],
            'mode' => ['nullable', 'in:paket,mobil'],
            'pickup' => ['nullable', 'string', 'max:150'],
            'destination' => ['nullable', 'string', 'max:150'],
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
        ]);

        $searchActive = $request->boolean('search');
        $mode = $filters['mode'] ?? 'paket';
        $pickup = trim((string) ($filters['pickup'] ?? ''));
        $destinationId = $filters['destination'] ?? null;
        $departureDate = $filters['departure_date'] ?? null;
        $passengers = (int) ($filters['passengers'] ?? 4);

        // Opsi dropdown destinasi selalu memakai data penuh, bukan hasil filter.
        $destinationOptions = Package::query()
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->latest('id')
            ->limit(8)
            ->get(['id', 'name']);

        $applyPackageFilters = function ($query) use (
            $searchActive,
            $mode,
            $pickup,
            $destinationId,
            $departureDate,
            $passengers
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

            if (filled($destinationId)) {
                $query->whereKey($destinationId);
            }

            if (filled($departureDate)) {
                $query->whereHas('trips', function ($tripQuery) use ($departureDate, $passengers) {
                    $tripQuery->whereDate('departure_date', $departureDate)
                        ->whereIn('status', ['available', 'open', 'almost_full']);

                    if ($passengers > 0) {
                        $tripQuery->whereRaw('(quota - booked_seats) >= ?', [$passengers]);
                    }
                });
            } elseif ($passengers > 0) {
                $query->whereHas('trips', function ($tripQuery) use ($passengers) {
                    $tripQuery->whereIn('status', ['available', 'open', 'almost_full'])
                        ->where('quota', '>=', $passengers);
                });
            }

            return $query;
        };

        // 1. Featured Packages (Rekomendasi Utama) - Data-driven from CMS toggle
        $featuredPackages = $applyPackageFilters(
            Package::with(['trips', 'itineraries', 'reviews'])
                ->where('is_active', true)
                ->where('is_featured', true)
                ->latest('id')
        )->get();

        // 2. Paket yang tampil pada section destinasi / hasil pencarian paket.
        $destinations = $applyPackageFilters(
            Package::query()
                ->where('is_active', true)
                ->orderByDesc('is_featured')
                ->latest('id')
        )->limit(8)->get();

        // 3. Armada: kapasitas kursi dan kata kunci nama/kategori/deskripsi.
        $vehicles = Vehicle::query()
            ->where('is_active', true)
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
                $searchActive && $mode === 'mobil' && $passengers > 0,
                fn ($query) => $query->where('seats', '>=', $passengers)
            )
            ->orderBy('price_per_day')
            ->limit(6)
            ->get();

        $resultCount = $searchActive
            ? ($mode === 'paket' ? $destinations->count() : $vehicles->count())
            : 0;

        $selectedDestination = $destinationOptions->firstWhere('id', (int) $destinationId);

        return view('pages.dashboard.beranda.index', [
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
            'resultCount' => $resultCount,
        ]);
    }

    // Customer Package Catalog
}
