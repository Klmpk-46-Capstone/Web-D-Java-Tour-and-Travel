<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminBookingController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $bookings = Booking::with(['package', 'trip', 'user', 'transactions'])
            ->when($status, fn($q) => $q->where('booking_status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            })
            ->latest('id')
            ->paginate(15);

        return view('admin.bookings.index', compact('bookings', 'status', 'search'));
    }

    public function show(Booking $booking): View
    {
        $booking->load(['package', 'trip', 'user', 'transactions', 'refunds']);
        return view('admin.bookings.show', compact('booking'));
    }
}
