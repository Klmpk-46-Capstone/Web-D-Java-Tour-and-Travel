<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $customers = User::withCount('bookings')
            ->where('role', 'customer')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('whatsapp', 'like', "%{$search}%");
            })
            ->latest('id')
            ->paginate(12);

        $totalCustomers = User::where('role', 'customer')->count();

        return view('admin.customers.index', compact('customers', 'search', 'totalCustomers'));
    }
}
