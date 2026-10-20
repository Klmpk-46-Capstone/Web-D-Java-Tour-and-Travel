<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminRefundController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $refunds = Refund::with(['booking.package', 'booking.trip', 'reviewer'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest('id')
            ->paginate(10);

        $pendingCount = Refund::where('status', 'pending')->count();
        $approvedCount = Refund::where('status', 'approved')->count();
        $rejectedCount = Refund::where('status', 'rejected')->count();
        $totalDisputed = Refund::where('status', 'pending')->sum('amount');

        return view('admin.refunds.index', compact(
            'refunds',
            'status',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'totalDisputed'
        ));
    }

    public function approve(Request $request, Refund $refund): JsonResponse|RedirectResponse
    {
        $adminNotes = $request->input('admin_notes', 'Disetujui oleh Super Admin Travel. Dana dikembalikan ke rekening asal pemesan.');

        DB::transaction(function () use ($refund, $adminNotes) {
            $refund->update([
                'status' => 'approved',
                'admin_notes' => $adminNotes,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => Carbon::now(),
            ]);

            $booking = $refund->booking;
            if ($booking) {
                $booking->update([
                    'booking_status' => 'cancelled',
                    'payment_status' => 'refunded',
                ]);

                // Free up seats on the trip
                $trip = $booking->trip;
                if ($trip) {
                    $newBooked = max(0, $trip->booked_seats - $booking->pax);
                    $newStatus = 'available';
                    if ($newBooked >= $trip->quota) {
                        $newStatus = 'full';
                    } elseif (($newBooked / max(1, $trip->quota)) >= 0.8) {
                        $newStatus = 'almost_full';
                    }

                    $trip->update([
                        'booked_seats' => $newBooked,
                        'status' => $newStatus,
                    ]);
                }

                // Update related transaction
                $booking->transactions()->update([
                    'status' => 'refunded',
                ]);
            }
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Pengajuan refund #{$refund->refund_code} berhasil disetujui. Kuota kursi trip telah dipulihkan dan status transaksi diperbarui.",
            ]);
        }

        return redirect()->back()->with('success', "Refund #{$refund->refund_code} disetujui. Kursi trip otomatis dikembalikan menjadi tersedia.");
    }

    public function reject(Request $request, Refund $refund): JsonResponse|RedirectResponse
    {
        $adminNotes = $request->input('admin_notes', 'Pengajuan refund ditolak sesuai syarat & ketentuan pembatalan kurang dari 48 jam keberangkatan.');

        DB::transaction(function () use ($refund, $adminNotes) {
            $refund->update([
                'status' => 'rejected',
                'admin_notes' => $adminNotes,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => Carbon::now(),
            ]);

            $booking = $refund->booking;
            if ($booking) {
                $booking->update([
                    'booking_status' => 'confirmed',
                ]);
            }
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Pengajuan refund #{$refund->refund_code} telah ditolak. Status booking dikembalikan ke terkonfirmasi.",
            ]);
        }

        return redirect()->back()->with('warning', "Refund #{$refund->refund_code} ditolak. Status reservasi tetap aktif.");
    }
}
