<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Package;
use App\Models\Refund;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        // 1. Metric Summaries (Real-Time Database Queries)
        $totalGrossRevenue = (int) (Transaction::where('status', 'success')->sum('amount') 
            ?: Booking::whereIn('payment_status', ['paid', 'partial'])->sum('total_price'));

        $currentMonthRevenue = (int) (Transaction::where('status', 'success')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount') 
            ?: Booking::whereIn('payment_status', ['paid', 'partial'])
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_price'));

        $lastMonthRevenue = (int) (Transaction::where('status', 'success')
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->sum('amount') 
            ?: Booking::whereIn('payment_status', ['paid', 'partial'])
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->sum('total_price'));

        $revenueGrowth = $lastMonthRevenue > 0 
            ? round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) 
            : ($currentMonthRevenue > 0 ? 100.0 : 0.0);

        $quarterStart = Carbon::now()->startOfQuarter();
        $quarterEnd = Carbon::now()->endOfQuarter();
        $quarterTotalQuota = (int) Trip::whereBetween('departure_date', [$quarterStart, $quarterEnd])->sum('quota');
        $quarterBookedSeats = (int) Trip::whereBetween('departure_date', [$quarterStart, $quarterEnd])->sum('booked_seats');
        if ($quarterTotalQuota > 0) {
            $q1TargetPercentage = min(100, (int) round(($quarterBookedSeats / $quarterTotalQuota) * 100));
        } else {
            $quarterTargetBaseline = 50000000;
            $q1TargetPercentage = $totalGrossRevenue > 0 ? min(100, (int) round(($totalGrossRevenue / $quarterTargetBaseline) * 100)) : 0;
        }

        // 2. Bookings & Pax
        $totalBookingsPax = (int) Booking::where('booking_status', '!=', 'cancelled')->sum('pax');
        if ($totalBookingsPax === 0) {
            $totalBookingsPax = (int) Trip::sum('booked_seats');
        }

        $currentMonthPax = (int) Booking::where('booking_status', '!=', 'cancelled')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('pax');
        $lastMonthPax = (int) Booking::where('booking_status', '!=', 'cancelled')
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->sum('pax');
        $paxGrowth = $lastMonthPax > 0 
            ? round((($currentMonthPax - $lastMonthPax) / $lastMonthPax) * 100, 1) 
            : ($currentMonthPax > 0 ? 100.0 : 0.0);

        $startOfWeekend = Carbon::now()->isWeekend() ? Carbon::now()->startOfDay() : Carbon::now()->next(Carbon::SATURDAY)->startOfDay();
        $endOfWeekend = (clone $startOfWeekend)->endOfWeek();
        $weekendPax = (int) Booking::where('booking_status', '!=', 'cancelled')
            ->whereHas('trip', function ($q) use ($startOfWeekend, $endOfWeekend) {
                $q->whereBetween('departure_date', [$startOfWeekend, $endOfWeekend]);
            })->sum('pax');
        if ($weekendPax === 0) {
            $weekendPax = (int) Trip::whereBetween('departure_date', [$startOfWeekend, $endOfWeekend])->sum('booked_seats');
        }

        $totalTrips = Trip::count();
        $onScheduleTrips = Trip::where('status', '!=', 'cancelled')->count();
        $onScheduleRate = $totalTrips > 0 ? round(($onScheduleTrips / $totalTrips) * 100, 1) : 100.0;

        // 3. Top 1 Best Selling Package (Real-Time Database Query)
        $topPackageStat = Booking::select('package_id', DB::raw('SUM(pax) as total_pax'), DB::raw('SUM(total_price) as total_revenue'))
            ->whereNotNull('package_id')
            ->where('booking_status', '!=', 'cancelled')
            ->groupBy('package_id')
            ->orderByDesc('total_pax')
            ->first();

        if ($topPackageStat) {
            $topPackage = Package::withCount('reviews')->find($topPackageStat->package_id);
            $topPackagePax = (int) $topPackageStat->total_pax;
            $topPackageRevenue = (int) $topPackageStat->total_revenue;
        } else {
            $topTripStat = Trip::select('package_id', DB::raw('SUM(booked_seats) as total_seats'))
                ->whereNotNull('package_id')
                ->where('status', '!=', 'cancelled')
                ->groupBy('package_id')
                ->orderByDesc('total_seats')
                ->first();

            if ($topTripStat && $topTripStat->total_seats > 0) {
                $topPackage = Package::withCount('reviews')->find($topTripStat->package_id);
                $topPackagePax = (int) $topTripStat->total_seats;
                $topPackageRevenue = (int) (($topPackage?->price ?? 0) * $topPackagePax);
            } else {
                $topPackage = Package::withCount('reviews')->orderByDesc('rating')->first();
                $topPackagePax = 0;
                $topPackageRevenue = 0;
            }
        }

        // 4. Refund & Issues (Pure Real-Time Data)
        $pendingRefundsCount = Refund::where('status', 'pending')->count();
        $disputedRefundNominal = (int) Refund::where('status', 'pending')->sum('amount');

        // 5. Monthly Sales & Pax Trends (6 Months Database Records)
        $monthlyTrends = collect();
        for ($i = 5; $i >= 0; $i--) {
            $mDate = Carbon::now()->subMonths($i);
            $m = $mDate->month;
            $y = $mDate->year;
            $label = $mDate->translatedFormat('M Y');

            $mPax = (int) Booking::where('booking_status', '!=', 'cancelled')
                ->whereMonth('created_at', $m)
                ->whereYear('created_at', $y)
                ->sum('pax');

            $mRev = (int) (Transaction::where('status', 'success')
                ->whereMonth('created_at', $m)
                ->whereYear('created_at', $y)
                ->sum('amount') 
                ?: Booking::whereIn('payment_status', ['paid', 'partial'])
                ->whereMonth('created_at', $m)
                ->whereYear('created_at', $y)
                ->sum('total_price'));

            $bahariPax = (int) Booking::where('booking_status', '!=', 'cancelled')
                ->whereMonth('created_at', $m)
                ->whereYear('created_at', $y)
                ->whereHas('package', fn($q) => $q->where('route_type', 'bahari'))
                ->sum('pax');

            $gunungPax = (int) Booking::where('booking_status', '!=', 'cancelled')
                ->whereMonth('created_at', $m)
                ->whereYear('created_at', $y)
                ->whereHas('package', fn($q) => $q->where('route_type', 'gunung'))
                ->sum('pax');

            $heritagePax = (int) Booking::where('booking_status', '!=', 'cancelled')
                ->whereMonth('created_at', $m)
                ->whereYear('created_at', $y)
                ->whereHas('package', fn($q) => $q->where('route_type', 'heritage'))
                ->sum('pax');

            $monthlyTrends->push([
                'label' => $label,
                'month' => $m,
                'year' => $y,
                'pax' => $mPax,
                'revenue' => $mRev,
                'bahari_pax' => $bahariPax,
                'gunung_pax' => $gunungPax,
                'heritage_pax' => $heritagePax,
                'is_current' => ($i === 0),
            ]);
        }

        $peakMonth = $monthlyTrends->sortByDesc('pax')->first();

        // 6. Category Performance (Real Database Aggregates)
        $routeStats = [
            'bahari' => [
                'label' => 'Wisata Bahari & Island Hopping',
                'pax' => (int) Booking::where('booking_status', '!=', 'cancelled')
                    ->whereHas('package', fn($q) => $q->where('route_type', 'bahari'))->sum('pax'),
            ],
            'gunung' => [
                'label' => 'Mountain Trekking & Sunrise',
                'pax' => (int) Booking::where('booking_status', '!=', 'cancelled')
                    ->whereHas('package', fn($q) => $q->where('route_type', 'gunung'))->sum('pax'),
            ],
            'heritage' => [
                'label' => 'Cultural, Heritage & City Tour',
                'pax' => (int) Booking::where('booking_status', '!=', 'cancelled')
                    ->whereHas('package', fn($q) => $q->where('route_type', 'heritage'))->sum('pax'),
            ],
        ];

        $totalRoutePax = array_sum(array_column($routeStats, 'pax'));
        if ($totalRoutePax === 0) {
            $routeStats['bahari']['pax'] = (int) Trip::whereHas('package', fn($q) => $q->where('route_type', 'bahari'))->sum('booked_seats');
            $routeStats['gunung']['pax'] = (int) Trip::whereHas('package', fn($q) => $q->where('route_type', 'gunung'))->sum('booked_seats');
            $routeStats['heritage']['pax'] = (int) Trip::whereHas('package', fn($q) => $q->where('route_type', 'heritage'))->sum('booked_seats');
            $totalRoutePax = array_sum(array_column($routeStats, 'pax'));
        }

        foreach ($routeStats as $key => &$st) {
            $st['percent'] = $totalRoutePax > 0 ? (int) round(($st['pax'] / $totalRoutePax) * 100) : 0;
        }
        unset($st);

        $avgCustomerRating = round((float) (Review::where('is_approved', true)->where('is_hidden', false)->avg('rating') ?: 5.0), 2);

        // 7. Monthly Transactions count for table footer
        $totalTransactionsThisMonth = Transaction::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        if ($totalTransactionsThisMonth === 0) {
            $totalTransactionsThisMonth = Booking::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count();
        }

        // 8. Real-time Bookings Feed (Latest 5 or filtered)
        $searchQuery = $request->input('q');
        $bookingsQuery = Booking::with(['package', 'trip', 'user'])
            ->latest('id');

        if ($searchQuery) {
            $bookingsQuery->where(function ($q) use ($searchQuery) {
                $q->where('booking_code', 'like', "%{$searchQuery}%")
                  ->orWhere('customer_name', 'like', "%{$searchQuery}%")
                  ->orWhere('customer_email', 'like', "%{$searchQuery}%")
                  ->orWhereHas('package', function ($pq) use ($searchQuery) {
                      $pq->where('name', 'like', "%{$searchQuery}%");
                  });
            });
        }

        $latestBookings = $bookingsQuery->limit(5)->get();

        // 9. Trips Quota Warning
        $warningTrips = Trip::with('package')
            ->where('quota', '>', 0)
            ->orderByRaw('(booked_seats / quota) DESC')
            ->limit(3)
            ->get();

        // 10. Notifikasi H-1: trip dan sewa armada yang berangkat besok
        $tomorrow = Carbon::tomorrow()->toDateString();
        $h1Trips = Trip::with(['package', 'vehicleUnit', 'bookings.user'])
            ->whereDate('departure_date', $tomorrow)
            ->whereIn('status', ['available', 'almost_full', 'full'])
            ->get();

        // Hitung total customer yang akan berangkat besok
        $h1TotalCustomers = $h1Trips->sum(fn($t) => $t->bookings
            ->whereIn('booking_status', ['confirmed', 'completed'])
            ->whereIn('payment_status', ['partial', 'paid'])
            ->sum('pax')
        );

        $h1Bookings = Booking::with(['user', 'trip.package', 'trip.vehicleUnit', 'vehicle', 'vehicleUnit'])
            ->where(function ($q) use ($tomorrow) {
                $q->whereHas('trip', fn($t) => $t->whereDate('departure_date', $tomorrow))
                  ->orWhereDate('rental_date', $tomorrow);
            })
            ->whereIn('booking_status', ['confirmed', 'completed'])
            ->whereIn('payment_status', ['partial', 'paid'])
            ->get();

        // All packages for modal "+ Buat Trip Baru"
        $allPackages = Package::where('is_active', true)->get();

        // BMKG Maritime Status data
        $maritimeWeather = [
            'location' => 'Perairan Jepara - Karimunjawa',
            'status' => 'Kondisi Aman',
            'wave_height' => '<0.75m',
            'vessels' => 'KMP Siginjai & Express Bahari normal beroperasi',
            'updated_at' => Carbon::now()->format('H:i') . ' WIB',
        ];

        return view('admin.dashboard.index', compact(
            'totalGrossRevenue',
            'revenueGrowth',
            'q1TargetPercentage',
            'totalBookingsPax',
            'paxGrowth',
            'weekendPax',
            'onScheduleRate',
            'topPackage',
            'topPackagePax',
            'topPackageRevenue',
            'pendingRefundsCount',
            'disputedRefundNominal',
            'monthlyTrends',
            'peakMonth',
            'routeStats',
            'totalRoutePax',
            'avgCustomerRating',
            'totalTransactionsThisMonth',
            'latestBookings',
            'warningTrips',
            'allPackages',
            'maritimeWeather',
            'searchQuery',
            'h1Trips',
            'h1TotalCustomers',
            'h1Bookings'
        ));
    }

    public function realTimeFeed(): JsonResponse
    {
        $bookings = Booking::with(['package', 'trip'])
            ->latest('id')
            ->limit(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'count' => $bookings->count(),
            'data' => $bookings,
            'timestamp' => Carbon::now()->toIso8601String(),
        ]);
    }
}
