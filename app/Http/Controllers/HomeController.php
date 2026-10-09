<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->validate([
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

        $destinations = Destination::query()
            ->where('is_active', true)
            ->when(
                filled($search['destination'] ?? null),
                function ($query) use ($search) {
                    $query->where(
                        'name',
                        'like',
                        '%' . $search['destination'] . '%'
                    );
                }
            )
            ->orderBy('name')
            ->limit(8)
            ->get();

        $vehicles = Vehicle::query()
            ->where('is_active', true)
            ->when(
                isset($search['passengers']),
                function ($query) use ($search) {
                    $query->where(
                        'seats',
                        '>=',
                        (int) $search['passengers']
                    );
                }
            )
            ->orderBy('price_per_day')
            ->limit(6)
            ->get();

        return view('pages.dashboard.beranda.index', [
            'destinations' => $destinations,
            'vehicles' => $vehicles,
            'search' => $search,
        ]);
    }
}
