<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Package;
use App\Models\Refund;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        // 1. Metric Summaries
        $totalGrossRevenue = 428850000; // Mockup metric baseline (or dynamic calculation)
        $q1TargetPercentage = 82; // 82% tercapai target Q1
        
        $totalBookingsPax = 1248; // 1.248 Pax
        $weekendPax = 96; // 96 Pax trip akhir pekan ini
        $onScheduleRate = 98.4; // 98.4% On-Schedule

        // Top 1 Best Selling Package
        $topPackage = Package::where('slug', 'karimunjawa-paradise-3d2n')->first() 
            ?? Package::orderByDesc('rating')->first();
        $topPackagePax = 342;
        $topPackageRevenue = 119700000;

        // Refund & Issues Review
        $pendingRefundsCount = Refund::where('status', 'pending')->count();
        $disputedRefundNominal = Refund::where('status', 'pending')->sum('amount');
        if ($disputedRefundNominal == 0) {
            $disputedRefundNominal = 3850000; // Mockup baseline
            $pendingRefundsCount = max(3, $pendingRefundsCount);
        }

        // 2. Real-time Bookings Feed (Latest 5 or filtered)
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

        // 3. Trips Quota Warning (Peringatan Kuota Penuh)
        $warningTrips = Trip::with('package')
            ->orderByRaw('(booked_seats / quota) DESC')
            ->limit(3)
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
            'q1TargetPercentage',
            'totalBookingsPax',
            'weekendPax',
            'onScheduleRate',
            'topPackage',
            'topPackagePax',
            'topPackageRevenue',
            'pendingRefundsCount',
            'disputedRefundNominal',
            'latestBookings',
            'warningTrips',
            'allPackages',
            'maritimeWeather',
            'searchQuery'
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
